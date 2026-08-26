# 11 — Concept module: signed-url-expiry

**What to build:** The developer sees "signed-url-expiry" on the dashboard under Performance & Security, toggles it on, and on the demo page generates a signed, time-limited download link with a visible countdown — following the link after it expires is visibly rejected instead of silently serving the file.

**Blocked by:** None — can start immediately (platform already shipped in tickets 01–04)

**Status:** done

- [x] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [x] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Performance & Security category, with a slug used as its Pennant flag name
- [x] A demo route generates a signed URL (Laravel's `URL::temporarySignedRoute()`) with a short expiry (e.g. 30–60s) pointing at a second, download-serving route; the demo page shows the link plus a live countdown to expiry
- [x] The download-serving route validates the signature/expiry itself (`hasValidSignature()`) and returns a clear non-200 response once expired or if the signature is tampered with
- [x] The demo route (link-generation) checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [x] A Pest HTTP feature test generates a signed link with the flag on and asserts the download route succeeds while the signature is valid
- [x] A Pest HTTP feature test asserts an expired (or tampered) signed link is rejected by the download route, and that the flag-off case on the generation route returns "unavailable"
- [x] A README.md inside the module's folder documents how the module was implemented and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [x] The module appears correctly on the dashboard (grouped under Performance & Security, toggleable) with no changes needed to the dashboard or registry code itself
