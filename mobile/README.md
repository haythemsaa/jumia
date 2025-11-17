# ICHRI Tunisia - Application Mobile

Application mobile Flutter pour la plateforme e-commerce multi-vendeurs ICHRI Tunisia.

## 🚀 Technologies

- **Flutter 3** - Framework UI multi-plateforme
- **Dart** - Langage de programmation
- **Provider** - State management
- **Dio/HTTP** - Client HTTP
- **Shared Preferences** - Stockage local

## 📁 Structure du projet

```
mobile/
├── lib/
│   ├── main.dart              # Point d'entrée
│   ├── models/               # Modèles de données
│   │   └── product.dart
│   ├── providers/            # State management (Provider)
│   │   ├── auth_provider.dart
│   │   ├── cart_provider.dart
│   │   └── products_provider.dart
│   ├── screens/              # Écrans de l'application
│   │   ├── home_screen.dart
│   │   ├── auth/
│   │   ├── products/
│   │   ├── cart/
│   │   └── profile/
│   ├── services/             # Services API
│   │   └── api_service.dart
│   └── widgets/              # Composants réutilisables
│       └── product_card.dart
├── android/                  # Configuration Android
├── ios/                      # Configuration iOS
└── pubspec.yaml             # Dépendances Flutter
```

## 🛠️ Installation

### Prérequis
- Flutter SDK 3.0+
- Dart SDK
- Android Studio / Xcode (pour émulateurs)
- Backend Laravel sur http://localhost:8000

### Étapes d'installation

```bash
# Installer les dépendances
cd mobile
flutter pub get

# Vérifier l'installation Flutter
flutter doctor

# Lancer sur émulateur/appareil
flutter run
```

## 📦 Dépendances principales

```yaml
dependencies:
  flutter:
    sdk: flutter
  provider: ^6.1.1           # State management
  dio: ^5.4.0               # HTTP client
  shared_preferences: ^2.2.2 # Stockage local
  cached_network_image: ^3.3.0 # Cache images
  google_fonts: ^6.1.0      # Polices Google
```

## 🎨 Fonctionnalités

### Authentification
- ✅ Connexion utilisateur
- ✅ Inscription
- ✅ Déconnexion
- ✅ Gestion de session avec tokens

### Produits
- ✅ Liste des produits
- ✅ Produits mis en avant
- ✅ Recherche et filtres
- ✅ Détails produit

### Panier
- ✅ Ajouter au panier
- ✅ Voir le panier
- ✅ Calcul du total

### Profil
- ✅ Profil utilisateur
- ✅ Mes commandes
- ✅ Paramètres

## 🔧 Configuration

### API Base URL
Modifier dans `lib/services/api_service.dart`:
```dart
static const String baseUrl = 'http://YOUR_API_URL/api';
```

## 📱 Builds

### Android
```bash
# Debug APK
flutter build apk --debug

# Release APK
flutter build apk --release

# App Bundle (Play Store)
flutter build appbundle
```

### iOS
```bash
# Debug
flutter build ios --debug

# Release
flutter build ios --release
```

## 🎨 Theme

L'application utilise la couleur principale orange de ICHRI:
- Couleur primaire: `#FF9900`
- Couleur secondaire: `#FF7700`

## 🧪 Tests

```bash
# Tests unitaires
flutter test

# Tests d'intégration
flutter test integration_test
```

## 📄 License

MIT License - ICHRI Tunisia

## 👥 Support

Pour toute question ou problème:
- Email: support@ichri.tn
- GitHub: https://github.com/haythemsaa/ichri
