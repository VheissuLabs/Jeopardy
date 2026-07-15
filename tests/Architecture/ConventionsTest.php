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
