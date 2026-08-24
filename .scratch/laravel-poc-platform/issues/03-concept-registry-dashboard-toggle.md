# 03 — Concept Registry and dashboard with live flag toggle

**What to build:** The developer opens a dashboard page and sees every registered concept module, grouped by its category, each showing its current Pennant flag state with a working toggle that flips and persists that state live — all without ever hand-maintaining a second list of concepts anywhere in the codebase.

**Blocked by:** 02 — Install and configure Laravel Pennant

**Status:** ready-for-agent

- [ ] A central `ConceptRegistry` exists (bound by a core/platform service provider) that any module's `ServiceProvider` can register itself into during `boot()`, supplying: slug, display name, description, category, demo route name
- [ ] The module convention (one folder per concept, each with its own `ServiceProvider` registered in `bootstrap/providers.php`) is established and documented — no third-party module package involved
- [ ] A dashboard route + controller + Blade view reads the `ConceptRegistry` and renders every registered concept grouped by category
- [ ] Each dashboard entry shows its live Pennant flag state (via `Feature::active($slug)`)
- [ ] Each dashboard entry has a functional toggle (a form/route) that calls `Feature::activate($slug)` / `Feature::deactivate($slug)` and redirects back, with the new state visible immediately and persisted in the database
- [ ] A Pest HTTP feature test registers a test-only fixture concept (not a real production module), hits the dashboard route, and asserts the fixture appears grouped under its category with the correct flag state
- [ ] The same or a companion test posts to the fixture's toggle route and asserts the flag's persisted state actually flips
