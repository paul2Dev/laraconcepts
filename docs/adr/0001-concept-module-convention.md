# 1. Concept module convention

## Status

Accepted

## Context

Every proof-of-concept built on this platform needs to be isolated from every other one, and needs to show up on the dashboard (see ticket `03-concept-registry-dashboard-toggle`) without hand-editing a shared index file each time a new concept is added.

## Decision

- Each concept lives in its own folder under `app/Modules/<ConceptName>/`, with its own `<ConceptName>ServiceProvider`, registered in `bootstrap/providers.php`.
- No third-party module package (e.g. `nwidart/laravel-modules`) is used — folders + native Laravel service providers only.
- A module's `ServiceProvider::boot()` resolves `App\Platform\ConceptRegistry` (bound as a singleton by `App\Platform\ConceptRegistryServiceProvider`, one of the core platform providers) and calls `register()` with an `App\Platform\ConceptRegistration`: slug, display name, description, category, demo route name.
- The concept's slug is also its Pennant feature flag name — no separate flag-naming scheme.
- The module's own demo route checks `Feature::active($slug)` before running its logic, returning a non-200 response when the flag is off. Gating is the demo route's own responsibility, not the registry's or the dashboard's.
- The dashboard (`App\Http\Controllers\ConceptDashboardController`) reads `ConceptRegistry` and never hand-lists concepts — adding a module never touches dashboard or registry code.

## Consequences

- Adding concept #N means adding one folder + one `ServiceProvider` + one `bootstrap/providers.php` line. No shared file is edited beyond that provider list, so two modules built in parallel can't conflict.
- The registry only knows what's booted in the current request, so a concept that forgets to register itself simply won't appear on the dashboard — there's no separate validation step enforcing registration.
