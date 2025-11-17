# ICHRI Tunisia - Marketplace E-commerce

Plateforme e-commerce multi-vendeurs complète pour la Tunisie, construite avec Laravel 11, Next.js 14 et Flutter 3.

> ⚠️ **Statut Actuel:** MVP Fonctionnel - En développement actif
>
> 📊 **[Analyse Compétitive & Roadmap](COMPETITIVE_ANALYSIS.md)** | 🔧 **[Guide d'Implémentation](IMPLEMENTATION_GUIDE.md)** | ✅ **[TODO Technique](TODO.md)**

## 🚀 Vue d'ensemble

ICHRI Tunisia est un marketplace complet permettant aux vendeurs de vendre leurs produits et aux clients d'acheter en ligne avec livraison en Tunisie.

### 🎯 Vision & Positionnement

**Objectif:** Devenir la plateforme e-commerce leader en Tunisie avec une expérience utilisateur supérieure à la concurrence.

**Différenciation:**
- 🇹🇳 **100% Tunisien** - Adapté au marché local
- 💰 **Commission compétitive** - 12% (vs 15-20% concurrents)
- ⚡ **Onboarding rapide** - Vendeurs approuvés en 24h
- 🛡️ **Transparence totale** - Pas de frais cachés
- 📱 **Multi-plateforme** - Web + iOS + Android

### Technologies

**Backend**
- Laravel 11 (PHP 8.4+)
- MySQL/PostgreSQL
- Laravel Sanctum (Authentication)
- RESTful API

**Frontend Web**
- Next.js 14 (React)
- TypeScript
- Tailwind CSS
- Zustand (State Management)

**Application Mobile**
- Flutter 3
- Dart
- Provider (State Management)

## 📁 Structure du projet

```
ichri/
├── backend/              # API Laravel 11
│   ├── app/
│   ├── database/
│   ├── routes/
│   └── README.md
├── frontend/            # Web Next.js 14
│   ├── app/
│   ├── components/
│   ├── lib/
│   └── README.md
├── mobile/              # Application Flutter 3
│   ├── lib/
│   ├── android/
│   ├── ios/
│   └── README.md
└── README.md           # Ce fichier
```

## 🎨 Fonctionnalités

### Pour les clients
- ✅ Navigation et recherche de produits
- ✅ Filtres avancés (catégories, prix, marques)
- ✅ Panier d'achat
- ✅ Gestion de commandes
- ✅ Système de notation et avis
- ✅ Programme de fidélité
- ✅ Multiples méthodes de paiement
- ✅ Suivi de livraison

### Pour les vendeurs
- ✅ Dashboard vendeur
- ✅ Gestion de produits
- ✅ Gestion de commandes
- ✅ Statistiques de ventes
- ✅ Système de commissions (12% par défaut)

### Administration
- ✅ Gestion des utilisateurs
- ✅ Validation des vendeurs
- ✅ Gestion des catégories
- ✅ Gestion des méthodes de livraison
- ✅ Système de coupons

## 🛠️ Installation

### Prérequis
- PHP 8.4+
- Composer
- Node.js 18+
- npm
- MySQL/PostgreSQL
- Flutter SDK 3+ (pour mobile)

### Backend Laravel

```bash
cd backend

# Installer les dépendances
composer install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate
php artisan db:seed

# Lancer le serveur
php artisan serve
```

API accessible sur http://localhost:8000

### Frontend Next.js

```bash
cd frontend

# Installer les dépendances
npm install

# Configuration
cp .env.local.example .env.local

# Lancer le serveur
npm run dev
```

Application web accessible sur http://localhost:3000

### Mobile Flutter

```bash
cd mobile

# Installer les dépendances
flutter pub get

# Lancer sur émulateur/appareil
flutter run
```

## 📊 Base de données

Le projet utilise 19 tables principales:
- users, vendors
- products, product_images, product_variants, product_reviews
- categories, brands
- carts, cart_items
- orders, order_items
- payments, shipping_methods
- addresses, coupons
- loyalty_points, wishlists

## 🔐 Authentification

- Système basé sur Laravel Sanctum
- Tokens API pour authentification
- Rôles: client, vendor, admin
- Protection des routes selon les rôles

## 📡 API Endpoints

### Authentification
```
POST   /api/register       # Inscription
POST   /api/login          # Connexion
POST   /api/logout         # Déconnexion
GET    /api/user           # Utilisateur actuel
```

### Produits
```
GET    /api/products       # Liste des produits
GET    /api/products/{id}  # Détails produit
POST   /api/products       # Créer produit (vendeur)
PUT    /api/products/{id}  # Modifier produit
DELETE /api/products/{id}  # Supprimer produit
```

### Panier
```
GET    /api/cart           # Voir le panier
POST   /api/cart           # Ajouter au panier
PUT    /api/cart/{id}      # Modifier quantité
DELETE /api/cart/{id}      # Retirer du panier
```

### Commandes
```
GET    /api/orders         # Liste des commandes
POST   /api/orders         # Créer commande
GET    /api/orders/{id}    # Détails commande
POST   /api/orders/{id}/cancel # Annuler commande
```

Voir la documentation complète dans `/backend/README.md`

## 🎨 Design

### Couleurs principales
- Orange ICHRI: `#FF9900`
- Orange foncé: `#FF7700`
- Gris: `#363636`

### Responsive Design
- ✅ Mobile-first
- ✅ Tablette
- ✅ Desktop

## 🚢 Livraison

4 méthodes de livraison en Tunisie:
- Livraison Standard (7 TND, 3-7 jours)
- Livraison Express (12 TND, 24-48h)
- Poste Tunisienne (5 TND, 5-10 jours)
- Aramex (15 TND, 2-5 jours)

## 💳 Paiement

**Actuellement (MVP):**
- ⚠️ Paiement à la livraison
- ⚠️ Carte bancaire (manuel)
- ⚠️ Virement bancaire (manuel)

**En développement (Sprint 1):**
- 🚧 E-Dinar (Monétique Tunisie)
- 🚧 Konnect Payment
- 🚧 D17 Payment
- 🚧 Split payment automatique

## 📈 Roadmap & Développement

### 🔴 Sprint 1 (Semaine 1-2) - **CRITIQUE**
**Fonctionnalités bloquantes pour mise en production**
- [ ] Intégration paiement en ligne (E-Dinar, Konnect, D17)
- [ ] Système de notifications (Push + Email + SMS)
- [ ] Système d'avis et reviews complet avec photos
- [ ] Retours et remboursements
- [ ] Factures PDF automatiques
- [ ] Monitoring et logs (Sentry)
- [ ] Backups automatiques quotidiens
- [ ] CI/CD avec GitHub Actions

**Impact:** Ces fonctionnalités sont **essentielles** pour être compétitif. Sans elles, la plateforme ne peut pas rivaliser avec Jumia ou Amazon.

### 🟡 Sprint 2 (Semaine 3-4) - **IMPORTANT**
**Amélioration expérience utilisateur**
- [ ] Chat en temps réel (client-vendeur-support)
- [ ] Suivi de livraison en temps réel avec GPS
- [ ] Recherche avancée (Elasticsearch/Meilisearch)
- [ ] Comparateur de produits
- [ ] OAuth social login (Google, Facebook, Apple)
- [ ] Optimisation images et CDN

**Impact:** Augmente significativement la satisfaction client et les conversions.

### 🟢 Sprint 3 (Semaine 5-6) - **CROISSANCE**
**Engagement et rétention**
- [ ] Programme fidélité avancé avec niveaux (Bronze→Platinum)
- [ ] Flash sales et deals du jour
- [ ] Recommandations personnalisées (Machine Learning)
- [ ] Email marketing automatisé
- [ ] Programme d'affiliation
- [ ] Dashboard analytics avancé pour vendeurs

**Impact:** Augmente la valeur vie client (LTV) et le taux de rétention.

### 🔵 Sprint 4 (Semaine 7-8) - **OPTIMISATION**
**Performance et scalabilité**
- [ ] Multi-devises (EUR, USD, TND)
- [ ] Multi-langues (Arabe RTL, Français, Anglais)
- [ ] PWA (Progressive Web App)
- [ ] SEO avancé et sitemap
- [ ] Tests automatisés (Coverage >80%)
- [ ] A/B Testing

**Impact:** Expansion internationale et optimisation conversions.

### 🚀 Phase Innovation (Mois 3+)
**Différenciation concurrentielle**
- [ ] Recherche par image (Visual Search)
- [ ] Recherche vocale
- [ ] AR pour essayage virtuel
- [ ] Live Shopping / Streaming
- [ ] Abonnement ICHRI Prime (livraison gratuite illimitée)
- [ ] Blockchain pour traçabilité
- [ ] Intelligence artificielle avancée

**Impact:** Innovation et leadership technologique.

---

## 📊 Gap Analysis vs Concurrents

| Fonctionnalité | ICHRI (Actuel) | Jumia | Amazon | Priorité |
|---|:---:|:---:|:---:|---|
| Paiement en ligne | ⚠️ Manuel | ✅ | ✅ | 🔴 CRITIQUE |
| Notifications temps réel | ❌ | ✅ | ✅ | 🔴 CRITIQUE |
| Avis avec photos | ❌ | ✅ | ✅ | 🔴 CRITIQUE |
| Retours en ligne | ❌ | ✅ | ✅ | 🔴 CRITIQUE |
| Chat support | ❌ | ✅ | ✅ | 🟡 Important |
| Tracking GPS | ❌ | ✅ | ✅ | 🟡 Important |
| Recherche avancée | ⚠️ Basique | ✅ | ✅ | 🟡 Important |
| Programme fidélité | ⚠️ Basique | ✅ | ✅ | 🟢 Moyen |
| Multi-langues | ❌ | ✅ | ✅ | 🟢 Moyen |
| Flash Sales | ❌ | ✅ | ✅ | 🟢 Moyen |

**Diagnostic:**
- ✅ **Forces:** Architecture solide, commission compétitive, focus local
- ⚠️ **Faiblesses:** Manque fonctionnalités critiques de confiance
- 🎯 **Objectif:** Atteindre 80% des fonctionnalités de Jumia en 2 mois

➡️ **Voir [Analyse Compétitive Complète](COMPETITIVE_ANALYSIS.md) pour détails**

---

## 💰 Investment & Resources

**Budget estimé Phase 1-2 (2 mois):**
- Développement: 28,000 TND
- Infrastructure & Services: 2,600 TND
- **Total:** ~30,600 TND

**Retour sur investissement attendu:**
- Augmentation conversions: +40%
- Réduction abandon panier: -30%
- Augmentation panier moyen: +25%
- Amélioration NPS: +35 points

➡️ **Voir [Guide d'Implémentation](IMPLEMENTATION_GUIDE.md) pour détails techniques**
➡️ **Voir [TODO Technique](TODO.md) pour tâches détaillées**

## 📄 Documentation

- [Backend Documentation](backend/README.md)
- [Frontend Documentation](frontend/README.md)
- [Mobile Documentation](mobile/README.md)
- [Specifications](Cahier_Specifications_Complet_Ecommerce_Tunisie.md)

## 🤝 Contribution

Les contributions sont les bienvenues! Veuillez:
1. Fork le projet
2. Créer une branche feature
3. Commit vos changements
4. Push vers la branche
5. Ouvrir une Pull Request

## 📝 License

MIT License - ICHRI Tunisia

## 👥 Contact

- Email: support@ichri.tn
- GitHub: https://github.com/haythemsaa/ichri

---

Développé avec ❤️ pour la Tunisie
