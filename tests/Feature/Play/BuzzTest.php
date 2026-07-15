<?php

use App\Enums\BuzzStatus;
use App\Enums\GameClueStatus;
use App\Events\PlayerBuzzed;
use App\Models\Game;
use App\Models\GameClue;
use App\Models\Player;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

function playAs(Player $player): TestCase
{
    return test()->withSession(["player_id.{$player->game_id}" => $player->id]);
}

it('accepts the first buzz and rejects the second', function () {
    Event::fake([PlayerBuzzed::class]);
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();
    [$alice, $bob] = Player::factory()->for($game)->count(2)->create();

    playAs($alice)->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertNoContent();
    playAs($bob)->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertStatus(409);

    expect($gameClue->currentBuzz()->player_id)->toBe($alice->id);
    Event::assertDispatchedTimes(PlayerBuzzed::class, 1);
});

it('rejects buzzes from locked-out players', function () {
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();
    $alice = Player::factory()->for($game)->create();
    $gameClue->buzzes()->create(['player_id' => $alice->id, 'status' => BuzzStatus::Incorrect]);

    playAs($alice)->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertStatus(409);
});

it('rejects buzzes on clues that are not open', function () {
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->create(['status' => GameClueStatus::Hidden]);
    $bob = Player::factory()->for($game)->create();

    playAs($bob)->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertStatus(409);
});

it('rejects buzzes from visitors who never joined', function () {
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();

    $this->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertForbidden();
});

it('rejects buzzes for clues belonging to another game', function () {
    $game = Game::factory()->active()->create();
    $otherGameClue = GameClue::factory()->open()->create();
    $alice = Player::factory()->for($game)->create();

    playAs($alice)->post(route('play.buzz', $game), ['game_clue_id' => $otherGameClue->id])->assertNotFound();
});
