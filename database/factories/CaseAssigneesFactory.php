<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CaseAssignees>
 */
class CaseAssigneesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_id' => \App\Models\Cases::factory(),
            'user_id' => \App\Models\User::factory(),
            'assigned_role' => $this->faker->randomElement([UserRole::Admin, UserRole::Investigator, UserRole::Officer]),
            'created_at' => now(),
        ];
    }
}
