# 09 — Concept module: job-progress

**What to build:** The developer sees "job-progress" on the dashboard under Real-time, toggles it on, and on the demo page uploads a large-ish file — a queued job processes it (e.g. chunked line/row counting) while broadcasting progress updates the page renders as a live progress bar, without polling.

**Blocked by:** 08 — Install and configure Laravel Reverb

**Status:** done

- [x] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [x] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Real-time category, with a slug used as its Pennant flag name
- [x] A demo route accepts a file upload and dispatches a queued job that processes it in chunks, broadcasting a progress event (percentage complete) after each chunk on a channel scoped to that upload
- [x] The demo page subscribes to that channel via Echo and renders a live-updating progress bar as events arrive
- [x] The demo route checks its own flag (`Feature::active($slug)`) before accepting an upload, returning a clear non-200 "unavailable" response when the flag is off
- [x] A Pest feature test dispatches the job directly (or via the route) with the flag on and asserts progress events are broadcast with increasing percentages, ending at 100
- [x] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [x] A README.md inside the module's folder documents how the module was implemented and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [x] The module appears correctly on the dashboard (grouped under Real-time, toggleable) with no changes needed to the dashboard or registry code itself
