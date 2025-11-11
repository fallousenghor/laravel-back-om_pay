#!/bin/bash

# Wait for database to be ready (if using PostgreSQL)
if [ "$DB_CONNECTION" = "pgsql" ]; then
  echo "Waiting for PostgreSQL to be ready..."
  while ! nc -z $DB_HOST $DB_PORT; do
    sleep 1
  done
  echo "PostgreSQL is ready!"
fi

# If env.production exists in the image, copy it to .env at runtime so
# runtime env is used (prevents using .env baked at build time).
if [ -f /var/www/html/.env.production ]; then
  echo "Found .env.production - copying to .env"
  cp /var/www/html/.env.production /var/www/html/.env
fi

# If APP_KEY is not set, generate one
if [ -f /var/www/html/.env ]; then
  if ! grep -q "^APP_KEY=\S" /var/www/html/.env; then
    echo "Generating APP_KEY"
    php artisan key:generate --force
  fi
fi

# Run database migrations (if needed)
php artisan migrate --force

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Start Apache
apache2-foreground