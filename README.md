# Stubborn - Guide

Ce projet est un site e-commerce Symfony avec :
- inscription / connexion,
- boutique,
- panier,
- commande,
- back-office admin (gestion produits),
- paiement Stripe (simulation ou test réel).

---

## 1) Avant de commencer

### Prérequis
- XAMPP installé (PHP + MySQL)
- Composer
- Windows PowerShell

### Dossier du projet
Tu dois être dans le dossier racine du projet (celui qui contient `bin`, `src`, `templates`, `public`, etc.).

---

## 2) Installation (une seule fois)

Ouvre PowerShell dans le projet et lance :

```powershell
$php='C:/Users/darkl/Downloads/xampp-windows-x64-8.2.12-0-VS16/xampp/php/php.exe'
& $php composer.phar install
& $php bin/console doctrine:database:create --if-not-exists
& $php bin/console doctrine:schema:update --force
& $php bin/console doctrine:fixtures:load --no-interaction
```

Ce que ça fait :
1. installe les dépendances,
2. crée la base `stubborn`,
3. crée les tables,
4. insère les données de départ.

---

## 3) Lancer le projet

### Méthode recommandée (simple)

```powershell
$php='C:/Users/darkl/Downloads/xampp-windows-x64-8.2.12-0-VS16/xampp/php/php.exe'
& $php -S 127.0.0.1:8000 -t public
```

Puis ouvre dans le navigateur :
`http://127.0.0.1:8000`

---

## 4) Comptes de test

- Admin : `admin@stubborn.local` / `admin123`
- Client : `client@stubborn.local` / `client123`

---

## 5) Vérifier que tout fonctionne (checklist rapide)

1. **Accueil** : les produits s’affichent.
2. **Connexion client** : accès boutique + panier.
3. **Ajout panier** : ajouter un produit avec une taille.
4. **Commande** : bouton “Finaliser ma commande” fonctionne.
5. **Connexion admin** : accès au back-office.

---

## 6) Ajouter un nouveau produit (admin)

1. Connecte-toi avec le compte admin.
2. Va sur **Back-office**.
3. Remplis : nom, prix, stocks.
4. Clique sur **Télécharger une image** et choisis un fichier depuis ton PC.
5. Clique sur **AJOUTER**.

### Règles image
- formats autorisés : `jpg`, `jpeg`, `png`, `webp`
- taille max : **2 Mo**
- le fichier est copié automatiquement dans `public/images/products/`

---

## 7) Modifier / supprimer un produit

Dans Back-office, pour chaque produit :
- **MODIFIER** : change les infos (tu peux aussi uploader une nouvelle image)
- **SUPPRIMER** : supprime le produit

---

## 8) Tester un achat

## A) Mode simulation (par défaut)

Dans `.env`, tu gardes :

```dotenv
STRIPE_SECRET_KEY=sk_test_***
```

Parcours :
1. connexion client,
2. ajout produit au panier,
3. finaliser commande.

Résultat attendu : la commande passe en mode simulé.

## B) Mode Stripe test réel

1. Mets une vraie clé Stripe test dans `.env` :

```dotenv
STRIPE_SECRET_KEY=sk_test_xxxxxxxxx
```

2. Recharge la config :

```powershell
$php='C:/Users/darkl/Downloads/xampp-windows-x64-8.2.12-0-VS16/xampp/php/php.exe'
& $php bin/console cache:clear
```

3. Refaire un achat.
4. Sur Stripe Checkout, utilise une carte de test (ex: `4242 4242 4242 4242`).

---

## 9) Tester un nouvel inscrit (client)

### Ce qui se passe
- Le compte est créé avec `isVerified = false`.
- Un email de confirmation est généré.
- Après clic sur le lien, `isVerified = true`.

### Important en local
Actuellement :

```dotenv
MAILER_DSN=null://null
```

Donc pas d’email réel.

Pour tester l’email de confirmation en local :
- installe MailHog ou Mailpit,
- mets par exemple :

```dotenv
MAILER_DSN=smtp://127.0.0.1:1025
```

Puis :
1. lance MailHog/Mailpit,
2. inscris un nouveau compte,
3. ouvre l’email capturé,
4. clique le lien de confirmation.

---

## 10) Réinitialiser les données (si besoin)

```powershell
$php='C:/Users/darkl/Downloads/xampp-windows-x64-8.2.12-0-VS16/xampp/php/php.exe'
& $php bin/console doctrine:fixtures:load --no-interaction
```

---

## 11) Lancer les tests

### Tous les tests

```powershell
$php='C:/Users/darkl/Downloads/xampp-windows-x64-8.2.12-0-VS16/xampp/php/php.exe'
& $php bin/phpunit
```

Résultat attendu : `OK`.

### Tests unitaires du panier

Fichier : `tests/Service/CartServiceTest.php`

Ce test vérifie notamment :
- l'ajout d'un produit dans le panier,
- l'incrément de quantité,
- le calcul du total,
- la suppression d'une ligne du panier.

Commande pour lancer seulement ce test :

```powershell
$php='C:/Users/darkl/Downloads/xampp-windows-x64-8.2.12-0-VS16/xampp/php/php.exe'
& $php bin/phpunit tests/Service/CartServiceTest.php
```

### Test unitaire d'un achat (checkout en simulation)

Fichier : `tests/Service/StripeServiceTest.php`

Ce test vérifie :
- le démarrage du checkout,
- le mode simulation Stripe (`sk_test_***`),
- la redirection vers l'URL de succès.

Commande pour lancer seulement ce test :

```powershell
$php='C:/Users/darkl/Downloads/xampp-windows-x64-8.2.12-0-VS16/xampp/php/php.exe'
& $php bin/phpunit tests/Service/StripeServiceTest.php
```

---

## 12) En cas de problème fréquent

### `Unknown database 'stubborn'`
Relance :

```powershell
$php='C:/Users/darkl/Downloads/xampp-windows-x64-8.2.12-0-VS16/xampp/php/php.exe'
& $php bin/console doctrine:database:create --if-not-exists
& $php bin/console doctrine:schema:update --force
& $php bin/console doctrine:fixtures:load --no-interaction
```

### Port Apache/MySQL déjà utilisé
Utilise la méthode recommandée (`php -S 127.0.0.1:8000 -t public`) et garde seulement MySQL actif.
