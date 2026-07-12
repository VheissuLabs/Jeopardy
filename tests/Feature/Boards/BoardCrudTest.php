<?php

use App\Models\Board;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use App\Models\User;

it('lists only my boards', function () {
    $me = User::factory()->create();
    Board::factory()->for($me)->create(['name' => 'Mine']);
    Board::factory()->create(['name' => 'Theirs']);

    $this->actingAs($me)->get(route('boards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('boards/Index')->has('boards', 1));
});

it('lists only my recent games', function () {
    $me = User::factory()->create();
    $game = Game::factory()->create(['user_id' => $me->id]);
    Game::factory()->create();

    $this->actingAs($me)->get(route('boards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('games', 1)
            ->where('games.0.code', $game->code));
});

it('creates a board and redirects to the editor', function () {
    $me = User::factory()->create();

    $this->actingAs($me)->post(route('boards.store'), ['name' => 'Movie Night'])
        ->assertRedirect();

    expect($me->boards()->where('name', 'Movie Night')->exists())->toBeTrue();
});

it('blocks editing boards I do not own', function () {
    $board = Board::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('boards.edit', $board))
        ->assertForbidden();
});

it('updates and deletes my board', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->create();

    $this->actingAs($me)->put(route('boards.update', $board), ['name' => 'Renamed'])->assertRedirect();
    expect($board->fresh()->name)->toBe('Renamed');

    $this->actingAs($me)->delete(route('boards.destroy', $board))->assertRedirect();
    $this->assertModelMissing($board);
});

it('adds categories and clues to my board', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->create();

    $this->actingAs($me)->post(route('categories.store', $board), ['name' => 'Science'])->assertRedirect();
    $category = $board->categories()->first();
    expect($category->name)->toBe('Science');

    $this->actingAs($me)->post(route('clues.store', $category), [
        'prompt' => 'This planet is red.',
        'correct_response' => 'What is Mars?',
    ])->assertRedirect();

    expect($category->clues()->count())->toBe(1);
});

it('blocks adding categories to boards I do not own', function () {
    $board = Board::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('categories.store', $board), ['name' => 'Sneaky'])
        ->assertForbidden();
});

it('validates clue input', function () {
    $me = User::factory()->create();
    $category = Category::factory()->for(Board::factory()->for($me))->create();

    $this->actingAs($me)->post(route('clues.store', $category), [
        'prompt' => '', 'correct_response' => '',
    ])->assertSessionHasErrors(['prompt', 'correct_response']);
});

it('updates and deletes clues and categories I own', function () {
    $me = User::factory()->create();
    $category = Category::factory()->for(Board::factory()->for($me))->create();
    $clue = Clue::factory()->for($category)->create();

    $this->actingAs($me)->put(route('clues.update', $clue), [
        'prompt' => 'Updated prompt.',
        'correct_response' => 'What is updated?',
    ])->assertRedirect();
    expect($clue->fresh()->prompt)->toBe('Updated prompt.');

    $this->actingAs($me)->delete(route('clues.destroy', $clue))->assertRedirect();
    $this->assertModelMissing($clue);

    $this->actingAs($me)->put(route('categories.update', $category), ['name' => 'Renamed'])->assertRedirect();
    expect($category->fresh()->name)->toBe('Renamed');

    $this->actingAs($me)->delete(route('categories.destroy', $category))->assertRedirect();
    $this->assertModelMissing($category);
});

it('blocks deleting clues on boards I do not own', function () {
    $clue = Clue::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('clues.destroy', $clue))
        ->assertForbidden();
});

it('requires auth for all board routes', function () {
    $this->get(route('boards.index'))->assertRedirect(route('login'));
});
