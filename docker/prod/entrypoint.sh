#!/bin/sh
set -e

# Idempotent — a no-op when there's nothing pending, so it's safe to run on
# every container start rather than needing a separate Coolify-side
# "post-deployment command" step to remember to configure.
php artisan migrate --force

exec supervisord -c /etc/supervisor/supervisord.conf
