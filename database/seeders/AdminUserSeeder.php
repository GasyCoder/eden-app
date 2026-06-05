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
            ],
        );
    }
}
