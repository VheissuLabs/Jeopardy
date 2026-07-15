<?php

namespace App\Models;

use Database\Factories\ClueFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @mixin IdeHelperClue */
#[UseFactory(ClueFactory::class)]
class Clue extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'prompt', 'correct_response', 'position'];

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
