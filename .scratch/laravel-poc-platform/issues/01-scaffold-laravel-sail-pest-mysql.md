# 01 — Scaffold Laravel 13 app with Sail, Pest, and MySQL

**What to build:** A running Laravel 13 application, started locally with a single Sail command, using MySQL as its database and Pest as its test runner. This is the foundation every later ticket builds on — no visible feature yet, but the base every module and the platform itself depends on.

**Blocked by:** None — can start immediately.

**Status:** done

- [x] `laravel new` scaffolds a Laravel 13 app (PHP 8.4) with the "None" starter kit (Blade + Tailwind + Vite ship by default)
- [x] Laravel Sail is installed and configured with a MySQL service; `sail up` brings the app up and it's reachable in the browser
- [x] Pest is the configured test runner (not PHPUnit); `sail artisan test` runs the (empty) suite green
- [x] The docker-compose service definitions Sail generates are the ones intended for later reuse toward a Coolify deployment (no deploy automation yet — just don't hand-roll a separate, divergent compose file)
- [x] `CLAUDE.md` and `docs/agents/` already present in the repo root are preserved, not overwritten, by the scaffold

## Notes

- Fixed a Laravel installer bug: generated `.env` had `APP_URL=http://localhost:8000:8000` (doubled port), which broke `artisan package:discover` with "Invalid URI: Host is malformed" — corrected to `http://localhost`.
- Worked around a framework bug in `Illuminate\Session\Console\SessionTableCommand::migrationExists()`: it always reports true because one of its two glob checks matches the ever-present `create_users_table` migration, regardless of whether a sessions migration exists. Wrote the standard sessions-table migration by hand from the framework's own stub instead.
- MySQL image is Sail's default `mysql:8.4`, not 9.0+ — the AI/ML module's native `VECTOR` type plan (spec's Further Notes) will need the image bumped when that module starts.
