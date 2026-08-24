# 19 — Concept module: multi-tenancy

**What to build:** The developer sees "multi-tenancy" on the dashboard under Architecture, toggles it on, and on the demo page can switch between seeded workspaces via a dropdown, seeing a demo resource's data change to match the selected workspace — proving data is genuinely scoped per tenant, not just filtered client-side.

**Blocked by:** None — can start immediately (isolated per spec.md; sequenced last for being the heaviest architecturally, not for any technical dependency)

**Status:** ready-for-agent

- [ ] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`, including its own migration(s) for a `workspaces` table and a demo resource scoped by `workspace_id`
- [ ] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Architecture category, with a slug used as its Pennant flag name
- [ ] A demo route/page offers a workspace-switch dropdown (seeded with 2+ demo workspaces) and lists the demo resource's records, scoped to the currently selected workspace only
- [ ] Tenant scoping is enforced at the query layer (e.g. a global scope keyed to the current workspace), not by filtering an unscoped result set in the controller
- [ ] The demo route checks the module's flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [ ] A Pest HTTP feature test switches workspace with the flag on and asserts only that workspace's records are returned, proving cross-tenant data never leaks
- [ ] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [ ] A README.md inside the module's folder documents how the module was implemented (including the scoping mechanism chosen) and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [ ] The module appears correctly on the dashboard (grouped under Architecture, toggleable) with no changes needed to the dashboard or registry code itself
