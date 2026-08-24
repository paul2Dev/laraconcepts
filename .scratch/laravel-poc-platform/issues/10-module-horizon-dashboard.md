# 10 — Concept module: horizon-dashboard

**What to build:** The developer sees "horizon-dashboard" on the dashboard under DevOps / Observability, toggles it on, and gets a working link into Laravel Horizon's own dashboard for monitoring queues — the lightest module on the platform, mostly installation and a gate around visibility, no custom UI of its own.

**Blocked by:** None — can start immediately (platform already shipped in tickets 01–04)

**Status:** ready-for-agent

- [ ] `laravel/horizon` is installed, configured, and Redis is added as the queue connection Horizon supervises (added as its own service in the Sail docker-compose definition)
- [ ] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [ ] The `ServiceProvider` registers the module into the `ConceptRegistry` under the DevOps / Observability category, with a slug used as its Pennant flag name, and a demo route name pointing at (or redirecting to) Horizon's own dashboard route
- [ ] The demo route/redirect checks its own flag (`Feature::active($slug)`) before allowing access, returning a clear non-200 "unavailable" response when the flag is off — Horizon's own dashboard must not be reachable while the concept is toggled off
- [ ] A Pest HTTP feature test hits the demo route with the flag on and asserts it's reachable (200 or a redirect into Horizon)
- [ ] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [ ] A README.md inside the module's folder documents how the module was implemented (including the Redis/Horizon setup) and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [ ] The module appears correctly on the dashboard (grouped under DevOps / Observability, toggleable) with no changes needed to the dashboard or registry code itself
