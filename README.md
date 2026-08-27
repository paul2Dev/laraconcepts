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

## Deployment (Coolify)

[`compose.prod.yaml`](compose.prod.yaml) + [`docker/prod/Dockerfile`](docker/prod/Dockerfile) are a separate, production-only setup — not the Sail `compose.yaml` used for local dev. The image is fully built (composer deps, `npm run build`) rather than bind-mounting the repo, and runs three processes under supervisord: the web server, the Horizon queue worker (see `app/Modules/HorizonDashboard`), and the Reverb WebSocket server (see `app/Modules/JobProgress`). On container start, `docker/prod/entrypoint.sh` runs `php artisan migrate --force` (safe on every deploy — a no-op with nothing pending) before starting supervisord.

In Coolify, point a new resource at this repo with `compose.prod.yaml` as the compose file. It needs these environment variables (mark the ones below with 🔒 as secrets):

| Variable | Required | Notes |
|---|---|---|
| `APP_KEY` | 🔒 yes | Generate with `php artisan key:generate --show` (run once, locally or in any PHP container) and paste the `base64:...` value — don't run `key:generate` in production itself, since a fresh key would invalidate every existing session/encrypted cookie on every deploy. |
| `APP_URL` | yes | The public HTTPS URL of the web app, e.g. `https://laraconcepts.paul2dev.com`. |
| `DB_PASSWORD` | 🔒 yes | Also used as the MySQL root password. |
| `REVERB_APP_ID` / `REVERB_APP_KEY` / `REVERB_APP_SECRET` | 🔒 yes | Any values — these just need to match between the server and the client build (`php artisan reverb:start` doesn't generate them for you; pick any non-empty strings, e.g. `openssl rand -hex 16`). |
| `REVERB_HOST` | yes | The public hostname the *browser* connects to for WebSockets — a separate domain/subdomain from `APP_URL`, e.g. `ws.laraconcepts.paul2dev.com`. Also passed as a build arg (`VITE_REVERB_HOST`), since Echo reads it from the compiled JS bundle, not a runtime env var. |
| `APP_NAME` | no | Defaults to `Laravel Concepts`. |
| `DB_DATABASE` / `DB_USERNAME` | no | Default to `laraconcepts` / `laraconcepts`. |
| `SESSION_DOMAIN` | no | Leave unset unless the app is served from a subdomain that needs the session cookie shared with a parent domain. |
| `REDIS_PASSWORD` | no | Leave unset — the bundled `redis` service has no `requirepass` configured and isn't exposed outside this compose network, so setting a password here would just break the connection (phpredis would send `AUTH` to a server that isn't expecting one). |

### Wiring up Reverb's second port

Reverb isn't a separate container — it's the third supervisord process inside the same `app` container/image, listening on its own port (`8081`) next to the web app's `8080`. Coolify's single "Domains" field on the `app` resource accepts a comma-separated list of FQDNs, each optionally suffixed with `:PORT` to say which container port that domain routes to (the first one with no suffix falls back to the resource's default port, `8080`). So this one field needs both domains, e.g.:

```
https://laraconcepts.paul2dev.com,https://ws.laraconcepts.paul2dev.com:8081
```

`REVERB_HOST` must be set to the second domain's hostname (without scheme/port) so both the server-side `broadcasting.php` config and the client-side Echo bundle point at it.

`APP_URL`'s scheme and `bootstrap/app.php`'s `trustProxies(at: '*')` together are what make redirects and session cookies resolve to `https://` correctly behind Coolify's reverse proxy — the app container itself only ever speaks plain HTTP on its internal ports.

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
