# Job Progress

## What it demonstrates

`GET /concepts/job-progress/demo` renders a small page with a file input. Picking a
file and submitting posts it to `POST /concepts/job-progress/demo` behind the
`job-progress` flag, which stores the upload and dispatches `ProcessUploadJob` onto the
queue, returning a `channel` name straight away. The page immediately subscribes to that
channel with Echo and renders a progress bar that fills in live as the job broadcasts
`percentage`/`lines_processed` updates after each chunk it processes, finishing at 100%
with no polling anywhere in the loop — every update after the initial POST arrives over
the Reverb WebSocket connection.

This is also the first module on the platform whose demo route serves an actual Blade
page instead of raw JSON: every other module's "demo" is something you hit directly
(`curl`, or the dashboard's plain link) and read the JSON body back. A live progress bar
only means something rendered, so `job-progress.demo` returns HTML, while
`job-progress.upload` stays a JSON endpoint the page's own JavaScript calls. Both routes
gate on the flag independently — `job-progress.demo` renders a plain "switched off" notice
with a `503` status when inactive (matching every sibling module's non-200-when-off
contract), and `job-progress.upload` refuses to accept a file with the same `503` +
`{"message": "unavailable"}` body the other modules return.

## How it works

**Fixed chunk count, not a fixed byte size.** `ProcessUploadJob` divides the file into
`CHUNKS = 5` roughly-equal pieces by size (`ceil($totalBytes / 5)`), reads and processes
one piece per loop iteration, and broadcasts a progress event after each one. Chunking by
a fixed *count* rather than a fixed byte size (e.g. "8 KB per chunk") keeps the percentage
sequence deterministic and small regardless of upload size — a 5 KB test fixture and a
50 MB real upload both produce five broadcasts ending at 100, not five vs. six thousand.
The last chunk always lands exactly on 100% because `ftell($stream)` equals `$totalBytes`
once the stream is exhausted, not because of a rounding fudge.

**Chunked "row counting" via `substr_count($chunk, "\n")`.** Each chunk is scanned for
newline bytes to keep a running `linesProcessed` count, so the job never holds more than
one chunk in memory — no `file()`/`explode()` slurping the whole upload up front. This is
the "chunked row counting" the ticket asks for as a stand-in for whatever the real
per-chunk work would be (parsing a CSV row, resizing an image tile, etc.) — the point
being demonstrated is the queue → chunk → broadcast → live-UI loop, not the counting
itself.

**A `usleep()` per chunk, same trick as `cache-benchmark`.** Counting newlines in a byte
chunk is fast enough that all five broadcasts fire within milliseconds of each other on
local disk — the progress bar would jump straight to 100% before a human could see it
move. `CacheBenchmarkDemoController::runExpensiveQuery()` already established this
platform's answer to "the real thing is too fast to demo": a flat `usleep()` standing in
for the per-chunk work a real job would actually do (parsing, resizing, etc.).
`CHUNK_DELAY_MICROSECONDS = 400_000` here does the same job, spacing the five broadcasts
~0.4s apart (~2s total) so the bar is visibly climbing rather than teleporting.

**`ShouldBroadcastNow` + `event()`, not `broadcast()`.** Ticket 08 already found that the
`broadcast()` helper's `PendingBroadcast` defers to `__destruct()`, which doesn't fire
reliably outside a long-running process — `event()` is the entry point that actually sends
the message immediately. `UploadProgressUpdated` implements `ShouldBroadcastNow` (not the
queued `ShouldBroadcast`) so each event goes out synchronously from inside the already-queued
job — queuing the broadcast a second time would only delay it and complicate the ordering
the progress bar depends on.

**A public channel scoped by upload ID, not a private one.** The channel name is
`job-progress.{uploadId}` where `$uploadId` is a server-generated UUID handed back in the
upload response — nothing about it is guessable, so there's no real need to require
Reverb's `Authorization` handshake for a single-developer local demo. A production
multi-tenant version of this would want a `PrivateChannel` with an entry in
`routes/channels.php` authorizing only the uploader; this module keeps the public channel
to stay focused on the job/broadcast/UI loop.

**The upload gets deleted once the job finishes.** There's nothing to browse or clean up
afterward — `ProcessUploadJob::handle()` deletes the stored file itself right before
returning, on both the empty-file short-circuit and the normal chunked path.

## Testing

- `sail artisan test --filter=JobProgressTest` — a 4,000-line fake CSV dispatched through
  the real upload route with the flag on, asserting `Event::dispatched` percentages are
  strictly increasing and end at 100; the upload route's flag-off gate returning `503`
  with the `unavailable` body; the demo page itself returning `503` when the flag is off
  and `200` when it's on; and the dashboard listing under "Real-time".
- Manually: `sail up -d` (the `queue` service — see Notes — must be running), toggle
  "Job Progress" on from `/concepts`, follow its demo link to
  `/concepts/job-progress/demo`, and upload any reasonably sized file — the progress bar
  fills live as Reverb events arrive, no page reload or polling involved. Toggling the flag
  off first and submitting shows the inline "unavailable" error instead.

## Notes

Tests run with `QUEUE_CONNECTION=sync` (see `phpunit.xml`), so `ProcessUploadJob::dispatch()`
runs the job inline before the HTTP response returns — the whole chunked broadcast sequence
already happened by the time the test asserts on it, no `Bus::fake()`/manual `handle()` call
needed. `Storage::fake('local')` keeps uploaded test fixtures off the real disk regardless.

**Sail had no queue worker.** `QUEUE_CONNECTION=database` (the app default) means
`ProcessUploadJob::dispatch()` just inserts a row into the `jobs` table — nothing consumes it
without a worker process. Every earlier module ran its logic synchronously inside the request,
so this never came up before. Added a `queue` service to `compose.yaml` running
`php artisan queue:listen --tries=1`, the same shape as ticket 08's `reverb` service — without
it, uploads sit at 0% forever with a job silently stuck in the `jobs` table. Ticket 10
(Horizon) will likely supervise queues properly later; this is the minimal fix to make this
module actually work today.

**Module routes had no `web` middleware group — silently, since ticket 04.** Every concept
module registers its routes with `Route::get()`/`Route::post()` straight from its own
`ServiceProvider::boot()`, not from `routes/web.php`. `bootstrap/app.php`'s `withRouting()`
only wraps `routes/web.php` in the `web` middleware group (session, CSRF, cookies) — routes
registered elsewhere don't get it unless asked for explicitly. Every module before this one
was a stateless GET-JSON endpoint, so nobody noticed. This module is the first with a real
Blade page (needing a working CSRF meta tag) and the first POST endpoint meant to be
CSRF-protected, so the gap became visible: `csrf_token()` in `demo.blade.php` rendered an
empty string and the upload route accepted requests with no CSRF check at all. Fixed by
wrapping this module's two routes in `Route::middleware('web')->group(...)` inside
`JobProgressServiceProvider::boot()` — scoped to this module only, since fixing it platform-wide
would mean editing every sibling module's `ServiceProvider` for a gap outside this ticket's
scope.

Being the platform's first module to render its own full page (every earlier module's demo
route is a JSON endpoint hit directly), this is also the first time the dashboard's page
shell (`<!DOCTYPE html>` through `@vite`) needed reuse rather than a second copy — pulled out
into `resources/views/components/layout.blade.php` (`<x-layout title="...">`), which
`resources/views/concepts/dashboard.blade.php` now shares with this module's `demo.blade.php`.
