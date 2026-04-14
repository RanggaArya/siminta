<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\RiwayatMaintenance;
use Illuminate\Support\Facades\Auth;


class RecentMaintenances extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = '12';
    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        $role = Auth::user()?->role;

        return !in_array($role, ['user']);
    }
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('perangkat.nomor_inventaris')
                    ->label('No. Inventaris')
                    ->searchable(),

                Tables\Columns\TextColumn::make('perangkat.nama_perangkat')
                    ->label('Perangkat')
                    ->searchable()
                    ->limit(24),

                Tables\Columns\TextColumn::make('lokasi.nama_lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(24),

                Tables\Columns\TextColumn::make('status_akhir')
                    ->label('Status Akhir')
                    ->searchable()
                    ->limit(24)
                    ->formatStateUsing(fn($state) => str_replace('_', ' ', ucfirst($state)))
                    ->color(fn($state) => match ($state) {
                        'berfungsi' => 'success',
                        'berfungsi_sebagian' => 'warning',
                        'tidak_berfungsi' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultPaginationPageOption(10);
    }

    protected function getQuery(): Builder
    {
        return RiwayatMaintenance::query()
            ->select(['id', 'perangkat_id', 'deskripsi', 'lokasi_id', 'status_akhir', 'created_at'])
            ->with(['perangkat:id,nomor_inventaris,nama_perangkat', 'lokasi:id,nama_lokasi'])
            ->latest()
            ->limit(10);
    }
}
