# Rate Limit Demo

## What it demonstrates

`GET /concepts/rate-limit-demo/demo` sits behind a **named rate limiter** dedicated to
this module, registered with `RateLimiter::for('rate-limit-demo', ...)` in the service
provider's `boot()` and attached to the route via Laravel's built-in
`throttle:rate-limit-demo` middleware — not the app's global `api`/`web` throttle, its own
limiter, keyed by IP, allowing 5 requests per 10 seconds (`Limit::perSecond(5, 10)`). Hit
the route repeatedly (curl loop, or mash refresh in a browser tab) and the first five
requests return `200` with a climbing `request_count`; the sixth within the window returns
`429 Too Many Requests` straight from the framework, `X-RateLimit-*`/`Retry-After` headers
included. This is Laravel's own fixed-window limiter doing the work, not a hand-rolled
reimplementation of it.

## How it works

**Two independent gates, two independent signals.** The throttle middleware and the
Pennant flag check don't know about each other, by design:

- The **throttle** runs first, as route middleware, before the controller ever executes.
  It fires purely off request volume — it throttles even if the flag is off.
- The **flag check** (`Feature::active('rate-limit-demo')`) runs inside the controller,
  after the throttle lets a request through. It gates the demo logic itself, returning
  `503` when the flag is off.

So a caller sees exactly one of three outcomes: `429` (too many requests, regardless of
the flag), `503` (flag off, request count didn't exceed the limit), or `200` with a
`request_count` (flag on, under the limit). The ticket's two Pest tests each pin down one
of the non-`429` outcomes; manually toggling the flag off and spamming the route proves
the third.

**The `request_count` counter.** The throttle middleware tracks its own attempt count
internally (that's what powers the `X-RateLimit-*` headers), but that's not part of its
public API. Instead the controller keeps a simple `Cache::increment()` counter of its own —
the same pattern `cache-benchmark` uses for its cache key — so a developer clicking through
the demo can watch a number climb in the JSON response. `Cache::add($key, 0)` seeds the key
immediately before every increment: the `database` store (Sail's default) returns `false`
from `increment()` on a key that was never `put`, unlike the `array` store the test suite
runs against, so skipping the seed step passes locally under Pest while silently returning
`false` for every real request.

## Testing

- `sail artisan test --filter=RateLimitDemoTest` — flag on/off gating, and 5 requests
  succeeding with the 6th throttled inside the same 10-second window.
- Manually: toggle "Rate Limit Demo" on from `/concepts`, then hit
  `/concepts/rate-limit-demo/demo` five times quickly (refresh, or
  `for i in {1..6}; do curl -s -o /dev/null -w '%{http_code}\n' http://localhost/concepts/rate-limit-demo/demo; done`) —
  the first five print `200`, the sixth prints `429`. Wait 10 seconds and it resets.

## Notes

Like `cache-benchmark`'s cache key, `request_count` is a single global counter, not
scoped per caller, so concurrent testers would see an interleaved count; fine for a
single-developer local demo. The *rate limit* itself stays correctly scoped per IP
(`->by($request->ip())`) regardless — only the display counter is shared.
