# 14 — Concept module: rag-chat

**What to build:** The developer sees "rag-chat" on the dashboard under Search & AI, toggles it on, and on the demo page asks a question in a chat UI, getting back an answer grounded in the semantic-search module's seeded dataset, with the specific source(s) it drew from visibly cited/highlighted in the response.

**Blocked by:** 07 — Concept module: semantic-search (reuses its retrieval index/embeddings rather than rebuilding one from scratch)

**Status:** ready-for-agent

- [ ] The module lives in its own folder with its own `ServiceProvider`, registered in `bootstrap/providers.php`
- [ ] The `ServiceProvider` registers the module into the `ConceptRegistry` under the Search & AI category, with a slug used as its Pennant flag name
- [ ] A demo route accepts a question, retrieves the top-N most relevant records from semantic-search's seeded dataset (via its `VECTOR_DISTANCE()` retrieval), and passes them as context to an LLM call
- [ ] The response includes both the generated answer and which retrieved source(s) it was grounded in (e.g. record IDs/titles), rendered as visible citations in the demo UI
- [ ] The demo route checks its own flag (`Feature::active($slug)`) before running, returning a clear non-200 "unavailable" response when the flag is off
- [ ] A Pest HTTP feature test asks a question with the flag on (mocking/faking the LLM call) and asserts the response includes both an answer and at least one cited source
- [ ] A Pest HTTP feature test hits the demo route with the flag off and asserts the "unavailable" response, proving the gate is real
- [ ] A README.md inside the module's folder documents how the module was implemented (including which LLM integration was chosen and how retrieval was grounded) and how to exercise/test it — this platform doubles as a learning reference, so write it for that purpose, not as a restatement of the code
- [ ] The module appears correctly on the dashboard (grouped under Search & AI, toggleable) with no changes needed to the dashboard or registry code itself
