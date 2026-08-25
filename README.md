# Laravel Concepts Platform

A sandbox Laravel app for building one proof-of-concept Laravel feature per module, each toggleable live via a feature flag. Every module registers itself in `App\Platform\ConceptRegistry` and shows up on the [`/concepts`](/concepts) dashboard — see `CONTEXT.md` and `docs/adr/0001-concept-module-convention.md` for the full convention.

## Infrastructure

- **Sail + MySQL + Pest** — local Docker dev environment, MySQL as the database engine, Pest as the test runner across the whole project.
- **Laravel Pennant** — first-party feature flags, one per concept, persisted to the database so state survives across requests and deploys.
- **ConceptRegistry + dashboard** — `/concepts` lists every registered concept grouped by category with a live on/off toggle backed by Pennant.
- **Laravel Reverb** — first-party WebSocket broadcasting server, running as its own Sail service, giving every future real-time module (job-progress, live-notifications, presence, live-collab) a working broadcast transport to build on.

## Concepts

- **Custom Casts & value objects** — round-trips a Money value object through a custom Eloquent cast backed by two database columns.
- **Cache Benchmark** — runs the same expensive query with the cache bypassed and then cached, timing both.
- **Rate Limit Demo** — spams a demo route behind a dedicated 5-requests-per-10-seconds limiter, returning a live request count until it throttles with a 429.
- **Semantic Search** — searches a seeded product catalog with a toggle between plain keyword LIKE matching and embedding-based semantic ranking.
