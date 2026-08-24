# 05 — Concept module: cache-benchmark

**What to build:** The developer sees "cache-benchmark" on the dashboard under Performance & Security, toggles it on, and on the demo page runs the same expensive query twice — once bypassing the cache, once through it — with both timings shown side by side so the win is visible, not just asserted.

**Blocked by:** None — can start immediately (platform + reference module already shipped in tickets 01–04)

**Status:** done

- [x] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [x] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Performance & Security category, with a slug used as its Pennant flag name
- [x] A demo route/controller runs a deliberately slow query (e.g. artificial delay or a genuinely expensive aggregate over seeded data) twice per request: once with `Cache::remember()`/`Cache::forget()` bypassed, once cached, and the response includes both timings in milliseconds
- [x] The demo route checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [x] A Pest HTTP feature test hits the demo route with the flag on and asserts the cached run reports a materially lower timing than the uncached run
- [x] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [x] A README.md inside the module's folder documents how the module was implemented and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [x] The module appears correctly on the dashboard (grouped under Performance & Security, toggleable) with no changes needed to the dashboard or registry code itself

## Comments

- Uses an artificial `usleep(100_000)` delay instead of a real DB aggregate — deterministic timing, no flake risk from environment/data-volume variance. Documented as a deliberate tradeoff in the module's README.
- Demo route: `GET /concepts/cache-benchmark/demo` (`cache-benchmark.demo`). Forgets the cache key, then calls `Cache::remember()` twice (guaranteed miss, then guaranteed hit), timing both with `microtime(true)`.
- Full suite: 15/15 passing (`sail artisan test`), Pint clean. `/code-review` ran both axes clean on Spec; Standards flagged 3 judgement calls, all fixed before commit: duplicated timing code (extracted a `timeCacheRemember()` helper), `bootstrap/providers.php` entries out of alphabetical order (fixed), and an undocumented concurrency limitation on the shared cache key (documented in the README as a known, accepted limitation for this single-developer sandbox).
- Verified manually against the local (non-testing) `database` cache store: flag off → 503; flag on → real timings (~107ms uncached vs ~1ms cached).
