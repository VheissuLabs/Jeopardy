<?php

// app/Actions/CLAUDE.md — Actions MUST be {Verb}{Noun}Action
arch('actions have an Action suffix')
    ->expect('App\Actions')
    ->toHaveSuffix('Action')
    // Fortify scaffolding: names are mandated by the Fortify contracts these classes implement.
    ->ignoring('App\Actions\Fortify');

// app/Http/Controllers/CLAUDE.md — controllers MUST be {SingularModel}Controller
arch('controllers have a Controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

// app/Http/Requests/CLAUDE.md — requests MUST be {Method}{SingularModel}Request
arch('form requests have a Request suffix')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request');

// app/CLAUDE.md — policies MUST be {SingularModel}Policy
arch('policies have a Policy suffix')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

// app/Models/CLAUDE.md — singular noun, no Model suffix
arch('models are not suffixed')
    ->expect('App\Models')
    ->not->toHaveSuffix('Model');

// app/Enums/CLAUDE.md — no Enum suffix
arch('enums are not suffixed')
    ->expect('App\Enums')
    ->not->toHaveSuffix('Enum');

// app/Events/CLAUDE.md — {Subject}{PastTenseVerb}, no Event suffix
arch('events are not suffixed')
    ->expect('App\Events')
    ->not->toHaveSuffix('Event');

// app/Http/Middleware/CLAUDE.md — descriptive, no suffix
arch('middleware is not suffixed')
    ->expect('App\Http\Middleware')
    ->not->toHaveSuffix('Middleware');

// app/Support/CLAUDE.md — domain noun, no Support suffix
arch('support classes are not suffixed')
    ->expect('App\Support')
    ->not->toHaveSuffix('Support');

// app/Concerns/CLAUDE.md — no Trait suffix
arch('concerns are not suffixed')
    ->expect('App\Concerns')
    ->not->toHaveSuffix('Trait');
