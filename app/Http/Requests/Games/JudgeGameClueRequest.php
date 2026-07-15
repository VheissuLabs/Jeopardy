<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;

class JudgeGameClueRequest extends FormRequest
{
    /**
     * The EnsureGameHost route middleware authorizes the host.
     */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'correct' => ['required', 'boolean'],
        ];
    }
}
