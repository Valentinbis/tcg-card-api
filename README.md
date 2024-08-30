# README - API Symfony 7

## Prérequis

Avant de commencer, assurez-vous d'avoir installé Docker sur votre machine.

## Installation du projet
### 1. Clonez ce dépôt sur votre machine :
```bash
git clone <lien-du-repo.git>
```

### 2. Accédez au répertoire du projet :
```bash
cd <nom-du-projet>
```

## Lancer le serveur de développement
Pour démarrer le serveur de développement Symfony ainsi que tout les outils utile à celui-ci, exécutez la commande suivante à la racine du projet :
```bash
docker-compose up --build -d
```

### 1. Installez les dépendances PHP à l'aide de Composer :
Il faut commencer par entrer dans le container :
```bash
docker exec -it cashtrack_api bash
```
Une fois dans le container il faut  installer les dépendances PHP
```bash
composer install
```
### 2. Base de données

Cette API utilise PostgreSQL comme base de données. Nous fournissons un fichier docker-compose.yml qui vous permettra de lancer une instance de PostgreSQL avec Docker.

Pour voir le schéma de la base de donnée veuillez cliquez sur ce lien :
https://dbdiagram.io/d/SuiviArgent-6554c2fe7d8bbd64653e5578

## Fixtures

Cette commande sert à alimenter la base de donnée de fausse donnée générer aléatoirement
```bash
php bin/console doctrine:fixtures:load 
```

Si vous ne voulez pas supprimer les données déjà présentes dans votre base de donnée ajouter '--append'

## Sonarqube

Après avoir démarrer le conteneur docker, vous pouvez executez cette commande pour démarrer le scan de sonarqube.
```bash
docker run \
    --rm \
    -e SONAR_HOST_URL="http://host.docker.internal:9000" \
    -e SONAR_SCANNER_OPTS="-Dsonar.projectKey=CashTrack" \
    -e SONAR_TOKEN="sqp_e83cbfa705278f8b8258af66522960cad618c909" \
    -v "/Users/v.bissay/Documents/dev/perso/Cashtrack/api/src:/usr/src" \
    sonarsource/sonar-scanner-cli K
``` 


## Commande de test

```bash
php bin/phpunit --coverage-html tests/Coverage
```

## Tâche cron

```bash
/usr/local/bin/php /app/bin/console app:generate-next-month-movements
```