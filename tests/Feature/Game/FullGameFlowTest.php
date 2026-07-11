<?php

use App\Models\Board;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use App\Models\User;

it('plays a full game end to end', function () {
    $host = User::factory()->create();
    $board = Board::factory()->for($host)->create();
    $category = Category::factory()->for($board)->create();
    Clue::factory()->for($category)->count(2)->create();

    // Host starts a game from the board; values are dealt shuffled per game.
    $this->actingAs($host)->post(route('games.store', $board))->assertRedirect();
    $game = Game::first();
    $hostSession = fn () => $this->withSession(["host_token.{$game->id}" => $game->host_token]);

    expect($game->gameClues()->pluck('value')->sort()->values()->all())->toBe([200, 400]);

    // Two contestants join from their phones.
    $this->post(route('join.store', $game), ['name' => 'Alice']);
    $alice = $game->players()->where('name', 'Alice')->first();
    $this->flushSession();
    $this->post(route('join.store', $game), ['name' => 'Bob']);
    $bob = $game->players()->where('name', 'Bob')->first();

    // Host begins and opens the $200 clue.
    $hostSession()->post(route('host.begin', $game));
    $gameClue200 = $game->gameClues()->where('value', 200)->first();
    $hostSession()->post(route('host.open', [$game, $gameClue200]));

    // Alice buzzes first and gets it wrong; Bob steals it.
    $this->withSession(["player_id.{$game->id}" => $alice->id])
        ->post(route('play.buzz', $game), ['game_clue_id' => $gameClue200->id])->assertNoContent();
    $hostSession()->post(route('host.judge', [$game, $gameClue200]), ['correct' => false]);
    $this->withSession(["player_id.{$game->id}" => $bob->id])
        ->post(route('play.buzz', $game), ['game_clue_id' => $gameClue200->id])->assertNoContent();
    $hostSession()->post(route('host.judge', [$game, $gameClue200]), ['correct' => true]);

    expect($alice->fresh()->score)->toBe(-200)
        ->and($bob->fresh()->score)->toBe(200);

    // The $400 clue gets skipped, then the host ends the game.
    $gameClue400 = $game->gameClues()->where('value', 400)->first();
    $hostSession()->post(route('host.open', [$game, $gameClue400]));
    $hostSession()->post(route('host.skip', [$game, $gameClue400]));
    $hostSession()->post(route('host.finish', $game));

    // The big screen shows the final standings with Bob on top.
    $this->get(route('screen.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('state.status', 'finished')
            ->where('state.players.0.name', 'Bob')
            ->where('state.players.0.score', 200));
});
