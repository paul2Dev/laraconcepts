# 20 — Concept module: image-text-search

**What to build:** The developer sees "image-text-search" on the dashboard under Search & AI, toggles it on, and on the demo page types a text query (e.g. "golden hour over a field") to see a grid of the seeded photos ranked by how well they match the query — proving a word or phrase can retrieve visually relevant images without any literal keyword overlap in a filename or label.

**Blocked by:** None — can start immediately (functionally independent; may informally reuse `image-similarity`'s (ticket 13) seeded photo set as the search corpus, per the same "reuse the storage/dataset shape, keep the flag independent" precedent spec.md already establishes between `semantic-search` and `image-similarity`)

**Status:** ready-for-agent

- [ ] A hand-rolled cross-modal embedder maps both text and images into the *same* vector space — a real CLIP model is out of scope per this platform's no-embedding-API constraint (see ticket 07's and ticket 13's READMEs for the established precedent of hand-rolled stand-ins). This is the crux of the module: unlike `semantic-search`'s text-only `ConceptEmbedder` or `image-similarity`'s image-only `ImageEmbedder`, a text query and an image embedding must land in a comparable space here, not two incompatible ones
- [ ] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [ ] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Search & AI category, with a slug used as its Pennant flag name
- [ ] A demo route accepts a text query, embeds it with the same cross-modal embedder used for the seeded images, and returns the N nearest seeded images ordered by ascending distance
- [ ] The demo route checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [ ] A Pest HTTP feature test submits a text query designed to match a known seeded image (e.g. a word describing its cluster/theme) with the flag on, and asserts that image is the nearest result, in ascending-distance order
- [ ] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [ ] A README.md inside the module's folder documents how the module was implemented — specifically how the cross-modal embedding space was constructed (or faked) and what its limitations are compared to a real CLIP model — and how to exercise/test it; this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [ ] The module appears correctly on the dashboard (grouped under Search & AI, toggleable) with no changes needed to the dashboard or registry code itself

## Comments

Split out of a conversation while building ticket 13 (`image-similarity`): the two
concepts looked similar at first glance (both search a seeded photo set) but need
incompatible embedding spaces — `image-similarity`'s grid-color embedder and
`semantic-search`'s synonym-lookup embedder can't be reused as-is for "type a word,
get back photos," since neither produces a representation the other's inputs can be
compared against. That's real, separate design work (a joint text/image embedding),
not a small addition to either existing module, hence its own flag and ticket rather
than folding into `image-similarity`.
