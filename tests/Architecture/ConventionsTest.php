<?php

// app/Models/CLAUDE.md — a docblock whose only content is a single @return
// generic must be written on one line: /** @return BelongsTo<User, $this> */
test('single-tag @return docblocks in models are single-line', function () {
    $offenders = [];

    foreach (glob(dirname(__DIR__, 2).'/app/Models/*.php') as $path) {
        preg_match_all(
            '{/\*\*\n\s+\* (@return [^\n]+)\n\s+\*/}',
            (string) file_get_contents($path),
            $matches,
        );

        foreach ($matches[1] as $tag) {
            $offenders[] = basename($path).': '.$tag;
        }
    }

    expect($offenders)->toBe([]);
});
