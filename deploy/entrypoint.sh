#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Render sets PORT; we listen on 8080 inside container

mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
  # Local/dev convenience: if no APP_KEY provided, fall back to .env generation.
  if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
  fi

  if [ -f .env ]; then
    php artisan key:generate --force >/dev/null 2>&1 || true
  else
    echo "ERROR: APP_KEY is required in production (set it in Render env vars)." >&2
    exit 1
  fi
fi

php artisan storage:link >/dev/null 2>&1 || true
php artisan config:clear >/dev/null 2>&1 || true

php-fpm -D
nginx -g 'daemon off;'
