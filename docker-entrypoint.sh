#!/bin/bash

# Version: 20251019-1912

set -e

cd /var/www

#!/bin/sh

set -e

# -----------------------------------------------------------------------------
# 0. Create missing authorizations (optional)
# -----------------------------------------------------------------------------
if [ -n "$PHOTOS_GID" ]; then

  GROUP_NAME="photos"

  if ! getent group "$PHOTOS_GID" > /dev/null; then
    echo "Creating group $GROUP_NAME with GID $PHOTOS_GID"
    groupadd -g "$PHOTOS_GID" "$GROUP_NAME"
  else
    GROUP_NAME=$(getent group "$PHOTOS_GID" | cut -d: -f1)
  fi

  if ! id www-data | grep -q "$PHOTOS_GID"; then
    echo "Adding www-data to group $GROUP_NAME (GID $PHOTOS_GID)"
    usermod -aG "$GROUP_NAME" www-data
  fi

else

  echo "No PHOTOS_GID set — skipping group setup."

fi

# -----------------------------------------------------------------------------
# 0. Set PHP Memory limit
# -----------------------------------------------------------------------------
PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-512M}"
echo "memory_limit = ${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/99-memory-limit.ini
echo "Setting PHP Memory limit to $PHP_MEMORY_LIMIT"

# -----------------------------------------------------------------------------
# 1. Wait for DB to be ready before running migrations
# -----------------------------------------------------------------------------
MAX_RETRIES=30
COUNT=0
SLEEP_TIME=3
echo "Waiting for database to be ready..."

if [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "mariadb" ]; then
    echo "Detected MariaDB/MySQL. Checking with mysqladmin..."
    until mysqladmin ping -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" --silent; do
        COUNT=$((COUNT+1))
        if [ $COUNT -ge $MAX_RETRIES ]; then
            echo "Error: MariaDB/MySQL not ready after $MAX_RETRIES attempts. Exiting."
            exit 1
        fi
        echo "MariaDB/MySQL is unavailable - sleeping $SLEEP_TIME seconds... ($COUNT/$MAX_RETRIES)"
        sleep $SLEEP_TIME
    done

elif [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "Detected PostgreSQL. Checking with pg_isready..."
    export PGPASSWORD="$DB_PASSWORD"
    until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" > /dev/null 2>&1; do
        COUNT=$((COUNT+1))
        if [ $COUNT -ge $MAX_RETRIES ]; then
            echo "Error: PostgreSQL not ready after $MAX_RETRIES attempts. Exiting."
            exit 1
        fi
        echo "PostgreSQL is unavailable - sleeping $SLEEP_TIME seconds... ($COUNT/$MAX_RETRIES)"
        sleep $SLEEP_TIME
    done

else
    echo "Warning: Unknown DB_CONNECTION value '$DB_CONNECTION'. Skipping database readiness check."
fi

echo "Database is ready! Continuing startup."

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
if [ "$(id -u)" -eq 0 ]; then

  echo "Setting permissions for storage and bootstrap/cache..."

  chown -R www-data:www-data storage bootstrap/cache || true

  chmod -R 775 storage bootstrap/cache || true

else

  echo "Not running as root — skipping chown/chmod"

fi

# -----------------------------------------------------------------------------
# 5. Run database migrations
# -----------------------------------------------------------------------------
php artisan migrate --force --seed

# -----------------------------------------------------------------------------
# 7. Start Supervisor (manages PHP-FPM + queue worker)
# -----------------------------------------------------------------------------
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
