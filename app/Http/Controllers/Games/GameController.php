<?php

namespace App\Http\Controllers\Games;

use App\Actions\Games\CreateGameFromBoardAction;
use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function store(Request $request, Board $board, CreateGameFromBoardAction $createGame): RedirectResponse
    {
        Gate::authorize('update', $board);

        $game = $createGame->run($board, $request->user());

        return to_route('games.show', $game);
    }

    public function show(Request $request, Game $game): Response
    {
        abort_unless($game->user_id === $request->user()->id, 403);

        return Inertia::render('games/Show', [
            'game' => [
                'code' => $game->code,
                'status' => $game->status->value,
                'boardName' => $game->board->name,
            ],
            'screenUrl' => route('screen.show', $game),
            'hostUrl' => route('host.console', $game).'?t='.$game->host_token,
        ]);
    }
}
