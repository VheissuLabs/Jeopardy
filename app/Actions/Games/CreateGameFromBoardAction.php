<?php

namespace App\Actions\Games;

use App\Enums\GameClueStatus;
use App\Enums\GameStatus;
use App\Models\Board;
use App\Models\Category;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateGameFromBoardAction
{
    public const CATEGORIES_PER_GAME = 6;

    public const CLUES_PER_CATEGORY = 5;

    public function run(Board $board, User $host): Game
    {
        return DB::transaction(function () use ($board, $host): Game {
            $game = Game::create([
                'board_id' => $board->id,
                'user_id' => $host->id,
                'code' => $this->generateUniqueCode(),
                'host_token' => Str::random(40),
                'status' => GameStatus::Lobby,
            ]);

            $board->categories()
                ->with('clues')
                ->get()
                ->shuffle()
                ->take(self::CATEGORIES_PER_GAME)
                ->each(function (Category $category) use ($game): void {
                    $drawnClues = $category->clues
                        ->shuffle()
                        ->take(self::CLUES_PER_CATEGORY)
                        ->values();

                    if ($drawnClues->isEmpty()) {
                        return;
                    }

                    $shuffledValues = collect(range(1, $drawnClues->count()))
                        ->map(fn (int $step) => $step * 200)
                        ->shuffle();

                    $drawnClues->each(fn ($clue, int $index) => $game->gameClues()->create([
                        'clue_id' => $clue->id,
                        'value' => $shuffledValues[$index],
                        'status' => GameClueStatus::Hidden,
                    ]));
                });

            return $game;
        });
    }

    protected function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (Game::where('code', $code)->exists());

        return $code;
    }
}
