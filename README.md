# Stubborn - Site e-commerce Symfony

Projet Symfony + MySQL (XAMPP) avec :
- Authentification client/admin (`ROLE_CLIENT`, `ROLE_ADMIN`)
- Boutique + fiche produit + panier + commande
- Back-office admin (ajout, modification, suppression produit)
- Upload d'image produit depuis l'ordinateur
- Stripe (simulation locale + mode test réel)
- Vérification email à l'inscription

## 1) Prérequis

- PHP 8.2 (XAMPP)
- MySQL/MariaDB (XAMPP)
- Composer

## 2) Configuration

Le `.env` est déjà configuré pour ce projet :

```dotenv
APP_ENV=dev
DATABASE_URL="mysql://root:@127.0.0.1:3306/stubborn?serverVersion=8.0.32&charset=utf8mb4"
STRIPE_SECRET_KEY=sk_test_***
MAILER_DSN=null://null
```

## 3) Installation rapide

Depuis la racine du projet :

```bash
php composer.phar install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load --no-interaction
```

## 4) Lancer le projet

### Option recommandée (serveur PHP intégré)

```bash
php -S 127.0.0.1:8000 -t public
```

Ouvrir ensuite :
`http://127.0.0.1:8000`

### Option XAMPP Apache

Si Apache est configuré correctement côté XAMPP, pointer vers le dossier `public/` et ouvrir le site via Apache.

## 5) Comptes de démonstration

- Admin : `admin@stubborn.local` / `admin123`
- Client : `client@stubborn.local` / `client123`

## 6) Ajouter un nouveau produit (Back-office)

1. Se connecter en **admin**
2. Aller dans **Back-office**
3. Remplir les champs : nom, prix, stocks
4. Dans **Télécharger une image**, cliquer pour ouvrir l'explorateur et choisir un fichier local
5. Cliquer sur **AJOUTER**

### Détails importants

- L'image est copiée automatiquement dans `public/images/products/`
- Le nom de fichier est sécurisé et rendu unique
- Formats autorisés : `jpg`, `jpeg`, `png`, `webp`
- Taille max : **2 Mo**

## 7) Modifier / supprimer un produit

- Dans Back-office, sur une ligne produit :
	- **MODIFIER** : ouvre l'édition (avec aperçu image actuelle)
	- **SUPPRIMER** : supprime le produit après confirmation

## 8) Tester un achat (panier + checkout)

### A) Mode simulation (par défaut)

Avec dans `.env` :

```dotenv
STRIPE_SECRET_KEY=sk_test_***
```

Parcours :
1. Se connecter en client
2. Aller en boutique
3. Ouvrir un produit
4. Choisir une taille + ajouter au panier
5. Aller au panier et cliquer **FINALISER MA COMMANDE**

Résultat : commande simulée validée et panier vidé.

### B) Mode Stripe test réel

1. Remplacer `STRIPE_SECRET_KEY` par une vraie clé test Stripe (`sk_test_...`)
2. Vider le cache :

```bash
php bin/console cache:clear
```

3. Refaire le parcours d'achat
4. Payer sur la page Stripe avec une carte de test (ex: `4242 4242 4242 4242`)

## 9) Vérifier un nouveau client inscrit

### Comportement

- À l'inscription, le compte est créé avec `isVerified = false`
- Un email de confirmation est généré
- Après clic sur le lien de vérification, `isVerified = true`

### En local (actuel)

Avec `MAILER_DSN=null://null`, les emails ne partent pas réellement.

Pour tester la vérification "en vrai", branche un outil SMTP local (MailHog/Mailpit), par exemple :

```dotenv
MAILER_DSN=smtp://127.0.0.1:1025
```

Puis :
1. Lancer MailHog/Mailpit
2. Inscrire un nouvel utilisateur
3. Ouvrir l'email reçu
4. Cliquer le lien de confirmation

## 10) Images initiales des fixtures

Les produits de base attendent les fichiers suivants dans `public/images/products/` :

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

Si besoin de réinitialiser les données :

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

## 11) Tests automatiques

```bash
php bin/phpunit
```

Résultat attendu actuel : tests OK.
