# ICHRI Tunisia - Application Mobile

Application mobile e-commerce native pour iOS et Android construite avec React Native.

## 📱 Fonctionnalités

### Core
- **Accueil**: Slider hero, ventes flash, produits tendance, catégories
- **Catalogue**: Recherche, filtres, tri, pagination
- **Détail Produit**: Gallery images, variantes, avis, ajout panier
- **Panier**: Gestion quantités, calcul total, codes promo
- **Commande**: Processus checkout complet
- **Authentification**: Login/Register JWT
- **Profil**: Dashboard utilisateur, commandes, favoris

### Techniques
- React Native 0.72
- React Navigation (Stack + Bottom Tabs)
- Axios pour API REST
- AsyncStorage pour cache local
- React Native Vector Icons
- Toast notifications
- Animations fluides
- Support multi-langues (FR/AR/EN)
- Architecture modulaire

## 🚀 Installation

### Prérequis
```bash
- Node.js >= 16
- React Native CLI
- Android Studio (Android)
- Xcode (iOS - Mac uniquement)
```

### Installation
```bash
cd mobile
npm install

# iOS uniquement
cd ios && pod install && cd ..
```

### Configuration
Editez `src/config/api.js`:
```javascript
BASE_URL: 'http://votre-api.com/api'
```

### Lancement
```bash
# Android
npm run android

# iOS
npm run ios

# Metro bundler
npm start
```

## 📁 Structure

```
mobile/
├── App.js                          # Point d'entrée
├── index.js                        # Bootstrap RN
├── src/
│   ├── screens/                    # Écrans
│   │   ├── HomeScreen.js          # Accueil
│   │   ├── ProductsScreen.js      # Liste produits
│   │   ├── ProductDetailScreen.js # Détail
│   │   ├── CartScreen.js          # Panier
│   │   ├── CheckoutScreen.js      # Commande
│   │   ├── LoginScreen.js         # Connexion
│   │   ├── RegisterScreen.js      # Inscription
│   │   ├── ProfileScreen.js       # Profil
│   │   ├── OrdersScreen.js        # Commandes
│   │   └── WishlistScreen.js      # Favoris
│   ├── components/                 # Composants réutilisables
│   │   └── ProductCard.js         # Carte produit
│   ├── navigation/                 # Navigation
│   │   └── AppNavigator.js        # Routes
│   ├── services/                   # Services
│   │   └── api.service.js         # Client API
│   ├── config/                     # Configuration
│   │   ├── api.js                 # Config API
│   │   └── theme.js               # Thème & styles
│   └── utils/                      # Utilitaires
│       └── helpers.js             # Helpers
├── android/                        # Config Android
├── ios/                            # Config iOS
└── package.json                    # Dépendances
```

## 🎨 Design System

### Couleurs
- Primary: #667eea (Purple)
- Secondary: #764ba2 (Dark Purple)
- Success: #4CAF50
- Error: #F44336

### Composants
- ProductCard avec image, prix, boutons
- Navigation Bottom Tabs
- Toast notifications
- Cartes avec shadow
- Boutons gradient

## 🔧 API Integration

Client API complet dans `services/api.service.js`:
- Authentification (login, register, logout)
- Produits (liste, détail, recherche)
- Panier (CRUD)
- Commandes (création, liste)
- Wishlist
- Profil utilisateur

## 📱 Écrans

1. **Home**: Banner slider, catégories, produits tendance
2. **Products**: Liste avec filtres/tri/recherche
3. **ProductDetail**: Images, description, variantes, add to cart
4. **Cart**: Liste articles, quantités, total
5. **Checkout**: Formulaire livraison, confirmation
6. **Login**: Authentification
7. **Register**: Inscription
8. **Profile**: Menu utilisateur
9. **Orders**: Historique commandes
10. **Wishlist**: Produits favoris

## 🌍 Multi-langue

Support FR/AR/EN via configuration.

## 📦 Build Production

### Android APK
```bash
cd android
./gradlew assembleRelease
# APK dans: android/app/build/outputs/apk/release/
```

### iOS IPA
```bash
# Ouvrir dans Xcode
open ios/ICHRITunisia.xcworkspace

# Product > Archive > Distribute
```

## 🧪 Tests

```bash
npm test
```

## 📝 Notes

- Backend API Laravel requis
- Token JWT via AsyncStorage
- Cache local pour offline
- Images optimisées

## 🚀 Déploiement

### Google Play
1. Créer keystore Android
2. Build signed APK/AAB
3. Upload sur Play Console

### App Store
1. Certificats Apple
2. Archive Xcode
3. Upload via App Store Connect

---

Built with React Native ⚛️
