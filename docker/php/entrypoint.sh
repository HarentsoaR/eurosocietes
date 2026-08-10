#!/bin/sh
set -e

cd /var/www/html

# Create storage directories if missing (image ships them empty)
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Storage symlink (public/storage -> storage/app/public)
php artisan storage:link --force 2>/dev/null || true

# Generate APP_KEY on first boot if not provided (exported, not written to .env)
if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY:-}" = "base64:" ]; then
    echo "Generating APP_KEY..."
    export APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
fi

# Cache routes/config (faster boot; config values come from env at build)
php artisan route:cache 2>/dev/null || true

# Run migrations + seeders on startup (disable with RUN_MIGRATIONS=false)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force

    if [ "${RUN_SEEDERS:-true}" = "true" ]; then
        echo "Seeding database..."
        php artisan db:seed --force --class=RolePermissionSeeder 2>/dev/null || true
        php artisan db:seed --force --class=DatabaseSeeder 2>/dev/null || true
    fi
fi

# Hand off to supervisord (or any command passed via CMD)
exec "$@"
