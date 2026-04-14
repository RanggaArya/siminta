<?php

namespace App\Filament\Resources\PenarikanAlatResource\Pages;

use App\Filament\Resources\PenarikanAlatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenarikanAlats extends ListRecords
{
    protected static string $resource = PenarikanAlatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Catat Penarikan Alat Baru'),
            Actions\Action::make('resume')
                ->label('Resume')
                ->icon('heroicon-o-document-text')
                ->url(PenarikanAlatResource::getUrl('resume'))
                ->openUrlInNewTab(false),
        ];
    }
}
