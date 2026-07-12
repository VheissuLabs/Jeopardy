<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function index(Request $request): Response
    {
        $boards = $request->user()->boards()
            ->withCount('categories')
            ->latest('updated_at')
            ->get()
            ->map(fn (Board $board) => [
                'id' => $board->id,
                'name' => $board->name,
                'categoriesCount' => $board->categories_count,
                'updatedAt' => $board->updated_at?->diffForHumans(),
            ]);

        $games = Game::query()
            ->whereBelongsTo($request->user(), 'host')
            ->with('board:id,name')
            ->withCount('players')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Game $game) => [
                'code' => $game->code,
                'boardName' => $game->board->name,
                'status' => $game->status->value,
                'playersCount' => $game->players_count,
                'createdAt' => $game->created_at?->diffForHumans(),
            ]);

        return Inertia::render('boards/Index', [
            'boards' => $boards,
            'games' => $games,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $board = $request->user()->boards()->create($validated);

        return to_route('boards.edit', $board);
    }

    public function edit(Board $board): Response
    {
        Gate::authorize('update', $board);

        $board->load('categories.clues');

        return Inertia::render('boards/Edit', [
            'board' => [
                'id' => $board->id,
                'name' => $board->name,
                'categories' => $board->categories->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'position' => $category->position,
                    'clues' => $category->clues->map(fn (Clue $clue) => [
                        'id' => $clue->id,
                        'prompt' => $clue->prompt,
                        'correctResponse' => $clue->correct_response,
                        'position' => $clue->position,
                    ]),
                ]),
            ],
        ]);
    }

    public function update(Request $request, Board $board): RedirectResponse
    {
        Gate::authorize('update', $board);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $board->update($validated);

        return back();
    }

    public function destroy(Board $board): RedirectResponse
    {
        Gate::authorize('delete', $board);

        $board->delete();

        return to_route('boards.index');
    }
}
