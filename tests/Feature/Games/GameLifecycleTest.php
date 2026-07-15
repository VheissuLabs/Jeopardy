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

it('creates a game from only the categories I picked', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->has(
        Category::factory()->count(3)->has(Clue::factory()->count(2))
    )->create();
    $picked = $board->categories->first();

    $this->actingAs($me)
        ->post(route('games.store', $board), ['categories' => [$picked->id]])
        ->assertRedirect();

    $drawnCategoryIds = Game::first()
        ->gameClues()
        ->with('clue')
        ->get()
        ->pluck('clue.category_id')
        ->unique();

    expect($drawnCategoryIds->all())->toBe([$picked->id]);
});

it('rejects picked categories that belong to another board', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->has(Category::factory()->has(Clue::factory()))->create();
    $foreignCategory = Category::factory()->create();

    $this->actingAs($me)
        ->post(route('games.store', $board), ['categories' => [$foreignCategory->id]])
        ->assertSessionHasErrors('categories.0');

    expect(Game::count())->toBe(0);
});

it('creates a game with a random draw of the requested size', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->has(
        Category::factory()->count(5)->has(Clue::factory()->count(2))
    )->create();

    $this->actingAs($me)
        ->post(route('games.store', $board), ['category_count' => 2])
        ->assertRedirect();

    $drawnCategoryIds = Game::first()
        ->gameClues()
        ->with('clue')
        ->get()
        ->pluck('clue.category_id')
        ->unique();

    expect($drawnCategoryIds)->toHaveCount(2);
});

it('rejects a category count combined with picked categories', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->has(Category::factory()->has(Clue::factory()))->create();

    $this->actingAs($me)
        ->post(route('games.store', $board), [
            'categories' => [$board->categories->first()->id],
            'category_count' => 2,
        ])
        ->assertSessionHasErrors('category_count');

    expect(Game::count())->toBe(0);
});

it('rejects a category count outside one to six', function (int $count) {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->has(Category::factory()->has(Clue::factory()))->create();

    $this->actingAs($me)
        ->post(route('games.store', $board), ['category_count' => $count])
        ->assertSessionHasErrors('category_count');
})->with([0, 7]);

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
