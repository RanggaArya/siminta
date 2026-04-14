<?php

namespace App\Filament\Resources\PerangkatResource\Pages;

use App\Filament\Resources\PerangkatResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePerangkat extends CreateRecord
{
    protected static string $resource = PerangkatResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (
            empty($data['nomor_inventaris'])
            && !empty($data['jenis_id'])
            && !empty($data['kategori_id'])
            && !empty($data['tahun_pengadaan'])
        ) {
            $data['nomor_inventaris'] = \App\Support\NomorInventarisGenerator::generate(
                (int) $data['jenis_id'],
                (int) $data['kategori_id'],
                (int) $data['tahun_pengadaan'],
            );
        }
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
