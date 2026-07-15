<?php

use App\Http\Controllers\Games\Host\ConsoleController;
use App\Http\Controllers\Games\Host\GameClueController;

// Host console endpoints (open/judge/skip, begin/finish) are deliberate non-resource
// actions; restructuring them is tracked as part of the controller-renaming pass.
arch()->preset()->laravel()->ignoring([
    GameClueController::class,
    ConsoleController::class,
]);

arch()->preset()->security();

// tests/CLAUDE.md — no debug/dump leftovers shipped to production
arch('no debug leftovers')
    ->expect(['dd', 'dump', 'ray', 'ddd', 'var_dump', 'die', 'exit'])
    ->not->toBeUsed();

// config/CLAUDE.md — env() only inside config/*.php
arch('no env outside config')
    ->expect('env')
    ->not->toBeUsed();
