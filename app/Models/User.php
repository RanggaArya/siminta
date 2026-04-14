<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan',
        'unit',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'permissions'       => 'array',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super-admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isTeknisi(): bool{
        return $this->role === 'teknisi';
    }

    public function isAdminOrSuper(): bool
    {
        return in_array($this->role, ['admin', 'super-admin'], true);
    }

    public static function defaultRolePermissions(): array
    {
        return [
            'super-admin' => [
                'dashboard.view',

                'user.manage',

                'resume.manage',

                'peminjaman.manage',

                'perangkat.manage',
                'perangkat.import',
                'perangkat.mutasi',
                'perangkat.jenis.manage',
                'perangkat.status.manage',
                'perangkat.kondisi.manage',
                'perangkat.lokasi.manage',
                'perangkat.kategori.manage',

                'maintenance.manage',

                'penarikan.manage',
            ],

            'admin' => [
                'dashboard.view',

                'resume.manage',
                'peminjaman.manage',
            ],

            'user' => [
                'dashboard.view',

                'peminjaman.view',
                'peminjaman.create',
            ],

            'teknisi' => [
                'dashboard.view',
                'maintenance.manage',
            ],
        ];
    }

    public function canDo(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
        return true;
        }

        $perms = [];

        if (is_array($this->permissions) && !empty($this->permissions)) {
        $perms = $this->permissions;
        } else {
        $defaults = self::defaultRolePermissions();
        $perms = $defaults[$this->role] ?? [];
        }

        return in_array($permission, $perms, true);
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $name = strtolower($user->name);
            $email = strtolower($user->email);

            if (str_contains($name, 'admin') || str_contains($email, 'admin')) {
                $user->role = 'admin';
            }
        });
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\FilamentResetPassword($token));
    }
}
