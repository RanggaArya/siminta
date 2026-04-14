<?php

namespace App\Filament\Resources\MutasiResource\Pages;

use App\Filament\Resources\MutasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMutasi extends EditRecord
{
    protected static string $resource = MutasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record->perangkat && $record->lokasi_mutasi_id) {

            $record->perangkat->update(['lokasi_id' => $record->lokasi_mutasi_id]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}