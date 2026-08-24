# 08 — Install and configure Laravel Reverb

**What to build:** Real-time broadcasting infrastructure for the whole platform, using Laravel's own first-party WebSocket server, so that every real-time concept module built afterward (job-progress, live-notifications, presence, live-collab) has a working broadcast transport to build its own channel on top of.

**Blocked by:** None — can start immediately (platform already shipped in tickets 01–04)

**Status:** ready-for-agent

- [ ] `laravel/reverb` is installed and configured as the app's broadcasting driver (`BROADCAST_CONNECTION=reverb`)
- [ ] The Reverb server runs as its own service in the Sail docker-compose definition, portable to the future Coolify deployment (mirrors how MySQL is already defined)
- [ ] Laravel Echo (frontend) is wired up and configured to connect to the local Reverb server
- [ ] A minimal smoke-test event/channel (not tied to any real concept module) proves the round trip end-to-end: a server-side broadcast is actually received client-side
- [ ] A Pest feature test asserts the smoke-test event is broadcast on its expected channel (using Laravel's broadcasting fake/assertion helpers — no real WebSocket connection needed in the test)
- [ ] The smoke-test event/channel is removed or clearly marked as scaffolding once the assertion above is in place — it isn't a concept module and shouldn't appear on the dashboard

## Notes

- This ticket mirrors ticket 02 (Pennant) in shape: pure infrastructure, no `ConceptRegistry` entry, no dashboard visibility — it exists so later modules have something real to depend on.
