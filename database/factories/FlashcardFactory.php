<?php

namespace Database\Factories;

use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Flashcard>
 */
class FlashcardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deck_id' => Deck::factory(),
            'front' => fake()->sentence(),
            'back' => fake()->sentence(),
            'grammar_tag' => fake()->randomElement(['Prepositions', 'Subject-Verb Agreement', 'Irregular Verbs', 'Vocabulary', 'Articles']),
            'explanation' => fake()->sentence(),
            'audio_text' => null,
            'interval_days' => 0,
            'ease_factor' => 2.50,
            'repetitions' => 0,
            'lapses' => 0,
            'due_at' => now(),
        ];
    }
}
