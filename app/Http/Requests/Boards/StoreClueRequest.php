<?php

namespace App\Http\Requests\Boards;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreClueRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Category $category */
        $category = $this->route('category');

        return $this->user()?->can('update', $category->board) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:1000'],
            'correct_response' => ['required', 'string', 'max:500'],
        ];
    }
}
