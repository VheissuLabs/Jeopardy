<?php

namespace App\Models;

use App\Enums\GameStatus;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $board_id
 * @property int $user_id
 * @property string $code
 * @property string $host_token
 * @property GameStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Board $board
 * @property-read User $host
 * @property-read Collection<int, Player> $players
 * @property-read Collection<int, GameClue> $gameClues
 */
#[Fillable(['board_id', 'user_id', 'code', 'host_token', 'controlling_player_id', 'status'])]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Board, $this>
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Player, $this>
     */
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

    /**
     * @return HasMany<GameClue, $this>
     */
    public function gameClues(): HasMany
    {
        return $this->hasMany(GameClue::class);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
        ];
    }
}
