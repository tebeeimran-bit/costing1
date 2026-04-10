#!/bin/bash

# Ensure storage directories exist
mkdir -p storage/framework/{views,sessions,cache} \
         storage/logs \
         storage/app/public \
         bootstrap/cache

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Create .env from environment variables if not exists
if [ ! -f ".env" ]; then
    printenv | grep -E '^(APP_|DB_|CACHE_|SESSION_|MAIL_|LOG_|QUEUE_|BROADCAST_|FILESYSTEM_|REDIS_)' | while IFS='=' read -r key value; do
        echo "${key}=\"${value}\""
    done | sort > .env
    echo "Generated .env from environment variables"
    cat .env
fi

# Discover packages (skipped during build)
php artisan package:discover --ansi || true

# Generate app key if missing
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force || true
fi

# Run migrations
php artisan migrate --force --no-interaction || true

# Cache config, routes, views for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting nginx + php-fpm..."

# Start services
exec /usr/bin/supervisord -c /etc/supervisord.conf
