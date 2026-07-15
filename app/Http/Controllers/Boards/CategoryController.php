<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boards\StoreCategoryRequest;
use App\Http\Requests\Boards\UpdateCategoryRequest;
use App\Models\Board;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function store(StoreCategoryRequest $request, Board $board): RedirectResponse
    {
        $board->categories()->create([
            ...$request->validated(),
            'position' => ($board->categories()->max('position') ?? 0) + 1,
        ]);

        return back();
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('update', $category->board);

        $category->delete();

        return back();
    }
}
