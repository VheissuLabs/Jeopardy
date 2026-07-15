<?php

arch()->preset()->laravel();

arch()->preset()->security();

// tests/CLAUDE.md — no debug/dump leftovers shipped to production
arch('no debug leftovers')
    ->expect(['dd', 'dump', 'ray', 'ddd', 'var_dump', 'die', 'exit'])
    ->not->toBeUsed();

// config/CLAUDE.md — env() only inside config/*.php
arch('no env outside config')
    ->expect('env')
    ->not->toBeUsed();
