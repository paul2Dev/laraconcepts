# 06 — Concept module: rate-limit-demo

**What to build:** The developer sees "rate-limit-demo" on the dashboard under Performance & Security, toggles it on, and on the demo page can spam a button that hits a rate-limited route — a visible counter tracks requests, and once the limit is hit, the response clearly signals a 429 throttle instead of silently succeeding.

**Blocked by:** None — can start immediately (platform + reference module already shipped in tickets 01–04)

**Status:** ready-for-agent

- [ ] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [ ] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Performance & Security category, with a slug used as its Pennant flag name
- [ ] A demo route is gated by a custom rate-limiting middleware (a dedicated limiter, not the app's global default) with a low, demo-friendly threshold (e.g. 5 requests/10s)
- [ ] The demo route checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off — independent of the throttle's own 429
- [ ] A Pest HTTP feature test hits the demo route repeatedly with the flag on and asserts the request under the limit succeeds while the request past the limit returns 429
- [ ] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [ ] A README.md inside the module's folder documents how the module was implemented and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [ ] The module appears correctly on the dashboard (grouped under Performance & Security, toggleable) with no changes needed to the dashboard or registry code itself
