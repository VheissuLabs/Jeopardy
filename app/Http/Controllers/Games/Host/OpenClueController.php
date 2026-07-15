<?php

namespace App\Http\Controllers\Games\Host;

use App\Enums\GameClueStatus;
use App\Enums\GameStatus;
use App\Events\ClueOpened;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameClue;
use Illuminate\Http\RedirectResponse;

class OpenClueController extends Controller
{
    public function __invoke(Game $game, GameClue $gameClue): RedirectResponse
    {
        abort_unless($game->status === GameStatus::Active, 422);
        abort_unless($gameClue->status === GameClueStatus::Hidden, 422);

        $gameClue->update([
            'status' => GameClueStatus::Open,
        ]);

        ClueOpened::dispatch($game);

        return back();
    }
}
