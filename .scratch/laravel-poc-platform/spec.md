# Laravel PoC Platform

Status: ready-for-agent

## Problem Statement

The developer wants a sandbox Laravel project to explore and learn Laravel 13's capabilities by building a series of proof-of-concept implementations, one per feature/pattern. Building every PoC directly in a shared `app/` tree makes concepts bleed into each other over time, and there is no way to demonstrate or compare a concept's "on" vs "off" behavior without editing code or redeploying. There is also no single place to see everything that has been built and its current status.

## Solution

Scaffold a Laravel 13 application (PHP 8.4, MySQL, Pest) running under Laravel Sail locally, with a docker-compose definition portable to a Coolify deployment later. Each concept lives in its own module (folder + `ServiceProvider`), registers itself in a central `ConceptRegistry`, and is gated behind a `laravel/pennant` feature flag named after the concept's slug. A dashboard route lists every registered concept grouped by category, shows each one's current flag state, and lets the developer flip it on/off live via a functional toggle. Each module ships its own Pest tests and a short `README.md` documenting what was learned. The first module built against this platform is Custom Casts & value objects, serving as the reference implementation for every module that follows.

## User Stories

1. As a developer, I want to scaffold a new concept as an isolated module following a fixed folder + ServiceProvider convention, so that no PoC's code, routes, or state can leak into another's.
2. As a developer, I want a module's ServiceProvider to register its own metadata (slug, name, description, category, demo route) into a central ConceptRegistry when it boots, so that I never have to maintain a second, hand-written list of concepts.
3. As a developer, I want a dashboard page listing every registered concept, so that I can see at a glance everything I've built and its current state.
4. As a developer, I want the dashboard to group concepts by their category (Search & AI, Real-time, DevOps / Observability, Performance & Security, Architecture), so that the backlog structure I agreed on is visible in the UI, not just in a doc.
5. As a developer, I want each concept's entry to show its Pennant feature flag state (on/off), so that I don't have to open `tinker` or read the database to know what's active.
6. As a developer, I want a functional toggle switch on each dashboard entry, so that I can activate or deactivate a concept's feature flag directly from the browser.
7. As a developer, I want toggled flag state to persist in the database (Pennant's default database driver), so that the on/off state survives across requests, deploys, and container restarts.
8. As a developer, I want to click through from a dashboard entry to that concept's own demo route, so that I can exercise the concept directly.
9. As a developer, I want a concept's demo route to check its own feature flag before rendering, so that turning a concept off actually blocks access to it, not just hides its dashboard link.
10. As a developer, I want a disabled concept's demo route to return a clear "not available" response instead of executing the concept's logic, so that the flag is a real gate and not cosmetic.
11. As a developer, I want the whole stack (app, MySQL, and any service a future module needs) to start with a single Sail command, so that local setup has no manual steps beyond `docker compose up`.
12. As a developer, I want the same docker-compose service definitions usable for a Coolify deployment, so that going to production later doesn't require re-architecting the environment.
13. As a developer, I want MySQL as the database engine (not SQLite), so that local behavior matches the intended production engine, including MySQL 9's native `VECTOR` type for future AI/ML modules.
14. As a developer, I want Pest as the test runner for the whole project, so that every module's tests share one consistent testing style.
15. As a developer, I want each module to ship with its own Pest test file(s) proving the concept's demonstrated behavior through its demo route, so that "proven" means "has a passing test," not just "has code."
16. As a developer, I want each module to ship a short `README.md` with what I learned building it, so that six months later I can recall the tradeoffs without re-reading the code.
17. As a developer, I want the Custom Casts & value objects module built as the first concept on this platform, so that it acts as the reference example every later module can copy the shape of.
18. As a developer, I want the platform's own mechanics (registry population, dashboard listing, toggle persistence, flag-gated access) covered by tests, so that the platform itself is trustworthy infrastructure, not just the modules built on top of it.
19. As a developer, I want new concept modules added to the registry without modifying any shared "index" file, so that adding module #20 never risks a merge conflict with module #3.
20. As a developer, I want the agreed module backlog and its 5-category, difficulty-ordered structure recorded somewhere durable, so that future work sessions know what to build next without re-deriving the plan.

## Implementation Decisions

- **Stack**: Laravel 13.x, PHP 8.4, `laravel new` starter kit "None" (ships Blade + Tailwind + Vite by default), MySQL, Pest as the test runner.
- **Local runtime**: Laravel Sail (official Docker dev environment) on WSL; the same docker-compose service definitions are the intended basis for a future Coolify deployment. No CI/CD automation for that deployment is part of this spec (see Out of Scope).
- **Feature flags**: `laravel/pennant`, the only first-party Laravel feature-flag package. Default database driver (persists to the `features` table Pennant's own migration creates). One flag per concept, keyed by the concept's slug.
- **Module convention**: one folder per concept under a dedicated modules directory, each with its own `ServiceProvider` registered in `bootstrap/providers.php`. No third-party module package (e.g. `nwidart/laravel-modules`) — folders + service providers only.
- **ConceptRegistry**: a central singleton bound by a core/platform service provider. Each module's `ServiceProvider::boot()` calls into the registry to register its own metadata: slug, display name, description, category, demo route name. The registry is the single source of truth the dashboard reads from — no second, manually maintained list of concepts anywhere in the codebase.
- **Dashboard**: one route + controller + Blade view. Reads the ConceptRegistry, groups entries by category, and for each entry queries `Feature::active($slug)` to render current state plus a toggle control.
- **Toggle mechanism**: a form/route (e.g. POST) per concept that calls `Feature::activate($slug)` / `Feature::deactivate($slug)`, then redirects back to the dashboard. This is a real write to Pennant's persisted state, not a cosmetic UI-only switch.
- **Gating enforcement**: each module's own demo route(s) check `Feature::active($slug)` (directly or via a shared middleware/gate helper) before executing the concept's logic, returning a non-200 "unavailable" response when the flag is off.
- **Backlog structure** (superseded 2026-08-24 — the original 24-concept, 5-category backlog below was replaced with a smaller catalog centered on embeddings/semantic search; each module is independent and flag-gated so it can be shown/hidden per audience):
  1. Search & AI — Custom Casts & value objects (done, ticket 04); semantic-search (text embeddings, MySQL 9 native `VECTOR`/`VECTOR_DISTANCE()` — confirm Sail's MySQL image is ≥9.0 before this module starts, done, ticket 07); image-similarity (image embeddings, reusing semantic-search's storage shape but functionally independent, done, ticket 13); image-text-search (cross-modal: text query → ranked seeded photos, reusing image-similarity's seeded photo set but its own embedder/flag, added 2026-08-26 as ticket 20, split out while building ticket 13 once it became clear the embedding spaces don't compose); rag-chat (chat UI with source citations, retrieval over the semantic-search index but its own flag); auto-classify (upload text/image → live-generated tags, single endpoint).
  2. Real-time — live-collab (shared editor, 2 browser sessions, Laravel Reverb, own channel); live-notifications (bell + feed, Reverb, separate channel from live-collab); presence (who's online / typing indicators, may share the Reverb connection but separate logic); job-progress (large file upload → live progress bar via queue + job + broadcast).
  3. DevOps / Observability — horizon-dashboard (Horizon enabled + a menu link, near-zero custom code); audit-log (activity feed table, optionally logs events from other modules or stands alone on a demo CRUD).
  4. Performance & Security — cache-benchmark (run-query button with/without cache, timings shown); rate-limit-demo (spam button + visual counter + throttle message, custom middleware on a dedicated route); signed-url-expiry (download link + visual countdown, fully isolated).
  5. Architecture — multi-tenancy (workspace switch dropdown; heaviest module architecturally, likely needs its own schema/scoping, isolated from the rest).
  - `feature-flags-admin` (the "control panel for every module" idea from the original catalog) is **already built** — it's exactly `ConceptRegistry` + the `/concepts` dashboard from tickets 01–03. Not a new module.
- **Implementation order** (agreed 2026-08-24): `feature-flags-admin` and Custom Casts & value objects are done (tickets 01–04) → cache-benchmark → rate-limit-demo → semantic-search → job-progress → then the rest, roughly: horizon-dashboard, signed-url-expiry, audit-log, image-similarity, rag-chat, auto-classify, live-notifications, presence, live-collab, multi-tenancy (last — heaviest architecturally). `image-text-search` (ticket 20, added 2026-08-26) slots in near `image-similarity`, whenever picked up next — not yet placed in the strict order above.

## Testing Decisions

- Good tests here assert on externally observable behavior only: HTTP response status/content and persisted flag state — never on internal registry data structures or private ServiceProvider wiring.
- **Single seam for everything**: Pest HTTP feature tests, using Laravel's test client, hitting real routes — the dashboard route, the per-concept toggle route, and each module's own demo route(s). A concept's demo route is also where its behavior is demonstrated, so the same seam proves both the platform mechanics and each module's concept in one pass.
- Platform-level behavior to test: a registered module appears on the dashboard grouped under its category; toggling a concept flips and persists its Pennant state; a disabled concept's demo route is blocked while an enabled one executes normally.
- Module-level behavior to test: each module's demo route exercises and asserts on the concept it's proving (e.g. the Custom Casts module's demo route round-trips a value through its cast and the response reflects the cast's transformation).
- No prior art exists yet in this repo (greenfield) — the Custom Casts module's tests become the template subsequent modules copy.

## Out of Scope

- Implementing any module beyond Custom Casts & value objects and `feature-flags-admin` (already delivered by tickets 01–04) — each remaining module is future work, planned as its own follow-up ticket once the platform and the first module land.
- Automating the Coolify deployment (CI/CD pipeline, deploy hooks) — only the docker-compose portability is a goal here, not a working pipeline.
- Provisioning Redis, Meilisearch, Reverb, or any other service a later module needs — added only when the module that requires it is actually built.
- Authentication/authorization on the dashboard or toggle routes — this is a single-developer local sandbox for now.
- CI test automation — running Pest is a local/manual concern for this spec; wiring it into a pipeline is future work.

## Further Notes

- MySQL 9.0+ ships a native `VECTOR` type and `VECTOR_DISTANCE()` function — the chosen vector store for `semantic-search` and `image-similarity` (confirmed 2026-08-24, over pgvector/Postgres or Meilisearch, to keep a single database engine) — but this depends on which MySQL image Sail pulls by default, which needs verifying before `semantic-search` starts.
- `laravel/mcp` (Model Context Protocol) and the Laravel AI SDK are both Laravel-blog-endorsed as the current recommended tools for LLM-facing modules, ahead of building `rag-chat` or `auto-classify`.
- The seam and category decisions above were confirmed interactively during the `/grilling` session that preceded this spec; re-run `/grilling` only if the platform shape needs to change, not for each new concept module.
- The backlog was fully replaced on 2026-08-24 with the embeddings/semantic-search-centered catalog above; the original 24-concept, 5-category list (Search & Data / DevOps / Real-time / Arhitectură & Performanță / AI-ML) is superseded and no longer in effect except for Custom Casts & value objects, which had already shipped under it.
