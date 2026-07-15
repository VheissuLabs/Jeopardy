<?php

namespace App\Http\Controllers\Games\Host;

use App\Enums\GameStatus;
use App\Events\GameFinished;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;

class FinishGameController extends Controller
{
    public function __invoke(Game $game): RedirectResponse
    {
        abort_if($game->status === GameStatus::Finished, 422);

        $game->update([
            'status' => GameStatus::Finished,
        ]);

        GameFinished::dispatch($game);

        return back();
    }
}
