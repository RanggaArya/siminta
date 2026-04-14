<?php

namespace App\Filament\Resources\PenarikanAlatResource\Pages;

use App\Filament\Resources\PenarikanAlatResource;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;
use App\Models\PenarikanAlat;
use Illuminate\Support\Facades\DB;

class ResumePenarikanAlats extends Page
{
    protected static string $resource = PenarikanAlatResource::class;
    protected static string $view = 'filament.penarikan.resume';
    protected static ?string $title = 'Resume Penarikan';
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

        if ($year && $monthFrom && $monthTo) {
            if ($monthFrom > $monthTo) {
                [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
            }

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
        $q = PenarikanAlat::query()
            ->leftJoin('lokasis', 'penarikan_alats.lokasi_id', '=', 'lokasis.id')
            ->leftJoin('users', 'penarikan_alats.user_id', '=', 'users.id');

        if ($year && $year !== 'all') {
            $yearInt = (int) $year;
            $q->whereYear('penarikan_alats.tanggal_penarikan', $yearInt);

            if ($monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
                $q->whereMonth('penarikan_alats.tanggal_penarikan', $monthFrom);
            }

            if ($monthFrom && $monthTo && $monthTo > $monthFrom) {
                $q->whereMonth('penarikan_alats.tanggal_penarikan', '>=', $monthFrom)
                  ->whereMonth('penarikan_alats.tanggal_penarikan', '<=', $monthTo);
            }
        }

        return $q->select(
                'penarikan_alats.nomor_inventaris',
                'penarikan_alats.nama_perangkat',
                'penarikan_alats.tipe',
                'lokasis.nama_lokasi as lokasi_snapshot',
                'penarikan_alats.tanggal_penarikan',
                'penarikan_alats.alasan_penarikan',
                'penarikan_alats.alasan_lainnya',
                'penarikan_alats.tindak_lanjut_tipe',
                'penarikan_alats.tindak_lanjut_detail',
                'users.name as dicatat_oleh',
            )
            ->orderBy('penarikan_alats.tanggal_penarikan')
            ->orderBy('penarikan_alats.id');
    }

    public function reloadData(): void
    {
        $items = $this->buildQuery($this->year, $this->monthFrom, $this->monthTo)->get();

        $this->rows = $items->map(fn ($r) => [
            'nomor_inventaris' => $r->nomor_inventaris,
            'nama_perangkat'   => $r->nama_perangkat,
            'tipe'             => $r->tipe,
            'lokasi'           => $r->lokasi_snapshot,
            'tanggal'          => $r->tanggal_penarikan,
            'alasan'           => $r->alasan_penarikan,
            'alasan_lain'      => $r->alasan_lainnya,
            'tindak_lanjut'    => $r->tindak_lanjut_tipe,
            'tindak_detail'    => $r->tindak_lanjut_detail,
            'dicatat_oleh'     => $r->dicatat_oleh,
        ])->toArray();

        $this->periodeLabel = $this->makePeriodeLabel($this->year, $this->monthFrom, $this->monthTo);
    }

    protected function getFormSchema(): array
    {
        $firstYear = PenarikanAlat::whereNotNull('tanggal_penarikan')
            ->orderBy('tanggal_penarikan', 'asc')
            ->value(DB::raw('YEAR(tanggal_penarikan)'));

        $firstYear = $firstYear ? (int) $firstYear : (int) now()->year;

        $yearRange = collect(range($firstYear, (int) now()->year))
            ->mapWithKeys(fn ($y) => [$y => $y])
            ->sortDesc()
            ->toArray();

        $years = ['all' => 'Semua Tahun'] + $yearRange;

        $months = collect(range(1, 12))->mapWithKeys(
            fn ($m) => [$m => \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F')]
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
                        if ($this->year === 'all') {
                            $this->year = (int) now()->year;
                        }

                        if ($this->monthFrom && $this->monthTo && $this->monthFrom > $this->monthTo) {
                            [$this->monthFrom, $this->monthTo] = [$this->monthTo, $this->monthFrom];
                        }
                        $this->reloadData();
                    }),

                Forms\Components\Select::make('monthTo')
                    ->label('Bulan sampai')
                    ->options($months)
                    ->reactive()
                    ->afterStateUpdated(function () {
                        if ($this->year === 'all') {
                            $this->year = (int) now()->year;
                        }

                        if ($this->monthFrom && $this->monthTo && $this->monthTo < $this->monthFrom) {
                            [$this->monthFrom, $this->monthTo] = [$this->monthTo, $this->monthFrom];
                        }
                        $this->reloadData();
                    }),

                Forms\Components\Placeholder::make('periode')
                    ->label('Periode')
                    ->content(fn () => $this->periodeLabel),
            ]),
            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('pdf')
                    ->label('Export PDF')
                    ->url(fn () => route('penarikan.resume.pdf', [
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
            'form' => \Filament\Forms\Form::make($this)
                ->schema($this->getFormSchema())
                ->columns(4),
        ];
    }
}
