<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('SUPER_ADMIN_PASSWORD');
        info('Seeding super admin user with email: superadmin@gmail.com');
        info('Super admin password: ' . $password);

        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'superadmin',
                'password' => $password,
                'role' => 'super_admin',
            ]
        );
    }
}
