# Audit Log

## What it demonstrates

`GET /concepts/audit-log/demo` behind the `audit-log` flag shows a tiny CRUD resource —
demo "notes", each just a title — with an activity feed table underneath. Add, rename, or
delete a note and the write completes, then the feed re-renders immediately with a new
row: who did it (`actor`, the caller's IP — there's no auth system on this platform),
what happened (`action`: `created`/`updated`/`deleted`), and what it happened to
(`subject`, e.g. `Note #3: Buy milk`), newest entry on top. `GET /concepts/audit-log/feed`
is the same feed as its own route — JSON for `Accept: application/json`, an HTML table
otherwise — the one the demo page's Alpine component actually calls after every write, and
the one the Pest test hits directly to prove persistence independent of the page's own
rendering.

## How it works

**Three thin controllers instead of one, split along the ticket's own seams.**
`AuditLogPageController` renders the demo page, `AuditLogNoteController` handles the
create/update/delete actions, and `AuditLogFeedController` serves the feed — the same
split the ticket describes ("a demo route/controller" for CRUD, "a second demo route" for
the feed). Each stays small and single-purpose rather than growing into one controller
that does page rendering, mutation, and feed rendering at once.

**Every write goes through one small `AuditLogRecorder::record()` call, not a model
event.** `store()`, `update()`, and `destroy()` on `AuditLogNoteController` each perform
the note mutation, then hand off to `AuditLogRecorder`, which inserts one `AuditLogEntry`
row. An Eloquent `deleted`/`created`/`updated` model observer would auto-fire without a
controller remembering to call it, which reads nicer for a real audit trail — but it would
also fire for the un-audited writes seeding/testing incidentally performs, and it can't
capture the *actor* (that's a property of the HTTP request, not the model). Recording
explicitly from the controller keeps "what gets audited" visible at the call site, which
matters more for a concept meant to be read.

**The write endpoints return the full fresh state, not a diff.** `store`/`update`/`destroy`
all respond with `{ notes: [...], entries: [...] }` — the complete current lists — rather
than just the one row that changed. The Alpine component on the demo page replaces its
whole `notes`/`entries` arrays with that response instead of patching them locally. This
means the client never has to reconcile "did my optimistic update match the server," at
the cost of a slightly bigger response body — a fine trade for a two-table demo, not
necessarily for a busier resource.

**Entries order by `id`, not `created_at`.** Two writes inside the same HTTP test (or a
fast double-click) can land in the same MySQL second; ordering by `created_at DESC` alone
leaves ties in an unspecified order, which silently breaks "newest first" under exactly
the conditions a demo is likely to hit. `id` is monotonic regardless of timestamp
resolution, so `AuditLogEntry::newestFirst()` — a local scope wrapping
`latest('id')` — is what actually guarantees the newest row first. It's a scope rather
than an inline call repeated at each of the three call sites (page, feed, and the
post-write state response) so the rule and its rationale live in one place.

## Testing

- `sail artisan test --filter=AuditLogDemoTest` — a create writes a matching audit entry
  and that entry appears in the feed response, both demo routes 503 when the flag is off,
  and the dashboard listing.
- Manually: toggle "Audit Log" on from `/concepts`, open `/concepts/audit-log/demo`, add a
  note, rename it, delete it — the feed table underneath grows a new row after each
  action, newest on top. Toggle the flag off and reload — the page falls back to the "off"
  message, and `curl -X POST http://localhost/concepts/audit-log/notes -H
  "Accept: application/json"` returns `503`.

## Notes

No pagination or trimming on the feed — every entry ever recorded for this demo resource
renders on every load. Fine for a single-developer local demo that gets reset by
`migrate:fresh`; a real audit log would page or window this.
