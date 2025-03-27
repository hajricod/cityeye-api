<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@cityeye.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        User::create([
            'name' => 'Investigator',
            'email' => 'investigator@cityeye.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Investigator,
        ]);

        User::create([
            'name' => 'Officer',
            'email' => 'officer@cityeye.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Officer,
        ]);
    }
}
