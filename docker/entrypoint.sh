#!/bin/sh
set -e

cd /app

if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY is not set. Generate one with: php artisan key:generate --show"
  exit 1
fi

# Ensure storage dirs exist (important when /storage is a mounted volume)
mkdir -p storage/app/payslips storage/fonts storage/logs \
         storage/framework/cache storage/framework/sessions storage/framework/views \
         bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Run migrations
php artisan migrate --force

# Cache config/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec supervisord -c /app/docker/supervisord.conf
