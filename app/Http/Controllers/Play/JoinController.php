<?php

namespace App\Http\Controllers\Play;

use App\Enums\GameStatus;
use App\Events\PlayerJoined;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JoinController extends Controller
{
    public function create(Request $request, Game $game): Response|RedirectResponse
    {
        if ($request->session()->has("player_id.{$game->id}")) {
            return to_route('play.show', $game);
        }

        return Inertia::render('play/Join', [
            'code' => $game->code,
            'boardName' => $game->board->name,
        ]);
    }

    public function store(Request $request, Game $game): RedirectResponse
    {
        abort_if($game->status === GameStatus::Finished, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:40'],
        ]);

        $player = $game->players()->create($validated);

        $request->session()->put("player_id.{$game->id}", $player->id);

        PlayerJoined::dispatch($game, $player);

        return to_route('play.show', $game);
    }
}
