<?php

namespace App\Models;

use App\Enums\GameStatus;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperGame
 */
#[UseFactory(GameFactory::class)]
class Game extends Model
{
    use HasFactory;

    protected $fillable = ['board_id', 'user_id', 'code', 'host_token', 'controlling_player_id', 'status'];

    /** @return BelongsTo<Board, $this> */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /** @return BelongsTo<User, $this> */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<Player, $this> */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Get the player who currently holds board control.
     *
     * @return BelongsTo<Player, $this>
     */
    public function controllingPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'controlling_player_id');
    }

    /** @return HasMany<GameClue, $this> */
    public function gameClues(): HasMany
    {
        return $this->hasMany(GameClue::class);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * The public channel name this game's realtime events broadcast on.
     * Mirrored on the frontend in resources/js/composables/useGameChannel.ts.
     */
    public function broadcastChannel(): string
    {
        return "game.{$this->code}";
    }

    /**
     * Session key holding the proven host token for this game.
     */
    public function hostTokenSessionKey(): string
    {
        return "host_token.{$this->id}";
    }

    /**
     * Session key holding the joined player's id for this game.
     */
    public function playerSessionKey(): string
    {
        return "player_id.{$this->id}";
    }

    /**
     * Games hosted by the given user, newest first, with board and player count.
     *
     * @param  Builder<Game>  $query
     */
    #[Scope]
    protected function recentlyHostedBy(Builder $query, User $host): void
    {
        $query->whereBelongsTo($host, 'host')
            ->with('board:id,name')
            ->withCount('players')
            ->latest();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
        ];
    }
}
