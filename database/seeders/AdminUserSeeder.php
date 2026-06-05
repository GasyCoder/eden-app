<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('EDEN_ADMIN_EMAIL', 'admin@eden.test')],
            [
                'name' => env('EDEN_ADMIN_NAME', 'Administrateur EDEN'),
                'password' => env('EDEN_ADMIN_PASSWORD', 'password'),
                'is_admin' => true,
                'role' => 'superadmin',
            ],
        );

        User::updateOrCreate(
            ['email' => env('EDEN_EDITOR_EMAIL', 'editor@eden.test')],
            [
                'name' => env('EDEN_EDITOR_NAME', 'Éditeur EDEN'),
                'password' => env('EDEN_EDITOR_PASSWORD', 'password'),
                'is_admin' => true,
                'role' => 'editor',
            ],
        );
    }
}
