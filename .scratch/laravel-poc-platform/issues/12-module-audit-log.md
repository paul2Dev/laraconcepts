# 12 — Concept module: audit-log

**What to build:** The developer sees "audit-log" on the dashboard under DevOps / Observability, toggles it on, and on the demo page performs simple CRUD actions against a demo resource, watching each action appear immediately in an activity feed table underneath.

**Blocked by:** None — can start immediately (platform already shipped in tickets 01–04)

**Status:** ready-for-agent

- [ ] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`, including its own migration(s) for a demo CRUD resource and its audit log table
- [ ] The `ServiceProvider` registers the module into the `ConceptRegistry` under the DevOps / Observability category, with a slug used as its Pennant flag name
- [ ] A demo route/controller offers simple create/update/delete actions on a demo resource; each action writes an audit log entry (actor, action, subject, timestamp) before returning
- [ ] A second demo route renders the audit log feed, newest entry first
- [ ] Both demo routes check the module's flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [ ] A Pest HTTP feature test performs a CRUD action with the flag on and asserts a corresponding audit log entry is persisted and appears in the feed response
- [ ] A Pest HTTP feature test hits either demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [ ] A README.md inside the module's folder documents how the module was implemented and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [ ] The module appears correctly on the dashboard (grouped under DevOps / Observability, toggleable) with no changes needed to the dashboard or registry code itself
