<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @mixin IdeHelperCategory */
#[UseFactory(CategoryFactory::class)]
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'name',
        'position',
    ];

    /** @return BelongsTo<Board, $this> */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /** @return HasMany<Clue, $this> */
    public function clues(): HasMany
    {
        return $this->hasMany(Clue::class)->orderBy('position');
    }
}
