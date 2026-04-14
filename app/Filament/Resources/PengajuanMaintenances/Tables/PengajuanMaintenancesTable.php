<?php

namespace App\Filament\Resources\PengajuanMaintenances\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;

class PengajuanMaintenancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('perangkats.nomor_inventaris')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor Inventaris'),

                TextColumn::make('perangkats.nama_perangkat')
                    ->searchable()
                    ->label('Perangkat'),

                TextColumn::make('lokasi.nama_lokasi')
                    ->label('Ruangan')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Ditambahkan Oleh')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                // Tambahkan filter jika perlu
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}