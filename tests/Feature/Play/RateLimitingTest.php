<?php

use App\Models\Game;
use Illuminate\Support\Facades\Route;

it('applies throttling to every public game endpoint', function (string $routeName, string $throttle) {
    expect(Route::getRoutes()->getByName($routeName)->gatherMiddleware())->toContain($throttle);
})->with([
    'join page' => ['join.create', 'throttle:60,1'],
    'join submit' => ['join.store', 'throttle:joins'],
    'big screen' => ['screen.show', 'throttle:60,1'],
    'play page' => ['play.show', 'throttle:60,1'],
    'buzz' => ['play.buzz', 'throttle:buzzes'],
]);

it('rate limits joining a game after ten attempts per minute', function () {
    $game = Game::factory()->create();

    foreach (range(1, 10) as $attempt) {
        $this->post(route('join.store', $game), ['name' => "Player {$attempt}"])->assertRedirect();
    }

    $this->post(route('join.store', $game), ['name' => 'Player 11'])->assertTooManyRequests();
});
