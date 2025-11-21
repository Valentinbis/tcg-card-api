# Documentation API avec Swagger/OpenAPI

## 📦 Installation

```bash
docker exec -it tcgcard_api composer require nelmio/api-doc-bundle
docker exec -it tcgcard_api php bin/console cache:clear
```

## 🚀 Configuration

Les fichiers de configuration sont déjà optimisés :

### `config/packages/nelmio_api_doc.yaml`
- ✅ Informations de l'API (titre, version, contact)
- ✅ Serveurs (dev et prod)
- ✅ Sécurité (token API)
- ✅ Tags pour organiser les endpoints
- ✅ Schémas de réponse prédéfinis
- ✅ Cache activé pour les performances
- ✅ Modèles avec groupes de sérialisation

### `config/routes/nelmio_api_doc.yaml`
- ✅ Route Swagger UI : `/api/documentation`
- ✅ Route OpenAPI JSON : `/api/doc.json`

## 📖 Accès à la documentation

Une fois le bundle installé :

| Type | URL | Description |
|------|-----|-------------|
| **Interface interactive** | http://localhost:8000/api/documentation | Swagger UI pour tester l'API |
| **JSON OpenAPI** | http://localhost:8000/api/doc.json | Spécification OpenAPI 3.0 |

## 🔐 Authentification

### Dans Swagger UI
1. Cliquez sur **"Authorize"** 🔓 en haut à droite
2. Entrez votre token API dans le champ `X-AUTH-TOKEN`
3. Cliquez sur **"Authorize"**
4. Testez les endpoints protégés directement

### Obtenir un token
```bash
# Inscription
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123","username":"testuser"}'

# Connexion
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

Le token sera retourné dans la réponse sous la clé `apiToken`.

## 📝 Endpoints documentés

La documentation est générée automatiquement à partir :
- Des routes Symfony
- Des PHPDoc sur les méthodes
- Des attributs `#[IsGranted]`
- Des groupes de sérialisation

### Organisation par tags

#### � Authentication
- `POST /api/register` - Inscription d'un nouvel utilisateur
- `POST /api/login` - Connexion et génération de token
- `GET /api/logout` - Déconnexion et invalidation du token

#### 👥 Users
- `GET /api/me` - Profil utilisateur connecté
- `GET /api/users` - Liste des utilisateurs
- `GET /api/user/{id}` - Détails d'un utilisateur
- `PUT /api/user/{id}` - Modifier un utilisateur
- `DELETE /api/user/{id}` - Supprimer un utilisateur (admin)

#### 🃏 Cards
- `GET /api/cards` - Liste des cartes avec filtres et pagination
- `POST /api/cards/{id}/languages` - Mise à jour des langues d'une carte

#### 🏥 System
- `GET /api` - Informations système et santé de l'API

## 🎨 Personnalisation

### Ajouter de la documentation à un endpoint

Il suffit d'ajouter un PHPDoc au-dessus de la méthode :

```php
/**
 * Récupère la liste des cartes avec filtres et pagination
 */
#[Route('/api/cards', methods: ['GET'])]
public function index(): JsonResponse
{
    // ...
}
```

NelmioApiDocBundle détecte automatiquement :
- La méthode HTTP (`GET`, `POST`, etc.)
- Le chemin de la route
- Les paramètres requis
- Les réponses possibles
- Les groupes de sérialisation

### Groupes de sérialisation

Contrôlez les données exposées :

```php
return $this->json($user, Response::HTTP_OK, [], [
    'groups' => ['user.show'] // Expose uniquement les champs du groupe
]);
```

Groupes disponibles :
- `user.show` : Données publiques de l'utilisateur
- `user.token` : Inclut le token API (sensible)
- `card:read` : Données de carte en lecture

## ⚡ Performances

### Cache
Le cache est activé via `cache.app` dans la configuration.

Pour vider le cache de documentation :
```bash
docker exec -it tcgcard_api php bin/console cache:pool:clear cache.app
```

### Optimisations
- Auto-découverte limitée aux routes `/api`
- Modèles pré-configurés (User, Card)
- Cache activé en production

## 🛠️ Commandes utiles

```bash
# Vider tout le cache
docker exec -it tcgcard_api php bin/console cache:clear

# Lister les routes de l'API
docker exec -it tcgcard_api php bin/console debug:router | grep /api

# Vérifier la configuration Nelmio
docker exec -it tcgcard_api php bin/console debug:config nelmio_api_doc
```

## 🚀 Prochaines étapes

### Pour aller plus loin (optionnel)
Si vous souhaitez une documentation encore plus détaillée, vous pouvez ajouter des attributs OpenAPI :

```php
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/users',
    summary: 'Liste tous les utilisateurs',
    tags: ['Users'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Liste des utilisateurs',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/User')
            )
        )
    ]
)]
#[Route('/api/users', methods: ['GET'])]
public function users(): JsonResponse
```

⚠️ **Note** : Cela alourdit le code. L'approche actuelle avec PHPDoc est plus légère et souvent suffisante.

## 📚 Documentation supplémentaire

- [ARCHITECTURE.md](ARCHITECTURE.md) - Architecture du projet
- [CONTRIBUTING.md](CONTRIBUTING.md) - Guide de contribution
- [SECURITY.md](SECURITY.md) - Politique de sécurité
- [NelmioApiDocBundle](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html) - Documentation officielle

## 🐛 Problèmes courants

### La documentation est vide
1. Vérifiez que le bundle est installé : `composer show nelmio/api-doc-bundle`
2. Videz le cache : `php bin/console cache:clear`
3. Vérifiez que les routes commencent par `/api`

### Le token ne fonctionne pas
1. Vérifiez le header : doit être `X-AUTH-TOKEN` (pas `Authorization`)
2. Copiez le token sans espaces ni guillemets
3. Vérifiez que l'utilisateur existe et que le token est valide

### Les schémas ne s'affichent pas
1. Vérifiez les groupes de sérialisation dans l'entité
2. Assurez-vous que les modèles sont définis dans `nelmio_api_doc.yaml`
3. Videz le cache de documentation

---

**Dernière mise à jour** : 5 novembre 2025
