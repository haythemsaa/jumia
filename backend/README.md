# Jumia Tunisia - E-Commerce Marketplace Backend

Backend API Laravel 11 pour une plateforme e-commerce marketplace type Jumia, adaptée au marché tunisien.

## Stack Technique

- **Framework:** Laravel 11
- **PHP:** 8.4+
- **Base de données:** MySQL/PostgreSQL
- **Cache:** Redis (recommandé)
- **Search:** Elasticsearch/Meilisearch (recommandé)

## Structure Créée

### Migrations Complètes

✅ 18 tables de base de données créées:
- users (multi-rôles: client, vendor, admin)
- vendors (boutiques vendeurs)
- categories (hiérarchique)
- brands
- products (complet avec variants, images, reviews)
- orders & order_items
- carts & cart_items
- addresses
- payments
- shipping_methods
- coupons
- loyalty_points
- wishlists

### Modèles Eloquent

✅ **Modèles principaux avec relations complètes:**
- User (avec helper methods isAdmin(), isVendor(), isClient())
- Vendor (avec métriques de ventes)
- Category (structure parent/enfant)
- Brand
- Product (avec scopes active(), published(), featured())
- Order (génération automatique numéro commande)
- OrderItem

⏳ **Modèles squelettes créés (à compléter):**
- ProductImage, ProductVariant, ProductReview
- Address, Cart, CartItem
- Payment, ShippingMethod, Coupon
- LoyaltyPoint, Wishlist

## Installation

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Configurer .env avec votre base de données
php artisan migrate
```

## Fonctionnalités Implémentées

- ✅ Structure complète base de données
- ✅ Modèles principaux avec relations
- ✅ Soft deletes sur entités critiques
- ✅ Système multi-vendeurs avec commission
- ✅ Gestion complète des commandes
- ✅ Support multi-paiements (COD, Carte, E-Dinar, D17)
- ✅ Programme fidélité
- ✅ Codes promo
- ✅ Système d'avis produits

## Prochaines Étapes

1. Compléter les modèles restants
2. Créer Controllers API (ProductController, OrderController, etc.)
3. Authentification multi-rôles (Laravel Sanctum)
4. Request validations
5. API Resources
6. Policies & Autorisations
7. Seeders de test
8. Documentation API (Swagger)

---

**Version:** 0.1.0 - Structure Backend MVP
**Date:** 17 novembre 2025
