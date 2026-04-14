<?php

namespace App\Filament\Resources\RiwayatMaintenanceResource\Pages;

use App\Filament\Resources\RiwayatMaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\User as AppUser;
use Illuminate\Support\Facades\Auth;

class ListRiwayatMaintenances extends ListRecords
{
    protected static string $resource = RiwayatMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        $auth = Auth::user();
        $canManage = $auth instanceof AppUser && $auth->isAdminOrSuper();

        return [
            Actions\CreateAction::make()->visible(fn() => $canManage)
                ->label('Tambah Maintenance')
                ->Icon('heroicon-o-plus'),
        ];
    }
}
