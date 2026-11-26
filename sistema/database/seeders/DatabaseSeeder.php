<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(
            ['name' => 'Administrador'],
            ['description' => 'Acceso total al sistema']
        );

        User::firstOrCreate(
            ['email' => 'admin@sanjuan.com'],
            [
                'role_id' => $admin->id,
                'name' => 'Administrador',
                'dni' => '12345678',
                'password' => 'admin12345', // el cast 'hashed' del modelo la encripta
            ]
        );
    }
}
