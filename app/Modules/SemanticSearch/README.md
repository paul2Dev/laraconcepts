# Semantic Search

## What it demonstrates

`GET /concepts/semantic-search/demo?query=...&mode=keyword|semantic` behind the
`semantic-search` flag searches a 16-product catalog (seeded lazily on first request via
`Article::query()->doesntExist()` — no manual `db:seed` step needed) two ways:

- `keyword` — a plain `LIKE '%query%'` against `title` and `body`.
- `semantic` — embeds the query with `ConceptEmbedder`, then ranks every article by
  cosine similarity (a plain dot product, since both vectors are already unit-normalized)
  computed in PHP, dropping zero-similarity matches.

Hitting the route with no query string at all still proves the point on the very first
click of the dashboard's plain demo link: it defaults to `query=notebook`,
`mode=semantic`. `keyword` mode on that word returns nothing — no product description
contains it literally — while `semantic` mode returns "UltraBook Pro 14" and "ChromeBook
Air", both of which describe a laptop/computer, which `notebook` maps to via the
embedder's synonym list.

## How it works

**A hand-rolled embedder instead of a real embedding API.** No embedding-generation
service (OpenAI, a local model via Ollama, etc.) is wired into this platform yet, and
adding one would mean an API key requirement and a network call on every seed/query —
neither is acceptable for a Pest suite that must run offline and deterministically.
`ConceptEmbedder` instead maps text to an 8-dimensional vector: each dimension is a
hand-picked product category (computing, audio, photography, fitness, kitchen, reading,
gaming, wearables), each with a short synonym list. A text's vector is just a normalized
count of which categories its words fall into.

This is a real embedding in the sense that matters for the demo — texts about the same
topic land close together in the vector space even when they don't share literal words —
but it's a hand-built lookup table, not a learned representation. The synonym lists are
deliberately generous (`notebook` is listed as a computing synonym even though no seeded
product description uses that word) so a query can hit a product through a synonym alone,
which is the whole point of proving semantic search does something keyword `LIKE` can't.
A real embedding model would learn this generalization from data instead of having it
hand-coded; swapping `ConceptEmbedder` for an API-backed embedder later is a one-file
change since `Article`, the cast, and the controller only depend on "text in, float array
out."

**The `VECTOR` column is just packed float32 bytes.** Laravel 13 already ships
`Blueprint::vector($column, $dimensions)` and grammar support for MySQL, but no Eloquent
cast to convert PHP arrays to and from it — that plumbing didn't exist yet, so this module
builds `VectorCast` from scratch, the same shape as `custom-casts`' `MoneyCast`.
Inspecting what `STRING_TO_VECTOR('[1,2,3]')` actually writes (`HEX(v)` →
`0000803F0000004000004040`) showed it's nothing more than three little-endian float32
values back to back — exactly what PHP's `pack('g*', ...$floats)` produces. So
`VectorCast::set()` packs the PHP array directly with no SQL function wrapping needed, and
`get()` reverses it with `unpack('g*', $value)`. No round-trip through
`STRING_TO_VECTOR`/`VECTOR_TO_STRING` at all — the column's wire format was reverse
engineered once, empirically, and now the cast talks to it directly.

## Testing

- `sail artisan test --filter=SemanticSearchDemoTest` — the keyword/semantic contrast on
  `notebook` (including the no-query-string default), the flag-off gate, and the
  dashboard listing.
- Manually: toggle "Semantic Search" on from `/concepts`, then follow its demo link
  directly, or compare `/concepts/semantic-search/demo?query=notebook&mode=keyword`
  (empty) against `?query=notebook&mode=semantic` (finds the laptops) in a browser or
  with `curl`.

## Notes

Getting a `VECTOR`-capable MySQL running locally took some doing — Sail's image had to
move from `8.4` to `9`, and `VECTOR_DISTANCE()` turned out to be MySQL HeatWave-only,
unavailable on any self-hosted image, which is why ranking happens in PHP above instead of
SQL.

phpMyAdmin can't browse this table — selecting the `embedding` column trips a known,
unresolved phpMyAdmin bug parsing MySQL 9's `VECTOR` type client-side (the query itself
succeeds on the server; phpMyAdmin's own result-parsing code is what fails). Query the
table with `sail artisan tinker`, `sail mysql`, or a phpMyAdmin SQL query that excludes
`embedding` instead.
