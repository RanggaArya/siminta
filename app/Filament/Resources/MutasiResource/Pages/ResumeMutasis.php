<?php

namespace App\Filament\Resources\MutasiResource\Pages;

use App\Filament\Resources\MutasiResource;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use App\Models\User as AppUser;
use App\Models\Mutasi;
use Illuminate\Support\Facades\DB;

class ResumeMutasis extends Page
{
    protected static string $resource = MutasiResource::class;
    protected static string $view = 'filament.mutasi.resume';
    protected static ?string $title = 'Resume Mutasi';
    protected static ?string $navigationLabel = 'Resume';
    protected static ?string $slug = 'resume';

    public int|string|null $year = null;
    public ?int $monthFrom = null;
    public ?int $monthTo = null;

    public array $rows = [];
    public array $grand = [];
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
        $q = Mutasi::query()
            ->leftJoin('lokasis as asal', 'mutasis.lokasi_asal_id', '=', 'asal.id')
            ->leftJoin('lokasis as tujuan', 'mutasis.lokasi_mutasi_id', '=', 'tujuan.id')
            ->leftJoin('kondisis', 'mutasis.kondisi_id', '=', 'kondisis.id')
            ->leftJoin('users', 'mutasis.user_id', '=', 'users.id');

        if ($year && $year !== 'all') {
            $yearInt = (int) $year;
            $q->whereYear('mutasis.tanggal_mutasi', $yearInt);

            if ($monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
                $q->whereMonth('mutasis.tanggal_mutasi', $monthFrom);
            }

            if ($monthFrom && $monthTo && $monthTo > $monthFrom) {
                $q->whereMonth('mutasis.tanggal_mutasi', '>=', $monthFrom)
                  ->whereMonth('mutasis.tanggal_mutasi', '<=', $monthTo);
            }
        }

        return $q->select(
                'mutasis.id',
                'mutasis.nomor_inventaris',
                'mutasis.nama_perangkat',
                'mutasis.tipe',
                'kondisis.nama_kondisi as kondisi',
                'asal.nama_lokasi as lokasi_asal',
                'tujuan.nama_lokasi as lokasi_tujuan',
                'mutasis.tanggal_mutasi',
                'mutasis.tanggal_diterima',
                'mutasis.alasan_mutasi',
                'users.name as dicatat_oleh'
            )
            ->orderBy('mutasis.tanggal_mutasi', 'asc')
            ->orderBy('mutasis.id', 'asc');
    }

    public function reloadData(): void
    {
        $items = $this->buildQuery($this->year, $this->monthFrom, $this->monthTo)->get();

        $this->rows = $items->map(fn ($r) => [
            'nomor_inventaris' => $r->nomor_inventaris,
            'nama_perangkat'   => $r->nama_perangkat,
            'tipe'             => $r->tipe,
            'kondisi'          => $r->kondisi,
            'lokasi_asal'      => $r->lokasi_asal,
            'lokasi_tujuan'    => $r->lokasi_tujuan,
            'tanggal_mutasi'   => $r->tanggal_mutasi,
            'tanggal_diterima' => $r->tanggal_diterima,
            'alasan_mutasi'    => $r->alasan_mutasi,
            'dicatat_oleh'     => $r->dicatat_oleh,
        ])->toArray();

        $this->periodeLabel = $this->makePeriodeLabel($this->year, $this->monthFrom, $this->monthTo);
    }

    protected function getFormSchema(): array
    {
        $firstYear = Mutasi::whereNotNull('tanggal_mutasi')
            ->orderBy('tanggal_mutasi', 'asc')
            ->value(DB::raw('YEAR(tanggal_mutasi)'));

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
                    ->url(fn () => route('mutasi.resume.pdf', [
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
}
