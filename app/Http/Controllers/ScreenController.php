<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Support\GameState;
use Inertia\Inertia;
use Inertia\Response;

class ScreenController extends Controller
{
    public function __invoke(Game $game): Response
    {
        return Inertia::render('screen/Show', [
            'state' => GameState::for($game),
            'joinUrl' => route('join.create', $game),
        ]);
    }
}
