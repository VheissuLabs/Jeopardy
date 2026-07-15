<?php

namespace App\Http\Controllers\Games\Host;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameClue;
use App\Support\GameState;
use Inertia\Inertia;
use Inertia\Response;

class ConsoleController extends Controller
{
    public function __invoke(Game $game): Response
    {
        $game->load('gameClues.clue.category');

        return Inertia::render('games/HostConsole', [
            'state' => GameState::for($game),
            'clues' => $game->gameClues->mapWithKeys(fn (GameClue $gameClue) => [
                $gameClue->id => [
                    'prompt' => $gameClue->clue->prompt,
                    'correctResponse' => $gameClue->clue->correct_response,
                    'category' => $gameClue->clue->category->name,
                    'value' => $gameClue->value,
                ],
            ]),
        ]);
    }
}
