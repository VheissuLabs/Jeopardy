# Jeopardy Game Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A live Jeopardy party game: hosts author boards, run games judged from their phone, contestants join via QR and buzz in, with realtime updates over Laravel Reverb.

**Architecture:** Reusable `Board → Category → Clue` authoring models owned by a user; a `Game` is a live session snapshotting board clues into `game_clues` rows with per-game state. All state lives in the DB (Inertia props re-hydrate on reload); Reverb broadcasts deltas on one public channel per game (`game.{code}`). Host console is token-guarded (URL token → session); contestants are session-identified players with no account.

**Tech Stack:** Laravel 13, Inertia v3 + Vue 3, Tailwind v4, Pest 4, Laravel Reverb, `@laravel/echo-vue`, `qrcode` (npm, client-side QR rendering), Wayfinder.

**Spec:** `docs/superpowers/specs/2026-07-11-jeopardy-game-design.md`

## Global Constraints

- Follow starter-kit model conventions: `#[Fillable([...])]` attribute, PHPDoc `@property` blocks, `casts()` method, factories for every model.
- Explicit return types and parameter types everywhere; curly braces always; enums in `App\Enums` with TitleCase keys.
- Events implement `ShouldBroadcastNow` (no queue worker needed for the game to feel instant).
- All broadcast payloads are built by `App\Support\GameState::for(Game $game): array` — one canonical state shape shared by every screen.
- Run `vendor/bin/pint --dirty --format agent` before every commit.
- Tests: Pest feature tests, `php artisan test --compact --filter=...`, factories for all models.
- Routes are named; frontend uses Wayfinder imports (`@/routes/...`) — run `php artisan wayfinder:generate` after adding routes.
- Deviation from spec (documented): players are identified by **session** (server-side) rather than a token cookie; `buzzes.status` enum (`waiting`/`incorrect`) replaces `locked_out` bool + `game_clues.buzzer` — the waiting buzz IS the current buzzer.

---

### Task 1: Install Reverb, Echo, and QR dependencies

**Files:**
- Modify: `composer.json`, `package.json`, `.env`, `resources/js/app.ts`, `config/broadcasting.php` (generated)

**Interfaces:**
- Produces: working `configureEcho({ broadcaster: 'reverb' })` app-wide; `useEchoPublic` available to all pages; `qrcode` importable.

- [ ] **Step 1: Install broadcasting + Reverb**

```bash
php artisan install:broadcasting --reverb --no-interaction
```

Expected: `laravel/reverb` in composer.json, `config/reverb.php` + `routes/channels.php` created, REVERB_* env vars added.

- [ ] **Step 2: Install frontend packages**

```bash
npm install laravel-echo pusher-js @laravel/echo-vue qrcode
npm install --save-dev @types/qrcode
```

- [ ] **Step 3: Configure Echo in `resources/js/app.ts`** (add near top, after imports):

```ts
import { configureEcho } from '@laravel/echo-vue';

configureEcho({
    broadcaster: 'reverb',
});
```

- [ ] **Step 4: Verify build passes**

```bash
npm run build
php artisan config:show broadcasting.default
```

Expected: build succeeds; broadcasting default is `reverb`.

- [ ] **Step 5: Commit**

```bash
git add -A && git commit -m "feat: install Reverb, Echo, and qrcode dependencies"
```

---

### Task 2: Data model — migrations, enums, models, factories

**Files:**
- Create: `app/Enums/GameStatus.php`, `app/Enums/GameClueStatus.php`, `app/Enums/BuzzStatus.php`
- Create: `app/Models/Board.php`, `Category.php`, `Clue.php`, `Game.php`, `Player.php`, `GameClue.php`, `Buzz.php`
- Create: migrations (via `php artisan make:model {Name} -mf` for each model, in the order listed)
- Create: `database/factories/{Board,Category,Clue,Game,Player,GameClue,Buzz}Factory.php`
- Test: `tests/Feature/Game/DataModelTest.php`

**Interfaces:**
- Produces: models + relations used by every later task:
  - `Board`: `user(): BelongsTo`, `categories(): HasMany`, `games(): HasMany`
  - `Category`: `board(): BelongsTo`, `clues(): HasMany`
  - `Clue`: `category(): BelongsTo` — columns `prompt`, `correct_response`, `value`, `position`
  - `Game`: `board(): BelongsTo`, `host(): BelongsTo` (user_id), `players(): HasMany`, `gameClues(): HasMany`; columns `code` (unique, 6 upper chars, route key), `host_token` (40 chars), `status` (GameStatus cast)
  - `Player`: `game(): BelongsTo`; columns `name`, `score` (int, default 0)
  - `GameClue`: `game(): BelongsTo`, `clue(): BelongsTo`, `buzzes(): HasMany`; `status` (GameClueStatus cast); helper `currentBuzz(): ?Buzz` (waiting buzz w/ player), `lockedOutPlayerIds(): array`
  - `Buzz`: `gameClue(): BelongsTo`, `player(): BelongsTo`; `status` (BuzzStatus cast); unique index (`game_clue_id`, `player_id`)
  - `Game::createForBoard(Board $board, User $host): self` static — generates unique code + host_token, snapshots all board clues into `game_clues` (status Hidden), status Lobby.

- [ ] **Step 1: Enums**

```php
<?php // app/Enums/GameStatus.php

namespace App\Enums;

enum GameStatus: string
{
    case Lobby = 'lobby';
    case Active = 'active';
    case Finished = 'finished';
}
```

```php
<?php // app/Enums/GameClueStatus.php

namespace App\Enums;

enum GameClueStatus: string
{
    case Hidden = 'hidden';
    case Open = 'open';
    case Answered = 'answered';
}
```

```php
<?php // app/Enums/BuzzStatus.php

namespace App\Enums;

enum BuzzStatus: string
{
    case Waiting = 'waiting';
    case Incorrect = 'incorrect';
}
```

- [ ] **Step 2: Generate models + migrations + factories**

```bash
php artisan make:model Board -mf --no-interaction
php artisan make:model Category -mf --no-interaction
php artisan make:model Clue -mf --no-interaction
php artisan make:model Game -mf --no-interaction
php artisan make:model Player -mf --no-interaction
php artisan make:model GameClue -mf --no-interaction
php artisan make:model Buzz -mf --no-interaction
```

- [ ] **Step 3: Fill migrations** (schema only shown; each in its generated file):

```php
// create_boards_table
Schema::create('boards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->timestamps();
});

// create_categories_table
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('board_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();
});

// create_clues_table
Schema::create('clues', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->text('prompt');
    $table->text('correct_response');
    $table->unsignedInteger('value');
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();
});

// create_games_table
Schema::create('games', function (Blueprint $table) {
    $table->id();
    $table->foreignId('board_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('code', 6)->unique();
    $table->string('host_token', 40);
    $table->string('status')->default('lobby');
    $table->timestamps();
});

// create_players_table
Schema::create('players', function (Blueprint $table) {
    $table->id();
    $table->foreignId('game_id')->constrained()->cascadeOnDelete();
    $table->string('name', 40);
    $table->integer('score')->default(0);
    $table->timestamps();
});

// create_game_clues_table
Schema::create('game_clues', function (Blueprint $table) {
    $table->id();
    $table->foreignId('game_id')->constrained()->cascadeOnDelete();
    $table->foreignId('clue_id')->constrained()->cascadeOnDelete();
    $table->string('status')->default('hidden');
    $table->timestamps();
    $table->unique(['game_id', 'clue_id']);
});

// create_buzzes_table
Schema::create('buzzes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('game_clue_id')->constrained()->cascadeOnDelete();
    $table->foreignId('player_id')->constrained()->cascadeOnDelete();
    $table->string('status')->default('waiting');
    $table->timestamps();
    $table->unique(['game_clue_id', 'player_id']);
});
```

- [ ] **Step 4: Models** — follow Team.php conventions (`#[Fillable]`, PHPDoc, `casts()`). Key excerpts (full relations per Interfaces block above):

```php
<?php // app/Models/Game.php

namespace App\Models;

use App\Enums\GameClueStatus;
use App\Enums\GameStatus;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['board_id', 'user_id', 'code', 'host_token', 'status'])]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    public static function createForBoard(Board $board, User $host): self
    {
        $game = self::create([
            'board_id' => $board->id,
            'user_id' => $host->id,
            'code' => self::generateUniqueCode(),
            'host_token' => Str::random(40),
            'status' => GameStatus::Lobby,
        ]);

        $board->categories()->with('clues')->get()
            ->flatMap->clues
            ->each(fn (Clue $clue) => $game->gameClues()->create([
                'clue_id' => $clue->id,
                'status' => GameClueStatus::Hidden,
            ]));

        return $game;
    }

    protected static function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function board(): BelongsTo { return $this->belongsTo(Board::class); }
    public function host(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function players(): HasMany { return $this->hasMany(Player::class); }
    public function gameClues(): HasMany { return $this->hasMany(GameClue::class); }

    public function getRouteKeyName(): string { return 'code'; }

    protected function casts(): array
    {
        return ['status' => GameStatus::class];
    }
}
```

```php
// app/Models/GameClue.php — helpers
public function currentBuzz(): ?Buzz
{
    return $this->buzzes()->where('status', BuzzStatus::Waiting)->with('player')->first();
}

/** @return array<int, int> */
public function lockedOutPlayerIds(): array
{
    return $this->buzzes()->where('status', BuzzStatus::Incorrect)->pluck('player_id')->all();
}
```

Factories: `BoardFactory` (`user_id => User::factory(), name => fake()->words(2, true)`), `CategoryFactory` (`board_id => Board::factory(), name => fake()->word(), position => 0`), `ClueFactory` (`category_id => Category::factory(), prompt => fake()->sentence(), correct_response => 'What is '.fake()->word().'?', value => 200, position => 0`), `GameFactory` (`board_id => Board::factory(), user_id => User::factory(), code => Str::upper(Str::random(6)), host_token => Str::random(40), status => GameStatus::Lobby`, states `active()`, `finished()`), `PlayerFactory` (`game_id => Game::factory(), name => fake()->firstName(), score => 0`), `GameClueFactory` (`game_id => Game::factory(), clue_id => Clue::factory(), status => GameClueStatus::Hidden`, states `open()`, `answered()`), `BuzzFactory` (`game_clue_id => GameClue::factory(), player_id => Player::factory(), status => BuzzStatus::Waiting`).

- [ ] **Step 5: Write test** `tests/Feature/Game/DataModelTest.php`:

```php
<?php

use App\Enums\GameClueStatus;
use App\Enums\GameStatus;
use App\Models\Board;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use App\Models\User;

it('creates a game from a board with snapshotted clues', function () {
    $board = Board::factory()->has(
        Category::factory()->count(2)->has(Clue::factory()->count(3))
    )->create();

    $game = Game::createForBoard($board, User::factory()->create());

    expect($game->status)->toBe(GameStatus::Lobby)
        ->and($game->code)->toHaveLength(6)
        ->and($game->host_token)->toHaveLength(40)
        ->and($game->gameClues)->toHaveCount(6)
        ->and($game->gameClues->every(fn ($gc) => $gc->status === GameClueStatus::Hidden))->toBeTrue();
});

it('resolves games by code in routes', function () {
    $game = Game::factory()->create();

    expect($game->getRouteKeyName())->toBe('code');
});
```

- [ ] **Step 6: Migrate + run tests**

```bash
php artisan migrate --no-interaction
php artisan test --compact --filter=DataModelTest
```

Expected: PASS.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A && git commit -m "feat: jeopardy data model (boards, games, players, buzzes)"
```

---

### Task 3: Board / Category / Clue CRUD backend

**Files:**
- Create: `app/Http/Controllers/Boards/BoardController.php`, `Boards/CategoryController.php`, `Boards/ClueController.php`
- Create: `app/Policies/BoardPolicy.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Boards/BoardCrudTest.php`

**Interfaces:**
- Produces routes (all `auth` middleware, named):
  - `boards.index` GET `/boards` → Inertia `boards/Index` with `boards` prop `[{id, name, categoriesCount, updatedAt}]`
  - `boards.store` POST `/boards` `{name}` → redirect `boards.edit`
  - `boards.edit` GET `/boards/{board}/edit` → Inertia `boards/Edit` with `board: {id, name, categories: [{id, name, position, clues: [{id, prompt, correctResponse, value, position}]}]}`
  - `boards.update` PUT `/boards/{board}` `{name}`; `boards.destroy` DELETE `/boards/{board}`
  - `categories.store` POST `/boards/{board}/categories` `{name}`; `categories.update` PUT `/categories/{category}` `{name}`; `categories.destroy` DELETE `/categories/{category}`
  - `clues.store` POST `/categories/{category}/clues` `{prompt, correct_response, value}`; `clues.update` PUT `/clues/{clue}`; `clues.destroy` DELETE `/clues/{clue}`
- All child controllers authorize `update` on the owning board via `BoardPolicy` (`$board->user_id === $user->id`).

- [ ] **Step 1: Write failing tests** `tests/Feature/Boards/BoardCrudTest.php`:

```php
<?php

use App\Models\Board;
use App\Models\Category;
use App\Models\Clue;
use App\Models\User;

it('lists only my boards', function () {
    $me = User::factory()->create();
    Board::factory()->for($me)->create(['name' => 'Mine']);
    Board::factory()->create(['name' => 'Theirs']);

    $this->actingAs($me)->get(route('boards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('boards/Index')->has('boards', 1));
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

it('adds categories and clues to my board', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->create();

    $this->actingAs($me)->post(route('categories.store', $board), ['name' => 'Science'])->assertRedirect();
    $category = $board->categories()->first();

    $this->actingAs($me)->post(route('clues.store', $category), [
        'prompt' => 'This planet is red.',
        'correct_response' => 'What is Mars?',
        'value' => 400,
    ])->assertRedirect();

    expect($category->clues()->count())->toBe(1);
});

it('validates clue input', function () {
    $me = User::factory()->create();
    $category = Category::factory()->for(Board::factory()->for($me))->create();

    $this->actingAs($me)->post(route('clues.store', $category), [
        'prompt' => '', 'correct_response' => '', 'value' => -5,
    ])->assertSessionHasErrors(['prompt', 'correct_response', 'value']);
});

it('deletes clues, categories, and boards I own', function () {
    $me = User::factory()->create();
    $clue = Clue::factory()->for(Category::factory()->for(Board::factory()->for($me)))->create();

    $this->actingAs($me)->delete(route('clues.destroy', $clue))->assertRedirect();
    expect(Clue::query()->count())->toBe(0);
});
```

Requires `User::boards(): HasMany` — add to `app/Models/User.php`.

- [ ] **Step 2: Run tests, expect failures** (`route not defined`).

- [ ] **Step 3: Implement.** `BoardPolicy` with `update(User $user, Board $board): bool => $board->user_id === $user->id` (and `view` same). Controllers validate inline (`$request->validate([...])`); positions auto-assigned as `max(position)+1`. Clue validation: `prompt: required|string`, `correct_response: required|string`, `value: required|integer|min:0|max:10000`. Category/Clue controllers call `$this->authorize('update', $categoryOrClue->...board)`. Routes appended to `routes/web.php`:

```php
Route::middleware(['auth'])->group(function () {
    Route::get('boards', [BoardController::class, 'index'])->name('boards.index');
    Route::post('boards', [BoardController::class, 'store'])->name('boards.store');
    Route::get('boards/{board}/edit', [BoardController::class, 'edit'])->name('boards.edit');
    Route::put('boards/{board}', [BoardController::class, 'update'])->name('boards.update');
    Route::delete('boards/{board}', [BoardController::class, 'destroy'])->name('boards.destroy');
    Route::post('boards/{board}/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('categories/{category}/clues', [ClueController::class, 'store'])->name('clues.store');
    Route::put('clues/{clue}', [ClueController::class, 'update'])->name('clues.update');
    Route::delete('clues/{clue}', [ClueController::class, 'destroy'])->name('clues.destroy');
});
```

- [ ] **Step 4: Run tests → PASS**, `php artisan wayfinder:generate`, pint, commit `feat: board authoring CRUD`.

---

### Task 4: GameState presenter + broadcast events

**Files:**
- Create: `app/Support/GameState.php`
- Create: `app/Events/{PlayerJoined,GameStarted,ClueOpened,PlayerBuzzed,AnswerJudged,GameFinished}.php`
- Test: `tests/Feature/Game/GameStateTest.php`

**Interfaces:**
- Produces `GameState::for(Game $game): array`:

```php
[
    'code' => string,
    'status' => 'lobby'|'active'|'finished',
    'boardName' => string,
    'players' => [['id' => int, 'name' => string, 'score' => int]],       // score desc
    'categories' => [['id' => int, 'name' => string, 'clues' => [
        ['gameClueId' => int, 'value' => int, 'status' => 'hidden'|'open'|'answered'],
    ]]],
    'openClue' => null | [
        'gameClueId' => int, 'category' => string, 'value' => int, 'prompt' => string,
        'buzzedPlayer' => null|['id' => int, 'name' => string],
        'lockedOutPlayerIds' => [int],
    ],
]
```
  (`prompt` intentionally excluded from `categories` grid; `correct_response` never leaves the server except on the host console page prop.)
- Every event: `implements ShouldBroadcastNow`, constructor `public function __construct(public Game $game) {}` (plus extras below), `broadcastOn(): Channel => new Channel('game.'.$this->game->code)`, `broadcastWith(): array => ['state' => GameState::for($this->game)]`. Extras: `PlayerJoined` also `public Player $player`; `PlayerBuzzed` also `public Player $player`; `AnswerJudged` also `public bool $correct, public Player $player`. Client listens for event class base names (`.listen('ClueOpened', ...)` via `useEchoPublic('game.CODE', ['PlayerJoined', ...])`).

- [ ] **Step 1: Failing test** `tests/Feature/Game/GameStateTest.php`:

```php
<?php

use App\Enums\GameClueStatus;
use App\Models\{Board, Category, Clue, Game, GameClue, Player, User};
use App\Support\GameState;

it('builds the canonical game state', function () {
    $board = Board::factory()->has(Category::factory()->has(Clue::factory()->count(2)))->create();
    $game = Game::createForBoard($board, User::factory()->create());
    $alice = Player::factory()->for($game)->create(['name' => 'Alice', 'score' => 400]);
    Player::factory()->for($game)->create(['name' => 'Bob', 'score' => -200]);

    $gameClue = $game->gameClues()->first();
    $gameClue->update(['status' => GameClueStatus::Open]);
    $gameClue->buzzes()->create(['player_id' => $alice->id]);

    $state = GameState::for($game->fresh());

    expect($state['players'][0]['name'])->toBe('Alice')
        ->and($state['categories'][0]['clues'])->toHaveCount(2)
        ->and($state['openClue']['gameClueId'])->toBe($gameClue->id)
        ->and($state['openClue']['buzzedPlayer']['name'])->toBe('Alice')
        ->and($state['openClue'])->not->toHaveKey('correctResponse')
        ->and($state['categories'][0]['clues'][0])->not->toHaveKey('prompt');
});
```

- [ ] **Step 2: Run → fail.** **Step 3: Implement** `GameState` (eager-load `players`, `board.categories.clues`, `gameClues.buzzes.player`; map per shape above; open clue = first gameClue with status Open). Create the six event classes per Interfaces. **Step 4: Test → PASS. Step 5: pint + commit** `feat: game state presenter and broadcast events`.

---

### Task 5: Game lifecycle + join backend

**Files:**
- Create: `app/Http/Controllers/Games/GameController.php` (store, show), `app/Http/Controllers/Play/JoinController.php` (create, store)
- Create: `app/Http/Middleware/EnsureGameHost.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Game/GameLifecycleTest.php`, `tests/Feature/Game/JoinGameTest.php`

**Interfaces:**
- `games.store` POST `/boards/{board}/games` (auth, must own board) → `Game::createForBoard`, redirect `games.show`.
- `games.show` GET `/games/{game}` (auth, host only) → Inertia `games/Show` props: `game: {code, status}`, `screenUrl` (route `screen.show`), `hostUrl` (route `host.console` + `?t={host_token}`).
- `EnsureGameHost` middleware: accepts `?t=` query or session key `host_token.{game->id}`; `hash_equals` against `game->host_token`; stores in session; 403 otherwise. Registered inline per-route (`->middleware(EnsureGameHost::class)`).
- `join.create` GET `/join/{game}` (public) → Inertia `play/Join` props `{code, boardName, playerName: null|string}` (playerName set if session already joined).
- `join.store` POST `/join/{game}` `{name: required|string|max:40}` → creates `Player`, `session(["player_id.{$game->id}" => $player->id])`, broadcasts `PlayerJoined`, redirect `play.show`. Rejects (`422`) if game finished.

- [ ] **Step 1: Failing tests:**

```php
<?php // tests/Feature/Game/GameLifecycleTest.php

use App\Models\{Board, Game, User};

it('creates a game from my board', function () {
    $me = User::factory()->create();
    $board = Board::factory()->for($me)->create();

    $response = $this->actingAs($me)->post(route('games.store', $board));

    $game = Game::first();
    $response->assertRedirect(route('games.show', $game));
});

it('shows host and screen urls to the game creator only', function () {
    $game = Game::factory()->create();

    $this->actingAs($game->host)->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('games/Show')
            ->where('hostUrl', fn ($url) => str_contains($url, 't='.$game->host_token)));

    $this->actingAs(User::factory()->create())->get(route('games.show', $game))->assertForbidden();
});
```

```php
<?php // tests/Feature/Game/JoinGameTest.php

use App\Events\PlayerJoined;
use App\Models\Game;
use Illuminate\Support\Facades\Event;

it('joins a game by code and broadcasts', function () {
    Event::fake([PlayerJoined::class]);
    $game = Game::factory()->create();

    $this->post(route('join.store', $game), ['name' => 'Karl'])
        ->assertRedirect(route('play.show', $game));

    expect($game->players()->where('name', 'Karl')->exists())->toBeTrue();
    Event::assertDispatched(PlayerJoined::class);
});

it('rejects joining a finished game', function () {
    $game = Game::factory()->finished()->create();

    $this->post(route('join.store', $game), ['name' => 'Late'])->assertForbidden();
});
```

- [ ] **Step 2: Run → fail. Step 3: Implement** controllers, middleware, routes:

```php
// routes/web.php additions
Route::middleware(['auth'])->group(function () {
    Route::post('boards/{board}/games', [GameController::class, 'store'])->name('games.store');
    Route::get('games/{game}', [GameController::class, 'show'])->name('games.show');
});
Route::get('join/{game}', [JoinController::class, 'create'])->name('join.create');
Route::post('join/{game}', [JoinController::class, 'store'])->name('join.store');
```

- [ ] **Step 4: Tests PASS → wayfinder:generate, pint, commit** `feat: game creation and contestant join flow`.

---

### Task 6: Host actions — begin, open clue, buzz, judge, skip, finish

The core game engine. All host POST routes under `EnsureGameHost`; buzz under player session.

**Files:**
- Create: `app/Http/Controllers/Games/HostConsoleController.php` (show, begin, finish)
- Create: `app/Http/Controllers/Games/HostClueController.php` (open, judge, skip)
- Create: `app/Http/Controllers/Play/BuzzController.php` (store)
- Create: `app/Http/Middleware/EnsureGamePlayer.php`
- Modify: `routes/web.php`, `app/Models/GameClue.php` (engine methods)
- Test: `tests/Feature/Game/BuzzTest.php`, `tests/Feature/Game/JudgeTest.php`

**Interfaces:**
- Routes:
  - `host.console` GET `/host/{game}` → Inertia `games/HostConsole` props `{state: GameState::for($game), clues: {gameClueId: {prompt, correctResponse, category, value}}}` (host sees everything)
  - `host.begin` POST `/host/{game}/begin` → status Active, broadcast `GameStarted`
  - `host.open` POST `/host/{game}/clues/{gameClue}/open` → only if Hidden + game Active; status Open, broadcast `ClueOpened`
  - `host.judge` POST `/host/{game}/clues/{gameClue}/judge` `{correct: required|boolean}`
  - `host.skip` POST `/host/{game}/clues/{gameClue}/skip` → status Answered, delete waiting buzzes, broadcast `AnswerJudged`-less state refresh via `ClueOpened`? No — broadcast `AnswerJudged` with `correct=false, player=null`? Keep it typed: broadcast `ClueSkipped` — NO. Decision: `host.skip` broadcasts `AnswerJudged` is wrong shape; instead reuse `GameStarted`-style state push: create seventh event `ClueClosed` (game only). Add to Task 4's event list.
  - `host.finish` POST `/host/{game}/finish` → status Finished, broadcast `GameFinished`
  - `play.buzz` POST `/play/{game}/buzz` `{game_clue_id}` (EnsureGamePlayer) → 200 on accepted, 409 on lost race/locked out/closed
- `EnsureGamePlayer`: resolves `session("player_id.{$game->id}")` to a Player on the game; 403 if absent; sets `$request->attributes->set('player', $player)`.
- Engine methods on `GameClue`:
  - `recordBuzz(Player $player): bool` — in `DB::transaction`: `lockForUpdate()` self, false unless status Open, no waiting buzz, player not locked out; creates waiting buzz.
  - `judge(bool $correct): void` — waiting buzz required; correct → `player->increment('score', value)`, status Answered, buzz deleted; incorrect → `player->decrement('score', value)`, buzz status Incorrect; if all game players locked out → status Answered.

- [ ] **Step 1: Failing tests:**

```php
<?php // tests/Feature/Game/BuzzTest.php

use App\Enums\BuzzStatus;
use App\Enums\GameClueStatus;
use App\Events\PlayerBuzzed;
use App\Models\{Game, GameClue, Player};
use Illuminate\Support\Facades\Event;

function playAs(Player $player): Illuminate\Testing\TestCase|Tests\TestCase
{
    return test()->withSession(["player_id.{$player->game_id}" => $player->id]);
}

it('accepts the first buzz and rejects the second', function () {
    Event::fake([PlayerBuzzed::class]);
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();
    [$alice, $bob] = Player::factory()->for($game)->count(2)->create();

    playAs($alice)->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertOk();
    playAs($bob)->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertConflict();

    expect($gameClue->currentBuzz()->player_id)->toBe($alice->id);
    Event::assertDispatchedTimes(PlayerBuzzed::class, 1);
});

it('rejects buzzes on closed clues and from locked-out players', function () {
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();
    $alice = Player::factory()->for($game)->create();
    $gameClue->buzzes()->create(['player_id' => $alice->id, 'status' => BuzzStatus::Incorrect]);

    playAs($alice)->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertConflict();

    $gameClue->update(['status' => GameClueStatus::Hidden]);
    $bob = Player::factory()->for($game)->create();
    playAs($bob)->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertConflict();
});

it('rejects buzzes from visitors who never joined', function () {
    $game = Game::factory()->active()->create();
    $gameClue = GameClue::factory()->for($game)->open()->create();

    $this->post(route('play.buzz', $game), ['game_clue_id' => $gameClue->id])->assertForbidden();
});
```

```php
<?php // tests/Feature/Game/JudgeTest.php

use App\Enums\GameClueStatus;
use App\Events\{AnswerJudged, ClueOpened, GameFinished, GameStarted};
use App\Models\{Clue, Game, GameClue, Player};
use Illuminate\Support\Facades\Event;

function hostGame(Game $game): Tests\TestCase
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
    $clue = Clue::factory()->create(['value' => 600]);
    $gameClue = GameClue::factory()->for($game)->for($clue)->open()->create();
    $alice = Player::factory()->for($game)->create();
    $gameClue->buzzes()->create(['player_id' => $alice->id]);

    hostGame($game)->post(route('host.judge', [$game, $gameClue]), ['correct' => true])->assertRedirect();

    expect($alice->fresh()->score)->toBe(600)
        ->and($gameClue->fresh()->status)->toBe(GameClueStatus::Answered);
    Event::assertDispatched(AnswerJudged::class, fn ($e) => $e->correct === true);
});

it('deducts points on incorrect, locks the player out, and reopens buzzing', function () {
    Event::fake([AnswerJudged::class]);
    $game = Game::factory()->active()->create();
    $clue = Clue::factory()->create(['value' => 600]);
    $gameClue = GameClue::factory()->for($game)->for($clue)->open()->create();
    [$alice, $bob] = Player::factory()->for($game)->count(2)->create();
    $gameClue->buzzes()->create(['player_id' => $alice->id]);

    hostGame($game)->post(route('host.judge', [$game, $gameClue]), ['correct' => false])->assertRedirect();

    expect($alice->fresh()->score)->toBe(-600)
        ->and($gameClue->fresh()->status)->toBe(GameClueStatus::Open)
        ->and($gameClue->fresh()->lockedOutPlayerIds())->toBe([$alice->id])
        ->and($gameClue->fresh()->recordBuzz($bob))->toBeTrue();
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
```

- [ ] **Step 2: Run → fail. Step 3: Implement** engine methods, `ClueClosed` event, controllers, middleware, routes:

```php
// routes/web.php additions
Route::middleware(EnsureGameHost::class)->prefix('host/{game}')->group(function () {
    Route::get('/', [HostConsoleController::class, 'show'])->name('host.console');
    Route::post('begin', [HostConsoleController::class, 'begin'])->name('host.begin');
    Route::post('finish', [HostConsoleController::class, 'finish'])->name('host.finish');
    Route::post('clues/{gameClue}/open', [HostClueController::class, 'open'])->name('host.open');
    Route::post('clues/{gameClue}/judge', [HostClueController::class, 'judge'])->name('host.judge');
    Route::post('clues/{gameClue}/skip', [HostClueController::class, 'skip'])->name('host.skip');
});
Route::post('play/{game}/buzz', [BuzzController::class, 'store'])
    ->middleware(EnsureGamePlayer::class)->name('play.buzz');
```

`GameClue` route binding must be scoped to the game (`{gameClue}` scoped via `->scopeBindings()` or manual check `abort_unless($gameClue->game_id === $game->id, 404)` — use `->scopeBindings()` on the group). Buzz controller returns `response()->noContent()` (204) on success and `response()->json([...], 409)` on `recordBuzz() === false`, broadcasting `PlayerBuzzed` only on success. Judge: `abort_unless` clue Open and waiting buzz exists (422 otherwise, idempotent-safe), then `$gameClue->judge($request->boolean('correct'))` and broadcast `AnswerJudged($game, $request->boolean('correct'), $player)`.

- [ ] **Step 4: Tests PASS → wayfinder:generate, pint, commit** `feat: buzz race and judging engine`.

---

### Task 7: Public screen + play pages backend

**Files:**
- Create: `app/Http/Controllers/ScreenController.php`, `app/Http/Controllers/Play/PlayController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Game/PublicPagesTest.php`

**Interfaces:**
- `screen.show` GET `/screen/{game}` (public) → Inertia `screen/Show` props `{state, joinUrl}` (`joinUrl = route('join.create', $game)`).
- `play.show` GET `/play/{game}` (EnsureGamePlayer) → Inertia `play/Show` props `{state, player: {id, name, score}}`; visitors without a session player are redirected to `join.create`. (Middleware tweak: `EnsureGamePlayer` redirects on GET, 403 on POST.)

- [ ] **Step 1: Failing tests:**

```php
<?php // tests/Feature/Game/PublicPagesTest.php

use App\Models\{Game, Player};

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
```

- [ ] **Step 2–4:** implement, tests PASS, wayfinder:generate, pint, commit `feat: public screen and play page endpoints`.

---

### Task 8: Frontend — shared game types, Echo composable, QR component

**Files:**
- Create: `resources/js/types/game.ts`, `resources/js/composables/useGameChannel.ts`, `resources/js/components/QrCode.vue`

**Interfaces:**
- `types/game.ts` exports `GameState`, `GamePlayer`, `OpenClue`, `BoardCategory` TS types mirroring `GameState::for` exactly (camelCase as produced by the presenter).
- `useGameChannel(code: string, onState: (state: GameState) => void)` — subscribes `useEchoPublic('game.'+code, ['PlayerJoined','GameStarted','ClueOpened','ClueClosed','PlayerBuzzed','AnswerJudged','GameFinished'], (e) => onState(e.state))`.
- `<QrCode :value="url" :size="256" />` — renders canvas via `qrcode`'s `toCanvas`.

- [ ] **Step 1: Implement all three** (types mirror Task 4 shape; composable wraps `useEchoPublic` from `@laravel/echo-vue`; QrCode uses `onMounted` + `watch` on `value`):

```vue
<!-- resources/js/components/QrCode.vue -->
<script setup lang="ts">
import QRCode from 'qrcode';
import { onMounted, ref, watch } from 'vue';

const props = withDefaults(defineProps<{ value: string; size?: number }>(), { size: 256 });
const canvas = ref<HTMLCanvasElement>();

function draw(): void {
    if (canvas.value) {
        QRCode.toCanvas(canvas.value, props.value, { width: props.size, margin: 1 });
    }
}

onMounted(draw);
watch(() => props.value, draw);
</script>

<template>
    <canvas ref="canvas" class="rounded-lg bg-white p-2" />
</template>
```

- [ ] **Step 2: Verify** `npm run build` + `npm run types:check` pass. Commit `feat: game frontend primitives (types, channel composable, QR)`.

---

### Task 9: Frontend — board editor pages

**Files:**
- Create: `resources/js/pages/boards/Index.vue`, `resources/js/pages/boards/Edit.vue`
- Modify: sidebar nav (`resources/js/components/AppSidebar.vue` or `NavMain` items) to add a "Boards" link.

**Interfaces:**
- Consumes Task 3 routes via Wayfinder (`@/routes/boards`, `@/routes/categories`, `@/routes/clues`).

- [ ] **Step 1: `boards/Index.vue`** — AppLayout page: list of boards (name, category count, updated), "New board" form (`<Form :action="boardsStore()">` name input), each row links to `boards.edit`, delete button, and a "Start game" button posting to `games.store`.
- [ ] **Step 2: `boards/Edit.vue`** — grid editor: rename board; column per category (add/rename/delete); per category a stack of clue cards sorted by value with inline add/edit/delete forms (`prompt` textarea, `correct_response` input, `value` number). Use Inertia `<Form>`/`useForm` with `preserveScroll`.
- [ ] **Step 3: Manual check** `npm run build`, visit `/boards`, create board with 2 categories × 2 clues.
- [ ] **Step 4: Smoke test** (Pest browser or feature render already covered by Task 3 Inertia assertions). Commit `feat: board editor UI`.

---

### Task 10: Frontend — host flow (games/Show + games/HostConsole)

**Files:**
- Create: `resources/js/pages/games/Show.vue`, `resources/js/pages/games/HostConsole.vue`

**Interfaces:**
- Consumes: Task 5/6 props and routes; `useGameChannel`; `QrCode`.

- [ ] **Step 1: `games/Show.vue`** (host's laptop, after creating a game): shows big-screen URL as a copyable link + "Open big screen" anchor (target _blank), and the **host QR** (`<QrCode :value="hostUrl" />`) with instruction "Scan with your phone to host". Note the host URL contains the secret token — page is host-only (Task 5 authz).
- [ ] **Step 2: `games/HostConsole.vue`** — mobile-first, no AppLayout (bare dark layout):
  - Local reactive `state` initialized from prop, updated via `useGameChannel`.
  - Lobby: player list + "Begin game" button (`host.begin`).
  - Active, no open clue: tap-able board grid (category columns, value buttons; disabled when answered) posting `host.open`.
  - Active, open clue: big card with **prompt** and **correct response** (from `clues` prop keyed by gameClueId), banner showing `openClue.buzzedPlayer` name; buttons: ✓ Correct / ✗ Incorrect (post `host.judge`, disabled until someone buzzes) and "Skip" (`host.skip`).
  - Footer: live scores; "End game" (`host.finish`, with confirm).
- [ ] **Step 3:** `npm run build` + `types:check`; manual smoke via two browser windows. Commit `feat: host console UI`.

---

### Task 11: Frontend — big screen + contestant pages

**Files:**
- Create: `resources/js/pages/screen/Show.vue`, `resources/js/pages/play/Join.vue`, `resources/js/pages/play/Show.vue`

- [ ] **Step 1: `screen/Show.vue`** — full-screen dark board:
  - Lobby: game code huge + `<QrCode :value="joinUrlAbsolute" :size="320" />` + joined player names appearing live.
  - Active: classic grid (category headers, gold value cells; answered cells blank). Open clue: full-screen blue card with prompt in large serif; buzz banner "{name} buzzed in!" on `PlayerBuzzed`; flash green/red on `AnswerJudged`.
  - Finished: standings podium sorted by score.
  - Note: `joinUrl` prop is a path; compute absolute via `new URL(joinUrl, window.location.origin).href`.
- [ ] **Step 2: `play/Join.vue`** — name input + join button (`join.store`).
- [ ] **Step 3: `play/Show.vue`** — giant circular BUZZ button, enabled only when `state.openClue && !openClue.buzzedPlayer && !openClue.lockedOutPlayerIds.includes(player.id)`; posts `play.buzz` with `game_clue_id`, absorbs 409 silently; shows own score huge + scoreboard list; "You're locked out" / "{name} is answering…" status line; final standings when finished.
- [ ] **Step 4:** build + types:check + full test suite `php artisan test --compact`. Commit `feat: big screen and contestant UI`.

---

### Task 12: Final verification & polish

- [ ] **Step 1:** `vendor/bin/pint --format agent`, `npm run lint`, `npm run format`, `vendor/bin/phpstan analyse` (fix findings).
- [ ] **Step 2:** Full suite: `php artisan test --compact` — all green.
- [ ] **Step 3:** End-to-end smoke with Reverb running (`php artisan reverb:start` in background): seed a demo board (`database/seeders/DemoBoardSeeder.php` with 3 categories × 3 clues), create game, join as two players in separate incognito windows, buzz, judge, verify realtime on the screen page.
- [ ] **Step 4:** Commit `chore: polish, demo seeder, static analysis fixes`.

## Self-Review Notes

- Spec coverage: authoring (T3/T9), lifecycle+QR join (T5/T10/T11), buzz race+judging+lockout+auto-close (T6), realtime events (T4), screens (T9–T11), tests throughout. `ClueClosed` event added for skip (spec's `AnswerJudged` reserved for real judgments).
- Deviations from spec are listed under Global Constraints (session players, buzz status enum).
- Type consistency: state shape defined once in Task 4 and mirrored in Task 8 types.
