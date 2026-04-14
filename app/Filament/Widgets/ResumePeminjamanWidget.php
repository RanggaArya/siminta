<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\Peminjaman;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ResumePeminjamanWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 12;

    public static function canView(): bool
    {
        $role = Auth::user()?->role;

        return !in_array($role, ['admin', 'super-admin']);
    }

    protected function getTableQuery(): Builder
    {
        return Peminjaman::query()
            ->when(
                !in_array(Auth::user()?->role, ['admin', 'super-admin']),
                fn ($q) => $q->where('requested_by_user_id', Auth::id())
            )
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nomor_inventaris')
                ->label('No. Inv')
                ->searchable(),

            Tables\Columns\TextColumn::make('nama_barang')
                ->label('Nama Barang')
                ->searchable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn($state) => $state === 'Menunggu' ? 'Menunggu acc' : $state)
                ->color(fn(string $state): string => match ($state) {
                    'Menunggu'     => 'gray',
                    'Dipinjam'     => 'warning',
                    'Dikembalikan' => 'success',
                    'Terlambat'    => 'danger',
                    'Ditolak'      => 'danger',
                    default        => 'secondary',
                }),

            Tables\Columns\TextColumn::make('tanggal_mulai')
                ->label('Mulai')
                ->date('d/m/Y'),

            Tables\Columns\TextColumn::make('tanggal_selesai')
                ->label('Selesai')
                ->date('d/m/Y'),
        ];
    }
}
