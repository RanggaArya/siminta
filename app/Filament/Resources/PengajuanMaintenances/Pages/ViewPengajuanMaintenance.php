<?php

namespace App\Filament\Resources\PengajuanMaintenances\Pages;

use App\Filament\Resources\PengajuanMaintenances\PengajuanMaintenanceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPengajuanMaintenance extends ViewRecord
{
    protected static string $resource = PengajuanMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
