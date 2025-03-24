<?php

namespace Database\Factories;

use App\Enums\CaseRole;
use App\Enums\CaseType;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CasePerson>
 */
class CasePersonFactory extends Factory
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
            'type' => $this->faker->randomElement(CaseType::cases())->value,
            'name' => $this->faker->name,
            'age' => $this->faker->numberBetween(18, 70),
            'gender' => $this->faker->randomElement(Gender::cases())->value,
            'role' => $this->faker->randomElement(CaseRole::cases())->value,
            'created_at' => now(),
        ];
    }
}
