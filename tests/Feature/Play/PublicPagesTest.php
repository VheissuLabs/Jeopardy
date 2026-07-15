<?php

use App\Models\Game;
use App\Models\Player;

it('shows the big screen without auth', function () {
    $game = Game::factory()->create();

    $this->get(route('screen.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('screen/Show')->has('state')->has('joinUrl'));
});

it('shows the play page to a joined player', function () {
    $player = Player::factory()->create();

    $this->withSession(["player_id.{$player->game_id}" => $player->id])
        ->get(route('play.show', $player->game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('play/Show')->where('player.name', $player->name));
});

it('redirects strangers on the play page to join', function () {
    $game = Game::factory()->create();

    $this->get(route('play.show', $game))->assertRedirect(route('join.create', $game));
});

it('does not leak correct responses to the play page or screen', function () {
    $player = Player::factory()->create();

    $this->withSession(["player_id.{$player->game_id}" => $player->id])
        ->get(route('play.show', $player->game))
        ->assertDontSee('correctResponse');

    $this->get(route('screen.show', $player->game))->assertDontSee('correctResponse');
});
