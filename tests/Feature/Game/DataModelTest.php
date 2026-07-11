<?php

use App\Actions\Games\CreateGameFromBoard;
use App\Enums\GameClueStatus;
use App\Enums\GameStatus;
use App\Models\Board;
use App\Models\Buzz;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use App\Models\GameClue;
use App\Models\Player;
use App\Models\User;

it('creates a game from a board with snapshotted clues', function () {
    $board = Board::factory()->has(
        Category::factory()->count(2)->has(Clue::factory()->count(3))
    )->create();

    $game = app(CreateGameFromBoard::class)->handle($board, User::factory()->create());

    expect($game->status)->toBe(GameStatus::Lobby)
        ->and($game->code)->toHaveLength(6)
        ->and($game->host_token)->toHaveLength(40)
        ->and($game->gameClues)->toHaveCount(6)
        ->and($game->gameClues->every(fn ($gameClue) => $gameClue->status === GameClueStatus::Hidden))->toBeTrue();
});

it('resolves games by code in routes', function () {
    $game = Game::factory()->create();

    expect($game->getRouteKeyName())->toBe('code');
});

it('tracks the current buzz and locked out players on a game clue', function () {
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();
    [$alice, $bob] = Player::factory()->for($game)->count(2)->create();

    $gameClue->buzzes()->create(['player_id' => $alice->id]);
    Buzz::factory()->incorrect()->create([
        'game_clue_id' => $gameClue->id,
        'player_id' => $bob->id,
    ]);

    expect($gameClue->currentBuzz()->player->is($alice))->toBeTrue()
        ->and($gameClue->lockedOutPlayerIds())->toBe([$bob->id]);
});
