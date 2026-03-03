# Stubborn - Site e-commerce Symfony

Projet Symfony + MySQL conforme au brief :
- Authentification `ROLE_CLIENT` / `ROLE_ADMIN`
- Routes demandées (`/`, `/login`, `/register`, `/products`, `/product/{id}`, `/cart`, `/admin`)
- Panier en session + finalisation de commande
- Service Stripe (mode simulation dev + mode réel si clé valide)
- Back-office admin (ajout / modification / suppression)
- Tests unitaires panier + achat simulé

## Prérequis

- PHP 8.2 (XAMPP)
- MySQL (XAMPP)
- Composer

## Installation

```bash
php composer.phar install
```

Configurer la base dans `.env` (déjà prêt pour XAMPP local) :

```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/stubborn?serverVersion=8.0.32&charset=utf8mb4"
```

Créer la base et le schéma :

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load --no-interaction
```

## Lancer l'application

```bash
php -S 127.0.0.1:8000 -t public
```

Puis ouvrir :
`http://127.0.0.1:8000`

## Comptes de démo

- Admin : `admin@stubborn.local` / `admin123`
- Client : `client@stubborn.local` / `client123`

## Images produits

Déposer les 10 images dans `public/images/products/` avec ces noms exacts :

- `blackbelt.jpeg`
- `bluebelt.jpeg`
- `street.jpeg`
- `pokeball.jpeg`
- `pinklady.jpeg`
- `snow.jpeg`
- `greyback.jpeg`
- `bluecloud.jpeg`
- `borninusa.jpeg`
- `greenschool.jpeg`

Puis recharger les fixtures :

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

## Stripe

Variable `.env` :

```dotenv
STRIPE_SECRET_KEY=sk_test_***
```

- Avec `sk_test_***`, le service est en **mode simulation**.
- Avec une vraie clé Stripe test, checkout Stripe est utilisé.

## Tests

```bash
php bin/phpunit
```

## Documentation livrable

Le contenu technique est décrit ici. Pour générer une version PDF du devoir :
- imprimer ce fichier (`README.md`) en PDF depuis l’éditeur,
- ou exporter en PDF via votre outil habituel.
