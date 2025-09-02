#!/bin/sh
set -e

# Create log directory and set permissions first
echo "Setting up storage directories and permissions..."
mkdir -p /app/storage/logs
mkdir -p /app/storage/framework/cache
mkdir -p /app/storage/framework/sessions  
mkdir -p /app/storage/framework/views
mkdir -p /app/bootstrap/cache

# Set ownership and permissions (must be done AFTER directories are created)
# Force fix permissions even if they exist from build
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# Ensure log directory specifically has correct permissions
chown -R www-data:www-data /app/storage/logs
chmod -R 775 /app/storage/logs

# Wait for database to be ready with better connection testing
echo "Waiting for database connection..."
max_attempts=30
attempt=0

until nc -z ${DB_HOST} 3306; do
  attempt=$((attempt + 1))
  if [ $attempt -ge $max_attempts ]; then
    echo "Database connection failed after $max_attempts attempts"
    exit 1
  fi
  echo "Database not ready (attempt $attempt/$max_attempts), waiting..."
  sleep 3
done

echo "Database connection established!"

# Run migrations
echo "Running database migrations..."
cd /app && php artisan migrate --force

echo "Application initialized successfully!"

# Start supervisord as www-data to prevent permission issues
exec "$@"