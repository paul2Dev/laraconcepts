# Presence

## What it demonstrates

`GET /concepts/presence/demo` renders a page that joins a Reverb **presence**
channel (as opposed to the plain public channel `live-notifications` and
`job-progress` use). On load, the page shows "You are `<Guest Name>`" and a
live roster of everyone else currently on the page — appearing and
disappearing in real time as tabs open and close, no refresh or polling.
Typing into the textarea whispers a `typing` event directly to every other
member of the channel, which renders "`<name>` is typing…" for two seconds.
The route checks `Feature::active('presence')` before rendering (`503` when
off), and — the part that's actually interesting — the presence channel
itself refuses to be joined at all when the flag is off, independent of the
page.

## How it works

**Presence channels need an authenticated guard user, and this platform has
none.** Every other channel type in Laravel can be public, but private and
presence channels are always "guarded": `Broadcasters/PusherBroadcaster::auth()`
throws before your `Broadcast::channel()` callback ever runs if
`$request->user()` resolves to `null`. This app has no `User` model or login
— visitors are anonymous — so there's no `Auth::user()` to hand it. The fix is
`Auth::viaRequest()`: it registers a custom guard whose "authentication" is
just a closure over the request. Here, that closure reads a guest id/name
pair out of the session and wraps them in `Illuminate\Auth\GenericUser` (the
same throwaway `Authenticatable` Laravel itself uses for guard testing) —
`null` if the session doesn't have one yet (i.e., the demo page was never
visited), which correctly fails the channel join.

**The guard is registered with `config(['auth.guards.presence-guest' => ...])`
at runtime, not in `config/auth.php`.** A custom guard driver normally needs a
matching entry under `auth.guards` so `Auth::guard('presence-guest')` knows
which driver to use. Editing `config/auth.php` would mean every module built
after this one shares a file this module doesn't own — the same reason
`live-notifications` registers its own routes instead of editing
`routes/web.php`. Setting the config key at boot time, from inside the
module's own `ServiceProvider`, keeps the module self-contained the same way.

**The guest identity is assigned once per session, in `show()`.** The first
visit to the demo page writes a random display name and a UUID into the
session; every later request (including the presence-channel join, which
hits a *different* route, `/broadcasting/auth`) reuses the same identity.
Opening the demo page in two genuinely separate sessions (not two tabs in one
browser, which share a session cookie) is what produces two distinct roster
entries — this is why the ticket's manual check calls for "two browser
sessions" rather than two tabs.

**The channel-join gate lives in the `Broadcast::channel()` callback, not the
guard.** The guard only answers "who is this visitor"; it has no opinion on
whether the concept is switched on. `PresenceServiceProvider` checks
`Feature::active(self::SLUG)` inside the channel callback itself and returns
`false` when it's off, which Laravel turns into a `403` from
`/broadcasting/auth` — the join is rejected before Reverb ever authorizes a
socket for it, regardless of whether the demo page happens to be open in a
stale tab.

**The typing indicator is a client event (`whisper`), not a server
broadcast.** `channel.whisper('typing', {...})` sends the event straight
between Reverb clients over the socket already opened for the presence
channel — it never touches a Laravel route, controller, or queue. Reverb
allows this by default for channel members (`REVERB_APP_MAX_MESSAGE_SIZE` /
`accept_client_events_from=members` in `config/reverb.php`, unchanged). This
is also why the typing indicator can't be asserted with Pest: there's no HTTP
request or broadcast event to fake or assert against, only a live socket
message — hence the manual check below.

## Testing

- `sail artisan test --filter=PresenceTest` — six cases:
  - joining the presence channel is rejected (`403`) when the flag is off;
  - joining is authorized (`200`, with the guest's name in `channel_data`)
    when the flag is on;
  - joining is rejected even with the flag on if no guest session exists yet;
  - the demo page itself gates on the flag (`503`/`200`);
  - visiting the demo page assigns a guest id/name into the session;
  - the dashboard lists it under "Real-time".

  The join tests hit the real `/broadcasting/auth` route Laravel registers
  for channel authorization. That route's behavior depends on the *actual*
  broadcast driver, and this suite runs with `BROADCAST_CONNECTION=null` (a
  no-op broadcaster, see `phpunit.xml`) so that other modules' tests don't
  need a live Reverb server. A no-op broadcaster also means no gate to test —
  so `PresenceTest` locally reconfigures the default connection to `reverb`
  with throwaway key/secret/app-id, then re-runs
  `PresenceServiceProvider::boot()` so the module's channel/guard registration
  attaches to that connection instead. This never opens a real socket:
  Pusher's auth response is a local HMAC signature, not a network call, so
  the throwaway credentials only need to be well-formed, never real.

- Manually: `sail up -d` (needs `reverb` running to relay the presence
  channel and client-event whispers; `horizon` isn't required here since
  nothing in this module is queued). Toggle "Presence" on from `/concepts`,
  then open `/concepts/presence/demo` in two different sessions — e.g. a
  normal window and a private/incognito one, since two tabs in the same
  browser share one session and thus one guest identity. Each session's
  roster should show the other's guest name within a second of the second
  session loading, and disappear again when that tab is closed. Typing in
  one session's textarea should show "`<name>` is typing…" in the other
  within a keystroke, clearing about two seconds after typing stops. Toggling
  the flag off and reloading either session shows the "switched off" notice,
  and a tab left open from before the toggle loses its roster (the channel
  join it already made gets rejected on the next re-auth attempt Echo makes).

## Notes

**No database table, no queue.** Unlike `job-progress` and
`live-notifications`, nothing here is ever broadcast through Laravel's own
broadcasting layer at runtime — presence join/leave events are handled
entirely by Reverb itself once a socket authorizes, and the typing indicator
is a client-to-client whisper. The only place this module's own PHP code
runs per "event" is the one `/broadcasting/auth` request per join.

**The guest guard is intentionally minimal.** `GenericUser` isn't backed by a
database row — it exists only for the lifetime of the request that reads it
out of session data. A multi-tenant version of this concept would replace
`Auth::viaRequest`'s closure with a real user lookup and scope the channel
name per room instead of a single shared `presence-demo` channel.
