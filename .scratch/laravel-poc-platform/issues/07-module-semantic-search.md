# 07 — Concept module: semantic-search

**What to build:** The developer sees "semantic-search" on the dashboard under Search & AI, toggles it on, and on the demo page searches a small seeded dataset (e.g. articles or products) with a toggle between plain keyword search and semantic (embedding-based) search — the two return visibly different result orderings, proving the embeddings are doing real work.

**Blocked by:** None — can start immediately (platform + reference module already shipped in tickets 01–04)

**Status:** done

- [x] Confirmed as a preflight check: the Sail MySQL image is ≥9.0 and supports the native `VECTOR` column type and `VECTOR_DISTANCE()` function (per spec.md's Further Notes) — if not, note the blocker and pick a fallback before continuing
- [x] A small seeded dataset (10–30 records) of demo content (articles or products) exists, each with a text embedding stored in a `VECTOR` column
- [x] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`, including its own migration(s) for the seeded table (mirroring ticket 04's `loadMigrationsFrom()` pattern)
- [x] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Search & AI category, with a slug used as its Pennant flag name
- [x] A demo route/controller accepts a query string and a `mode` (`keyword` or `semantic`); `keyword` mode does a plain `LIKE`/fulltext match, `semantic` mode embeds the query and ranks results by `VECTOR_DISTANCE()`
- [x] The demo route checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [x] A Pest HTTP feature test hits the demo route in `semantic` mode with the flag on and asserts a query returns a result that a plain keyword match would miss (proving the embeddings are doing real ranking work, not just decoration)
- [x] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [x] A README.md inside the module's folder documents how the module was implemented (including which embedding-generation approach was chosen and why) and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [x] The module appears correctly on the dashboard (grouped under Search & AI, toggleable) with no changes needed to the dashboard or registry code itself

## Comments

Preflight check failed on both counts: Sail was on `mysql:8.4` (no `VECTOR` type at all),
and even a throwaway `mysql:9` container confirmed `VECTOR_DISTANCE()`/`DISTANCE()` is
gated to MySQL HeatWave (Oracle Cloud) — no self-hosted MySQL image has it, at any
version. Fallback (confirmed with the user before touching shared Docker infra): bumped
`compose.yaml` to `mysql:9` for the native `VECTOR` column type (keeps the spec's
single-database-engine decision intact), and rank results by cosine similarity computed
in PHP instead of `VECTOR_DISTANCE()`. Full reasoning and the empirical evidence are in
`app/Modules/SemanticSearch/README.md`. Bumping the image required recreating the local
`sail-mysql` volume — a handful of pre-existing demo rows from earlier modules were lost
and regenerate themselves on first use.

No external embedding API is wired into this platform yet, so the module uses a
hand-rolled, deterministic `ConceptEmbedder` (a small synonym-to-category lookup table)
instead — keeps the Pest suite offline and reproducible. Also documented in the README,
along with the tradeoff and what it'd take to swap in a real embedding model later.
