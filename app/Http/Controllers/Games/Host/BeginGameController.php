<?php

namespace App\Http\Controllers\Games\Host;

use App\Enums\GameStatus;
use App\Events\GameStarted;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;

class BeginGameController extends Controller
{
    public function __invoke(Game $game): RedirectResponse
    {
        abort_unless($game->status === GameStatus::Lobby, 422);

        $game->update([
            'status' => GameStatus::Active,
            'controlling_player_id' => $game->players()
                ->inRandomOrder()
                ->value('id'),
        ]);

        GameStarted::dispatch($game);

        return back();
    }
}
