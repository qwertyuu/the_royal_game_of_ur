#!/bin/sh
set -e

# Create log directory and set permissions first
echo "Setting up storage directories and permissions..."
mkdir -p /app/storage/logs
mkdir -p /app/storage/framework/cache
mkdir -p /app/storage/framework/sessions  
mkdir -p /app/storage/framework/views
mkdir -p /app/bootstrap/cache

# Set ownership and permissions
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan migrate:status > /dev/null 2>&1; do
  echo "Database not ready, waiting..."
  sleep 2
done

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

echo "Application initialized successfully!"

# Start supervisord
exec "$@"