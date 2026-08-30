# 17 — Concept module: presence

**What to build:** The developer sees "presence" on the dashboard under Real-time, toggles it on, and opening the demo page in two browser sessions shows each session the other as "online," with a typing indicator that appears live while the other session is composing.

**Blocked by:** 08 — Install and configure Laravel Reverb

**Status:** done

- [x] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [x] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Real-time category, with a slug used as its Pennant flag name
- [x] A demo route/page joins a Reverb presence channel scoped to this module (may share the underlying Reverb connection with other modules, but its own channel and logic)
- [x] The demo page renders the live roster of who's currently present, and broadcasts/renders a typing indicator while a session is composing input
- [x] The demo route/page checks the module's flag (`Feature::active($slug)`) before allowing a session to join, returning a clear non-200 "unavailable" response when the flag is off
- [x] A Pest feature test asserts joining the presence channel is rejected/blocked when the flag is off
- [x] A Pest feature test (or a documented manual two-session check, if presence state genuinely can't be asserted through Pest's HTTP client) demonstrates the join/typing behavior when the flag is on
- [x] A README.md inside the module's folder documents how the module was implemented, including how presence state was actually tested, and how to exercise it manually — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [x] The module appears correctly on the dashboard (grouped under Real-time, toggleable) with no changes needed to the dashboard or registry code itself
