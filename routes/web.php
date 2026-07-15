<?php

use App\Http\Controllers\Boards\BoardController;
use App\Http\Controllers\Boards\CategoryController;
use App\Http\Controllers\Boards\ClueController;
use App\Http\Controllers\Games\GameController;
use App\Http\Controllers\Games\HostClueController;
use App\Http\Controllers\Games\HostConsoleController;
use App\Http\Controllers\Play\BuzzController;
use App\Http\Controllers\Play\JoinController;
use App\Http\Controllers\Play\PlayController;
use App\Http\Controllers\ScreenController;
use App\Http\Middleware\EnsureGameHost;
use App\Http\Middleware\EnsureGamePlayer;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('boards', BoardController::class)->except(['create', 'show']);

    Route::post('boards/{board}/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::post('categories/{category}/clues', [ClueController::class, 'store'])->name('clues.store');
    Route::put('clues/{clue}', [ClueController::class, 'update'])->name('clues.update');
    Route::delete('clues/{clue}', [ClueController::class, 'destroy'])->name('clues.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::post('boards/{board}/games', [GameController::class, 'store'])->name('games.store');
    Route::get('games/{game}', [GameController::class, 'show'])->name('games.show');
});

Route::middleware(EnsureGameHost::class)->prefix('host/{game}')->scopeBindings()->group(function () {
    Route::get('/', [HostConsoleController::class, 'show'])->name('host.console');
    Route::post('begin', [HostConsoleController::class, 'begin'])->name('host.begin');
    Route::post('finish', [HostConsoleController::class, 'finish'])->name('host.finish');
    Route::post('clues/{gameClue}/open', [HostClueController::class, 'open'])->name('host.open');
    Route::post('clues/{gameClue}/judge', [HostClueController::class, 'judge'])->name('host.judge');
    Route::post('clues/{gameClue}/skip', [HostClueController::class, 'skip'])->name('host.skip');
});

Route::get('screens/{game}', ScreenController::class)->middleware('throttle:60,1')->name('screen.show');

Route::get('join/{game}', [JoinController::class, 'create'])->middleware('throttle:60,1')->name('join.create');
Route::post('join/{game}', [JoinController::class, 'store'])->middleware('throttle:joins')->name('join.store');

Route::middleware(EnsureGamePlayer::class)->group(function () {
    Route::get('play/{game}', PlayController::class)->middleware('throttle:60,1')->name('play.show');
    Route::post('play/{game}/buzz', [BuzzController::class, 'store'])->middleware('throttle:buzzes')->name('play.buzz');
});

require __DIR__.'/settings.php';
