# Dockerfile production pour Railway
FROM php:8.4-fpm-alpine AS base

# Installation des dépendances système
RUN apk update && apk add --no-cache \
    postgresql-dev \
    icu-dev \
    zip \
    unzip \
    curl \
    bash \
    nginx \
    supervisor

# Installation des extensions PHP
RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    opcache

# Configuration opcache pour production
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# =====================================
# Stage build : Installation des dépendances
# =====================================
FROM base AS build

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Créer les dossiers nécessaires
RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data var && \
    chmod -R 755 var

# =====================================
# Stage final : Production
# =====================================
FROM base AS production

# Copier les fichiers buildés
COPY --from=build --chown=www-data:www-data /app /app

# Configuration Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configuration Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Donner les permissions d'exécution au script de démarrage
RUN chmod +x docker/start.sh

# Script de démarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Port exposé
EXPOSE 8080

# Lancer le script de démarrage
CMD ["/start.sh"]
