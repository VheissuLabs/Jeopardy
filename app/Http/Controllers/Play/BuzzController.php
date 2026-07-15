<?php

namespace App\Http\Controllers\Play;

use App\Actions\Games\RecordBuzzAction;
use App\Events\PlayerBuzzed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Play\StoreBuzzRequest;
use App\Models\Game;
use App\Models\GameClue;
use App\Models\Player;
use Illuminate\Http\Response;

class BuzzController extends Controller
{
    public function store(StoreBuzzRequest $request, Game $game, RecordBuzzAction $recordBuzz): Response
    {
        $gameClue = GameClue::query()
            ->whereBelongsTo($game)
            ->findOrFail((int) $request->validated('game_clue_id'));

        /** @var Player $player */
        $player = $request->attributes->get('player');

        if (! $recordBuzz->run($gameClue, $player)) {
            return response()->noContent(409);
        }

        PlayerBuzzed::dispatch($game, $player);

        return response()->noContent();
    }
}
