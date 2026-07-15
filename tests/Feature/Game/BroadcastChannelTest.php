<?php

use App\Events\AnswerJudged;
use App\Events\ClueClosed;
use App\Events\ClueOpened;
use App\Events\GameFinished;
use App\Events\GameStarted;
use App\Events\PlayerBuzzed;
use App\Events\PlayerJoined;
use App\Models\Game;
use App\Models\Player;

it('exposes the public game channel from the model', function () {
    $game = Game::factory()->create();

    expect($game->broadcastChannel())->toBe("game.{$game->code}");
});

it('broadcasts every game event on the game channel', function (string $eventClass) {
    $game = Game::factory()->create();
    $player = Player::factory()->for($game)->create();

    $event = match ($eventClass) {
        AnswerJudged::class => new AnswerJudged($game, true, $player),
        ClueClosed::class => new ClueClosed($game),
        PlayerBuzzed::class, PlayerJoined::class => new $eventClass($game, $player),
        default => new $eventClass($game),
    };

    expect($event->broadcastOn()->name)->toBe("game.{$game->code}");
})->with([
    AnswerJudged::class,
    ClueClosed::class,
    ClueOpened::class,
    GameFinished::class,
    GameStarted::class,
    PlayerBuzzed::class,
    PlayerJoined::class,
]);
