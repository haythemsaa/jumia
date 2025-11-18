# ICHRI Tunisia - E-Commerce Platform

🇹🇳 Plateforme e-commerce complète pour le marché tunisien avec backend Laravel, frontend Bootstrap 5 et application mobile React Native.

## 📋 Table des Matières

- [Vue d'ensemble](#vue-densemble)
- [Technologies](#technologies)
- [Structure du Projet](#structure-du-projet)
- [Installation](#installation)
- [Déploiement](#déploiement)
- [Fonctionnalités](#fonctionnalités)
- [Documentation](#documentation)
- [Licence](#licence)

## 🎯 Vue d'ensemble

ICHRI Tunisia est une solution e-commerce complète comprenant :

- **Backend API** : Laravel 11 avec architecture RESTful
- **Frontend Web** : Application Bootstrap 5 moderne et responsive
- **Application Mobile** : React Native pour iOS et Android
- **Admin Panel** : Interface d'administration complète
- **Vendor Dashboard** : Espace vendeur multi-marchés

## 🚀 Technologies

### Backend
- Laravel 11.x (PHP 8.2+)
- MySQL / PostgreSQL
- Redis (Cache & Queues)
- Laravel Sanctum (Authentication)
- Laravel Scout (Search)

### Frontend
- Bootstrap 5.3.2
- JavaScript ES6+
- Swiper.js
- AOS Animations

### Mobile
- React Native 0.72
- React Navigation 6
- Axios
- AsyncStorage

### DevOps
- Docker & Docker Compose
- GitHub Actions (CI/CD)
- Nginx
- Supervisor (Queue Workers)

## 📁 Structure du Projet

```
ichri-tunisia/
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── ...
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
├── frontend/                   # Bootstrap 5 Web App
│   ├── index.html
│   ├── css/
│   ├── js/
│   └── pages/
├── mobile/                     # React Native App
│   ├── src/
│   │   ├── screens/
│   │   ├── components/
│   │   ├── navigation/
│   │   └── services/
│   └── package.json
├── docker/                     # Docker configurations
├── docs/                       # Documentation
└── README.md
```

## 🛠️ Installation

### Prérequis

- PHP 8.2+
- Composer
- Node.js 16+
- MySQL 8.0+
- Redis
- Docker (optionnel)

### Installation Backend

```bash
# Cloner le repository
git clone https://github.com/haythemsaa/jumia.git
cd jumia/backend

# Installer les dépendances
composer install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate --seed

# Lancer le serveur
php artisan serve
```

### Installation Frontend

```bash
cd frontend

# Configurer l'API URL dans js/config.js
# Ouvrir index.html dans un navigateur
# Ou utiliser un serveur local
python -m http.server 8080
```

### Installation Mobile

```bash
cd mobile

# Installer les dépendances
npm install

# iOS
cd ios && pod install && cd ..
npm run ios

# Android
npm run android
```

## 🐳 Installation Docker

```bash
# Cloner et démarrer
git clone https://github.com/haythemsaa/jumia.git
cd jumia

# Lancer avec Docker Compose
docker-compose up -d

# Migrations
docker-compose exec app php artisan migrate --seed
```

Accès :
- API : http://localhost:8000
- Frontend : http://localhost:8080
- PhpMyAdmin : http://localhost:8081

## ✨ Fonctionnalités

### Pour les Clients
- ✅ Navigation produits avec filtres avancés
- ✅ Recherche intelligente
- ✅ Panier persistant
- ✅ Checkout multi-étapes
- ✅ Méthodes de paiement tunisiennes (E-Dinar, Konnect, D17, Cash)
- ✅ Suivi de commandes
- ✅ Wishlist
- ✅ Programme de fidélité (4 niveaux)
- ✅ Avis et notes produits
- ✅ Multi-langue (FR/AR/EN)

### Pour les Vendeurs
- ✅ Dashboard vendeur
- ✅ Gestion produits
- ✅ Gestion commandes
- ✅ Statistiques ventes
- ✅ Système de commissions
- ✅ Payouts automatiques

### Pour les Admins
- ✅ Dashboard administrateur
- ✅ Gestion utilisateurs
- ✅ Gestion catégories
- ✅ Gestion commandes globale
- ✅ Statistiques avancées
- ✅ Configuration système
- ✅ Logs et monitoring

### Technique
- ✅ API REST complète (70+ endpoints)
- ✅ Authentication JWT
- ✅ Rate limiting
- ✅ Caching Redis
- ✅ Queue workers
- ✅ Email notifications
- ✅ Push notifications (mobile)
- ✅ Image optimization
- ✅ SEO friendly
- ✅ Security best practices

## 📊 Base de Données

50+ tables incluant :
- Users & Authentication
- Products & Categories
- Orders & Order Items
- Cart & Wishlist
- Reviews & Ratings
- Payments & Transactions
- Shipping Methods & Zones
- Vendors & Commissions
- Loyalty Program
- Flash Sales
- Coupons
- Notifications

## 🔐 Sécurité

- CSRF Protection
- XSS Prevention
- SQL Injection Protection
- Rate Limiting
- JWT Authentication
- Password Hashing (bcrypt)
- HTTPS Enforced
- Security Headers
- Input Validation
- API Throttling

## 🌍 Internationalisation

Support complet pour :
- 🇫🇷 Français (défaut)
- 🇹🇳 العربية (RTL)
- 🇬🇧 English

## 📱 Application Mobile

### Fonctionnalités
- Navigation intuitive (Bottom Tabs + Stack)
- Catalogue produits avec recherche
- Panier synchronisé
- Checkout mobile-optimized
- Profil utilisateur
- Historique commandes
- Push notifications
- Mode offline (cart)

### Plateformes
- iOS (iPhone & iPad)
- Android (Phone & Tablet)

## 📚 Documentation

- [Documentation Backend](./backend/README.md)
- [Documentation Frontend](./frontend/README.md)
- [Documentation Mobile](./mobile/README.md)
- [API Documentation](./docs/API.md)
- [Deployment Guide](./docs/DEPLOYMENT.md)

## 🚀 Déploiement

### Production Checklist

**Backend**
- [ ] Configurer .env production
- [ ] Activer cache & optimizations
- [ ] Configurer queue workers
- [ ] Setup cron jobs
- [ ] Configurer SSL
- [ ] Activer monitoring

**Frontend**
- [ ] Minifier CSS/JS
- [ ] Optimiser images
- [ ] Configurer CDN
- [ ] Setup caching headers
- [ ] Activer HTTPS

**Mobile**
- [ ] Build signed APK/AAB
- [ ] Build iOS IPA
- [ ] Upload Google Play
- [ ] Upload App Store
- [ ] Configurer push notifications

## 🧪 Tests

```bash
# Backend tests
cd backend
php artisan test

# Frontend tests
cd frontend
npm test

# Mobile tests
cd mobile
npm test
```

## 📈 Performance

- Backend response time : < 200ms
- Frontend load time : < 2s
- Mobile app size : < 30MB
- API rate limit : 60 req/min
- Cache TTL : 5 minutes

## 🤝 Contribution

Les contributions sont les bienvenues ! Voir [CONTRIBUTING.md](./CONTRIBUTING.md)

## 📄 Licence

Ce projet est sous licence privée. Tous droits réservés.

## 👥 Équipe

- **Backend** : Laravel 11 API
- **Frontend** : Bootstrap 5 Web App
- **Mobile** : React Native iOS/Android
- **DevOps** : Docker & CI/CD

## 📞 Support

Pour toute question ou support :
- Email : support@ichri.tn
- Documentation : https://docs.ichri.tn
- Issues : https://github.com/haythemsaa/jumia/issues

## 🎉 Remerciements

Merci à tous les contributeurs et aux technologies open-source utilisées dans ce projet.

---

**ICHRI Tunisia** - Votre marketplace tunisienne de confiance 🇹🇳

Fait avec ❤️ en Tunisie
