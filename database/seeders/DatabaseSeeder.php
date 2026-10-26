<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $email = env('ADMIN_EMAIL', 'admin@example.com');

        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(16);

        $admin = User::create([
            'name' => env('ADMIN_NAME', 'Super Admin'),
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        $admin->assignRole(User::SUPER_ADMIN_ROLE);

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn("Created {$email} with generated password: {$password}");
            $this->command?->warn('Store it safely and change it after first login.');
        }
    }
}
