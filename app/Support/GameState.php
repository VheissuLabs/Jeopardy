<?php

namespace App\Support;

use App\Enums\BuzzStatus;
use App\Enums\GameClueStatus;
use App\Models\Buzz;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use App\Models\GameClue;
use App\Models\Player;

class GameState
{
    /**
     * @return array{
     *     code: string,
     *     status: string,
     *     boardName: string,
     *     players: array<int, array{id: int, name: string, score: int}>,
     *     categories: array<int, array{id: int, name: string, clues: array<int, array{gameClueId: int, value: int, status: string}>}>,
     *     openClue: array{gameClueId: int, category: string, value: int, prompt: string, buzzedPlayer: array{id: int, name: string}|null, lockedOutPlayerIds: array<int, int>}|null
     * }
     */
    public static function for(Game $game): array
    {
        $game->load(['board.categories.clues', 'players', 'gameClues.buzzes.player', 'gameClues.clue.category']);

        $gameCluesByClueId = $game->gameClues->keyBy('clue_id');

        $openGameClue = $game->gameClues->first(
            fn (GameClue $gameClue) => $gameClue->status === GameClueStatus::Open
        );

        return [
            'code' => $game->code,
            'status' => $game->status->value,
            'boardName' => $game->board->name,
            'players' => $game->players
                ->sortByDesc('score')
                ->values()
                ->map(fn (Player $player) => [
                    'id' => $player->id,
                    'name' => $player->name,
                    'score' => $player->score,
                ])
                ->all(),
            'categories' => $game->board->categories
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'clues' => $category->clues
                        ->map(fn (Clue $clue) => [
                            'gameClueId' => $gameCluesByClueId[$clue->id]?->id,
                            'value' => $gameCluesByClueId[$clue->id]->value ?? 0,
                            'status' => $gameCluesByClueId[$clue->id]?->status->value ?? GameClueStatus::Hidden->value,
                        ])
                        ->sortBy('value')
                        ->values()
                        ->all(),
                ])
                ->all(),
            'openClue' => $openGameClue ? self::openClue($openGameClue) : null,
        ];
    }

    /**
     * @return array{gameClueId: int, category: string, value: int, prompt: string, buzzedPlayer: array{id: int, name: string}|null, lockedOutPlayerIds: array<int, int>}
     */
    protected static function openClue(GameClue $gameClue): array
    {
        $waitingBuzz = $gameClue->buzzes->first(fn (Buzz $buzz) => $buzz->status === BuzzStatus::Waiting);

        return [
            'gameClueId' => $gameClue->id,
            'category' => $gameClue->clue->category->name,
            'value' => $gameClue->value,
            'prompt' => $gameClue->clue->prompt,
            'buzzedPlayer' => $waitingBuzz ? [
                'id' => $waitingBuzz->player->id,
                'name' => $waitingBuzz->player->name,
            ] : null,
            'lockedOutPlayerIds' => $gameClue->buzzes
                ->filter(fn (Buzz $buzz) => $buzz->status === BuzzStatus::Incorrect)
                ->pluck('player_id')
                ->values()
                ->all(),
        ];
    }
}
