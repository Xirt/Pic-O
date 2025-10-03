#!/bin/bash
set -e

cd /var/www

# -----------------------------------------------------------------------------
# 1. Wait for MariaDB to be ready before running migrations
# -----------------------------------------------------------------------------
MAX_RETRIES=30
COUNT=0
echo "Waiting for database to be ready..."

until mysqladmin ping -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" --silent; do
    COUNT=$((COUNT+1))
    if [ $COUNT -ge $MAX_RETRIES ]; then
        echo "Error: Database not ready after $MAX_RETRIES attempts. Exiting."
        exit 1
    fi
    echo "Database not ready - retrying in 3s... ($COUNT/$MAX_RETRIES)"
    sleep 3
done

echo "Database is ready!"

# -----------------------------------------------------------------------------
# 2. Clear & rebuild caches
# -----------------------------------------------------------------------------
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# -----------------------------------------------------------------------------
# 3. Set permissions for storage and cache directories
# -----------------------------------------------------------------------------
echo "Setting permissions for storage and bootstrap/cache..."
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# -----------------------------------------------------------------------------
# 5. Run database migrations
# -----------------------------------------------------------------------------
php artisan migrate --force --seed

# -----------------------------------------------------------------------------
# 7. Start Supervisor (manages PHP-FPM + queue worker)
# -----------------------------------------------------------------------------
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
