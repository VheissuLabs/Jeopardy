<?php

namespace App\Http\Controllers\Games\Host;

use App\Actions\Games\SkipClueAction;
use App\Enums\GameClueStatus;
use App\Events\ClueClosed;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameClue;
use Illuminate\Http\RedirectResponse;

class SkipClueController extends Controller
{
    public function __invoke(Game $game, GameClue $gameClue, SkipClueAction $skipClue): RedirectResponse
    {
        abort_unless($gameClue->status === GameClueStatus::Open, 422);

        $revealedResponse = $skipClue->run($gameClue);

        ClueClosed::dispatch($game, $revealedResponse);

        return back();
    }
}
