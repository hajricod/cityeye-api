<?php

namespace Database\Seeders;

use App\Enums\AuthorizationLevel;
use App\Models\CaseAssignees;
use App\Models\CasePerson;
use App\Models\Cases;
use App\Models\User;
use Illuminate\Database\Seeder;

class CasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $rankMap = [
            AuthorizationLevel::Low->value => 1,
            AuthorizationLevel::Medium->value => 2,
            AuthorizationLevel::High->value => 3,
            AuthorizationLevel::Critical->value => 4,
        ];

        $officers = User::where('role', 'officer')->get();

        Cases::factory(50)->create()->each(function ($case) use ($officers, $rankMap) {
            $caseLevelValue = $case->authorization_level instanceof \BackedEnum
                ? $case->authorization_level->value
                : $case->authorization_level;

            $caseRank = $rankMap[$caseLevelValue];

            // Assign 1–2 eligible officers
            $eligibleOfficers = $officers->filter(function ($officer) use ($rankMap, $caseRank) {
                $officerLevelValue = $officer->authorization_level instanceof \BackedEnum
                    ? $officer->authorization_level->value
                    : $officer->authorization_level;

                $officerRank = $rankMap[$officerLevelValue];
                return $officerRank >= $caseRank;
            });

            $toAssign = $eligibleOfficers->random(min(2, $eligibleOfficers->count()));

            foreach ($toAssign as $officer) {
                CaseAssignees::create([
                    'case_id' => $case->id,
                    'user_id' => $officer->id,
                    'assigned_role' => 'officer'
                ]);
            }

            // Create 1–4 suspects
            CasePerson::factory(rand(1, 4))->create([
                'case_id' => $case->id,
                'type' => 'suspect',
            ]);

            // Create 1–3 victims
            CasePerson::factory(rand(1, 3))->create([
                'case_id' => $case->id,
                'type' => 'victim',
            ]);

            // Create 1–5 witnesses
            CasePerson::factory(rand(1, 5))->create([
                'case_id' => $case->id,
                'type' => 'witness',
            ]);
        });
    }
}
