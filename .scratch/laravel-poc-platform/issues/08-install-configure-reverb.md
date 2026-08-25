# 08 — Install and configure Laravel Reverb

**What to build:** Real-time broadcasting infrastructure for the whole platform, using Laravel's own first-party WebSocket server, so that every real-time concept module built afterward (job-progress, live-notifications, presence, live-collab) has a working broadcast transport to build its own channel on top of.

**Blocked by:** None — can start immediately (platform already shipped in tickets 01–04)

**Status:** done (uncommitted — awaiting go-ahead)

- [x] `laravel/reverb` is installed and configured as the app's broadcasting driver (`BROADCAST_CONNECTION=reverb`)
- [x] The Reverb server runs as its own service in the Sail docker-compose definition, portable to the future Coolify deployment (mirrors how MySQL is already defined)
- [x] Laravel Echo (frontend) is wired up and configured to connect to the local Reverb server
- [x] A minimal smoke-test event/channel (not tied to any real concept module) proves the round trip end-to-end: a server-side broadcast is actually received client-side
- [x] A Pest feature test asserts the smoke-test event is broadcast on its expected channel (using Laravel's broadcasting fake/assertion helpers — no real WebSocket connection needed in the test)
- [x] The smoke-test event/channel is removed or clearly marked as scaffolding once the assertion above is in place — it isn't a concept module and shouldn't appear on the dashboard

## Notes

- This ticket mirrors ticket 02 (Pennant) in shape: pure infrastructure, no `ConceptRegistry` entry, no dashboard visibility — it exists so later modules have something real to depend on.
- `composer require laravel/reverb` needed `-W` (`--with-all-dependencies`): the locked `guzzlehttp/psr7 3.0.1` doesn't satisfy Reverb's `^2.6` constraint, so Guzzle and its `psr7`/`promises` companions were downgraded to the 2.x line Reverb supports. `php artisan install:broadcasting --reverb` alone can't get past that (it shells out to `composer update` without `-W` and reverts on failure), and its own Node-install step needs a TTY that isn't available in a non-interactive shell — both steps were finished manually (config publish, `.env` vars, `npm install laravel-echo pusher-js`).
- Reverb runs as its own `reverb` compose service (same Sail 8.5 image/build as `laravel.test`, entrypoint's default `start-container` execs `php artisan reverb:start` when given a command). Server-side traffic (the app publishing an event) and browser traffic (Echo subscribing) cross different networks, so they need different host/port pairs: `REVERB_HOST=reverb` + `REVERB_PORT=8080` for the app container reaching Reverb over the internal `sail` Docker network, vs `FORWARD_REVERB_PORT=8081` (mapped to the container's 8080) + `VITE_REVERB_HOST=localhost` for the browser reaching it from the host. `8080` was already claimed by `phpmyadmin`'s default forwarded port, hence `8081`.
- Verified the round trip without a browser: a standalone Node script (`pusher-js`, same protocol Echo's Reverb connector speaks) subscribed to the `reverb-smoke-test` channel over the host-mapped port, then `event(new ReverbSmokeTestEvent(...))` from `artisan tinker` was received client-side. The `broadcast()` helper's `PendingBroadcast` didn't fire in that same manual check — it defers to `__destruct()`, which never ran before tinker's process tore down — so the working entry point for one-off dispatch is `event()`, not `broadcast()`.
- The smoke-test event lives at `tests/Fixtures/Broadcasting/ReverbSmokeTestEvent.php`, following the same test-only-fixture convention as `tests/Fixtures/Concepts/FixtureConceptServiceProvider.php` — it's covered by `Tests\` autoloading but ships nowhere near `app/Modules`, so there's no production scaffolding left behind.
