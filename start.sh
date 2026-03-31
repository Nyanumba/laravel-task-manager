#!/bin/sh

echo "========================================="
echo " TaskFlow starting up..."
echo " PORT = $PORT"
echo " APP_ENV = $APP_ENV"
echo "========================================="

# Stop immediately if any command fails
set -e

# Clear all caches
echo "[1/5] Clearing caches..."
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear

# Generate app key if not set
echo "[2/5] Checking APP_KEY..."
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set. Generating one now..."
    php artisan key:generate --force
fi

# Run migrations
echo "[3/5] Running migrations..."
php artisan migrate --force

# Set storage permissions again at runtime
echo "[4/5] Setting storage permissions..."
chmod -R 777 storage bootstrap/cache

# Start the server
echo "[5/5] Starting Laravel server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}