<?php

namespace App\Filament\Resources\PeminjamanResource\Pages;

use App\Filament\Resources\PeminjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Peminjaman;
use Illuminate\Database\Eloquent\Builder;
use Carbon\carbon;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ListPeminjaman extends ListRecords
{
    protected static string $resource = PeminjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Peminjaman'),
            Actions\Action::make('resume')
                ->label('Resume')
                ->icon('heroicon-o-document-text')
                ->url(PeminjamanResource::getUrl('resume'))
                ->openUrlInNewTab(false)
                ->visible(function (): bool {
                    $user = Auth::user();
                    if (!$user instanceof User) {
                        return false;
                    }
                    return $user->isAdminOrSuper();
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        Peminjaman::query()
            ->where('status', 'Dipinjam')
            ->whereNotNull('tanggal_selesai')
            ->where('tanggal_selesai', '<', Carbon::today())
            ->update(['status' => 'Terlambat']);

        return parent::getTableQuery();
    }
}
