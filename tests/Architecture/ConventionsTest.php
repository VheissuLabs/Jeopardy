<?php

// app/Models/CLAUDE.md — a docblock whose only content is a single tag
// must be written on one line: /** @return BelongsTo<User, $this> */,
// /** @mixin IdeHelperBoard */
test('single-tag docblocks in models are single-line', function () {
    $offenders = [];

    foreach (glob(dirname(__DIR__, 2).'/app/Models/*.php') as $path) {
        preg_match_all(
            '{/\*\*\n\s*\* (@\w+[^\n]*)\n\s*\*/}',
            (string) file_get_contents($path),
            $matches,
        );

        foreach ($matches[1] as $tag) {
            $offenders[] = basename($path).': '.$tag;
        }
    }

    expect($offenders)->toBe([]);
});

// app/CLAUDE.md — a chain either fits on one line or breaks every link onto
// its own line; no line of a multi-line chain may carry two links.
test('multi-line method chains put every link on its own line', function () {
    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/app', FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $lines = explode("\n", (string) file_get_contents($file->getPathname()));

        foreach ($lines as $index => $line) {
            $partOfMultilineChain = str_starts_with(ltrim($line), '->')
                || (isset($lines[$index + 1]) && str_starts_with(ltrim($lines[$index + 1]), '->'));

            if (! $partOfMultilineChain) {
                continue;
            }

            // Collapse parenthesised groups so `->` inside call arguments
            // (a different object's inline chain) doesn't count as a link.
            $collapsed = $line;

            do {
                $previous = $collapsed;
                $collapsed = (string) preg_replace('/\([^()]*\)/', '#', $collapsed);
            } while ($collapsed !== $previous);

            if (str_contains($collapsed, '#->')) {
                $offenders[] = basename($file->getPathname()).':'.($index + 1).' '.trim($line);
            }
        }
    }

    expect($offenders)->toBe([]);
});

// app/Models/CLAUDE.md — $fillable/$hidden arrays are multi-line, one element
// per line; a populated single-line array forces horizontal scrolling.
test('model attribute arrays are one element per line', function () {
    $offenders = [];

    foreach (glob(dirname(__DIR__, 2).'/app/Models/*.php') as $path) {
        preg_match_all(
            '{protected \$(?:fillable|hidden) = \[.+\];}',
            (string) file_get_contents($path),
            $matches,
        );

        foreach ($matches[0] as $line) {
            $offenders[] = basename($path).': '.$line;
        }
    }

    expect($offenders)->toBe([]);
});
