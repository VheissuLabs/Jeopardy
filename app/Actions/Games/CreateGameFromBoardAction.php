<?php

namespace App\Actions\Games;

use App\Enums\GameClueStatus;
use App\Enums\GameStatus;
use App\Models\Board;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateGameFromBoardAction
{
    public const CATEGORIES_PER_GAME = 6;

    public const CLUES_PER_CATEGORY = 5;

    public const VALUE_STEP = 200;

    /** @param array<int, int>|null $categoryIds picked by the host; null draws $categoryCount (default six) at random */
    public function run(Board $board, User $host, ?array $categoryIds = null, ?int $categoryCount = null): Game
    {
        return DB::transaction(function () use ($board, $host, $categoryIds, $categoryCount): Game {
            $game = $this->createLobbyGame($board, $host);

            $this->drawCategories($board, $categoryIds, $categoryCount)
                ->each(fn (Category $category) => $this->snapshotClues($game, $category));

            return $game;
        });
    }

    protected function createLobbyGame(Board $board, User $host): Game
    {
        return Game::create([
            'board_id' => $board->id,
            'user_id' => $host->id,
            'code' => $this->generateUniqueCode(),
            'host_token' => Str::random(40),
            'status' => GameStatus::Lobby,
        ]);
    }

    /**
     * @param  array<int, int>|null  $categoryIds
     * @return Collection<int, Category>
     */
    protected function drawCategories(Board $board, ?array $categoryIds, ?int $categoryCount): Collection
    {
        if ($categoryIds !== null) {
            return $board->categories()
                ->whereIn('id', $categoryIds)
                ->with('clues')
                ->get();
        }

        return $board->categories()
            ->with('clues')
            ->get()
            ->shuffle()
            ->take($categoryCount ?? self::CATEGORIES_PER_GAME);
    }

    protected function snapshotClues(Game $game, Category $category): void
    {
        $drawnClues = $category->clues
            ->shuffle()
            ->take(self::CLUES_PER_CATEGORY)
            ->values();

        if ($drawnClues->isEmpty()) {
            return;
        }

        $values = $this->shuffledValueLadder($drawnClues->count());

        $drawnClues->each(fn (Clue $clue, int $index) => $game->gameClues()
            ->create([
                'clue_id' => $clue->id,
                'value' => $values[$index],
                'status' => GameClueStatus::Hidden,
            ]));
    }

    /** @return Collection<int, int> */
    protected function shuffledValueLadder(int $clueCount): Collection
    {
        return collect(range(1, $clueCount))
            ->map(fn (int $step) => $step * self::VALUE_STEP)
            ->shuffle();
    }

    protected function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (Game::where('code', $code)->exists());

        return $code;
    }
}
