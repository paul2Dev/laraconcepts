# 03 — Concept Registry and dashboard with live flag toggle

**What to build:** The developer opens a dashboard page and sees every registered concept module, grouped by its category, each showing its current Pennant flag state with a working toggle that flips and persists that state live — all without ever hand-maintaining a second list of concepts anywhere in the codebase.

**Blocked by:** 02 — Install and configure Laravel Pennant

**Status:** done

- [x] A central `ConceptRegistry` exists (bound by a core/platform service provider) that any module's `ServiceProvider` can register itself into during `boot()`, supplying: slug, display name, description, category, demo route name
- [x] The module convention (one folder per concept, each with its own `ServiceProvider` registered in `bootstrap/providers.php`) is established and documented — no third-party module package involved
- [x] A dashboard route + controller + Blade view reads the `ConceptRegistry` and renders every registered concept grouped by category
- [x] Each dashboard entry shows its live Pennant flag state (via `Feature::active($slug)`)
- [x] Each dashboard entry has a functional toggle (a form/route) that calls `Feature::activate($slug)` / `Feature::deactivate($slug)` and redirects back, with the new state visible immediately and persisted in the database
- [x] A Pest HTTP feature test registers a test-only fixture concept (not a real production module), hits the dashboard route, and asserts the fixture appears grouped under its category with the correct flag state
- [x] The same or a companion test posts to the fixture's toggle route and asserts the flag's persisted state actually flips

## Comments

- `ConceptRegistry`/`ConceptRegistration` live in `app/Platform/`, kept separate from the future `app/Modules/` tree per the ADR. Module convention documented in `CONTEXT.md` + `docs/adr/0001-concept-module-convention.md`.
- Fixture concept for the tests lives at `tests/Fixtures/Concepts/FixtureConceptServiceProvider.php` — registers into the real `ConceptRegistry` from `boot()` and gates its own demo route on its flag, mirroring what a real module will do.
- Full suite: 9/9 passing (`sail artisan test`), Pint clean. `/code-review` caught one real issue (fixture demo route wasn't gated per the ADR) and it was fixed before commit.
