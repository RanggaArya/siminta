<?php

namespace App\Filament\Exports;

use App\Models\Supervisi;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class SupervisiExporter extends Exporter
{
    protected static ?string $model = Supervisi::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.name')
                ->label('User'),
            ExportColumn::make('perangkat.nomor_inventaris')
                ->label('Nomor Inventaris'),
            ExportColumn::make('perangkat.nama_perangkat')
                ->label('Nama Perangkat'),
            ExportColumn::make('tanggal')
                ->label('Tanggal')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d M Y H:i') : '-'),
            ExportColumn::make('keterangan')
                ->label('Keterangan'),
        ];
    }

    // Tambahkan method untuk modify query berdasarkan options
    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with(['user', 'perangkat']);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export supervisi selesai dengan ' . number_format($export->successful_rows) . ' data.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' data gagal di-export.';
        }

        return $body;
    }
}