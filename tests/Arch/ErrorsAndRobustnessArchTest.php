<?php

// Code Principles §4 — Errors & robustness (also covers the §9 anti-slop
// checklist item on debug leftovers): fail fast, validate at boundaries,
// no debugging/insecure cruft left behind.

arch()->preset()->php();

arch()->preset()->security();

arch('casts validate their input and fail fast on the wrong type')
    ->expect([
        'App\Modules\CustomCasts\Casts',
        'App\Modules\SemanticSearch\Casts',
    ])
    ->classes()
    ->toImplement('Illuminate\Contracts\Database\Eloquent\CastsAttributes');

arch('env() is only read at the config boundary, never reached for downstream')
    ->expect('App')
    ->not->toUse('env');
