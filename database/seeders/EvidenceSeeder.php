<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Cases;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Database\Seeder;

class EvidenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('role', [UserRole::Admin, UserRole::Investigator, UserRole::Officer])->get();

        $cases = Cases::all();

        foreach ($cases as $case) {
            Evidence::factory(rand(1, 5))->create([
                'case_id' => $case->id,
                'uploaded_by' => $users->random()->id,
            ]);
        }
    }
}
