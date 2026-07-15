<?php

// app/CLAUDE.md — a docblock whose only content is a single tag is written
// on one line (/** @return array<string, mixed> */), unless the collapsed
// line would exceed 120 characters.
test('single-tag docblocks are single-line', function () {
    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/app', FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        preg_match_all(
            '{^( *)/\*\*\n\s*\* (@\w+[^\n]*)\n\s*\*/}m',
            (string) file_get_contents($file->getPathname()),
            $matches,
            PREG_SET_ORDER,
        );

        foreach ($matches as $match) {
            if (strlen("{$match[1]}/** {$match[2]} */") <= 120) {
                $offenders[] = basename($file->getPathname()).': '.$match[2];
            }
        }
    }

    expect($offenders)->toBe([]);
});

// app/CLAUDE.md — chains of 3+ calls break every link onto its own line;
// two-call chains may stay inline; no line may ever carry two links of a
// multi-line chain.
test('method chains of three or more calls break every link onto its own line', function () {
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
            // Collapse parenthesised groups so `->` inside call arguments
            // (a different object's inline chain) doesn't count as a link.
            $collapsed = $line;

            do {
                $previous = $collapsed;
                $collapsed = (string) preg_replace('/\([^()]*\)/', '#', $collapsed);
            } while ($collapsed !== $previous);

            $inlineLinkJoints = preg_match_all('/#\??->/', $collapsed);

            if ($inlineLinkJoints === 0) {
                continue;
            }

            $partOfMultilineChain = str_starts_with(ltrim($line), '->')
                || (isset($lines[$index + 1]) && str_starts_with(ltrim($lines[$index + 1]), '->'));

            if ($partOfMultilineChain || $inlineLinkJoints >= 2) {
                $offenders[] = basename($file->getPathname()).':'.($index + 1).' '.trim($line);
            }
        }
    }

    expect($offenders)->toBe([]);
});

// app/CLAUDE.md — associative array literals are multi-line, one entry per
// line; plain lists may stay inline.
test('associative array literals put every entry on its own line', function () {
    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/app', FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        foreach (explode("\n", (string) file_get_contents($file->getPathname())) as $index => $line) {
            if (preg_match('/\[[^\[\]]* => [^\[\]]*\]/', $line)) {
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
