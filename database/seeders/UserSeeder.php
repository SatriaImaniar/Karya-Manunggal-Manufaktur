<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed data user: 1 Admin/SPV dan 2 Teknisi.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin SPV',
                'email' => 'admin@pm-system.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'teknisi1@pm-system.com',
                'password' => Hash::make('password'),
                'role' => 'teknisi',
            ],
            [
                'name' => 'Andi Prasetyo',
                'email' => 'teknisi2@pm-system.com',
                'password' => Hash::make('password'),
                'role' => 'teknisi',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
