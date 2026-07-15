<?php

use App\Events\PlayerJoined;
use App\Models\Game;
use Illuminate\Support\Facades\Event;

it('shows the join page without auth', function () {
    $game = Game::factory()->create();

    $this->get(route('join.create', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('play/Join')->where('code', $game->code));
});

it('joins a game by code and broadcasts', function () {
    Event::fake([PlayerJoined::class]);
    $game = Game::factory()->create();

    $this->post(route('join.store', $game), ['name' => 'Karl'])
        ->assertRedirect(route('play.show', $game));

    expect($game->players()->where('name', 'Karl')->exists())->toBeTrue()
        ->and(session("player_id.{$game->id}"))->toBe($game->players()->first()->id);
    Event::assertDispatched(PlayerJoined::class);
});

it('validates the player name', function () {
    $game = Game::factory()->create();

    $this->post(route('join.store', $game), ['name' => ''])->assertSessionHasErrors('name');
});

it('rejects joining a finished game', function () {
    $game = Game::factory()->finished()->create();

    $this->post(route('join.store', $game), ['name' => 'Late'])->assertForbidden();
});

it('redirects an already joined player straight to play', function () {
    $game = Game::factory()->create();
    $this->post(route('join.store', $game), ['name' => 'Karl']);

    $this->get(route('join.create', $game))->assertRedirect(route('play.show', $game));
});
