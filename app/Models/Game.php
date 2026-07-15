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

/** @mixin IdeHelperGame */
#[UseFactory(GameFactory::class)]
class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'user_id',
        'code',
        'host_token',
        'controlling_player_id',
        'status',
    ];

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

    /** @return BelongsTo<Player, $this> */
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

    public function broadcastChannel(): string
    {
        return "game.{$this->code}";
    }

    public function hostTokenSessionKey(): string
    {
        return "host_token.{$this->id}";
    }

    public function playerSessionKey(): string
    {
        return "player_id.{$this->id}";
    }

    /** @param  Builder<Game>  $query */
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
