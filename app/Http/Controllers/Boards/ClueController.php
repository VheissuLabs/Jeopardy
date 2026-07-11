<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Clue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClueController extends Controller
{
    public function store(Request $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category->board);

        $validated = $this->validateClue($request, valueRequired: false);

        $category->clues()->create([
            ...$validated,
            'value' => $validated['value'] ?? $this->randomValue($category),
            'position' => ($category->clues()->max('position') ?? 0) + 1,
        ]);

        return back();
    }

    public function update(Request $request, Clue $clue): RedirectResponse
    {
        Gate::authorize('update', $clue->category->board);

        $clue->update($this->validateClue($request));

        return back();
    }

    public function destroy(Clue $clue): RedirectResponse
    {
        Gate::authorize('update', $clue->category->board);

        $clue->delete();

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateClue(Request $request, bool $valueRequired = true): array
    {
        return $request->validate([
            'prompt' => ['required', 'string', 'max:1000'],
            'correct_response' => ['required', 'string', 'max:500'],
            'value' => [$valueRequired ? 'required' : 'nullable', 'integer', 'min:0', 'max:10000'],
        ]);
    }

    protected function randomValue(Category $category): int
    {
        $standardValues = collect([200, 400, 600, 800, 1000]);
        $usedValues = $category->clues()->pluck('value');
        $unusedValues = $standardValues->diff($usedValues);

        return $unusedValues->isNotEmpty()
            ? $unusedValues->random()
            : $standardValues->random();
    }
}
