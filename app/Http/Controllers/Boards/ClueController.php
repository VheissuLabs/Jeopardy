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

        $category->clues()->create([
            ...$this->validateClue($request),
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
    protected function validateClue(Request $request): array
    {
        return $request->validate([
            'prompt' => ['required', 'string', 'max:1000'],
            'correct_response' => ['required', 'string', 'max:500'],
        ]);
    }
}
