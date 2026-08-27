# Production/Coolify image — built via compose.prod.yaml, not compose.yaml
# (that one is Sail's dev setup: it bind-mounts the whole repo and runs an
# unbuilt, unoptimized tree). This Dockerfile bakes a built,
# dependency-complete app into the image itself, so nothing is mounted live
# in production.
#
# PHP 8.4 — not the abstract "^8.3" composer.json declares (that's just the
# floor the app author is willing to support, not what's actually
# resolved), and not the 8.5 Sail's dev image happens to run either.
# composer.lock's locked symfony/laravel-framework versions hard-require
# PHP >=8.4.1.

# ---- Stage 1: frontend build -----------------------------------------------
FROM node:24-bookworm-slim AS frontend-build
WORKDIR /app

# Reverb's client key/host/port/scheme are compiled into the built JS by
# Vite (import.meta.env.VITE_*, see resources/js/echo.js) — there's no
# runtime env for the browser to read them from, so they have to be known
# at image build time. None of these are secret: REVERB_APP_KEY is a public
# client identifier (like a Pusher app key), not the broadcasting secret.
ARG VITE_APP_NAME
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_APP_NAME=$VITE_APP_NAME \
    VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage 2: runtime -------------------------------------------------------
FROM php:8.4-cli-bookworm AS runtime

# mbstring and the xml family (dom/simplexml/xmlwriter) aren't compiled
# into the base php image and need their dev libs first — everything else
# Laravel's composer.lock requires (ctype, session, tokenizer, openssl,
# ...) is already built in. pcntl lets Horizon's master process handle
# SIGTERM gracefully (finish in-flight jobs, deregister from Redis) instead
# of supervisord having to hard-kill it. The -dev packages are kept (not
# purged after) since the compiled extensions dynamically link against
# their runtime .so files, which apt can't tell are still needed once
# nothing *it* tracks depends on them.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libonig-dev \
        libxml2-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        unzip \
        supervisor \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo_mysql bcmath opcache mbstring dom xml simplexml xmlwriter pcntl gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Composer itself only needs its own binary, not a separate build stage —
# `composer dump-autoload` triggers composer.json's "post-autoload-dump"
# hook (Laravel's `artisan package:discover`), which needs the app's own
# full PHP + extensions (pdo_mysql, redis, ...) to actually boot. Running
# it inside the official composer:2 image's minimal PHP instead (a bare
# stage just for `composer install`) fails there for exactly that reason —
# package discovery can't bootstrap without the extensions above.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/prod/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/prod/supervisord.conf /etc/supervisor/supervisord.conf

WORKDIR /var/www/html

# Vendor download is the slow part and only depends on these two files, so
# it's a separate layer from the full source copy below — an app-only
# change (no composer.json/lock touched) skips straight to cache. The
# autoloader can't be optimized yet here (no app/ present to classmap), so
# it's regenerated after the full source copy instead.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --no-progress --prefer-dist
COPY . .
# .dockerignore excludes storage/framework/{cache,sessions,views} and
# storage/logs (their contents shouldn't ship in the image) — but that also
# excludes the directories themselves, .gitignore placeholder and all, so
# they don't exist post-COPY. Blade's compiler needs storage/framework/views
# to exist just to construct, and that construction happens as a side
# effect of package:discover below.
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
RUN composer dump-autoload --no-dev --optimize --no-interaction

COPY --from=frontend-build /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data storage bootstrap/cache

COPY docker/prod/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data

# >=1024 so the process doesn't need root/CAP_NET_BIND_SERVICE — Coolify's
# reverse proxy terminates TLS and forwards to these ports, the same way it
# would to any other container. 8080 is the web app; 8081 is Reverb's
# WebSocket server, which needs its own Coolify domain (see README).
EXPOSE 8080 8081

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
