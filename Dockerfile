# Production image (used by Render). For local development use docker compose / Laravel Sail instead.

# ---- 1. PHP dependencies (no dev packages) ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader --ignore-platform-reqs

# ---- 2. Frontend assets ----
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
# Tailwind also scans vendor views (pagination), so the PHP dependencies are needed here.
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# ---- 3. Runtime: nginx + PHP-FPM ----
FROM serversideup/php:8.3-fpm-nginx

ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    SSL_MODE=off

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build
COPY --chmod=755 docker/entrypoint.d/ /etc/entrypoint.d/

RUN php artisan package:discover --ansi

USER www-data
