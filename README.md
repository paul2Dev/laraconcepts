# Laravel Concepts Platform

A sandbox Laravel app for building one proof-of-concept Laravel feature per module, each toggleable live via a feature flag. Every module registers itself in `App\Platform\ConceptRegistry` and shows up on the [`/concepts`](/concepts) dashboard — see `CONTEXT.md` and `docs/adr/0001-concept-module-convention.md` for the full convention.

## Infrastructure

- **Sail + MySQL + Pest** — local Docker dev environment, MySQL as the database engine, Pest as the test runner across the whole project.
- **Laravel Pennant** — first-party feature flags, one per concept, persisted to the database so state survives across requests and deploys.
- **ConceptRegistry + dashboard** — `/concepts` lists every registered concept grouped by category with a live on/off toggle backed by Pennant.
- **Laravel Reverb** — first-party WebSocket broadcasting server, running as its own Sail service, giving every future real-time module (job-progress, live-notifications, presence, live-collab) a working broadcast transport to build on.

## Module structure

Every concept is a self-contained module under `app/Modules/<ConceptName>/`. To add a new concept, you only ever touch files inside that one folder plus a single line in `bootstrap/providers.php` — nothing shared is edited, so parallel modules never conflict.
```
app/Modules/<ConceptName>/
├── <ConceptName>ServiceProvider.php   # entry point: registers the concept + its route(s)
├── Http/Controllers/                  # the demo controller(s) exercised by the flag
├── Models/                            # Eloquent models scoped to this concept
├── Casts/                             # custom Eloquent casts, if the concept needs one
├── ValueObjects/                      # plain value objects backing a cast/model
├── Embeddings/, Search/               # concept-specific services (naming is free — one folder per responsibility)
├── database/migrations/               # migrations, auto-loaded by the ServiceProvider
├── resources/views/                   # Blade views for the demo route, if any
└── README.md                          # what it demonstrates / how it works / testing / notes
```

Folder names past `Http`, `Models`, `database` are not fixed — a module has whatever folders its concept needs (`SemanticSearch` has `Embeddings/` + `Search/`, `CustomCasts` has `Casts/` + `ValueObjects/`). The only structural requirement is the `ServiceProvider` and the `README.md`.

The `ServiceProvider::boot()` method is where a module plugs itself into the platform — this is the whole contract:

```php
public function boot(ConceptRegistry $registry): void
{
    $this->loadMigrationsFrom(__DIR__.'/database/migrations');

    $registry->register(new ConceptRegistration(
        slug: self::SLUG,              // also the Pennant feature flag name
        name: 'Custom Casts & value objects',
        description: '...',
        category: 'Search & Data',
        demoRoute: 'custom-casts.demo',
    ));

    Route::get('/concepts/custom-casts/demo', [CustomCastsDemoController::class, 'show'])
        ->name('custom-casts.demo');
}
```

- **Register with `ConceptRegistry`** — this is what makes the module show up on the `/concepts` dashboard. Nothing else needs to know the module exists.
- **Gate the demo route yourself** — the controller checks `Feature::active(self::SLUG)` and refuses to run when the flag is off; the registry/dashboard never enforce this.
- **Add one line to `bootstrap/providers.php`** — `<ConceptName>ServiceProvider::class`, so Laravel boots it.

That's the whole recipe: folder + `ServiceProvider` + one line in `bootstrap/providers.php`.

## Concepts

**Search & AI**

- **Semantic Search** — searches a seeded product catalog with a toggle between plain keyword LIKE matching and embedding-based semantic ranking.
- **Image Similarity** — uploads an image and ranks a seeded set of real photos by visual similarity, using a hand-rolled grid-color embedding and cosine distance.
- **Image Text Search** — types a text query and ranks a seeded set of real photos by relevance, using a hand-rolled cross-modal embedder that maps both text and images into the same vector space.

**Search & Data**

- **Custom Casts & value objects** — round-trips a Money value object through a custom Eloquent cast backed by two database columns.

**Performance & Security**

- **Cache Benchmark** — runs the same expensive query with the cache bypassed and then cached, timing both.
- **Rate Limit Demo** — spams a demo route behind a dedicated 5-requests-per-10-seconds limiter, returning a live request count until it throttles with a 429.
- **Signed URL Expiry** — generates a short-lived signed download link with a live countdown; the download route rejects it once expired or if the signature is tampered with.

**Real-time**

- **Job Progress** — uploads a file to a queued job that counts it in byte chunks, broadcasting live percentage-complete updates over a Reverb channel scoped to that upload.

**DevOps / Observability**

- **Audit Log** — performs CRUD actions on a demo note, writing an actor/action/subject audit log entry for every write and watching it land in a live activity feed.
- **Horizon Dashboard** — gates access to Laravel Horizon's own dashboard for monitoring the Redis-backed queue behind a Pennant flag.
