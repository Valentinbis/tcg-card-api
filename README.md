# README - API Symfony 7

## Prérequis

Avant de commencer, assurez-vous d'avoir installé PHP 8.3 sur votre machine. Vous pouvez télécharger PHP 8.3 depuis le site officiel de PHP et suivre les instructions d'installation appropriées pour votre système d'exploitation.

Assurez-vous également d'avoir Docker installé sur votre machine pour lancer la base de données PostgreSQL.

## Configuration de l'environnement

Après avoir installé PHP 8.3, ajoutez le chemin d'installation de PHP à la variable d'environnement de votre système pour pouvoir exécuter les commandes PHP depuis n'importe quel répertoire dans votre terminal.

## Installation du projet
### 1. Clonez ce dépôt sur votre machine :
```bash
git clone <lien-du-repo.git>
```

### 2. Accédez au répertoire du projet :
```bash
cd <nom-du-projet>
```

### 3. Installez les dépendances PHP à l'aide de Composer :
```bash
composer install
```

## Lancer le serveur de développement
Pour démarrer le serveur de développement Symfony, exécutez la commande suivante :
```bash
symfony serve
```

## Base de données

Cette API utilise PostgreSQL comme base de données. Nous fournissons un fichier docker-compose.yml qui vous permettra de lancer une instance de PostgreSQL avec Docker. Assurez-vous que Docker est installé et fonctionne correctement sur votre machine avant de continuer.

Pour lancer la base de données PostgreSQL, exécutez la commande suivante à la racine du projet :

```bash
docker-compose up -d
``` 

Pour voir le schéma de la base de donnée veuillez cliquez sur ce lien :
https://dbdiagram.io/d/SuiviArgent-6554c2fe7d8bbd64653e5578

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