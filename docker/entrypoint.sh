#!/bin/bash
set -e

cd /var/www/html

# Create .env from .env.example if missing
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Synchronize key Docker environment variables into .env if present
sync_env() {
    local key="$1"
    local val="$2"
    if [ -n "$val" ]; then
        if grep -q "^${key}=" .env; then
            sed -i "s|^${key}=.*|${key}=${val}|" .env
        else
            echo "${key}=${val}" >> .env
        fi
    fi
}

sync_env "APP_URL" "${APP_URL:-http://localhost:8004}"
sync_env "DB_CONNECTION" "${DB_CONNECTION:-mysql}"
sync_env "DB_HOST" "${DB_HOST:-driveed_hub_db}"
sync_env "DB_PORT" "${DB_PORT:-3306}"
sync_env "DB_DATABASE" "${DB_DATABASE:-drivingapp}"
sync_env "DB_USERNAME" "${DB_USERNAME:-driveed_user}"
sync_env "DB_PASSWORD" "${DB_PASSWORD:-driveed_password}"

# Ensure storage directories exist
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

# Install dependencies if vendor directory is empty or missing
if [ ! -f vendor/autoload.php ]; then
    echo "Vendor dependencies not found. Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Ensure application key is set
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generating Laravel application key..."
    php artisan key:generate --force
fi

# Link storage directory
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Wait for MySQL if configured
if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "Checking database connection to ${DB_HOST:-driveed_hub_db}:${DB_PORT:-3306}..."
    max_retries=30
    counter=0
    until php -r "
        try {
            \$host = getenv('DB_HOST') ?: 'driveed_hub_db';
            \$port = getenv('DB_PORT') ?: '3306';
            \$db   = getenv('DB_DATABASE') ?: 'drivingapp';
            \$user = getenv('DB_USERNAME') ?: 'driveed_user';
            \$pass = getenv('DB_PASSWORD') ?: 'driveed_password';
            new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3
            ]);
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; do
        counter=$((counter + 1))
        if [ $counter -ge $max_retries ]; then
            echo "Warning: Database connection could not be established within $max_retries attempts. Continuing..."
            break
        fi
        sleep 2
    done

    if [ $counter -lt $max_retries ]; then
        echo "Database is ready! Running migrations..."
        php artisan migrate --force || echo "Migrations finished with warnings or already applied."
    fi
elif [ "${DB_CONNECTION:-}" = "sqlite" ]; then
    SQLITE_PATH="${DB_DATABASE:-database/database.sqlite}"
    if [ ! -f "$SQLITE_PATH" ]; then
        touch "$SQLITE_PATH"
        chown www-data:www-data "$SQLITE_PATH"
        chmod 664 "$SQLITE_PATH"
    fi
    echo "Running SQLite migrations..."
    php artisan migrate --force || true
fi

# Clear stale caches
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Adjust file ownership and permissions for Apache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "DriveED Hub is starting up on port 80 (accessible via host port 8004)..."
exec "$@"
