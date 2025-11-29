#!/bin/bash
set -e

echo "Starting Laravel application..."

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

echo "Starting server on port ${PORT:-8080}..."

# Start the server
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}

