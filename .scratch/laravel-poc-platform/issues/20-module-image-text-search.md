# 20 — Concept module: image-text-search

**What to build:** The developer sees "image-text-search" on the dashboard under Search & AI, toggles it on, and on the demo page types a text query (e.g. "golden hour over a field") to see a grid of the seeded photos ranked by how well they match the query — proving a word or phrase can retrieve visually relevant images without any literal keyword overlap in a filename or label.

**Blocked by:** None — can start immediately (functionally independent; may informally reuse `image-similarity`'s (ticket 13) seeded photo set as the search corpus, per the same "reuse the storage/dataset shape, keep the flag independent" precedent spec.md already establishes between `semantic-search` and `image-similarity`)

**Status:** done

- [x] A hand-rolled cross-modal embedder maps both text and images into the *same* vector space — a real CLIP model is out of scope per this platform's no-embedding-API constraint (see ticket 07's and ticket 13's READMEs for the established precedent of hand-rolled stand-ins). This is the crux of the module: unlike `semantic-search`'s text-only `ConceptEmbedder` or `image-similarity`'s image-only `ImageEmbedder`, a text query and an image embedding must land in a comparable space here, not two incompatible ones
- [x] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [x] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Search & AI category, with a slug used as its Pennant flag name
- [x] A demo route accepts a text query, embeds it with the same cross-modal embedder used for the seeded images, and returns the N nearest seeded images ordered by ascending distance
- [x] The demo route checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [x] A Pest HTTP feature test submits a text query designed to match a known seeded image (e.g. a word describing its cluster/theme) with the flag on, and asserts that image is the nearest result, in ascending-distance order
- [x] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [x] A README.md inside the module's folder documents how the module was implemented — specifically how the cross-modal embedding space was constructed (or faked) and what its limitations are compared to a real CLIP model — and how to exercise/test it; this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [x] The module appears correctly on the dashboard (grouped under Search & AI, toggleable) with no changes needed to the dashboard or registry code itself

## Comments

Split out of a conversation while building ticket 13 (`image-similarity`): the two
concepts looked similar at first glance (both search a seeded photo set) but need
incompatible embedding spaces — `image-similarity`'s grid-color embedder and
`semantic-search`'s synonym-lookup embedder can't be reused as-is for "type a word,
get back photos," since neither produces a representation the other's inputs can be
compared against. That's real, separate design work (a joint text/image embedding),
not a small addition to either existing module, hence its own flag and ticket rather
than folding into `image-similarity`.

Whole-image average color turned out too weak a signal to separate the 16 real seeded
photos into their four themed clusters (verified empirically: leave-one-out
nearest-centroid classification on the raw grid-color vectors scored 6/16) — real
stock photos vary too much in exposure and framing for a coarse color heuristic to
carry cluster meaning reliably. Went with a hand-written caption per seeded photo
instead (`SeededImageSeeder::CAPTIONS`, worded differently than each photo's own
filename/label) as the stand-in for what a real CLIP model would learn from an
(image, caption) pair. `CrossModalEmbedder::embed()` is a single function shared by
both modalities — a caption and a live query are embedded identically, through the
same fixed keyword-bucket space `ConceptEmbedder` (semantic-search) already uses for
text alone. This is disclosed as a deliberate limitation in the module README: no
pixel is ever read for the embedding itself, only for the display thumbnail.

Corpus reused from `image-similarity` per the ticket's suggested precedent: same 16
JPEGs, copied into this module's own `resources/seed-images/` (not shared at runtime)
and re-seeded into an independent `image_text_search_images` table, same
module-isolation convention `image-similarity` already established by duplicating
`semantic-search`'s `VectorCast`.

On request, reworked the demo page after the first pass: it now lists the full 16-photo
seeded set on load (empty search box, placeholder shows an example query) and narrows
to only the matched results once a query is submitted, mirroring `semantic-search`'s
existing "no query → show everything" behavior (`ArticleSearcher::all()`) rather than
inventing a new pattern.
