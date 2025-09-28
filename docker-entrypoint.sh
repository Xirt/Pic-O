#!/bin/bash
set -e

cd /var/www
if [ ! -f ".env" ]; then
    echo "Copying .env.docker to .env..."
    cp .env.docker .env
fi

# -----------------------------------------------------------------------------
# 1. Ensure Composer dependencies are installed
# -----------------------------------------------------------------------------
if [ ! -d "vendor" ]; then
    echo "No vendor/ directory found. Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-scripts
else
    echo "Vendor directory already present. Skipping Composer install."
fi

# -----------------------------------------------------------------------------
# 2. Wait for MariaDB to be ready before running migrations
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
# 3. Set permissions for storage and cache directories
# -----------------------------------------------------------------------------
echo "Setting permissions for storage and bootstrap/cache..."
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true
chown www-data:www-data /var/www/.env
chmod 644 /var/www/.env


# -----------------------------------------------------------------------------
# 4. Generate APP_KEY if missing
# -----------------------------------------------------------------------------
if ! grep -q '^APP_KEY=' .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# -----------------------------------------------------------------------------
# 5. Run database migrations
# -----------------------------------------------------------------------------
php artisan migrate --force

# -----------------------------------------------------------------------------
# 6. Clear & rebuild caches
# -----------------------------------------------------------------------------
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# -----------------------------------------------------------------------------
# 7. Start Supervisor (manages PHP-FPM + queue worker)
# -----------------------------------------------------------------------------
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
