<?php

namespace Database\Factories;

use App\Enums\BuzzStatus;
use App\Models\Buzz;
use App\Models\GameClue;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buzz>
 */
class BuzzFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_clue_id' => GameClue::factory(),
            'player_id' => Player::factory(),
            'status' => BuzzStatus::Waiting,
        ];
    }

    public function incorrect(): static
    {
        return $this->state(['status' => BuzzStatus::Incorrect]);
    }
}
