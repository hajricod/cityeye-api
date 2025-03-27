<?php

namespace Database\Seeders;

use App\Models\CaseComment;
use App\Models\Cases;
use Illuminate\Database\Seeder;

class CaseCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = Cases::with('assignees')->get();

        foreach ($cases as $case) {
            // For each case, pick 1–5 assignees and create comments
            $commenters = $case->assignees->random(min(3, $case->assignees->count()));

            foreach ($commenters as $user) {
                CaseComment::factory()->count(rand(1, 3))->create([
                    'case_id' => $case->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
