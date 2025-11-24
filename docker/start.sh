#!/bin/bash

echo "=== STARTING TCG CARD API ==="
echo "Current directory: $(pwd)"
echo "Environment: $APP_ENV"
echo "Database URL: ${DATABASE_URL:0:50}..."

# Attendre que la DB soit prête
echo "Waiting for database..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Database not ready, waiting..."
  sleep 2
done

echo "Database ready, running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Migrations completed, starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf