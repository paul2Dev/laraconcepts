# 02 — Install and configure Laravel Pennant

**What to build:** Feature-flag infrastructure for the whole platform, using Laravel's own first-party package, so that every concept module built afterward has a real, persisted on/off switch to be gated behind.

**Blocked by:** 01 — Scaffold Laravel 13 app with Sail, Pest, and MySQL

**Status:** done (uncommitted — awaiting go-ahead)

- [x] `laravel/pennant` is installed and its migration (the `features` table) has run against the MySQL database
- [x] Pennant's default database driver is the configured driver (flag state persists across requests, not just in-memory for one request)
- [x] A Pest feature test defines an ad-hoc flag, activates it, and asserts both `Feature::active()` returns true and the state is persisted in the `features` table
- [x] The same test (or a second one) deactivates the flag and asserts `Feature::active()` returns false and the persisted state reflects it

## Notes

- `tests/Pest.php` didn't exist even though ticket 01 installed Pest with `--pest` — needed it (`uses(TestCase::class)->in('Feature', 'Unit')`) for functional `it()`-style tests to get the Laravel app bootstrapped; class-based tests (the two ExampleTest files) didn't need it, which is why the gap wasn't caught in ticket 01.
- Also fixed the fallout from ticket 01's incorrect duplicate `sessions` migration (see that ticket's updated notes) — deleted the file and re-ran a clean `migrate:fresh` on both the app and `testing` databases so migration bookkeeping is now correct.
- Full suite: 4/4 passing (`sail artisan test`).
