# Laravel Concepts Platform

A sandbox Laravel app for building one proof-of-concept Laravel feature per **module**, each toggleable live via a feature flag.

## Glossary

- **Concept** — a single Laravel feature/pattern being demonstrated (e.g. "Custom Casts & value objects"). Identified by its **slug**, which doubles as its Pennant feature flag name.
- **Module** — the folder + `ServiceProvider` that implements one concept. See `docs/adr/0001-concept-module-convention.md`.
- **ConceptRegistry** (`App\Platform\ConceptRegistry`) — the single source of truth listing every registered concept. A singleton bound by `App\Platform\ConceptRegistryServiceProvider`. Modules register into it from their own `ServiceProvider::boot()`; nothing else maintains a second list.
- **ConceptRegistration** (`App\Platform\ConceptRegistration`) — the value object a module registers: slug, name, description, category, demo route name.
- **Category** — the backlog grouping a concept belongs to (e.g. "Search & Data"). Plain string on `ConceptRegistration`; the dashboard groups by it. The agreed 5-category backlog lives in `.scratch/laravel-poc-platform/spec.md`.
- **Demo route** — the route name where a concept's behavior is actually exercised. Each demo route checks its own flag (`Feature::active($slug)`) and refuses to run when the concept is off.
- **Dashboard** — `GET /concepts` (`concepts.dashboard`), lists every registered concept grouped by category with its live flag state and a toggle. `POST /concepts/{concept}/toggle` (`concepts.toggle`) flips a concept's flag.
