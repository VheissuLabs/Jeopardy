<?php

namespace App\Http\Requests\Games;

use App\Actions\Games\CreateGameFromBoardAction;
use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('board')) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $board = $this->route('board');

        return [
            'categories' => [
                'sometimes',
                'array',
                'min:1',
                'max:'.CreateGameFromBoardAction::CATEGORIES_PER_GAME,
            ],
            'categories.*' => [
                'integer',
                Rule::exists('categories', 'id')->where('board_id', $board instanceof Board ? $board->id : null),
            ],
            'category_count' => [
                'sometimes',
                'integer',
                'between:1,'.CreateGameFromBoardAction::CATEGORIES_PER_GAME,
                'prohibits:categories',
            ],
        ];
    }
}
