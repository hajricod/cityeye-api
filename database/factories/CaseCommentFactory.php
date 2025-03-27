<?php

namespace Database\Factories;

use App\Models\CaseComment;
use App\Models\Cases;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CaseComment>
 */
class CaseCommentFactory extends Factory
{
    protected $model = CaseComment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_id' => Cases::inRandomOrder()->first()?->id ?? Cases::factory(),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'comment' => $this->faker->realTextBetween(20, 120),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
