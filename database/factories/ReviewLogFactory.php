<?php

namespace Database\Factories;

use App\Models\Flashcard;
use App\Models\ReviewLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewLog>
 */
class ReviewLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'flashcard_id' => Flashcard::factory(),
            'rating' => fake()->randomElement([1, 3, 5]),
            'interval_before' => 0,
            'interval_after' => 1,
            'ease_factor_before' => 2.50,
            'ease_factor_after' => 2.50,
            'reviewed_at' => now(),
        ];
    }
}
