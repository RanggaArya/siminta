<?php

namespace App\Filament\Resources\PengajuanMaintenances\Pages;

use App\Filament\Resources\PengajuanMaintenances\PengajuanMaintenanceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanMaintenance extends EditRecord
{
    protected static string $resource = PengajuanMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
