<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boards\StoreBoardRequest;
use App\Http\Requests\Boards\UpdateBoardRequest;
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
        return Inertia::render('boards/Index', [
            'boards' => $request->user()
                ->boards()
                ->withCount('categories')
                ->latest('updated_at')
                ->get()
                ->map($this->boardSummary(...)),
            'games' => Game::recentlyHostedBy($request->user())
                ->limit(10)
                ->get()
                ->map($this->gameSummary(...)),
        ]);
    }

    public function store(StoreBoardRequest $request): RedirectResponse
    {
        $board = $request->user()
            ->boards()
            ->create($request->validated());

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

    public function update(UpdateBoardRequest $request, Board $board): RedirectResponse
    {
        $board->update($request->validated());

        return back();
    }

    public function destroy(Board $board): RedirectResponse
    {
        Gate::authorize('delete', $board);

        $board->delete();

        return to_route('boards.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function boardSummary(Board $board): array
    {
        return [
            'id' => $board->id,
            'name' => $board->name,
            'categoriesCount' => $board->categories_count,
            'updatedAt' => $board->updated_at?->diffForHumans(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function gameSummary(Game $game): array
    {
        return [
            'code' => $game->code,
            'boardName' => $game->board->name,
            'status' => $game->status->value,
            'playersCount' => $game->players_count,
            'createdAt' => $game->created_at?->diffForHumans(),
        ];
    }
}
