# 18 — Concept module: live-collab

**What to build:** The developer sees "live-collab" on the dashboard under Real-time, toggles it on, and opening the demo page's shared text editor in two browser sessions shows edits made in one appear live in the other, over its own Reverb channel.

**Blocked by:** 08 — Install and configure Laravel Reverb

**Status:** done

- [x] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [x] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Real-time category, with a slug used as its Pennant flag name
- [x] A demo route/page offers a shared text editor backed by a demo document; edits broadcast on a Reverb channel scoped to that document, distinct from any other real-time module's channel
- [x] Two sessions open on the same demo document see each other's edits applied live, without a page refresh
- [x] The demo route/page checks the module's flag (`Feature::active($slug)`) before allowing edits, returning a clear non-200 "unavailable" response when the flag is off
- [x] A Pest feature test submits an edit with the flag on and asserts it's broadcast on the document's channel (via Laravel's broadcast assertion helpers)
- [x] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [x] A README.md inside the module's folder documents how the module was implemented (including how concurrent edits are reconciled, if at all) and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [x] The module appears correctly on the dashboard (grouped under Real-time, toggleable) with no changes needed to the dashboard or registry code itself
