<?php

namespace App\Http\Controllers\Play;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureGamePlayer;
use App\Models\Game;
use App\Support\GameState;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayController extends Controller
{
    public function show(Request $request, Game $game): Response
    {
        $player = EnsureGamePlayer::playerFrom($request);

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
