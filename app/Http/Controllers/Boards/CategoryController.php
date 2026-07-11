<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function store(Request $request, Board $board): RedirectResponse
    {
        Gate::authorize('update', $board);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $board->categories()->create([
            ...$validated,
            'position' => ($board->categories()->max('position') ?? 0) + 1,
        ]);

        return back();
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category->board);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $category->update($validated);

        return back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('update', $category->board);

        $category->delete();

        return back();
    }
}
