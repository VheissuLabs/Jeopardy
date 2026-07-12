<?php

use App\Actions\Games\RecordBuzzAction;
use App\Enums\GameClueStatus;
use App\Events\AnswerJudged;
use App\Events\ClueClosed;
use App\Events\ClueOpened;
use App\Events\GameFinished;
use App\Events\GameStarted;
use App\Models\Game;
use App\Models\GameClue;
use App\Models\Player;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

function hostGame(Game $game): TestCase
{
    return test()->withSession(["host_token.{$game->id}" => $game->host_token]);
}

it('begins the game from the lobby', function () {
    Event::fake([GameStarted::class]);
    $game = Game::factory()->create();

    hostGame($game)->post(route('host.begin', $game))->assertRedirect();

    expect($game->fresh()->status->value)->toBe('active');
    Event::assertDispatched(GameStarted::class);
});

it('lets the host in with the url token and blocks bad tokens', function () {
    $game = Game::factory()->create();

    $this->get(route('host.console', $game).'?t='.$game->host_token)->assertOk();
    $this->flushSession();
    $this->get(route('host.console', $game).'?t=wrong-token')->assertForbidden();
});

it('opens a hidden clue', function () {
    Event::fake([ClueOpened::class]);
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->create();

    hostGame($game)->post(route('host.open', [$game, $gameClue]))->assertRedirect();

    expect($gameClue->fresh()->status)->toBe(GameClueStatus::Open);
    Event::assertDispatched(ClueOpened::class);
});

it('awards points for a correct answer and closes the clue', function () {
    Event::fake([AnswerJudged::class]);
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create(['value' => 600]);
    $alice = Player::factory()->for($game)->create();
    $gameClue->buzzes()->create(['player_id' => $alice->id]);

    hostGame($game)->post(route('host.judge', [$game, $gameClue]), ['correct' => true])->assertRedirect();

    expect($alice->fresh()->score)->toBe(600)
        ->and($gameClue->fresh()->status)->toBe(GameClueStatus::Answered);
    Event::assertDispatched(AnswerJudged::class, fn (AnswerJudged $event) => $event->correct === true);
});

it('keeps the score on incorrect, locks the player out, and reopens buzzing', function () {
    Event::fake([AnswerJudged::class]);
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create(['value' => 600]);
    [$alice, $bob] = Player::factory()->for($game)->count(2)->create();
    $gameClue->buzzes()->create(['player_id' => $alice->id]);

    hostGame($game)->post(route('host.judge', [$game, $gameClue]), ['correct' => false])->assertRedirect();

    expect($alice->fresh()->score)->toBe(0)
        ->and($gameClue->fresh()->status)->toBe(GameClueStatus::Open)
        ->and($gameClue->fresh()->lockedOutPlayerIds())->toBe([$alice->id])
        ->and(app(RecordBuzzAction::class)->run($gameClue->fresh(), $bob))->toBeTrue();
});

it('auto-closes the clue when every player is locked out', function () {
    Event::fake([AnswerJudged::class]);
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();
    $only = Player::factory()->for($game)->create();
    $gameClue->buzzes()->create(['player_id' => $only->id]);

    hostGame($game)->post(route('host.judge', [$game, $gameClue]), ['correct' => false])->assertRedirect();

    expect($gameClue->fresh()->status)->toBe(GameClueStatus::Answered);
});

it('rejects judging when nobody has buzzed', function () {
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();

    hostGame($game)->post(route('host.judge', [$game, $gameClue]), ['correct' => true])->assertStatus(422);
});

it('skips an open clue, clearing waiting buzzes', function () {
    Event::fake([ClueClosed::class]);
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();
    $alice = Player::factory()->for($game)->create();
    $gameClue->buzzes()->create(['player_id' => $alice->id]);

    hostGame($game)->post(route('host.skip', [$game, $gameClue]))->assertRedirect();

    expect($gameClue->fresh()->status)->toBe(GameClueStatus::Answered)
        ->and($alice->fresh()->score)->toBe(0)
        ->and($gameClue->buzzes()->count())->toBe(0);
    Event::assertDispatched(ClueClosed::class);
});

it('finishes the game', function () {
    Event::fake([GameFinished::class]);
    $game = Game::factory()->active()->create();

    hostGame($game)->post(route('host.finish', $game))->assertRedirect();

    expect($game->fresh()->status->value)->toBe('finished');
    Event::assertDispatched(GameFinished::class);
});

it('blocks host actions without the host token', function () {
    $game = Game::factory()->create();

    $this->post(route('host.begin', $game))->assertForbidden();
});
