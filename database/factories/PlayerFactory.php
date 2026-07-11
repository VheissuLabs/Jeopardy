<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'name' => fake()->firstName(),
            'score' => 0,
        ];
    }
}
