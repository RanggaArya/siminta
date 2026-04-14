<?php

namespace App\Filament\Resources\RiwayatMaintenanceResource\Pages;

use App\Filament\Resources\RiwayatMaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiwayatMaintenance extends EditRecord
{
    protected static string $resource = RiwayatMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $record = $this->record;

        if (! empty($record->status_akhir) && $record->perangkat && $record->perangkat->isFillable('status_operasional')) {
            $record->perangkat->update(['status_operasional' => $record->status_akhir]);
        }
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
