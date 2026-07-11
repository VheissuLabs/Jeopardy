<?php

namespace App\Models;

use App\Enums\BuzzStatus;
use Database\Factories\BuzzFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_clue_id
 * @property int $player_id
 * @property BuzzStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read GameClue $gameClue
 * @property-read Player $player
 */
#[Fillable(['game_clue_id', 'player_id', 'status'])]
class Buzz extends Model
{
    /** @use HasFactory<BuzzFactory> */
    use HasFactory;

    protected $table = 'buzzes';

    /**
     * @return BelongsTo<GameClue, $this>
     */
    public function gameClue(): BelongsTo
    {
        return $this->belongsTo(GameClue::class);
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BuzzStatus::class,
        ];
    }
}
