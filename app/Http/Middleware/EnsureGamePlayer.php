<?php

namespace App\Http\Middleware;

use App\Models\Game;
use App\Models\Player;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGamePlayer
{
    public function handle(Request $request, Closure $next): Response
    {
        $game = $request->route('game');

        abort_unless($game instanceof Game, 404);

        $player = Player::query()
            ->whereBelongsTo($game)
            ->find($request->session()->get("player_id.{$game->id}"));

        if (! $player) {
            abort_unless($request->isMethod('GET'), 403);

            return to_route('join.create', $game);
        }

        $request->attributes->set('player', $player);

        return $next($request);
    }
}
