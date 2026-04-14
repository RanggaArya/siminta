<?php

namespace App\Filament\Resources\KondisiResource\Pages;

use App\Filament\Resources\KondisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKondisis extends ManageRecords
{
    protected static string $resource = KondisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Tambah Kondisi')
                ->Icon('heroicon-o-plus'),
        ];
    }
}
