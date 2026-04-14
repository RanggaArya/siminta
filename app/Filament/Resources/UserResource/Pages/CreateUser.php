<?php

namespace App\Filament\Resources\UserResource\Pages;


use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User as AppUser;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $auth = Auth::user();

        if (! ($auth instanceof AppUser)) {
            return $data;
        }

        if ($auth->isAdmin() && ! $auth->isSuperAdmin()) {
            $data['role'] = 'user';
        }

        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
