FROM php:8.2-fpm

# Version: 20251004-1850

# -----------------------------------------------------------------------------
# 1. Install dependencies
# -----------------------------------------------------------------------------
RUN apt-get update && apt-get install -y \
        git \
        curl \
        zip \
        unzip \
        nginx \
        supervisor \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libssl-dev \
        mariadb-client \
         postgresql-client \
        libmagickwand-dev \
        libmagickcore-dev \
        build-essential \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && apt-get purge -y build-essential \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------------------------------------------------
# 2. Install Composer
# -----------------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------------------------
# 3. Configure Nginx
# -----------------------------------------------------------------------------
RUN rm /etc/nginx/sites-enabled/default
COPY nginx.conf /etc/nginx/conf.d/default.conf

# -----------------------------------------------------------------------------
# 4. Configure Supervisor
# -----------------------------------------------------------------------------
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# -----------------------------------------------------------------------------
# 5. Set working directory
# -----------------------------------------------------------------------------
WORKDIR /var/www

# -----------------------------------------------------------------------------
# 6. Copy Pic-O code
# -----------------------------------------------------------------------------
COPY src/ /var/www

# -----------------------------------------------------------------------------
# 7. Create environment key
# -----------------------------------------------------------------------------
RUN if ! grep -q '^APP_KEY=.\+' /var/www/.env.docker; then \
        APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')" && \
        sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" /var/www/.env.docker; \
    fi \
    && mv /var/www/.env.docker /var/www/.env

# -----------------------------------------------------------------------------
# 8. Copy entrypoint script
# -----------------------------------------------------------------------------
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# -----------------------------------------------------------------------------
# 9. Install dependencies
# -----------------------------------------------------------------------------
RUN composer install --no-dev --optimize-autoloader --no-scripts

# -----------------------------------------------------------------------------
# 10. Expose port
# -----------------------------------------------------------------------------
EXPOSE 80

# -----------------------------------------------------------------------------
# 11. Set Entrypoint
# -----------------------------------------------------------------------------
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]