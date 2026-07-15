<?php

// app/Models/CLAUDE.md — models represent a table
arch('models extend Eloquent')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

// app/Enums/CLAUDE.md
arch('enums are enums')
    ->expect('App\Enums')
    ->toBeEnums();

// app/Concerns/CLAUDE.md
arch('concerns are traits')
    ->expect('App\Concerns')
    ->toBeTraits();

// app/Http/Requests/CLAUDE.md
arch('form requests extend FormRequest')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

// app/Providers/CLAUDE.md
arch('providers extend ServiceProvider')
    ->expect('App\Providers')
    ->toExtend('Illuminate\Support\ServiceProvider');
