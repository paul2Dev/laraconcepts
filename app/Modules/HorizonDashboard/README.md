# Horizon Dashboard

## What it demonstrates

`GET /concepts/horizon-dashboard/demo` gates access to Laravel Horizon's own dashboard —
the module has no UI of its own. With the Pennant flag off, the route returns `503` and
never touches Horizon. With the flag on, it redirects (`302`) to `horizon.index`, which
serves Horizon's real dashboard: queue throughput, wait times, recent/failed jobs, all
backed by the Redis connection Horizon supervises. Toggling the flag off again makes the
demo route 503 immediately, even though `/horizon` itself is still mounted underneath.

## How it works

**One flag, checked in two places.** The Pennant flag (`Feature::active('horizon-dashboard')`)
gates `HorizonDashboardController::show()` before the redirect is issued, and also backs
Horizon's own `viewHorizon` gate (`App\Providers\HorizonServiceProvider::gate()`, published by
`horizon:install`), which protects `/horizon` itself regardless of this module's route. In the
`local` environment Horizon's `Authorize` middleware bypasses `viewHorizon` entirely, so the
demo route's own check is the only thing you see locally; in production `viewHorizon` is what
actually runs, and it defers to the same flag — no separate allowlist to maintain, and hitting
`/horizon` directly (bypassing the demo route) is gated the same way.

**`QUEUE_CONNECTION` moved to `redis`, the connection Horizon supervises.** `redis` was
already defined as a queue connection in `config/queue.php` (using the `default` Redis
connection in `config/database.php`) — installing Horizon just made it worth actually
using. Since `job-progress`'s `ProcessUploadJob` was the only queued job in the app, flipping
the app-wide default let it start being supervised by Horizon without touching its dispatch
call: the job resolves whatever `QUEUE_CONNECTION` says, same as it always did, and that env
var is what changed. This is also why the platform's old `queue` Sail service
(`queue:listen` against the `database` connection) was removed rather than kept alongside
`horizon` — nothing dispatches to `database` any more, so a worker for it would sit idle.
Tests are unaffected: `phpunit.xml` forces `QUEUE_CONNECTION=sync` regardless of `.env`, so
`ProcessUploadJob` still runs inline under Pest. `REDIS_HOST` in `.env` points at the `redis`
service by name (matching how `REVERB_HOST`/`DB_HOST` already point at their own service
names inside the Sail network), while `.env.example` keeps `127.0.0.1` as the outside-Sail
default.

**No custom UI, no custom Pest coverage of Horizon's internals.** The dashboard at
`/horizon` is Laravel Horizon's own package code — this module doesn't wrap, re-render, or
test any of it. The two feature tests only pin down this module's own gate: flag off means
`503` before Horizon is ever reached, flag on means a redirect *toward* Horizon, not an
assertion about what Horizon itself renders.

## Testing

- `sail artisan test --filter=HorizonDashboardTest` — flag on/off gating, and the dashboard
  listing.
- Manually: `sail up -d` brings up `redis` and `horizon` alongside everything else (`horizon`
  is now one of `laravel.test`'s dependencies, same as `reverb`), then toggle "Horizon
  Dashboard" on from `/concepts` and follow `/concepts/horizon-dashboard/demo` — it redirects
  into `/horizon`, Laravel's own dashboard. Toggle it off and reload — `503` before the
  redirect fires. Upload a file through `/concepts/job-progress/demo` (flag on) and watch it
  show up under Horizon's "Completed Jobs" — real end-to-end proof the supervisor is doing
  something, not just idling.
- `sail artisan horizon:status` confirms the `horizon` service is actually supervising the
  queue; `sail artisan tinker --execute="dd(Redis::ping());"` confirms Redis connectivity
  independent of Horizon.

## Notes

Horizon's own dashboard assets/config were installed via `php artisan horizon:install`,
which also registered `App\Providers\HorizonServiceProvider` in `bootstrap/providers.php`
automatically.
