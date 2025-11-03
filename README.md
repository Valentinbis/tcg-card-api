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

### Configuration pour Windows

Si vous utilisez Windows, suivez ces étapes supplémentaires :

#### 1. Configuration Git pour les fins de ligne
Avant de cloner le projet, configurez Git pour utiliser LF :
```bash
git config --global core.autocrlf false
git config --global core.eol lf
```

#### 2. Configuration de l'environnement
Copiez le fichier de configuration Windows :
```bash
copy .env.windows .env.local
```

Sur macOS/Linux, la configuration par défaut du `.env` suffit.

#### 3. WSL2 (Recommandé pour Windows)
Pour de meilleures performances, il est fortement recommandé d'utiliser WSL2 :
- Installez WSL2 et Docker Desktop avec l'intégration WSL2
- Clonez le projet dans le système de fichiers WSL2 (ex: `/home/user/projets/`)
- Lancez VS Code avec WSL: `code .` depuis WSL

### 1. Installez les dépendances PHP à l'aide de Composer :
Il faut commencer par entrer dans le container :
```bash
docker exec -it tcgcard_api bash
```
Une fois dans le container il faut  installer les dépendances PHP
```bash
composer install
```
### 2. Base de données

Cette API utilise PostgreSQL comme base de données. Nous fournissons un fichier docker-compose.yml qui vous permettra de lancer une instance de PostgreSQL avec Docker.

#### Initialisation automatique
Lors du premier démarrage, la base de données sera automatiquement initialisée avec les données de sauvegarde situées dans `docker/db/init/`.

#### Réinitialiser la base de données
Pour repartir avec une base de données fraîche :
```bash
docker compose down -v
docker compose up -d
```

Pour voir le schéma de la base de donnée veuillez cliquez sur ce lien :
//insérer lien bdd

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
    -e SONAR_SCANNER_OPTS="-Dsonar.projectKey=tcgcard" \
    -e SONAR_TOKEN="sqp_e83cbfa705278f8b8258af66522960cad618c909" \
    -v "/Users/v.bissay/Documents/dev/perso/tcgcard/api/src:/usr/src" \
    sonarsource/sonar-scanner-cli K
``` 


## Commande de test

```bash
php bin/phpunit --coverage-html tests/Coverage
```