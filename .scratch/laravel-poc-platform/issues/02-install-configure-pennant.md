# 02 — Install and configure Laravel Pennant

**What to build:** Feature-flag infrastructure for the whole platform, using Laravel's own first-party package, so that every concept module built afterward has a real, persisted on/off switch to be gated behind.

**Blocked by:** 01 — Scaffold Laravel 13 app with Sail, Pest, and MySQL

**Status:** ready-for-agent

- [ ] `laravel/pennant` is installed and its migration (the `features` table) has run against the MySQL database
- [ ] Pennant's default database driver is the configured driver (flag state persists across requests, not just in-memory for one request)
- [ ] A Pest feature test defines an ad-hoc flag, activates it, and asserts both `Feature::active()` returns true and the state is persisted in the `features` table
- [ ] The same test (or a second one) deactivates the flag and asserts `Feature::active()` returns false and the persisted state reflects it
