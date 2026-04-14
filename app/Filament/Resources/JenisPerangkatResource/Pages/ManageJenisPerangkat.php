<?php

namespace App\Filament\Resources\JenisPerangkatResource\Pages;

use App\Filament\Resources\JenisPerangkatResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisPerangkat extends ManageRecords
{
    protected static string $resource = JenisPerangkatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Tambah Jenis Perangkat')
                ->Icon('heroicon-o-plus'),
        ];
    }
}
