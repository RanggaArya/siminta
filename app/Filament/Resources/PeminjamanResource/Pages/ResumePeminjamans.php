<?php

namespace App\Filament\Resources\PeminjamanResource\Pages;

use App\Filament\Resources\PeminjamanResource;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ResumePeminjamans extends Page
{
    protected static string $resource = PeminjamanResource::class;
    protected static string $view = 'filament.peminjaman.resume';
    protected static ?string $title = 'Resume Peminjaman';
    protected static ?string $navigationLabel = 'Resume';
    protected static ?string $slug = 'resume';

    public int|string|null $year = null;
    public ?int $monthFrom = null;
    public ?int $monthTo = null;


    public array $rows = [];
    public string $periodeLabel = '';

    public function mount(): void
    {
        /** @var AppUser|null $auth */
        $auth = Auth::user();
        abort_unless($auth && $auth->isAdminOrSuper(), 403);

        $this->year      = (int) now()->year;
        $this->monthFrom = (int) now()->month;
        $this->monthTo   = (int) now()->month;

        $this->reloadData();
    }


    protected function makePeriodeLabel($year, ?int $monthFrom, ?int $monthTo): string
    {
        if ($year === 'all' || (!$year && !$monthFrom && !$monthTo)) {
            return 'SEMUA TAHUN';
        }

        $year = $year ? (int) $year : null;

        if (!$year && ($monthFrom || $monthTo)) {
            $year = (int) now()->year;
        }

        if ($year && !$monthFrom && !$monthTo) {
            return (string) $year;
        }

        if ($year && $monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
            $single = \Illuminate\Support\Carbon::create($year, $monthFrom, 1)
                ->locale('id')->translatedFormat('F Y');

            return mb_strtoupper($single);
        }

        if ($year && $monthFrom && $monthTo && $monthTo > $monthFrom) {
            $start = \Illuminate\Support\Carbon::create($year, $monthFrom, 1)
                ->locale('id')->translatedFormat('F');
            $end = \Illuminate\Support\Carbon::create($year, $monthTo, 1)
                ->locale('id')->translatedFormat('F Y');

            return mb_strtoupper($start . '–' . $end);
        }

        return (string) ($year ?? 'SEMUA TAHUN');
    }

    protected function buildQuery($year, ?int $monthFrom, ?int $monthTo)
    {
        $q = Peminjaman::query()
            ->leftJoin('users as requester', 'peminjamans.requested_by_user_id', '=', 'requester.id')
            ->leftJoin('users as approver', 'peminjamans.approved_by_user_id', '=', 'approver.id');

        if ($year && $year !== 'all') {
            $yearInt = (int) $year;
            $q->whereYear('peminjamans.tanggal_mulai', $yearInt);

            if ($monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
                $q->whereMonth('peminjamans.tanggal_mulai', $monthFrom);
            }

            if ($monthFrom && $monthTo && $monthTo > $monthFrom) {
                $q->whereMonth('peminjamans.tanggal_mulai', '>=', $monthFrom)
                    ->whereMonth('peminjamans.tanggal_mulai', '<=', $monthTo);
            }
        }

        return $q->select(
            'peminjamans.id',
            'peminjamans.nomor_inventaris',
            'peminjamans.nama_barang',
            'peminjamans.merk',
            'peminjamans.kondisi_terakhir',
            'peminjamans.pihak_kedua_nama',
            'peminjamans.peminjam_email',
            'peminjamans.tanggal_mulai',
            'peminjamans.tanggal_selesai',
            'peminjamans.status',
            'peminjamans.alasan_pinjam',
            'approver.name as pihak_pertama_nama'
        )
            ->orderBy('peminjamans.tanggal_mulai', 'asc')
            ->orderBy('peminjamans.id', 'asc');
    }




    public function reloadData(): void
    {
        $items = $this->buildQuery($this->year, $this->monthFrom, $this->monthTo)->get();

        $this->rows = $items->map(fn($r) => [
            'nomor_inventaris' => $r->nomor_inventaris,
            'nama_barang'      => $r->nama_barang,
            'merk'             => $r->merk,
            'kondisi'          => $r->kondisi_terakhir,
            'peminjam'         => $r->pihak_kedua_nama,
            'email'            => $r->peminjam_email,
            'tanggal_mulai'    => $r->tanggal_mulai,
            'tanggal_selesai'  => $r->tanggal_selesai,
            'status'           => $r->status,
            'alasan'           => $r->alasan_pinjam,
            'dicatat_oleh'     => $r->pihak_pertama_nama,
        ])->toArray();

        $this->periodeLabel = $this->makePeriodeLabel($this->year, $this->monthFrom, $this->monthTo);
    }

    protected function getFormSchema(): array
    {
        $firstYear = Peminjaman::whereNotNull('tanggal_mulai')
            ->orderBy('tanggal_mulai', 'asc')
            ->value(DB::raw('YEAR(tanggal_mulai)'));

        $firstYear = $firstYear ? (int) $firstYear : (int) now()->year;

        $yearRange = collect(range($firstYear, (int) now()->year))
            ->mapWithKeys(fn($y) => [$y => $y])
            ->sortDesc()
            ->toArray();

        $years = ['all' => 'Semua Tahun'] + $yearRange;

        $months = collect(range(1, 12))->mapWithKeys(
            fn($m) => [$m => \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F')]
        );

        return [
            Forms\Components\Grid::make(4)->schema([
                Forms\Components\Select::make('year')
                    ->label('Tahun')
                    ->options($years)
                    ->reactive()
                    ->afterStateUpdated(function () {
                        if ($this->year === 'all') {
                            $this->monthFrom = null;
                            $this->monthTo   = null;
                        }
                        $this->reloadData();
                    }),

                Forms\Components\Select::make('monthFrom')
                    ->label('Bulan dari')
                    ->options($months)
                    ->reactive()
                    ->afterStateUpdated(function () {
                        if ($this->monthTo && $this->monthFrom > $this->monthTo) {
                            $temp = $this->monthFrom;
                            $this->monthFrom = $this->monthTo;
                            $this->monthTo = $temp;
                        }

                        if ($this->year === 'all') {
                            $this->year = (int) now()->year;
                        }

                        $this->reloadData();
                    }),


                Forms\Components\Select::make('monthTo')
                    ->label('Bulan sampai')
                    ->options($months)
                    ->reactive()
                    ->afterStateUpdated(function () {
                        if ($this->monthFrom && $this->monthTo && $this->monthTo < $this->monthFrom) {
                            $temp = $this->monthTo;
                            $this->monthTo = $this->monthFrom;
                            $this->monthFrom = $temp;
                        }

                        if ($this->year === 'all') {
                            $this->year = (int) now()->year;
                        }

                        $this->reloadData();
                    }),


                Forms\Components\Placeholder::make('periode')
                    ->label('Periode')
                    ->content(fn() => $this->periodeLabel),
            ]),
            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('pdf')
                    ->label('Export PDF')
                    ->url(fn() => route('peminjaman.resume.pdf', [
                        'year'       => $this->year,
                        'month_from' => $this->monthFrom,
                        'month_to'   => $this->monthTo,
                    ]))
                    ->openUrlInNewTab(),
            ])->alignLeft(),
        ];
    }

    protected function getForms(): array
    {
        return [
            'form' => Forms\Form::make($this)
                ->schema($this->getFormSchema())
                ->columns(4),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('resume.view');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('resume.view');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('resume.view');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('resume.view');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('resume.view');
    }

    
}
