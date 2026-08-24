# 13 — Concept module: image-similarity

**What to build:** The developer sees "image-similarity" on the dashboard under Search & AI, toggles it on, and on the demo page uploads an image to see a grid of the most visually similar images from a small seeded demo set.

**Blocked by:** None — can start immediately (functionally independent of ticket 07's semantic-search; may informally reuse its `VECTOR`-column storage shape, per spec.md)

**Status:** ready-for-agent

- [ ] A small seeded dataset (10–30 images) exists, each with an image embedding (e.g. via a CLIP-style model) stored in a `VECTOR` column
- [ ] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`, including its own migration(s) for the seeded image table
- [ ] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Search & AI category, with a slug used as its Pennant flag name
- [ ] A demo route accepts an uploaded image, embeds it with the same model used for the seeded set, and returns the N nearest seeded images by `VECTOR_DISTANCE()`
- [ ] The demo route checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [ ] A Pest HTTP feature test uploads an image with the flag on and asserts the response's nearest matches are ordered by ascending distance
- [ ] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [ ] A README.md inside the module's folder documents how the module was implemented (including which image-embedding approach was chosen and why) and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [ ] The module appears correctly on the dashboard (grouped under Search & AI, toggleable) with no changes needed to the dashboard or registry code itself
