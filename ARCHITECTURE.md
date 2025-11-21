# Architecture du projet TCG Card API

## 📋 Vue d'ensemble

API REST développée avec **Symfony 7** pour la gestion de collections de cartes Pokémon TCG.

### Stack technique
- **Framework** : Symfony 7.x
- **PHP** : 8.2+
- **Base de données** : PostgreSQL 16
- **ORM** : Doctrine
- **Tests** : PHPUnit avec couverture de code
- **Documentation** : OpenAPI 3.0 (NelmioApiDocBundle)
- **Containerisation** : Docker & Docker Compose

---

## 🗂️ Structure des dossiers

### `/src` - Code source
```
src/
├── Attribute/          # Attributs personnalisés (LogAction, LogPerformance, LogSecurity)
├── Command/            # Commandes console Symfony
├── Controller/         # Contrôleurs (API REST + Web)
│   └── API/           # Contrôleurs API (UserController, RegistrationController, etc.)
├── DataFixtures/       # Fixtures pour alimenter la BDD de test
├── DTO/               # Data Transfer Objects (PaginationDTO, CardViewDTO)
├── Entity/            # Entités Doctrine (User, Card, UserCard, etc.)
├── Enum/              # Enums PHP 8.1+ (LanguageEnum, etc.)
├── EventSubscriber/   # Subscribers Symfony (événements)
├── Form/              # Types de formulaires Symfony
├── Logger/            # Services de logging personnalisés
├── Repository/        # Repositories Doctrine
├── Security/          # Authentification, Voters, Guards
├── Serializer/        # Normalizers/Denormalizers personnalisés
└── Service/           # Services métier (CardService, PaginationService, etc.)
```

### `/config` - Configuration
```
config/
├── packages/          # Configuration des bundles Symfony
│   ├── doctrine.yaml
│   ├── nelmio_api_doc.yaml  # Configuration Swagger/OpenAPI
│   ├── security.yaml
│   └── ...
└── routes/           # Définition des routes
```

### `/tests` - Tests unitaires et fonctionnels
```
tests/
├── Unit/             # Tests unitaires (services, entities)
├── Feature/          # Tests fonctionnels (controllers)
└── Coverage/        # Rapports de couverture de code HTML
```

### `/docker` - Configuration Docker
```
docker/
├── db/
│   └── init/         # Scripts d'initialisation PostgreSQL (dumps SQL)
└── configs/          # Configurations PHP, cron, etc.
```

---

## 🔐 Authentification

### Système de tokens API
L'API utilise un système de token API personnalisé :

1. **Inscription** : `POST /api/register` → Crée un compte et retourne un token
2. **Connexion** : `POST /api/login` → Retourne un token API
3. **Utilisation** : Ajoutez le header `X-AUTH-TOKEN: votre_token` à chaque requête
4. **Déconnexion** : `GET /api/logout` → Invalide le token

### Rôles et permissions
- `ROLE_USER` : Utilisateur standard (accès aux endpoints de base)
- `ROLE_ADMIN` : Administrateur (suppression d'utilisateurs, etc.)

Les permissions sont gérées par des **Voters** :
- `AdminVoter` : Vérifie les droits administrateur
- `UserVoter` : Vérifie les droits utilisateur

---

## 📊 Gestion des données

### Entités principales

#### `User`
- Authentification et profil utilisateur
- Token API pour l'authentification stateless
- Relations avec `UserCard` (collection de l'utilisateur)

#### `Card`
- Informations sur les cartes Pokémon TCG
- Données provenant de l'API TCGdex
- Support multi-langues

#### `UserCard`
- Table de liaison entre `User` et `Card`
- Gestion des langues possédées par l'utilisateur

### Repositories
Les repositories étendent `ServiceEntityRepository` et contiennent les requêtes DQL complexes.

---

## 🎯 Patterns et bonnes pratiques

### Attributs personnalisés
Le projet utilise des attributs PHP 8 pour la traçabilité :

```php
#[LogAction('action_name', 'Description de l\'action')]
#[LogPerformance(threshold: 0.3)] // Log si > 300ms
#[LogSecurity('security_event', 'Description', 'warning')]
```

### DTOs (Data Transfer Objects)
Utilisés pour structurer les données entre les couches :
- `PaginationDTO` : Paramètres de pagination
- `CardViewDTO` : Représentation simplifiée d'une carte

### Serialization Groups
Les entités utilisent des groupes de sérialisation :
- `user.show` : Données publiques de l'utilisateur
- `user.token` : Inclut le token API (sensible)
- `card:read` : Données de carte en lecture

### MapEntity
Depuis Symfony 6.2+, utilisation de `#[MapEntity]` pour résoudre automatiquement les entités :

```php
public function user(#[MapEntity] User $user): JsonResponse
```

---

## 🧪 Tests

### Structure des tests
- **Tests unitaires** : Testent les services, repositories, entities isolément
- **Tests fonctionnels** : Testent les endpoints via des requêtes HTTP
- **Coverage** : Génération de rapport HTML avec `--coverage-html`

### Commandes
```bash
# Tous les tests
php bin/phpunit

# Avec couverture HTML
php bin/phpunit --coverage-html tests/Coverage

# Filtrer par groupe
php bin/phpunit --group security
```

### Fixtures
Les fixtures permettent de peupler la base avec des données de test :
```bash
php bin/console doctrine:fixtures:load
php bin/console doctrine:fixtures:load --append # Sans supprimer les données
```

---

## 📖 Documentation API

### Swagger UI
La documentation interactive est générée automatiquement via **NelmioApiDocBundle** :

- **URL** : http://localhost:8000/api/documentation
- **JSON OpenAPI** : http://localhost:8000/api/doc.json

### Configuration
Fichier : `config/packages/nelmio_api_doc.yaml`

Caractéristiques :
- Auto-découverte des endpoints via les routes Symfony
- Tags pour organiser les endpoints (Authentication, Users, Cards, System)
- Schémas de réponse définis
- Support de l'authentification par token
- Cache activé pour les performances

### Documentation des endpoints
Chaque méthode de contrôleur dispose d'un commentaire PHPDoc :

```php
/**
 * Récupère le profil de l'utilisateur connecté
 */
#[Route('/api/me', methods: ['GET'])]
public function me(): JsonResponse
```

NelmioApiDocBundle génère automatiquement la documentation à partir :
- Des routes et leurs méthodes HTTP
- Des commentaires PHPDoc
- Des attributs `#[IsGranted]`
- Des groupes de sérialisation

---

## 🐳 Docker

### Services
- `tcgcard_api` : Container PHP-FPM avec Symfony
- `tcgcard_db` : PostgreSQL 16
- `tcgcard_php` : Serveur web (si configuré)

### Volumes
- `./docker/db/init:/docker-entrypoint-initdb.d:ro` : Initialisation automatique de la BDD
- Cache Composer optimisé pour Windows (`compose.override.yaml`)

### Commandes utiles
```bash
# Démarrer les containers
docker compose up -d

# Accéder au container API
docker exec -it tcgcard_api bash

# Voir les logs
docker compose logs -f tcgcard_api

# Réinitialiser la BDD
docker compose down -v
docker compose up -d
```

---

## 🔧 Configuration

### Variables d'environnement
Fichiers `.env` :
- `.env` : Configuration par défaut (macOS/Linux)
- `.env.local` : Configuration locale (ignoré par Git)
- `.env.windows` : Configuration spécifique Windows

Variables importantes :
- `APP_ENV` : Environnement (dev/prod)
- `DATABASE_URL` : Connexion PostgreSQL
- `APP_NAME` : Nom de l'application

### Compatibilité Windows
Le projet inclut des optimisations pour Windows :
- `.gitattributes` : Force les fins de ligne LF
- `compose.override.yaml` : Volumes en mode cached
- `.dockerignore` : Optimise les builds Docker

---

## 🚀 Roadmap & Améliorations possibles

### Performance
- ✅ Cache APCu pour Doctrine
- ✅ Preload PHP pour les classes fréquentes
- ⏳ Redis pour le cache applicatif
- ⏳ Mise en cache HTTP avec Varnish

### Sécurité
- ✅ Rate limiting via configuration
- ⏳ JWT au lieu de tokens API simples
- ⏳ Rotation automatique des tokens
- ⏳ 2FA (authentification à deux facteurs)

### Fonctionnalités
- ⏳ Webhooks pour notifications
- ⏳ Export de collection (PDF, Excel)
- ⏳ Statistiques de collection
- ⏳ Partage de collection publique

### DevOps
- ✅ Tests automatisés avec PHPUnit
- ✅ SonarQube pour l'analyse de code
- ⏳ CI/CD avec GitHub Actions
- ⏳ Déploiement automatique

---

## 📞 Support

Pour toute question ou suggestion :
- Email : support@tcgcard.com
- Documentation API : http://localhost:8000/api/documentation
