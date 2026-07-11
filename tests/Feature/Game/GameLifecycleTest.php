<?php

use App\Models\Board;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use App\Models\User;

it('creates a game from my board with snapshotted clues', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->has(Category::factory()->has(Clue::factory()->count(2)))->create();

    $response = $this->actingAs($me)->post(route('games.store', $board));

    $game = Game::first();
    $response->assertRedirect(route('games.show', $game));
    expect($game->gameClues()->count())->toBe(2);
});

it('blocks creating games from boards I do not own', function () {
    $board = Board::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('games.store', $board))
        ->assertForbidden();
});

it('shows host and screen urls to the game creator only', function () {
    $game = Game::factory()->create();

    $this->actingAs($game->host)->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('games/Show')
            ->where('hostUrl', fn ($url) => str_contains($url, 't='.$game->host_token))
            ->where('screenUrl', route('screen.show', $game)));

    $this->actingAs(User::factory()->create())->get(route('games.show', $game))->assertForbidden();
});
