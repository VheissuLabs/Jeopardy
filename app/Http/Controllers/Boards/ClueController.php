<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boards\StoreClueRequest;
use App\Http\Requests\Boards\UpdateClueRequest;
use App\Models\Category;
use App\Models\Clue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ClueController extends Controller
{
    public function store(StoreClueRequest $request, Category $category): RedirectResponse
    {
        $category->clues()->create([
            ...$request->validated(),
            'position' => ($category->clues()->max('position') ?? 0) + 1,
        ]);

        return back();
    }

    public function update(UpdateClueRequest $request, Clue $clue): RedirectResponse
    {
        $clue->update($request->validated());

        return back();
    }

    public function destroy(Clue $clue): RedirectResponse
    {
        Gate::authorize('update', $clue->category->board);

        $clue->delete();

        return back();
    }
}
