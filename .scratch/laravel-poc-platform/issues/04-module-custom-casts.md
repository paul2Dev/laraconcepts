# 04 — Concept module: Custom Casts & value objects

**What to build:** The first real concept on the platform. The developer sees "Custom Casts & value objects" on the dashboard under Search & Data, can toggle it on, follow its demo link, and watch a value round-trip through a custom Eloquent cast in the response — and when the flag is off, the same route refuses to run the concept's logic. This module is the reference shape every later module (2 through 24) copies.

**Blocked by:** 03 — Concept Registry and dashboard with live flag toggle

**Status:** ready-for-agent

- [ ] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [ ] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Search & Data category, with a slug used as its Pennant flag name
- [ ] A demo route/controller exercises a custom Eloquent cast (a value object cast, not a scalar cast) round-tripping through a model attribute, and the response reflects the cast's transformation
- [ ] The demo route checks its own flag (`Feature::active($slug)`) before running the concept's logic, returning a clear non-200 "unavailable" response when the flag is off
- [ ] A Pest HTTP feature test hits the demo route with the flag on and asserts the cast's round-trip behavior in the response
- [ ] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [ ] A short `README.md` inside the module's folder documents what was learned building the custom cast (tradeoffs, gotchas — not a restatement of the code)
- [ ] The module appears correctly on the dashboard from ticket 03 (grouped under Search & Data, toggleable) with no changes needed to the dashboard or registry code itself
