<?php

namespace App\Http\Middleware;

use App\Models\Game;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGameHost
{
    public const TOKEN_QUERY_KEY = 't';

    public function handle(Request $request, Closure $next): Response
    {
        $game = $request->route('game');

        abort_unless($game instanceof Game, 404);

        $token = $request->query(self::TOKEN_QUERY_KEY, $request->session()->get($game->hostTokenSessionKey()));

        abort_unless(is_string($token) && hash_equals($game->host_token, $token), 403);

        $request->session()->put($game->hostTokenSessionKey(), $game->host_token);

        return $next($request);
    }
}
