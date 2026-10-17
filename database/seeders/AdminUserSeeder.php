<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('EDEN_ADMIN_EMAIL', 'bezaraflorent@gmail.com');
        $admin = User::firstOrNew(['email' => $email]);

        if (! $admin->exists) {
            $admin->fill([
                'name' => env('EDEN_ADMIN_NAME', 'Administrateur EDEN'),
                'password' => env('EDEN_ADMIN_PASSWORD', 'Dev@@2021!!'),
            ]);
            $admin->forceFill(['email_verified_at' => now()]);
        }

        // Promote the configured bootstrap account without resetting its password
        // whenever seeders are run again.
        $admin->forceFill(['is_admin' => true, 'role' => 'superadmin'])->save();
    }
}
