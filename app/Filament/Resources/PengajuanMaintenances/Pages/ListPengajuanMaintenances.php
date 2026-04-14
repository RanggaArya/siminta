<?php

namespace App\Filament\Resources\PengajuanMaintenances\Pages;

use App\Filament\Resources\PengajuanMaintenances\PengajuanMaintenanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanMaintenances extends ListRecords
{
    protected static string $resource = PengajuanMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
