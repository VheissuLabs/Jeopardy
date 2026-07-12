<?php

namespace App\Http\Controllers\Games;

use App\Enums\GameStatus;
use App\Events\GameFinished;
use App\Events\GameStarted;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameClue;
use App\Support\GameState;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HostConsoleController extends Controller
{
    public function show(Game $game): Response
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

    public function begin(Game $game): RedirectResponse
    {
        abort_unless($game->status === GameStatus::Lobby, 422);

        $game->update([
            'status' => GameStatus::Active,
            'controlling_player_id' => $game->players()->inRandomOrder()->value('id'),
        ]);

        GameStarted::dispatch($game);

        return back();
    }

    public function finish(Game $game): RedirectResponse
    {
        abort_if($game->status === GameStatus::Finished, 422);

        $game->update(['status' => GameStatus::Finished]);

        GameFinished::dispatch($game);

        return back();
    }
}
