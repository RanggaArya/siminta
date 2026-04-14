<?php

namespace App\Filament\Resources\PengajuanMaintenances\Schemas;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class PengajuanMaintenanceInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Pengajuan')
                    ->schema([
                        TextEntry::make('perangkats.nomor_inventaris')
                            ->label('Nomor Inventaris'),
                        TextEntry::make('perangkats.nama_perangkat')
                            ->label('Nama Perangkat'),
                        TextEntry::make('lokasi.nama_lokasi')
                            ->label('Lokasi'),
                        TextEntry::make('user.name')
                            ->label('Diajukan Oleh'),
                        TextEntry::make('created_at')
                            ->label('Tanggal Pengajuan')
                            ->dateTime(),
                    ])->columns(2),
                
                Section::make('Detail')
                    ->schema([
                        TextEntry::make('keterangan')
                            ->label('Keterangan Keluhan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}