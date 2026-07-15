<?php

namespace App\Http\Requests\Play;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuzzRequest extends FormRequest
{
    /**
     * The EnsureGamePlayer route middleware authorizes the buzzing player.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'game_clue_id' => ['required', 'integer'],
        ];
    }
}
