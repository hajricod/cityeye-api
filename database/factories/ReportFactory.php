<?php

namespace Database\Factories;

use App\Enums\CaseStatus;
use App\Enums\UserRole;
use App\Models\Cases;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domains = ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com', 'protonmail.com', 'icloud.com'];
        $randomDomain = $this->faker->randomElement($domains);

        $cities = [
            'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix',
            'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'
        ];

        $areas = [
            'Downtown', 'Uptown', 'Suburbs', 'Industrial Zone', 'Financial District',
            'Harbor Area', 'Residential Block A', 'Market Street', 'East End', 'West Side'
        ];

        $descriptions = [
            'Loud noises heard during the night near the alley.',
            'Suspicious individual seen loitering around the property.',
            'Witnessed a possible theft at the local store.',
            'Observed damage to public property in the park.',
            'Strange vehicle parked for hours on our street.',
            'Heard a commotion and glass breaking last night.',
            'Noticed missing items from the backyard shed.',
            'Saw someone running away from the building late at night.',
            'Reported a dispute between neighbors escalating quickly.',
            'Found evidence of forced entry at the office entrance.',
        ];

        $emailUsername = $this->faker->unique()->userName;

        return [
            'case_id' => Cases::factory(),
            'report_id' => Str::upper(uniqid('REP-')),
            'name' => $this->faker->name,
            'email' => $emailUsername . '@' . $randomDomain,
            'civil_id' => $this->faker->numerify('#########'),
            'role' => UserRole::Citizen,
            'area' => Arr::random($areas),
            'city' => Arr::random($cities),
            'description' => $this->faker->randomElement($descriptions),
            'status' => $this->faker->randomElement(CaseStatus::cases())->value,
            'created_at' => now(),
        ];
    }
}
