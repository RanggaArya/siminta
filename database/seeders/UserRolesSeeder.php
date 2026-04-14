<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserRolesSeeder extends Seeder
{
    public function run(): void
    {
        // SUPER ADMIN
        User::firstOrCreate(
            ['email' => 'owner@example.com'],
            ['name' => 'Owner', 'password' => 'password-super', 'role' => 'super-admin']
        );

        User::firstOrCreate(
            ['email' => 'askalaskia19@gmail.com'],
            ['name' => 'Azkal Azkiya', 'password' => '12345678', 'role' => 'super-admin']
        );

        // ADMIN
        User::firstOrCreate(
            ['email' => 'itadmin@example.com'],
            ['name' => 'IT Admin', 'password' => 'password-admin', 'role' => 'admin']
        );

        // USER
        User::firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff', 'password' => 'password-user', 'role' => 'user']
        );
    }
}
