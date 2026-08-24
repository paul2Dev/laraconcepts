# 04 — Concept module: Custom Casts & value objects

**What to build:** The first real concept on the platform. The developer sees "Custom Casts & value objects" on the dashboard under Search & Data, can toggle it on, follow its demo link, and watch a value round-trip through a custom Eloquent cast in the response — and when the flag is off, the same route refuses to run the concept's logic. This module is the reference shape every later module (2 through 24) copies.

**Blocked by:** 03 — Concept Registry and dashboard with live flag toggle

**Status:** done

- [x] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [x] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Search & Data category, with a slug used as its Pennant flag name
- [x] A demo route/controller exercises a custom Eloquent cast (a value object cast, not a scalar cast) round-tripping through a model attribute, and the response reflects the cast's transformation
- [x] The demo route checks its own flag (`Feature::active($slug)`) before running the concept's logic, returning a clear non-200 "unavailable" response when the flag is off
- [x] A Pest HTTP feature test hits the demo route with the flag on and asserts the cast's round-trip behavior in the response
- [x] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [x] A short `README.md` inside the module's folder documents what was learned building the custom cast (tradeoffs, gotchas — not a restatement of the code)
- [x] The module appears correctly on the dashboard from ticket 03 (grouped under Search & Data, toggleable) with no changes needed to the dashboard or registry code itself

## Comments

- `Money` is a value object backed by two DB columns (`price_amount`, `price_currency`) mapped through `MoneyCast` (`Illuminate\Contracts\Database\Eloquent\CastsAttributes`), not `Attribute::make()` — the multi-column mapping is exactly what forces the older cast interface. Lives at `app/Modules/CustomCasts/`, migration loaded from the module's own `database/migrations/` folder via `loadMigrationsFrom()`.
- Demo route: `GET /concepts/custom-casts/demo` (`custom-casts.demo`), creates a `Product`, reloads it fresh from the DB (forcing `MoneyCast::get()` to actually run) and returns the round-tripped `Money` in JSON.
- Full suite: 12/12 passing (`sail artisan test`), Pint clean. `/code-review` ran both axes clean (no hard violations); fixed two judgement calls before commit — the Pennant slug was hardcoded in both the `ServiceProvider` and the controller (now a single `SLUG` constant), and `MoneyCast::get()` had a defensive null-check for a DB state the non-nullable migration columns make unreachable (removed).
