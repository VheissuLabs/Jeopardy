<?php

use App\Actions\Games\CreateGameFromBoardAction;
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

    $game = app(CreateGameFromBoardAction::class)->run($board, User::factory()->create());

    expect($game->status)->toBe(GameStatus::Lobby)
        ->and($game->code)->toHaveLength(6)
        ->and($game->host_token)->toHaveLength(40)
        ->and($game->gameClues)->toHaveCount(6)
        ->and($game->gameClues->every(fn ($gameClue) => $gameClue->status === GameClueStatus::Hidden))->toBeTrue();
});

it('draws at most five random clues per category with a shuffled value ladder', function () {
    $board = Board::factory()->has(
        Category::factory()->has(Clue::factory()->count(10))
    )->create();

    $game = app(CreateGameFromBoardAction::class)->run($board, User::factory()->create());

    expect($game->gameClues)->toHaveCount(5)
        ->and($game->gameClues->pluck('value')->sort()->values()->all())
        ->toBe([200, 400, 600, 800, 1000]);
});

it('can deal different clues to different games from the same board', function () {
    $board = Board::factory()->has(
        Category::factory()->has(Clue::factory()->count(10))
    )->create();
    $host = User::factory()->create();

    $drawnClueIds = collect(range(1, 5))
        ->map(fn () => app(CreateGameFromBoardAction::class)->run($board, $host)->gameClues->pluck('clue_id')->sort()->values())
        ->unique(fn ($clueIds) => $clueIds->implode(','));

    expect($drawnClueIds->count())->toBeGreaterThan(1);
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

it('draws at most six random categories per game', function () {
    $board = Board::factory()->has(
        Category::factory()->count(9)->has(Clue::factory()->count(2))
    )->create();

    $game = app(CreateGameFromBoardAction::class)->run($board, User::factory()->create());

    $drawnCategoryIds = $game->gameClues
        ->load('clue')
        ->pluck('clue.category_id')
        ->unique();

    expect($drawnCategoryIds)->toHaveCount(6);
});

it('uses every category when the board has six or fewer', function () {
    $board = Board::factory()->has(
        Category::factory()->count(3)->has(Clue::factory()->count(2))
    )->create();

    $game = app(CreateGameFromBoardAction::class)->run($board, User::factory()->create());

    $drawnCategoryIds = $game->gameClues
        ->load('clue')
        ->pluck('clue.category_id')
        ->unique();

    expect($drawnCategoryIds)->toHaveCount(3);
});

it('creates a game from only the picked categories', function () {
    $board = Board::factory()->has(
        Category::factory()->count(4)->has(Clue::factory()->count(2))
    )->create();
    $picked = $board->categories->take(2)->pluck('id');

    $game = app(CreateGameFromBoardAction::class)->run($board, User::factory()->create(), $picked->all());

    $drawnCategoryIds = $game->gameClues
        ->load('clue')
        ->pluck('clue.category_id')
        ->unique();

    expect($drawnCategoryIds->sort()->values()->all())->toBe($picked->sort()->values()->all());
});
