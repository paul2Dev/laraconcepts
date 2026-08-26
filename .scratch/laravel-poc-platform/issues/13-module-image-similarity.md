# 13 — Concept module: image-similarity

**What to build:** The developer sees "image-similarity" on the dashboard under Search & AI, toggles it on, and on the demo page uploads an image to see a grid of the most visually similar images from a small seeded demo set.

**Blocked by:** None — can start immediately (functionally independent of ticket 07's semantic-search; may informally reuse its `VECTOR`-column storage shape, per spec.md)

**Status:** done

- [x] A small seeded dataset (10–30 images) exists, each with an image embedding (e.g. via a CLIP-style model) stored in a `VECTOR` column
- [x] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`, including its own migration(s) for the seeded image table
- [x] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Search & AI category, with a slug used as its Pennant flag name
- [x] A demo route accepts an uploaded image, embeds it with the same model used for the seeded set, and returns the N nearest seeded images by `VECTOR_DISTANCE()`
- [x] The demo route checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [x] A Pest HTTP feature test uploads an image with the flag on and asserts the response's nearest matches are ordered by ascending distance
- [x] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [x] A README.md inside the module's folder documents how the module was implemented (including which image-embedding approach was chosen and why) and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [x] The module appears correctly on the dashboard (grouped under Search & AI, toggleable) with no changes needed to the dashboard or registry code itself

## Comments

Reused ticket 07's (`semantic-search`) already-established fallback rather than
re-deriving it: `VECTOR_DISTANCE()` is MySQL HeatWave-only and unavailable on the
self-hosted `mysql:9` image this platform runs, so ranking is computed in PHP
(cosine distance) instead, same as `semantic-search`. The `VECTOR` column is still
used for storage, per the ticket's "reusing semantic-search's storage shape" note —
`VectorCast` is duplicated into this module rather than shared, per the module
isolation convention.

No CLIP model or embedding API is wired into this platform yet (same constraint as
ticket 07), so the module uses a hand-rolled `ImageEmbedder`: downsamples the image to
a 4×4 grid via GD and reads each cell's RGB, producing a 48-dim vector.

First pass used synthetic GD-generated color squares for the seed set, purely to keep
the Pest suite offline. Replaced on request with 16 real photos (Unsplash, via Lorem
Picsum, Unsplash License) bundled into `resources/seed-images/`, picked in four
visually-coherent clusters (forest greens, cool water/sky, golden hour, brown-toned
animals/rustic) so the coarse color-grid embedder still produces a believable ranking.
Bundling static files keeps the same offline/reproducible property as the synthetic
approach — the seeder reads from disk, no network call at seed or test time. Full
reasoning and photo credits in `app/Modules/ImageSimilarity/README.md`.
