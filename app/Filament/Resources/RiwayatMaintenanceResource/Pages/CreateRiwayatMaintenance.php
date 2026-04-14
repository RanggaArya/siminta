<?php

namespace App\Filament\Resources\RiwayatMaintenanceResource\Pages;

use App\Filament\Resources\RiwayatMaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateRiwayatMaintenance extends CreateRecord
{
    protected static string $resource = RiwayatMaintenanceResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        return $data;
    }

    protected function afterCreate(): void
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
