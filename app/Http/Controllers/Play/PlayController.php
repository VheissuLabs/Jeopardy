<?php

namespace App\Http\Controllers\Play;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Player;
use App\Support\GameState;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayController extends Controller
{
    public function show(Request $request, Game $game): Response
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');

        return Inertia::render('play/Show', [
            'state' => GameState::for($game),
            'player' => [
                'id' => $player->id,
                'name' => $player->name,
                'score' => $player->score,
            ],
        ]);
    }
}
