<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSuperSeeder extends Seeder 
{
    public function run(): void
    {
        // SUPER ADMIN
        User::firstOrCreate(
            ['email' => 'super@rs.com'], 
            ['name' => 'Super Admin', 'password' => '123456', 'role' => 'super-admin'] 
        ); 
    }
}
