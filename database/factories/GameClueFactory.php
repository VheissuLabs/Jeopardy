<?php

namespace Database\Factories;

use App\Enums\GameClueStatus;
use App\Models\Clue;
use App\Models\Game;
use App\Models\GameClue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameClue>
 */
class GameClueFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'clue_id' => Clue::factory(),
            'value' => fake()->randomElement([200, 400, 600, 800, 1000]),
            'status' => GameClueStatus::Hidden,
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => GameClueStatus::Open]);
    }

    public function answered(): static
    {
        return $this->state(['status' => GameClueStatus::Answered]);
    }
}
