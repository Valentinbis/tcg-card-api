#!/bin/bash

echo "=== STARTING TCG CARD API ==="
echo "Current directory: $(pwd)"
echo "Environment: $APP_ENV"
echo "Database URL: ${DATABASE_URL:0:50}..."

# Créer les dossiers nécessaires et corriger les permissions
mkdir -p var/cache var/log var/cache/prod/pools var/cache/prod/pools/app var/cache/prod/pools/system
chown -R www-data:www-data var/cache var/log var/cache/prod/pools
chmod -R 777 var/cache var/log var/cache/prod/pools

# Créer un fichier de test pour vérifier les permissions
touch var/cache/prod/test_permissions.tmp && rm var/cache/prod/test_permissions.tmp

# Attendre que la DB soit prête
echo "Waiting for database..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Database not ready, waiting..."
  sleep 2
done

echo "Database ready, running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Clearing and warming up cache..."
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod

# S'assurer que les dossiers de rate limiter existent
mkdir -p var/cache/prod/pools/app var/cache/prod/pools/system
chmod -R 777 var/cache/prod/pools

# Créer un dossier temporaire pour tester les permissions de cache
mkdir -p var/cache/prod/pools/app/test var/cache/prod/pools/system/test
chmod -R 777 var/cache/prod/pools/app/test var/cache/prod/pools/system/test
rmdir var/cache/prod/pools/app/test var/cache/prod/pools/system/test

echo "Cache ready, starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf