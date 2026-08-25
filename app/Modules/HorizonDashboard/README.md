# Horizon Dashboard

## What it demonstrates

`GET /concepts/horizon-dashboard/demo` gates access to Laravel Horizon's own dashboard —
the module has no UI of its own. With the Pennant flag off, the route returns `503` and
never touches Horizon. With the flag on, it redirects (`302`) to `horizon.index`, which
serves Horizon's real dashboard: queue throughput, wait times, recent/failed jobs, all
backed by the Redis connection Horizon supervises. Toggling the flag off again makes the
demo route 503 immediately, even though `/horizon` itself is still mounted underneath.

## How it works

**Two independent gates, same as `rate-limit-demo`.** The Pennant flag
(`Feature::active('horizon-dashboard')`) is this module's own gate, checked in
`HorizonDashboardController::show()` before the redirect is issued. Horizon ships with
its own separate gate (`viewHorizon`, defined in `App\Providers\HorizonServiceProvider`,
published by `horizon:install`) that protects `/horizon` itself regardless of this
module — in the `local` environment Horizon's `Authorize` middleware allows everyone
through automatically, so both gates pass locally. In a non-local environment, someone
could have the Pennant flag on and still hit Horizon's own `viewHorizon` gate — that gate
is Horizon's concern, not this module's, and this module doesn't touch it.

**Redis is a second queue connection, not a replacement for the default one.**
`QUEUE_CONNECTION` stays `database` — the platform's existing `job-progress` module keeps
dispatching to it, worked by the `queue` Sail service (`queue:listen`). Horizon supervises
the separate `redis` queue connection (already defined in `config/queue.php`, using the
`default` Redis connection in `config/database.php`), run by a new `horizon` Sail service
(`php artisan horizon`) that depends on a new `redis` service (`redis:alpine`). Nothing in
the app is forced onto Redis by installing Horizon — a job would need to explicitly
`->onConnection('redis')` to be picked up by it. `REDIS_HOST` in `.env` points at the
`redis` service by name (matching how `REVERB_HOST`/`DB_HOST` already point at their own
service names inside the Sail network), while `.env.example` keeps `127.0.0.1` as the
outside-Sail default.

**No custom UI, no custom Pest coverage of Horizon's internals.** The dashboard at
`/horizon` is Laravel Horizon's own package code — this module doesn't wrap, re-render, or
test any of it. The two feature tests only pin down this module's own gate: flag off means
`503` before Horizon is ever reached, flag on means a redirect *toward* Horizon, not an
assertion about what Horizon itself renders.

## Testing

- `sail artisan test --filter=HorizonDashboardTest` — flag on/off gating, and the dashboard
  listing.
- Manually: `sail up -d redis horizon` to bring up the Redis-backed queue and its
  supervisor, then toggle "Horizon Dashboard" on from `/concepts` and follow
  `/concepts/horizon-dashboard/demo` — it redirects into `/horizon`, Laravel's own
  dashboard. Toggle it off and reload — `503` before the redirect fires.
- `sail artisan horizon:status` confirms the `horizon` service is actually supervising the
  queue; `sail artisan tinker --execute="dd(Redis::ping());"` confirms Redis connectivity
  independent of Horizon.

## Notes

The `horizon` Sail service is separate from the pre-existing `queue` service on purpose —
merging them would have silently stopped `job-progress`'s database-queued jobs from being
worked, since Horizon only supervises Redis-backed connections. Horizon's own dashboard
assets/config were installed via `php artisan horizon:install`, which also registered
`App\Providers\HorizonServiceProvider` in `bootstrap/providers.php` automatically.
