# Cache Benchmark

## What it demonstrates

`GET /concepts/cache-benchmark/demo` runs the same "expensive" operation twice through
`Cache::remember()`: the first call is a cache miss (it actually runs the slow work and
stores the result), the second call is a cache hit (it reads the stored result straight
back). Both calls are timed with `microtime(true)`, and the response returns both
timings in milliseconds so the win is visible in the JSON, not just asserted in a test.

## How it works

**An artificial delay, not a real slow query.** The ticket allowed either "a genuinely
expensive aggregate over seeded data" or "an artificial delay." A real aggregate's timing
depends on the machine, the seeded data volume, and MySQL's own query cache/buffer pool
state on a given run — none of which this module controls. `usleep(100_000)` (100ms) is
deterministic: every uncached run costs ~100ms regardless of environment, so the Pest test
can assert a hard threshold instead of a fuzzy "cached should be faster" comparison that
could flake.

**Works across cache stores without touching the code.** The module never names a cache
store — it just calls the `Cache` facade, so it reads whatever `CACHE_STORE` resolves to.
Locally that's Sail's `database` driver (persists across requests, matching what deploying
this behind Coolify would actually see); `phpunit.xml` overrides it to the `array` driver
for tests. Both work identically here because the miss/hit pair happens within a single
request — the array driver's in-process lifetime is long enough to cover that, even though
it wouldn't survive a second, separate HTTP request the way `database` does.

## Testing

- `sail artisan test --filter=CacheBenchmarkDemoTest` — flag on/off gating, and the
  cached run reporting a materially lower time than the uncached run.
- Manually: toggle "Cache Benchmark" on from `/concepts`, then hit
  `/concepts/cache-benchmark/demo` — `uncached_ms` should sit around 100, `cached_ms`
  well under half that.

## Notes

`CACHE_KEY` is a single global key with no per-request scoping. Two concurrent hits to the
demo route can interleave — one request's `Cache::forget()` firing mid-timing of another's
`remember()` call would corrupt both callers' reported numbers. Fine for a single-developer
local demo (see spec.md's Out of Scope on auth), not fine if this route were ever exposed
to concurrent traffic.
