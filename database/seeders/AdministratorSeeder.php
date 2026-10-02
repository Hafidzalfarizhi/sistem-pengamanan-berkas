<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdministratorSeeder extends Seeder
{
    /**
     * Akun Administrator awal. SEGERA ubah password setelah login pertama
     * (menu Profil > Ubah Password).
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'password' => 'Admin@4567',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ]
        );
    }
}
