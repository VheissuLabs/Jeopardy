<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Clue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clue>
 */
class ClueFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'prompt' => fake()->sentence(),
            'correct_response' => 'What is '.fake()->word().'?',
            'position' => 0,
        ];
    }
}
