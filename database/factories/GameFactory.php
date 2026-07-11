<?php

namespace Database\Factories;

use App\Enums\GameStatus;
use App\Models\Board;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'user_id' => User::factory(),
            'code' => Str::upper(Str::random(6)),
            'host_token' => Str::random(40),
            'status' => GameStatus::Lobby,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => GameStatus::Active]);
    }

    public function finished(): static
    {
        return $this->state(['status' => GameStatus::Finished]);
    }
}
