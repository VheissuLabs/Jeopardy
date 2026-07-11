# Jeopardy Game — Design Spec (2026-07-11)

## Purpose

A party Jeopardy game. A host authors question boards ahead of time, runs a live game
on a big screen, judges answers from their phone, and contestants buzz in and watch
their scores update live on their phones via Laravel Reverb. No Final Jeopardy
write-in round, no Double Jeopardy, no Daily Doubles.

## Roles & Devices

| Role | Device | Auth |
|---|---|---|
| Host (authoring) | Laptop | Existing Fortify login |
| Host (in-game console) | Phone (scans private host QR) | Signed host token in URL |
| Big-screen board | TV / projector browser | Public URL by game code |
| Contestant | Phone (scans join QR from big screen) | Signed player token, no account |

## Data Model

- **boards**: `user_id`, `name`. A reusable question set owned by a host.
- **categories**: `board_id`, `name`, `position` (1–6 typical).
- **clues**: `category_id`, `prompt` (the "answer" shown on the board), `correct_response`
  (what the contestant should say), `value` (e.g. 200–1000), `position`.
- **games**: `board_id`, `user_id` (host), `code` (short join code), `status`
  (`lobby` | `active` | `finished`), `host_token`.
- **players**: `game_id`, `name`, `score` (signed int), `token`.
- **game_clues**: `game_id`, `clue_id`, `status` (`hidden` | `open` | `answered`),
  created for every clue when the game starts.
- **buzzes**: `game_clue_id`, `player_id`, `created_at`, `locked_out` (bool),
  unique on (`game_clue_id`, `player_id`).

## Game Flow

1. Host builds a board in the **board editor** (grid CRUD of categories × clues).
2. Host clicks "Start game" → game created in `lobby` status. Host sees:
   - the big-screen URL to open on the TV, and
   - a **private host QR** to scan with their phone (opens host console).
3. Big screen (lobby) shows a **contestant join QR** + join code. Contestants scan,
   enter their name, and appear live in the lobby list.
4. Host taps "Begin" on their phone → game `active`, big screen shows the board grid.
5. Host taps a clue on their phone → clue becomes `open`; big screen shows the prompt;
   contestants' buzz buttons enable. Host phone shows the **prompt and the correct
   response** plus **Correct / Incorrect** buttons.
6. First buzz wins (server-side atomic check). Big screen banners the buzzer's name;
   other buzz buttons disable.
7. Host judges:
   - **Correct** → player gains clue value; clue `answered`; back to board.
   - **Incorrect** → player loses clue value and is locked out of this clue; buzzing
     reopens for remaining players. If everyone is locked out, clue auto-closes.
   - **Skip / reveal** → clue closes with no score change.
8. When all clues are answered (or host ends the game), status `finished`; big screen
   and phones show final standings.

## Realtime (Laravel Reverb)

- New dependencies: `laravel/reverb`, `laravel-echo` + `pusher-js` (npm).
- One public channel per game: `game.{code}` (code is unguessable enough for a party
  game; write actions are still token-protected server-side).
- Events broadcast: `PlayerJoined`, `GameStarted`, `ClueOpened`, `PlayerBuzzed`,
  `AnswerJudged` (carries updated scores + clue status), `GameFinished`.
- Buzz race: `POST /play/{game}/buzz` accepts a buzz only if the clue is `open` and
  the player isn't locked out; a DB transaction + unique constraint makes the first
  accepted buzz authoritative. Later buzzes get a 409 and are ignored client-side.
- All state lives in the DB; a page reload on any device re-hydrates full state via
  Inertia props. Reverb pushes deltas only.

## Pages (Inertia Vue)

1. `boards/Index.vue`, `boards/Edit.vue` — authenticated board editor.
2. `games/HostConsole.vue` — mobile-first: current clue prompt + correct response,
   buzz queue, Correct / Incorrect / Skip buttons, board grid to pick clues, lobby
   "Begin" and "End game" controls.
3. `games/BigScreen.vue` — lobby QR + player list, then board grid, open clue,
   buzz banner, persistent score strip, final standings.
4. `play/Join.vue`, `play/Play.vue` — name entry, then giant buzz button (enabled
   only when eligible), own score, live scoreboard.

## Routes & Auth

- Authenticated (Fortify): board CRUD, game creation.
- Host console: `/host/{game:code}?t={host_token}` — middleware validates the token.
- Big screen: `/screen/{game:code}` — read-only, public.
- Contestant: `/join/{game:code}` (creates player, issues token cookie),
  `/play/{game:code}` + buzz endpoint — middleware resolves player from token.

## Error Handling

- Duplicate/late buzzes rejected server-side, silently absorbed client-side.
- Disconnects: reload re-hydrates from DB; Echo re-subscribes automatically.
- Judging is idempotent: judging an already-answered clue is a no-op with a warning.

## Testing (Pest)

- Board/category/clue CRUD authorization and validation.
- Joining: QR code path creates player, duplicate names allowed, token issued.
- Buzz race: only first eligible buzz accepted; locked-out player rejected.
- Judging: correct adds value, incorrect deducts + locks out + reopens, all-locked-out
  auto-closes; events dispatched (`Event::fake()` assertions).
- Game lifecycle: lobby → active → finished transitions.

## Out of Scope (for now)

Final Jeopardy / wagering, Double Jeopardy round, Daily Doubles, spectator chat,
board sharing between hosts, contestant accounts.
