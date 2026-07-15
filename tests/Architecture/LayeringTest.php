<?php

// app/Support/CLAUDE.md — stateless helpers stay out of the HTTP layer
arch('support stays out of the HTTP layer')
    ->expect('App\Support')
    ->not->toUse('App\Http');

// app/Http/Controllers/CLAUDE.md — validation lives in FormRequests
arch('controllers do not use the Validator facade')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\Validator');

// app/Http/Controllers/CLAUDE.md — MUST NOT validate inline ($request->validate([...]))
test('controllers do not validate inline', function () {
    $controllerFiles = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/app/Http/Controllers', FilesystemIterator::SKIP_DOTS),
    );

    $offenders = [];

    foreach ($controllerFiles as $file) {
        if ($file->getExtension() === 'php' && str_contains(file_get_contents($file->getPathname()), '->validate(')) {
            $offenders[] = $file->getFilename();
        }
    }

    expect($offenders)->toBe([]);
});
