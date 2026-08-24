# 16 — Concept module: live-notifications

**What to build:** The developer sees "live-notifications" on the dashboard under Real-time, toggles it on, and on the demo page triggers a notification (e.g. a button) that appears instantly in a bell icon + feed, over its own Reverb channel, without a page refresh.

**Blocked by:** 08 — Install and configure Laravel Reverb

**Status:** ready-for-agent

- [ ] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [ ] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Real-time category, with a slug used as its Pennant flag name
- [ ] A demo route triggers a notification (Laravel's notification system, broadcast channel) on a channel distinct from any other real-time module's channel
- [ ] The demo page subscribes via Echo and renders incoming notifications live in a bell icon + feed UI, unread count included
- [ ] The demo route checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [ ] A Pest feature test triggers a notification with the flag on and asserts it's broadcast on the module's own channel (via Laravel's notification/broadcast assertion helpers)
- [ ] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [ ] A README.md inside the module's folder documents how the module was implemented and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [ ] The module appears correctly on the dashboard (grouped under Real-time, toggleable) with no changes needed to the dashboard or registry code itself
