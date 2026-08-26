# Image Similarity

## What it demonstrates

`GET /concepts/image-similarity/demo` behind the `image-similarity` flag shows a
16-photo seeded demo set (real photographs, bundled in the repo — see below), rendered
as a thumbnail grid for reference. `POST` to the same path with a multipart `image`
field embeds the upload with the same embedder used for the seeded set, ranks every
seeded photo by cosine distance, and returns the 5 nearest as JSON, ordered ascending —
lowest distance (most similar) first. The seeded set is seeded lazily on first request
(`SeededImage::query()->doesntExist()`), same as `semantic-search`.

The 16 photos were deliberately picked in four visually-coherent clusters — forest
greens, cool water/sky/snow tones, warm golden-hour landscapes, and brown-toned
animal/rustic shots — so that even the simple color-grid embedder below produces a
believable "closest matches" ranking: uploading a photo from one cluster (or a crop/
re-save of a seeded photo itself) reliably surfaces the rest of that cluster ahead of
the other three.

## How it works

**A hand-rolled grid-color embedder instead of a real CLIP model.** Same rationale as
`semantic-search`'s `ConceptEmbedder` (see its README for the full argument): no
embedding-generation API is wired into this platform, and a real CLIP model would need
either a network call per request or a bundled ML runtime, neither acceptable for an
offline, deterministic Pest suite. `ImageEmbedder` instead resizes the image to a 4×4
grid with GD's `imagecopyresampled` (bilinear downsampling — each output pixel is a
blend of the source region it covers) and reads the RGB of each of the 16 cells,
producing a 48-dimension vector, L2-normalized. This captures coarse color *and*
layout — a photo with sky on top and ground below lands in a different part of the
vector space than one that's inverted — which a plain whole-image color average
wouldn't. It's a legitimate visual signature, not a learned representation: it ranks
photos by "looks like the same palette and composition," not "shows the same subject."
Two photos of different dogs in different settings won't necessarily rank close; two
photos of *anything* under the same golden-hour lighting will. That's an honest
limitation to point out when demoing this module, not a bug — swapping in a real CLIP
model later only means replacing `ImageEmbedder::embed()`, since the model/cast/
controller only depend on "image bytes in, float array out."

**Real bundled photos instead of synthetic placeholders.** `resources/seed-images/`
ships 16 JPEGs (240×240, ~220 KB total) sourced from [Lorem Picsum](https://picsum.photos)
/ [Unsplash](https://unsplash.com), free to use under the
[Unsplash License](https://unsplash.com/license) (see Notes for sourcing details and
photo credits). `SeededImageSeeder` derives each display label from its filename and
reads the file from disk, embeds it, and stores it as a base64 data URI alongside its
embedding — no network call at seed time, so the Pest suite stays offline and
reproducible.

**Distance, not similarity.** The demo returns `1 - cosine_similarity` per result
("distance": 0 means identical), matching the vocabulary MySQL's own `VECTOR_DISTANCE()`
uses (`'COSINE'` metric) — see `semantic-search`'s README for why the actual ranking
happens in PHP rather than through that SQL function: `VECTOR_DISTANCE()` is MySQL
HeatWave-only and unavailable on the self-hosted `mysql:9` image this platform runs.
The `VECTOR` column is still used for storage (same wire format, same `VectorCast`,
duplicated into this module rather than shared with `semantic-search` per the
module-isolation convention), just not for the ranking computation itself.

## Testing

- `sail artisan test --filter=ImageSimilarityDemoTest` — uploading a bundled seed photo
  and asserting it's its own nearest result in ascending-distance order, the flag-off
  gate, and the dashboard listing.
- Manually: toggle "Image Similarity" on from `/concepts`, open its demo link, and
  upload any image (a photo, a screenshot, one of the seeded JPEGs) to see the 5
  nearest seeded photos and their distances.

## Notes

Grid size (4×4) is a tradeoff: coarse enough that a photo and a differently-cropped
version of the same photo still land close together, fine enough to separate the four
seeded clusters from each other. It isn't tuned against anything beyond this specific
seeded set — a real embedding model wouldn't need this kind of manual dimension
picking.

An earlier draft of this module used GD-generated solid-color squares as the seed set
instead of real photos, entirely to keep the Pest suite deterministic. Bundling real
JPEGs turned out to keep that same property (the files are static, checked into the
repo, and the Pest suite uploads one of them directly rather than regenerating
anything) while giving a far more convincing demo — worth knowing if a future module
faces the same "synthetic vs. real fixture" choice.

Photo credits (Unsplash, via Picsum), grouped by the cluster described above:

| Cluster | Filenames | Photographer |
|---|---|---|
| Forest / green | forest-river, forest-path, lake-forest, standing-stones | Jerry Adney, James Forbes, Paul Jarvis, Joeri Römer |
| Water / sky / snow | sea-wake, city-bridge, snow-peaks, misty-pines | Fré Sonneveld, Anders Jildén, Go Wild, Sylwia Bartyzel |
| Golden hour | sunset-field, golden-hills, golden-hiker, orchard-sunset | Kenneth Thewissen, Daniel Genser, Danka & Peter, Jennifer Langley |
| Animals / rustic | leopard-road, highland-cow, black-puppy, rustic-shed | Martyn Seddon, Elias Carlsson, André Spieker, Alexander Shustov |
