# Référence rapide des commandes

## 🚀 Démarrage rapide

```bash
# Démarrer l'environnement
docker compose up -d

# Installer les dépendances
docker exec -it tcgcard_api composer install

# Vérifier que tout fonctionne
./bin/diagnose.sh

# Accéder à l'API
curl http://localhost:8000/api
```

---

## 🐳 Docker

### Gestion des containers
```bash
# Démarrer
docker compose up -d

# Arrêter
docker compose down

# Redémarrer
docker compose restart

# Reconstruire les images
docker compose up --build -d

# Voir les logs
docker compose logs -f tcgcard_api
docker compose logs -f tcgcard_db

# État des containers
docker compose ps
```

### Accès aux containers
```bash
# Shell dans le container API
docker exec -it tcgcard_api bash

# Shell dans le container DB
docker exec -it tcgcard_db bash

# Commande ponctuelle
docker exec tcgcard_api php bin/console cache:clear
```

---

## 📦 Composer

```bash
# Installer les dépendances
docker exec -it tcgcard_api composer install

# Ajouter un package
docker exec -it tcgcard_api composer require vendor/package

# Mettre à jour
docker exec -it tcgcard_api composer update

# Audit de sécurité
docker exec -it tcgcard_api composer audit

# Autoloader optimisé (production)
docker exec -it tcgcard_api composer dump-autoload --optimize
```

---

## 🗄️ Base de données

### PostgreSQL
```bash
# Se connecter à la base
docker exec -it tcgcard_db psql -U tcgcard -d tcgcard

# Dump de la base
docker exec tcgcard_db pg_dump -U tcgcard tcgcard > backup.sql

# Restaurer un dump
docker exec -i tcgcard_db psql -U tcgcard tcgcard < backup.sql

# Réinitialiser la base
docker compose down -v
docker compose up -d
```

### Doctrine
```bash
# Créer une migration
docker exec tcgcard_api php bin/console make:migration

# Exécuter les migrations
docker exec tcgcard_api php bin/console doctrine:migrations:migrate

# Voir le statut des migrations
docker exec tcgcard_api php bin/console doctrine:migrations:status

# Créer la base (si elle n'existe pas)
docker exec tcgcard_api php bin/console doctrine:database:create

# Valider le schéma
docker exec tcgcard_api php bin/console doctrine:schema:validate
```

### Fixtures
```bash
# Charger les fixtures (écrase les données)
docker exec tcgcard_api php bin/console doctrine:fixtures:load

# Ajouter sans supprimer
docker exec tcgcard_api php bin/console doctrine:fixtures:load --append
```

---

## 🧪 Tests

```bash
# Tous les tests
docker exec tcgcard_api php bin/phpunit

# Avec couverture HTML
docker exec tcgcard_api php bin/phpunit --coverage-html tests/Coverage

# Tests d'un fichier spécifique
docker exec tcgcard_api php bin/phpunit tests/Unit/Service/CardServiceTest.php

# Filtrer par nom de test
docker exec tcgcard_api php bin/phpunit --filter testGetCard

# Tests avec groupe
docker exec tcgcard_api php bin/phpunit --group security
```

---

## 🔧 Symfony Console

### Cache
```bash
# Vider le cache
docker exec tcgcard_api php bin/console cache:clear

# Réchauffer le cache
docker exec tcgcard_api php bin/console cache:warmup

# Vider un pool de cache spécifique
docker exec tcgcard_api php bin/console cache:pool:clear cache.app
```

### Debug
```bash
# Lister les routes
docker exec tcgcard_api php bin/console debug:router

# Filtrer les routes
docker exec tcgcard_api php bin/console debug:router | grep /api

# Détails d'une route
docker exec tcgcard_api php bin/console debug:router api_cards

# Lister les services
docker exec tcgcard_api php bin/console debug:container

# Configuration d'un bundle
docker exec tcgcard_api php bin/console debug:config doctrine
```

### Création de code
```bash
# Créer une entité
docker exec -it tcgcard_api php bin/console make:entity

# Créer un contrôleur
docker exec -it tcgcard_api php bin/console make:controller

# Créer un service
docker exec -it tcgcard_api php bin/console make:service

# Créer un test
docker exec -it tcgcard_api php bin/console make:test

# Créer une commande
docker exec -it tcgcard_api php bin/console make:command
```

---

## 📖 Documentation API

```bash
# Installer Swagger
docker exec -it tcgcard_api composer require nelmio/api-doc-bundle

# Vider le cache après installation
docker exec -it tcgcard_api php bin/console cache:clear

# Accéder à Swagger UI
open http://localhost:8000/api/documentation
```

---

## 🔍 Analyse de code

### PHPStan
```bash
# Analyse statique
docker exec tcgcard_api vendor/bin/phpstan analyse src tests
```

### PHP CS Fixer
```bash
# Vérifier le style
docker exec tcgcard_api vendor/bin/php-cs-fixer fix --dry-run

# Corriger automatiquement
docker exec tcgcard_api vendor/bin/php-cs-fixer fix
```

### SonarQube
```bash
# Scanner le projet
docker run --rm \
  -e SONAR_HOST_URL="http://host.docker.internal:9000" \
  -e SONAR_TOKEN="votre_token" \
  -v "$(pwd):/usr/src" \
  sonarsource/sonar-scanner-cli
```

---

## 🔐 Sécurité

```bash
# Audit des dépendances
docker exec tcgcard_api composer audit

# Scanner les vulnérabilités Docker
docker scout cves tcgcard_api
```

---

## 📊 Logs

```bash
# Logs Symfony (dev)
docker exec tcgcard_api tail -f var/log/dev.log

# Logs Symfony (prod)
docker exec tcgcard_api tail -f var/log/prod.log

# Logs Docker
docker compose logs -f

# Logs d'un service spécifique
docker compose logs -f tcgcard_api

# Dernières lignes
docker compose logs --tail=100 tcgcard_api
```

---

## 🛠️ Maintenance

```bash
# Nettoyer les containers arrêtés
docker container prune

# Nettoyer les images inutilisées
docker image prune -a

# Nettoyer les volumes inutilisés
docker volume prune

# Nettoyer tout
docker system prune -a --volumes

# Espace disque utilisé
docker system df
```

---

## 🌐 Réseau

```bash
# Lister les réseaux
docker network ls

# Inspecter un réseau
docker network inspect tcg-card_default

# Ping entre containers
docker exec tcgcard_api ping tcgcard_db
```

---

## 📝 Variables d'environnement

```bash
# Voir toutes les variables
docker exec tcgcard_api env

# Voir une variable spécifique
docker exec tcgcard_api printenv APP_ENV

# Charger .env.local
cp .env.example .env.local
```

---

## 🚨 Dépannage

### Container ne démarre pas
```bash
# Voir les logs d'erreur
docker compose logs tcgcard_api

# Forcer la reconstruction
docker compose up --build --force-recreate -d
```

### Permission denied
```bash
# Corriger les permissions du dossier var/
docker exec tcgcard_api chmod -R 777 var/
```

### Base de données corrompue
```bash
# Réinitialiser complètement
docker compose down -v
docker compose up -d
```

### Cache bloqué
```bash
# Supprimer manuellement
docker exec tcgcard_api rm -rf var/cache/*
docker exec tcgcard_api php bin/console cache:warmup
```

---

## 📚 Ressources

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/index.html)
- [PHPUnit](https://phpunit.de/documentation.html)
- [Docker](https://docs.docker.com/)
- [PostgreSQL](https://www.postgresql.org/docs/)

---

**Astuce** : Ajoutez ces alias dans votre `~/.zshrc` ou `~/.bashrc` :

```bash
alias dc='docker compose'
alias dce='docker exec -it tcgcard_api'
alias dps='docker compose ps'
alias dlogs='docker compose logs -f'
alias sf='docker exec tcgcard_api php bin/console'
```

Puis rechargez : `source ~/.zshrc`

Usage :
```bash
dc up -d          # docker compose up -d
dce bash          # docker exec -it tcgcard_api bash
sf cache:clear    # docker exec tcgcard_api php bin/console cache:clear
```
