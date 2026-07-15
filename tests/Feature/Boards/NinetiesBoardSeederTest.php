<?php

use App\Models\Board;
use Database\Seeders\NinetiesBoardSeeder;

it('seeds the 90s board with eight full categories', function () {
    $this->seed(NinetiesBoardSeeder::class);

    $board = Board::where('name', 'Totally 90s')->firstOrFail();

    expect($board->categories)->toHaveCount(8)
        ->and($board->categories->every(fn ($category) => $category->clues()->count() === 8))->toBeTrue();
});
