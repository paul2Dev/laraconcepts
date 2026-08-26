# Signed URL Expiry

## What it demonstrates

`GET /concepts/signed-url-expiry/demo` generates a **signed, time-limited URL**
(`URL::temporarySignedRoute()`) pointing at a second route,
`GET /concepts/signed-url-expiry/download`, with a 45-second expiry. The demo page shows
the raw generated link plus a live countdown, and a "Download" button that fetches it.

Follow the link while it's valid and the download route returns `200` with a small text
file attached. Wait past the 45-second window (or edit the `signature` query param) and
the exact same URL now returns `403` — the download route rejects it itself, it isn't
silently serving the file to an expired or forged request.

## How it works

**Two independent checks, same shape as `rate-limit-demo`'s two-gate pattern.** The
generation route (`show()`) and the download route (`download()`) each guard a different
concern:

- `show()` checks the Pennant flag (`Feature::active('signed-url-expiry')`) before it will
  even mint a link — flag off means no link is generated, the page returns `503` with an
  "unavailable" message instead of the countdown UI.
- `download()` never touches the flag. It only calls `$request->hasValidSignature()` —
  Laravel's own HMAC-based check that recomputes the signature from the route name, its
  parameters, and the `expires` query param, and compares it to the `signature` query
  param on the incoming request. A link is honored purely on cryptographic validity, not
  on whether the concept is currently toggled on. That's deliberate: once you've been
  handed a valid signed link, its validity is self-contained — it shouldn't stop working
  just because someone flips the demo off after the fact.

**The countdown is derived from the same `expires` timestamp embedded in the URL**, not a
separate value tracked server-side. The Blade view passes `$expiresAt->timestamp` (seconds)
to Alpine, which multiplies it into milliseconds and ticks a `setInterval` against
`Date.now()` — so what the browser counts down is exactly the instant the framework will
also use for its own comparison, with no risk of the two drifting.

**Tampering** is demonstrated by mutating the `signature` query param (any change to it, or
to `expires`, invalidates the HMAC) rather than by round-tripping through a second signing
key or algorithm — the whole point is that `hasValidSignature()` is doing real cryptographic
verification, not just checking "is there a signature present."

## Testing

- `sail artisan test --filter=SignedUrlExpiryTest` — covers: a valid signed link succeeding,
  an expired one (via `$this->travel()`) getting rejected, a tampered one getting rejected,
  the generation route refusing when the flag is off, the demo page rendering when the flag
  is on, and the dashboard listing.
- Manually: toggle "Signed URL Expiry" on from `/concepts`, open the demo page, watch the
  countdown, and click "Download" — it succeeds. Wait for the countdown to hit zero and
  click "Download" again — it's rejected. Copying the link and editing a character of the
  `signature` query param before visiting it produces the same rejection immediately,
  without waiting for expiry.

## Notes

The "download" itself is a small in-memory text response with a `Content-Disposition`
header, not a file read from storage — nothing in the ticket calls for a real stored file,
and the mechanic being demonstrated is the signature/expiry check, not file serving.
