<?php

namespace Database\Seeders;

use App\Enums\AuthorizationLevel;
use App\Enums\UserRole;
use App\Models\Cases;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::create([
            'name' => 'Admin',
            'email' => 'admin@cityeye.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        User::factory(100)->create();

        Cases::factory(50)->create();

        $this->call([
            ReportSeeder::class,
        ]);
    }
}
