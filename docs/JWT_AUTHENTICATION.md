# JWT Authentication - Documentation API

## Vue d'ensemble

Le projet utilise **deux systèmes d'authentification parallèles** :

| | Web (formulaire) | API (JWT) |
|---|---|---|
| **Firewall** | `main` | `api` |
| **Pattern** | Tout sauf `/api` | `/api/*` |
| **Méthode** | Session + cookie | Token Bearer stateless |
| **Bundle** | Symfony Security | lexik/jwt-authentication-bundle |

Les deux firewalls utilisent le même `UserProvider` (entité `User`, identifié par `email`).

## Architecture de sécurité

### Comment ça fonctionne

1. Le client envoie `email` + `password` en JSON sur `POST /api/login`
2. Le `json_login` authenticator vérifie les credentials
3. Si valide, lexik génère un **JWT signé avec RSA (RS256)** contenant :
   - `username` (email de l'utilisateur)
   - `roles` (ROLE_USER, ROLE_ADMIN...)
   - `iat` (date de création)
   - `exp` (date d'expiration = iat + 3600s)
4. Le client inclut le token dans le header `Authorization: Bearer <token>` pour chaque requête
5. Le firewall `api` vérifie la signature du token avec la clé publique RSA

### Pourquoi c'est sécurisé

- **Stateless** : pas de session serveur, chaque requête est indépendante
- **Signature RSA** : le token est signé avec une clé privée (`config/jwt/private.pem`), vérifiable uniquement avec la clé publique correspondante — impossible à falsifier
- **TTL de 1h** : les tokens expirent automatiquement après 3600 secondes
- **Clés hors git** : les fichiers `.pem` sont dans le `.gitignore`

## Setup / Installation

### Prérequis

- PHP avec extension OpenSSL
- lexik/jwt-authentication-bundle installé (`composer require lexik/jwt-authentication-bundle`)

### Générer les clés JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

Crée :
- `config/jwt/private.pem` — clé privée (signature)
- `config/jwt/public.pem` — clé publique (vérification)

### Variables d'environnement

Présentes dans `.env` (ajoutées automatiquement) :

```dotenv
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=<passphrase_generee>
```

### Regénérer les clés (si nécessaire)

```bash
php bin/console lexik:jwt:generate-keypair --overwrite
```

## Endpoints API

### POST /api/login

Obtenir un token JWT.

**Request :**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "gabriel.saintlouis99@gmail.com", "password": "admin123"}'
```

**Response 200 :**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

**Response 401 :**
```json
{
    "code": 401,
    "message": "Invalid credentials."
}
```

---

### GET /api/posts

Liste tous les articles triés par priorité puis date.

**Headers :**
```
Authorization: Bearer <token>
```

**Request :**
```bash
curl http://localhost:8000/api/posts \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

**Response 200 :**
```json
[
    {
        "id": 1,
        "title": "Mon article",
        "slug": "mon-article",
        "content": "Contenu de l'article...",
        "picture": "image.jpg",
        "publishedAt": "2026-02-12T10:00:00+00:00",
        "priority": 1,
        "category": {
            "id": 1,
            "name": "Technologie"
        },
        "author": {
            "id": 1,
            "firstName": "Gabriel",
            "lastName": "Saint-Louis"
        },
        "commentsCount": 3
    }
]
```

---

### GET /api/posts/{id}

Détail d'un article avec ses commentaires.

**Request :**
```bash
curl http://localhost:8000/api/posts/1 \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

**Response 200 :**
```json
{
    "id": 1,
    "title": "Mon article",
    "slug": "mon-article",
    "content": "Contenu complet de l'article...",
    "picture": "image.jpg",
    "publishedAt": "2026-02-12T10:00:00+00:00",
    "priority": 1,
    "category": {
        "id": 1,
        "name": "Technologie",
        "description": "Articles tech"
    },
    "author": {
        "id": 1,
        "firstName": "Gabriel",
        "lastName": "Saint-Louis"
    },
    "comments": [
        {
            "id": 1,
            "content": "Super article !",
            "createdAt": "2026-02-12T11:00:00+00:00",
            "author": {
                "id": 2,
                "firstName": "Joel",
                "lastName": "Nutsugan"
            }
        }
    ]
}
```

---

### Requête sans token → 401

```bash
curl http://localhost:8000/api/posts
```

```json
{
    "code": 401,
    "message": "JWT Token not found"
}
```

## Fichiers de configuration

| Fichier | Rôle |
|---------|------|
| `config/packages/lexik_jwt_authentication.yaml` | Config du bundle (clés, TTL) |
| `config/packages/security.yaml` | Firewalls `api` + `main`, access_control |
| `config/jwt/private.pem` | Clé privée RSA (hors git) |
| `config/jwt/public.pem` | Clé publique RSA (hors git) |
| `src/Controller/Api/ApiLoginController.php` | Endpoint login |
| `src/Controller/Api/ApiPostController.php` | Endpoints posts |

## Commandes utiles

```bash
# Générer les clés JWT
php bin/console lexik:jwt:generate-keypair

# Regénérer (écraser)
php bin/console lexik:jwt:generate-keypair --overwrite

# Voir les routes API
php bin/console debug:router | grep api

# Vider le cache
php bin/console cache:clear
```
