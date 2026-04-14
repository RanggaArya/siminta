<?php

namespace App\Filament\Resources\PengajuanMaintenances\Pages;

use App\Filament\Resources\PengajuanMaintenances\PengajuanMaintenanceResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PengajuanMaintenanceNotification; // Pastikan notifikasi ini ada
use Illuminate\Support\Facades\Auth;

class CreatePengajuanMaintenance extends CreateRecord
{
    protected static string $resource = PengajuanMaintenanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function afterCreate(): void
    {
        // Mengirim notifikasi ke super-admin
        // $admins = User::query()
        //     ->where('role', 'super-admin') // Sesuaikan jika kolom role di database berbeda
        //     ->limit(1)
        //     ->get();

        // // Pastikan class PengajuanMaintenanceNotification sudah dibuat
        // if (class_exists(PengajuanMaintenanceNotification::class)) {
        //     Notification::send($admins, new PengajuanMaintenanceNotification($this->record));
        // }

      $chatId = config('services.telegram_default_chat_id');
        
        if ($chatId) {
            Notification::route('telegram', $chatId)
                ->notify(new PengajuanMaintenanceNotification($this->record));
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}