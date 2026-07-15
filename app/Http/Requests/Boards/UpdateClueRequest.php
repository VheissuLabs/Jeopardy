<?php

namespace App\Http\Requests\Boards;

use App\Models\Clue;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClueRequest extends FormRequest
{
    public function authorize(): bool
    {
        $clue = $this->route('clue');

        return $clue instanceof Clue
            && ($this->user()?->can('update', $clue->category->board) ?? false);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:1000'],
            'correct_response' => ['required', 'string', 'max:500'],
        ];
    }
}
