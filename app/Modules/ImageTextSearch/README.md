# Image Text Search

## What it demonstrates

`GET /concepts/image-text-search/demo?query=...` behind the `image-text-search` flag
takes a plain text query, embeds it, and ranks the same 16-photo seeded set
`image-similarity` uses by ascending distance — closest (most relevant) first. Hitting
the route with no query string still proves the point on the dashboard's plain demo
link: it defaults to `query=golden hour over a field`, and the nearest result is
"Sunset Field", a photo whose filename/label share only the word "field" with the
query — "golden" and "hour" retrieve it purely through the embedding, not a substring
match. The flag-off Pest test proves the gate is real the same way every other
module's does: same route, flag off, `503` with no query run at all.

## How it works

**The crux: text and images share one vector space, not two.** `semantic-search`'s
`ConceptEmbedder` only ever sees text; `image-similarity`'s `ImageEmbedder` only ever
sees image bytes. Neither can be reused here, because a query and a photo need to land
somewhere directly comparable. `CrossModalEmbedder` solves this by exposing exactly one
embedding function, `embed(string $text): array`, and feeding *both* modalities through
it: a live query goes in as typed, and each seeded photo goes in as a short
hand-written caption (see `SeededImageSeeder::CAPTIONS`) describing what's in the
photo, in different words than its own filename/label. Both land in the same
4-dimension space — one dimension per hand-picked visual theme (forest/green,
water/sky/snow, golden hour, animals/rustic, the same four clusters
`image-similarity`'s README documents) — via the exact keyword-bucket-and-normalize
trick `ConceptEmbedder` already uses for text alone.

**This is the "faked" half of the cross-modal claim, and it's worth being honest about
it.** A real CLIP model learns the image side directly from pixels, trained on millions
of (image, caption) pairs, and would need neither a hand-picked cluster vocabulary nor
a caption written by a human. This module never reads a single pixel for the embedding
itself — the bundled JPEGs are decoded only to be re-encoded as a display thumbnail
(same base64-data-URI storage `image-similarity` uses). The "cross-modal" property is
real (query text and photo captions genuinely land in one shared, comparable space,
and a query can retrieve a photo with zero literal word overlap with its filename), but
the "image understanding" behind it is a human-written caption standing in for what a
trained model would infer. Swapping in real CLIP later means replacing
`SeededImageSeeder`'s caption lookup with an actual image encoder and giving
`CrossModalEmbedder::embed()` an image-bytes overload — the model, cast, and searcher
only depend on "input in, float array out," same as the other two modules' embedders.

**Corpus reuse, independent flag.** The 16 JPEGs are the same files
`image-similarity` bundles (copied into this module's own `resources/seed-images/`,
not shared at runtime, per the module-isolation convention — same reasoning
`image-similarity` used when it duplicated `semantic-search`'s `VectorCast` rather than
importing it). Storage shape (`label`, `image`, `embedding` columns; lazy seeding on
first request) is identical to `image-similarity`'s table, just under its own name
(`image_text_search_images`) and gated by its own flag, per the "reuse the storage/
dataset shape, keep the flag independent" precedent in `spec.md`.

## Testing

- `sail artisan test --filter=ImageTextSearchDemoTest` — a query ("trees by calm
  water") chosen so it shares no words with its target's filename/label ("Lake
  Forest") and wins by a clear margin over every other seeded photo, asserted in
  ascending-distance order; the flag-off gate; the dashboard listing.
- Manually: toggle "Image Text Search" on from `/concepts`, open its demo link, and
  try a few queries — "cool misty mountain air" (→ Snow Peaks), "a farm animal
  outdoors" (→ Leopard Road / Highland Cow / Black Puppy, a near-tie), or the default
  "golden hour over a field".

## Notes

Within a cluster, captions that only ever hit that one cluster's keywords produce
*identical* normalized vectors (a one-hot direction has no notion of magnitude), so
some queries land in an exact tie between two or three photos in the same theme — the
"farm animal" example above is one. That's an honest property of a 4-dimension space
built from keyword counts, not a bug; captions were deliberately written to blend a
second, minor theme into each photo (e.g. Lake Forest's caption mixes forest and water
words) specifically so at least one clean, unambiguous query exists per photo for
demo/testing purposes, without claiming the whole seeded set is pairwise distinguishable.

Picking a test query that shares zero words with its target's filename/label was
deliberate, not incidental — a query that happens to overlap (e.g. "golden hills") would
pass even with a naive `LIKE`-based fallback and wouldn't prove the embedding is doing
anything.
