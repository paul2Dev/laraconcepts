# Live Notifications

## What it demonstrates

`GET /concepts/live-notifications/demo` renders a page with a "Send test
notification" button and a bell icon. Clicking the button posts to
`POST /concepts/live-notifications/demo/notify` behind the `live-notifications`
flag, which sends a Laravel notification — `DemoNotificationPosted` — to an
on-demand notifiable over the `broadcast` channel. The page is already
subscribed via Echo to the `live-notifications` Reverb channel, so the
notification's `title`/`body` appear in the feed the instant it's sent, the
bell's unread badge increments, and opening the bell clears it — all without a
page refresh or any polling. Both routes gate on the flag independently and
return a `503` when it's off: the demo page as a plain "switched off" notice
(matching every sibling module), and the notify endpoint as
`{"message": "unavailable"}`.

## How it works

**Laravel's own notification system, not a raw broadcast event.** Every other
real-time module (`job-progress`) broadcasts by dispatching a plain
`ShouldBroadcastNow` event. This module is the first to go through
`Illuminate\Notifications\Notification` instead: `DemoNotificationPosted`
defines `via()` (`['broadcast']`), `broadcastOn()`, `broadcastAs()`, and
`toBroadcast()`, and is sent with
`Notification::send(new AnonymousNotifiable, new DemoNotificationPosted(...))`.
There's no authenticated user in this demo to notify, so `AnonymousNotifiable`
stands in for "whoever's watching the page" — the same on-demand notifiable
Laravel itself uses for things like contact-form emails with no `User` model
behind them.

**`broadcastOn()` wins over the anonymous-notifiable default.** Laravel's
`BroadcastNotificationCreated` event (the thing that actually gets broadcast
under the hood) first checks whether the anonymous notifiable was routed to a
channel via `->route('broadcast', ...)`; only when that's absent does it fall
back to the notification's own `broadcastOn()`. This module never calls
`->route()`, so `DemoNotificationPosted::broadcastOn()` — a plain public
`Channel('live-notifications')` — is what actually gets used. That channel
name is fixed and shared by every visitor, unlike `job-progress`'s
per-upload-scoped channel, because there's no per-session state to isolate
here: anyone with the demo page open is meant to see every notification fired.

**`toBroadcast()` returns a `BroadcastMessage`, and Laravel fills in the rest.**
The payload only needs `title`, `body`, and `created_at` — `id` (a UUID Laravel
assigns to every notification instance) and `type` are merged in automatically
by `BroadcastNotificationCreated::broadcastWith()`, which is why the frontend
can key its feed list on `notification.id` without the module generating one
itself.

**A small server-side sample pool, not user-typed input.** `notify()` picks a
random `{title, body}` pair from a fixed list (`Arr::random()`) rather than
accepting a message from the request — the point being demonstrated is the
notification → broadcast → live-UI loop, not a form. Firing the button
repeatedly produces visibly different feed entries.

**The feed is Alpine state, not a model.** Unlike `audit-log`'s persisted
entries, this module keeps no database table — the bell's unread count and
feed list live entirely in the page's own `x-data`, reset on reload. That's a
deliberate scope cut: the concept being demonstrated is the broadcast, not
notification history.

## Testing

- `sail artisan test --filter=LiveNotificationsTest` — sends a notification
  through the real route with `Notification::fake()` active and asserts it
  with `Notification::assertSentOnDemand()`, checking both that it went out on
  the `broadcast` channel and that `broadcastOn()` resolves to this module's
  own `live-notifications` channel; asserts the notify route's `503` +
  `unavailable` body and `Notification::assertNothingSent()` when the flag is
  off; asserts the demo page's `503`/`200` gate; and asserts the dashboard
  listing under "Real-time".
- Manually: `sail up -d` (the `reverb` and `horizon` services must be running —
  see Notes), toggle "Live Notifications" on from `/concepts`, open
  `/concepts/live-notifications/demo`, and click "Send test notification" a
  few times — each click adds an entry to the bell's unread count immediately,
  and opening the bell reveals the feed. Opening the same page in a second tab
  shows both tabs update from a single click, since the channel isn't scoped
  per browser session. Toggling the flag off and clicking the button shows the
  inline "unavailable" error instead.

## Notes

**Still queued through Horizon in real usage, even with no job of its own.**
This module never calls `Bus::dispatch()` directly, but
`BroadcastNotificationCreated` — the event Laravel raises internally to
actually broadcast a notification — implements `ShouldBroadcast`, which queues
it like any other queued broadcast event. With `QUEUE_CONNECTION=redis` (this
app's default), that means the `horizon` Sail service has to be running to
relay the broadcast out over Reverb, exactly as `job-progress` requires it for
its own progress events. Tests don't need it: `QUEUE_CONNECTION=sync` in
`phpunit.xml` runs it inline before the response returns.

**No `PrivateChannel`/`routes/channels.php` entry.** The channel carries no
sensitive data and isn't scoped to a specific user, so it stays a public
`Channel` like `job-progress`'s — no authorization callback needed. A
multi-tenant version of this concept would notify a specific `User` instead of
an anonymous notifiable and broadcast on that user's private channel instead.
