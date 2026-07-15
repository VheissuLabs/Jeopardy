<?php

namespace App\Models;

use App\Enums\BuzzStatus;
use App\Enums\GameClueStatus;
use Database\Factories\GameClueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperGameClue
 */
#[Fillable(['game_id', 'clue_id', 'value', 'status'])]
class GameClue extends Model
{
    /** @use HasFactory<GameClueFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<Clue, $this>
     */
    public function clue(): BelongsTo
    {
        return $this->belongsTo(Clue::class);
    }

    /**
     * @return HasMany<Buzz, $this>
     */
    public function buzzes(): HasMany
    {
        return $this->hasMany(Buzz::class);
    }

    public function currentBuzz(): ?Buzz
    {
        return $this->buzzes()->where('status', BuzzStatus::Waiting)->with('player')->first();
    }

    /**
     * @return array<int, int>
     */
    public function lockedOutPlayerIds(): array
    {
        return $this->buzzes()->where('status', BuzzStatus::Incorrect)->pluck('player_id')->all();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GameClueStatus::class,
        ];
    }
}
