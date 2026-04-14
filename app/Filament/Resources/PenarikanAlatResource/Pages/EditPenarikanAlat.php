<?php

namespace App\Filament\Resources\PenarikanAlatResource\Pages;

use App\Filament\Resources\PenarikanAlatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Status;

class EditPenarikanAlat extends EditRecord
{
    protected static string $resource = PenarikanAlatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $record = $this->record;
        $perangkat = $record->perangkat;
        if (!$perangkat) return;

        $alasan = $record->alasan_penarikan ?? [];
        $newStatusId = null;

        if (in_array('Tidak Layak Pakai', $alasan) || in_array('Melebihi Masa Pakai', $alasan)) {
            $newStatusId = Status::where('nama_status', 'Sudah tidak digunakan')->value('id');
        } elseif (in_array('Rusak', $alasan)) {
            $newStatusId = Status::where('nama_status', 'Rusak')->value('id');
        }
        if ($newStatusId) {
            $perangkat->update(['status_id' => $newStatusId]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
