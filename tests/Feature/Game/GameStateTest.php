<?php

use App\Actions\Games\CreateGameFromBoardAction;
use App\Enums\GameClueStatus;
use App\Models\Board;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Player;
use App\Models\User;
use App\Support\GameState;

it('builds the canonical game state', function () {
    $board = Board::factory()->has(Category::factory()->has(Clue::factory()->count(2)))->create();
    $game = app(CreateGameFromBoardAction::class)->run($board, User::factory()->create());
    $alice = Player::factory()->for($game)->create(['name' => 'Alice', 'score' => 400]);
    Player::factory()->for($game)->create(['name' => 'Bob', 'score' => -200]);

    $gameClue = $game->gameClues()->first();
    $gameClue->update(['status' => GameClueStatus::Open]);
    $gameClue->buzzes()->create(['player_id' => $alice->id]);

    $state = GameState::for($game->fresh());

    expect($state['code'])->toBe($game->code)
        ->and($state['players'][0]['name'])->toBe('Alice')
        ->and($state['players'][1]['score'])->toBe(-200)
        ->and($state['categories'])->toHaveCount(1)
        ->and($state['categories'][0]['clues'])->toHaveCount(2)
        ->and($state['openClue']['gameClueId'])->toBe($gameClue->id)
        ->and($state['openClue']['prompt'])->toBe($gameClue->clue->prompt)
        ->and($state['openClue']['buzzedPlayer']['name'])->toBe('Alice')
        ->and($state['openClue']['lockedOutPlayerIds'])->toBe([])
        ->and($state['openClue'])->not->toHaveKey('correctResponse')
        ->and($state['categories'][0]['clues'][0])->not->toHaveKey('prompt');
});

it('reports no open clue when the board is idle', function () {
    $board = Board::factory()->has(Category::factory()->has(Clue::factory()))->create();
    $game = app(CreateGameFromBoardAction::class)->run($board, User::factory()->create());

    expect(GameState::for($game)['openClue'])->toBeNull();
});
