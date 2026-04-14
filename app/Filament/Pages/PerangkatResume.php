<?php

namespace App\Filament\Pages;

use App\Models\Perangkat;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\User as AppUser;

class PerangkatResume extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Resume Perangkat';
    protected static ?int $navigationSort     = 10;

    protected static string $view = 'filament.pages.perangkat-resume';

    public int|string|null $year = null;
    public ?int $monthFrom = null;
    public ?int $monthTo = null;

    public array $grandTotal = [];
    public $rows;

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function mount(): void
    {
        $now = Carbon::now();

        $this->year      = $this->year      ?? (int) $now->year;
        $this->monthFrom = $this->monthFrom ?? (int) $now->month;
        $this->monthTo   = $this->monthTo   ?? (int) $now->month;

        if ($this->monthFrom && $this->monthTo && $this->monthFrom > $this->monthTo) {
            [$this->monthFrom, $this->monthTo] = [$this->monthTo, $this->monthFrom];
        }

        $this->loadData();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        $months = collect(range(1, 12))->mapWithKeys(
            fn ($m) => [$m => Carbon::create()->month($m)->locale('id')->translatedFormat('F')]
        );

        $firstYear = Perangkat::whereNotNull('created_at')
            ->orderBy('created_at', 'asc')
            ->value(DB::raw('YEAR(created_at)'));

        $firstYear = $firstYear ? (int) $firstYear : (int) now()->year;

        $yearRange = collect(range($firstYear, (int) now()->year))
            ->mapWithKeys(fn ($y) => [$y => $y])
            ->sortDesc()
            ->toArray();

        $years = ['all' => 'Semua Tahun'] + $yearRange;

        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('year')
                    ->label('Tahun')
                    ->options($years)
                    ->reactive()
                    ->afterStateUpdated(function () {
                        if ($this->year === 'all') {
                            $this->monthFrom = null;
                            $this->monthTo   = null;
                        }
                        $this->loadData();
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
                        $this->loadData();
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
                        $this->loadData();
                    }),
            ]),
        ]);
    }

    public function loadData(): void
    {
        $dateField = 'created_at';

        $monthFrom = $this->monthFrom;
        $monthTo   = $this->monthTo;

        if ($monthFrom && $monthTo && $monthFrom > $monthTo) {
            [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
        }

        $query = Perangkat::query()
            ->leftJoin('statuses', 'perangkats.status_id', '=', 'statuses.id');

        if ($this->year && $this->year !== 'all') {
            $yearInt = (int) $this->year;

            $query->whereYear("perangkats.$dateField", $yearInt);

            if ($monthFrom && (!$monthTo || $monthTo == $monthFrom)) {
                $query->whereMonth("perangkats.$dateField", $monthFrom);
            }

            if ($monthFrom && $monthTo && $monthTo > $monthFrom) {
                $query->whereMonth("perangkats.$dateField", '>=', $monthFrom)
                      ->whereMonth("perangkats.$dateField", '<=', $monthTo);
            }
        }

        $this->rows = $query->select(
                'perangkats.nama_perangkat',
                DB::raw("COUNT(CASE WHEN statuses.nama_status = 'Digunakan' THEN 1 END) AS aktif_count"),
                DB::raw("SUM(CASE WHEN statuses.nama_status = 'Digunakan' THEN perangkats.harga ELSE 0 END) AS aktif_sum"),
                DB::raw("COUNT(CASE WHEN statuses.nama_status = 'Dalam Perbaikan' THEN 1 END) AS rusak_count"),
                DB::raw("SUM(CASE WHEN statuses.nama_status = 'Dalam Perbaikan' THEN perangkats.harga ELSE 0 END) AS rusak_sum"),
                DB::raw("COUNT(CASE WHEN statuses.nama_status = 'Tidak Digunakan' THEN 1 END) AS tidak_digunakan_count"),
                DB::raw("SUM(CASE WHEN statuses.nama_status = 'Tidak Digunakan' THEN perangkats.harga ELSE 0 END) AS tidak_digunakan_sum"),
                DB::raw("COUNT(perangkats.id) AS total_count"),
                DB::raw("SUM(perangkats.harga) AS total_sum")
            )
            ->groupBy('perangkats.nama_perangkat')
            ->orderBy('perangkats.nama_perangkat')
            ->get();

        $this->grandTotal = [
            'aktif_count'           => (int) $this->rows->sum('aktif_count'),
            'aktif_sum'             => (float) $this->rows->sum('aktif_sum'),
            'rusak_count'           => (int) $this->rows->sum('rusak_count'),
            'rusak_sum'             => (float) $this->rows->sum('rusak_sum'),
            'tidak_digunakan_count' => (int) $this->rows->sum('tidak_digunakan_count'),
            'tidak_digunakan_sum'   => (float) $this->rows->sum('tidak_digunakan_sum'),
            'total_count'           => (int) $this->rows->sum('total_count'),
            'total_sum'             => (float) $this->rows->sum('total_sum'),
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

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user instanceof AppUser && $user->canDo('resume.view');
    }
}
