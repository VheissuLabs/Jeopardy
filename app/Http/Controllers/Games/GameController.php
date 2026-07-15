<?php

namespace App\Http\Controllers\Games;

use App\Actions\Games\CreateGameFromBoardAction;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureGameHost;
use App\Http\Requests\Games\StoreGameRequest;
use App\Models\Board;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function store(StoreGameRequest $request, Board $board, CreateGameFromBoardAction $createGame): RedirectResponse
    {
        $game = $createGame->run($board, $request->user(), $request->validated('categories'));

        return to_route('games.show', $game);
    }

    public function show(Request $request, Game $game): Response
    {
        abort_unless($game->user_id === $request->user()->id, 403);

        $game->load('board');

        return Inertia::render('games/Show', [
            'game' => [
                'code' => $game->code,
                'status' => $game->status->value,
                'boardName' => $game->board->name,
            ],
            'screenUrl' => route('screen.show', $game),
            'hostUrl' => route('host.console', [
                'game' => $game,
                EnsureGameHost::TOKEN_QUERY_KEY => $game->host_token,
            ]),
        ]);
    }
}
