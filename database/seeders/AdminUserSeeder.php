<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('eden.admin.email');
        $name = config('eden.admin.name');
        $password = config('eden.admin.password');

        if (! filled($email) || ! filled($name) || ! filled($password)) {
            throw new \RuntimeException('Définissez EDEN_ADMIN_EMAIL, EDEN_ADMIN_NAME et EDEN_ADMIN_PASSWORD dans le fichier .env avant de lancer ce seeder.');
        }

        $admin = User::firstOrNew(['email' => $email]);

        if (! $admin->exists) {
            $admin->fill([
                'name' => $name,
                'password' => $password,
            ]);
            $admin->forceFill(['email_verified_at' => now()]);
        }

        // Promote the configured bootstrap account without resetting its password
        // whenever seeders are run again.
        $admin->forceFill(['is_admin' => true, 'role' => 'superadmin'])->save();
    }
}
