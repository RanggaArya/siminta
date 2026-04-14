<?php

namespace App\Filament\Resources\PenarikanAlatResource\Pages;

use App\Filament\Resources\PenarikanAlatResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Status;

class CreatePenarikanAlat extends CreateRecord
{
    protected static string $resource = PenarikanAlatResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $perangkat = $record->perangkat;
        if (!$perangkat) return; 

        $alasan = $record->alasan_penarikan ?? []; 
        $newStatusId = null;

        if (in_array('Tidak Layak Pakai', $alasan) || in_array('Melebihi Masa Pakai', $alasan)) {
            $newStatusId = Status::where('nama_status', 'Sudah tidak digunakan')->value('id');
        } 
        elseif (in_array('Rusak', $alasan)) {
            $newStatusId = Status::where('nama_status', 'Rusak')->value('id');
        }
        if ($newStatusId) {
            $perangkat->update(['status_id' => $newStatusId]);
        }
    }

    protected function getRedirectUrl(): string
    {
        $record = $this->record;

        if ($record->tindak_lanjut_tipe === 'Pindahan') {
            
            \Filament\Notifications\Notification::make()
                ->title('Penarikan Alat Berhasil Disimpan')
                ->body('Sekarang, silakan catat mutasi untuk perangkat pengganti.')
                ->success()
                ->send();

            return \App\Filament\Resources\MutasiResource::getUrl('create');
        }

        return $this->getResource()::getUrl('index');
    }
}