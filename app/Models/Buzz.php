<?php

namespace App\Models;

use App\Enums\BuzzStatus;
use Database\Factories\BuzzFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @mixin IdeHelperBuzz */
#[UseFactory(BuzzFactory::class)]
class Buzz extends Model
{
    use HasFactory;

    protected $table = 'buzzes';

    protected $fillable = [
        'game_clue_id',
        'player_id',
        'status',
    ];

    /** @return BelongsTo<GameClue, $this> */
    public function gameClue(): BelongsTo
    {
        return $this->belongsTo(GameClue::class);
    }

    /** @return BelongsTo<Player, $this> */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => BuzzStatus::class,
        ];
    }
}
