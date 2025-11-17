# Ichri Tunisia - E-Commerce Marketplace Backend

Backend API Laravel 11 complet pour une plateforme e-commerce marketplace type Ichri, adaptée au marché tunisien.

## Stack Technique

- **Framework:** Laravel 11
- **PHP:** 8.4+
- **Authentification:** Laravel Sanctum (Token-based API)
- **Base de données:** MySQL/PostgreSQL
- **Cache:** Redis (recommandé)
- **Search:** Elasticsearch/Meilisearch (recommandé)

## Installation

```bash
cd backend

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env.example .env
php artisan key:generate

# Configurer la base de données dans .env
DB_CONNECTION=mysql
DB_DATABASE=ichri_tunisia
DB_USERNAME=root
DB_PASSWORD=

# Exécuter les migrations
php artisan migrate

# Peupler la base avec des données de test
php artisan db:seed
```

## Structure Complète

### Migrations (19 tables)

✅ **Tables de base de données:**
- `users` - Multi-rôles (client, vendor, admin) avec Sanctum tokens
- `vendors` - Boutiques vendeurs avec système d'approbation
- `categories` - Structure hiérarchique parent/enfant
- `brands` - Marques de produits
- `products` - Catalogue complet avec variants, images, reviews
- `product_images` - Images multiples par produit
- `product_variants` - Variations (taille, couleur, etc.)
- `product_reviews` - Système d'avis clients
- `addresses` - Adresses livraison/facturation
- `carts` & `cart_items` - Panier (anonyme et authentifié)
- `orders` & `order_items` - Système de commandes
- `payments` - Historique des paiements
- `shipping_methods` - Méthodes de livraison
- `coupons` - Codes promo
- `loyalty_points` - Programme fidélité
- `wishlists` - Listes de souhaits
- `personal_access_tokens` - Tokens Sanctum

### Modèles Eloquent (14 modèles complets)

✅ **Tous les modèles avec relations complètes:**

1. **User** - Avec traits HasApiTokens, SoftDeletes
   - Helper methods: `isAdmin()`, `isVendor()`, `isClient()`
   - Relations: vendor, addresses, orders, cart, reviews, wishlist

2. **Vendor** - Profils vendeurs
   - Méthodes: `getTotalEarnings()`, `getTotalCommissionPaid()`
   - Statuts: pending, approved, rejected, suspended

3. **Product** - Catalogue produits
   - Scopes: `active()`, `published()`, `featured()`, `inStock()`
   - Auto-gestion du stock et des ratings
   - Relations: vendor, category, brand, images, variants, reviews

4. **Category** - Catégories hiérarchiques
   - Structure parent/enfant illimitée

5. **Cart & CartItem** - Panier complet
   - Méthodes: `addItem()`, `removeItem()`, `getSubtotal()`
   - Support utilisateurs authentifiés et anonymes

6. **Order & OrderItem** - Système de commandes
   - Génération automatique de numéro de commande
   - Workflow complet de statuts
   - Calcul automatique des commissions vendeurs
   - Restauration stock lors d'annulation

7. **Payment** - Gestion paiements
   - Méthodes multi-gateway (COD, Carte, E-Dinar, D17)

8. **Coupon** - Codes promo
   - Validation automatique
   - Calcul des remises (pourcentage ou montant fixe)

9. **LoyaltyPoint** - Programme fidélité
   - Méthodes: `awardPoints()`, `redeemPoints()`, `getUserBalance()`

10. **+ 4 autres modèles** (ProductImage, ProductVariant, ProductReview, ShippingMethod, Address, Wishlist)

### Controllers API (6 controllers complets)

✅ **Controllers REST avec toutes les fonctionnalités:**

#### 1. AuthController
- `POST /api/register` - Inscription (client/vendor)
- `POST /api/login` - Connexion avec token
- `POST /api/logout` - Déconnexion
- `GET /api/user` - Profil utilisateur

#### 2. ProductController
- `GET /api/products` - Liste avec filtres avancés
  - Recherche, catégorie, marque, prix, stock
  - Tri et pagination
- `GET /api/products/{id}` - Détails produit
- `GET /api/products/featured` - Produits vedettes
- `GET /api/products/{id}/related` - Produits similaires
- `POST /api/products` - Créer (vendeur uniquement)
- `PUT /api/products/{id}` - Modifier
- `DELETE /api/products/{id}` - Supprimer

#### 3. CartController
- `GET /api/cart` - Obtenir le panier
- `POST /api/cart` - Ajouter un article
- `PUT /api/cart/items/{id}` - Modifier quantité
- `DELETE /api/cart/items/{id}` - Supprimer article
- `DELETE /api/cart/clear` - Vider le panier

#### 4. OrderController
- `GET /api/orders` - Liste des commandes
- `POST /api/orders` - Créer une commande
  - Gestion automatique du stock
  - Application des coupons
  - Création des paiements
  - Calcul des commissions
- `GET /api/orders/{id}` - Détails commande
- `PUT /api/orders/{id}/cancel` - Annuler (avec restauration stock)

#### 5. CategoryController
- `GET /api/categories` - Liste hiérarchique
- `GET /api/categories/{id}` - Détails avec produits
- `POST /api/categories` - Créer (admin)
- `PUT /api/categories/{id}` - Modifier (admin)
- `DELETE /api/categories/{id}` - Supprimer (admin)

#### 6. VendorController
- `GET /api/vendors` - Liste vendeurs approuvés
- `GET /api/vendors/{id}` - Détails vendeur
- `POST /api/vendor/register` - Inscription vendeur
- `GET /api/vendor/dashboard` - Statistiques vendeur
- `GET /api/vendor/products` - Produits du vendeur
- `GET /api/vendor/orders` - Commandes du vendeur
- `PUT /api/vendor/orders/{id}/status` - Mettre à jour statut

### Middleware

✅ **Middleware personnalisé:**
- `EnsureUserIsVendor` - Protection des routes vendeurs
  - Vérification du rôle vendor
  - Vérification de l'approbation du compte

### Seeders (Données de test)

✅ **Seeders créés:**
- `CategorySeeder` - 5 catégories principales + sous-catégories
  - Électronique, Mode, Maison & Décoration, Beauté & Santé, Sports
- `BrandSeeder` - 21 marques (Samsung, Apple, Nike, Zara, etc.)
- `ShippingMethodSeeder` - 4 méthodes de livraison tunisiennes
  - Standard, Express, Poste Tunisienne, Aramex
- `DatabaseSeeder` - Utilisateurs de test
  - Admin: admin@ichri.tn
  - Client: client@example.com
  - Vendeur: vendor@example.com (avec boutique approuvée)

**Mot de passe pour tous:** `password`

## Routes API Complètes

### Routes Publiques
```
POST   /api/register
POST   /api/login
GET    /api/products
GET    /api/products/featured
GET    /api/products/{id}
GET    /api/products/{id}/related
GET    /api/categories
GET    /api/categories/{id}
GET    /api/vendors
GET    /api/vendors/{id}
```

### Routes Protégées (Authentification requise)
```
POST   /api/logout
GET    /api/user
GET    /api/cart
POST   /api/cart
PUT    /api/cart/items/{id}
DELETE /api/cart/items/{id}
DELETE /api/cart/clear
GET    /api/orders
POST   /api/orders
GET    /api/orders/{id}
PUT    /api/orders/{id}/cancel
POST   /api/vendor/register
```

### Routes Vendeur (Middleware: vendor)
```
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
GET    /api/vendor/dashboard
GET    /api/vendor/products
GET    /api/vendor/orders
PUT    /api/vendor/orders/{id}/status
```

## Fonctionnalités Business

### Système Multi-Vendeurs
- Inscription et approbation vendeurs
- Commission automatique (défaut: 12%)
- Dashboard avec statistiques
- Gestion des produits et commandes

### Gestion des Commandes
- Création automatique depuis le panier
- Vérification stock en temps réel
- Application automatique des coupons
- Calcul des frais de livraison
- Génération numéro de commande unique
- Workflow de statuts complet
- Annulation avec restauration stock

### Système de Paiement
- Support multi-gateway: COD, Carte, E-Dinar, D17, Virement
- Historique des transactions
- Statuts de paiement

### Programme Fidélité
- Points gagnés sur les commandes
- Points utilisables comme remise
- Expiration automatique après 1 an

### Codes Promo
- Remise pourcentage ou montant fixe
- Montant minimum d'achat
- Plafond de remise
- Limite d'utilisation globale et par utilisateur
- Période de validité

## Utilisation de l'API

### Authentification

```bash
# Inscription
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Connexion
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Utiliser le Token

```bash
# Requête authentifiée
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Architecture & Design Patterns

- **Repository Pattern** via Eloquent Models
- **RESTful API** design
- **Token-based Authentication** (Sanctum)
- **Soft Deletes** sur entités critiques
- **Query Scopes** pour requêtes réutilisables
- **Eager Loading** pour optimisation N+1
- **Transactions** pour opérations critiques
- **Auto-calculations** (ratings, commissions, stock)

## Sécurité

✅ **Implémenté:**
- Authentification token-based
- Validation des requêtes
- Protection CSRF
- Hashage des mots de passe (bcrypt)
- Middleware d'autorisation
- Soft deletes pour traçabilité

## Prochaines Évolutions (Optionnel)

1. **API Resources** - Transformation données JSON
2. **Form Requests** - Validation avancée
3. **Policies** - Autorisations fines
4. **Tests** - Unitaires et d'intégration
5. **Documentation API** - Swagger/OpenAPI
6. **Rate Limiting** - Protection contre abus
7. **Queue Jobs** - Emails, notifications asynchrones
8. **Search Engine** - Elasticsearch/Meilisearch

## Support

Pour toute question ou problème:
- Email: admin@ichri.tn
- GitHub Issues

---

**Version:** 1.0.0 - Backend Complet
**Date:** 17 novembre 2025
**Statut:** Production Ready ✅
