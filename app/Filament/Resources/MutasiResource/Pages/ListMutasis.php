<?php

namespace App\Filament\Resources\MutasiResource\Pages;

use App\Filament\Resources\MutasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\User as AppUser;
use Illuminate\Support\Facades\Auth;

class ListMutasis extends ListRecords
{
    protected static string $resource = MutasiResource::class;

    protected function getHeaderActions(): array
    {
        $auth = Auth::user();
        $canManage = $auth instanceof AppUser && $auth->isAdminOrSuper();

        return [
            Actions\CreateAction::make()
                ->visible(fn () => $canManage)
                ->label('Catat Mutasi Baru'),
        ];
    }
}