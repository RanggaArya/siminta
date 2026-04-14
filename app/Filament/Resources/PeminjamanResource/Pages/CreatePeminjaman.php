<?php

namespace App\Filament\Resources\PeminjamanResource\Pages;

use App\Filament\Resources\PeminjamanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PeminjamanRequested;
use App\Models\User;

class CreatePeminjaman extends CreateRecord
{
    protected static string $resource = PeminjamanResource::class;
    public static function canAccess(array $parameters = []): bool
    {
        return Auth::check()
            && ! in_array(Auth::user()?->role, ['admin', 'super-admin'], true);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $u = Auth::user();

        $data['requested_by_user_id'] = $data['requested_by_user_id'] ?? $u?->id;
        $data['pihak_kedua_nama']     = $data['pihak_kedua_nama']     ?? $u?->name;
        $data['peminjam_email']       = $data['peminjam_email']       ?? $u?->email;

        $data['status'] = $data['status'] ?? 'Menunggu';

        return $data;
    }
    protected function afterCreate(): void
    {
        $admins = User::query()
            ->whereIn('role', ['admin', 'super-admin'])
            ->get();

        Notification::send($admins, new PeminjamanRequested($this->record));
    }
}
