#!/bin/bash
set -e

cd /var/www

# Ensure storage folders exist with correct permissions
mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/photos storage/app
chmod -R 775 storage bootstrap/cache

# Clear and rebuild caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate APP_KEY if missing
if [ -z "$(grep APP_KEY .env | cut -d= -f2)" ]; then
    php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force || true

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf