<?php

namespace App\Http\Middleware;

use App\Models\Game;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGameHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $game = $request->route('game');

        abort_unless($game instanceof Game, 404);

        $token = $request->query('t', $request->session()->get("host_token.{$game->id}"));

        abort_unless(is_string($token) && hash_equals($game->host_token, $token), 403);

        $request->session()->put("host_token.{$game->id}", $game->host_token);

        return $next($request);
    }
}
