# Live Collab

## What it demonstrates

`GET /concepts/live-collab/demo` renders a page with a single `<textarea>` bound
to one shared demo document. Typing in it posts the full document content to
`POST /concepts/live-collab/demo/edit` behind the `live-collab` flag, which
saves it and broadcasts a `DocumentEdited` event on a Reverb channel scoped to
that document's ID. Every other tab or session with the same page open is
already subscribed to that channel and applies the new content the instant it
arrives — no refresh, no polling. Both routes gate on the flag independently
and return a `503` when it's off: the demo page as a plain "switched off"
notice (matching every sibling module), and the edit endpoint as
`{"message": "unavailable"}`.

## How it works

**One fixed demo document, not a document picker.** The ticket asks for "the
demo page's shared text editor," singular, so the controller works with a
single row (`id = 1`) in `live_collab_documents`, created on first visit via
`firstOrCreate`. This is the same shortcut `presence` takes with its one fixed
`demo` channel name rather than a room picker — the concept being demonstrated
is the broadcast loop, not multi-document management. The document is DB-backed
(unlike `live-notifications`' pure Alpine state) specifically so that a fresh
page load — including a brand new session that never sent an edit — shows
whatever the document currently holds, which is what makes "open a second
session and see it converge" a meaningful check.

**Concurrent edits are reconciled by last write wins — there is no merge.**
Every edit POST sends the *entire* textarea content, not a diff, and every
broadcast replaces the receiving tab's entire textarea content the same way.
If two sessions type at genuinely the same moment, whichever edit's HTTP
request reaches the server last simply overwrites what the other saved, and
the next broadcast pushes that final version to everyone — the losing edit is
silently discarded, not merged character-by-character. Real collaborative
editors (Google Docs, Figma) solve this with Operational Transformation or a
CRDT that merges concurrent character-level ops instead of replacing the whole
document; that's deliberately out of scope here. The point being demonstrated
is the broadcast/live-UI loop over Reverb, not conflict-free replication — the
README calls this out explicitly because the ticket asks how conflicts are
handled, and "not handled, whole-document overwrite" is the honest answer.

**A client ID stops a session from clobbering its own in-progress typing.**
Without it, every keystroke's broadcast would also arrive back at the sender
itself, replacing whatever it had typed one debounce-cycle later with what it
had *already* saved — annoying, and on a slower connection, actively
corrective of nothing. `demo.blade.php` generates one `crypto.randomUUID()`
per page load and sends it with every edit; `DocumentEdited::broadcastWith()`
carries it back out, and the Echo listener ignores any event whose `client_id`
matches its own. Two different browser sessions naturally get two different
UUIDs, so their edits still cross-apply normally.

**A 300ms debounce, not a broadcast per keystroke.** Posting and broadcasting
on every single `input` event would mean a network round-trip and a Reverb
message per keystroke — noisy, and unnecessary for a text field with no
character-level merge to keep in sync anyway. The textarea's `x-model` keeps
typing feeling instant locally regardless; the debounce only delays when the
*rest of the world* finds out.

**A public channel scoped by document ID, not a private one.** Same reasoning
as `job-progress`'s `job-progress.{uploadId}`: the channel name
(`live-collab.{documentId}`) carries no sensitive data and isn't guessable in
a way that matters for a single-developer local demo, so it needs no
`Broadcast::channel()` authorization callback or `routes/channels.php` entry.
A production multi-tenant version would want a `PrivateChannel` authorizing
only users with access to that specific document.

**`ShouldBroadcastNow` + `Event::dispatch()`, same as `job-progress`.** Ticket
08 already established that `broadcast()`'s `PendingBroadcast` doesn't reliably
fire from `__destruct()`; `DocumentEdited::dispatch()` (the `Dispatchable`
trait's static helper) resolves to `event(new DocumentEdited(...))`, which
sends immediately since the event implements `ShouldBroadcastNow` rather than
the queued `ShouldBroadcast`.

## Testing

- `sail artisan test --filter=LiveCollabTest` — posts an edit through the real
  route with the flag on and asserts, via `Event::fake()` +
  `Event::assertDispatched()`, that a `DocumentEdited` event went out on
  `live-collab.{documentId}` with the submitted content, and that the document
  row was actually updated; asserts the edit route's `503` +
  `{"message": "unavailable"}` response (and that nothing is saved or
  broadcast) when the flag is off; asserts the demo page's `503`/`200` gate;
  and asserts the dashboard listing under "Real-time".
- Manually: `sail up -d` (needs `reverb` running to relay the broadcast;
  `horizon` isn't required since nothing here is queued — `ShouldBroadcastNow`
  sends synchronously). Toggle "Live Collab" on from `/concepts`, then open
  `/concepts/live-collab/demo` in two different sessions (a normal window and
  a private/incognito one — two tabs in the same browser also work fine here,
  since unlike `presence` this module has no per-session identity, only a
  per-page-load client ID for echo-suppression). Type in one session's
  textarea; after a beat (~300ms after you stop typing) the other session's
  textarea updates to match, with no refresh. Typing in both at once shows
  whichever one's edit lands last on the server "win" the shared document.
  Toggling the flag off and reloading either session shows the "switched off"
  notice.

## Notes

**No optimistic-lock or version check on save.** `edit()` always overwrites
the document's `content` unconditionally — there's no `updated_at` compare or
`If-Match`-style precondition, which is what makes the last-write-wins
behavior described above possible in the first place (and is exactly the gap
a CRDT/OT-based version would close).

**The debounce means a very fast typist's earliest keystrokes never get their
own broadcast.** Only the content standing at the end of each 300ms quiet
window is ever sent — this is intentional (see How it works) and matches the
UX of e.g. autosave-style editors, not a bug.
