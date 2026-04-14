<?php

namespace App\Filament\Resources\LokasiResource\Pages;

use App\Filament\Resources\LokasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLokasis extends ManageRecords
{
    protected static string $resource = LokasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Tambah Lokasi')
                ->Icon('heroicon-o-plus'),
        ];
    }
}
