# JUMIA Tunisia - Frontend

Frontend Next.js 14 pour la plateforme e-commerce multi-vendeurs JUMIA Tunisia.

## 🚀 Technologies

- **Next.js 14** - Framework React avec App Router
- **TypeScript** - Typage statique
- **Tailwind CSS** - Framework CSS utility-first
- **Zustand** - State management
- **Axios** - Client HTTP
- **React Hot Toast** - Notifications
- **React Icons** - Bibliothèque d'icônes

## 📁 Structure du projet

```
frontend/
├── app/                   # Pages Next.js (App Router)
│   ├── auth/             # Pages d'authentification
│   ├── products/         # Pages produits
│   ├── cart/             # Page panier
│   └── page.tsx          # Page d'accueil
├── components/           # Composants React
│   └── layout/          # Header, Footer, Layout
├── lib/                 # Bibliothèques et utilitaires
│   ├── api/            # Services API
│   └── stores/         # Stores Zustand
└── types/              # Types TypeScript
```

## 🛠️ Installation

### Prérequis
- Node.js 18+ et npm
- Backend Laravel sur http://localhost:8000

### Étapes

```bash
# Installer les dépendances
npm install

# Configurer l'environnement
cp .env.local.example .env.local

# Lancer le serveur
npm run dev
```

Application accessible sur http://localhost:3000

## 📦 Scripts

```bash
npm run dev     # Serveur de développement
npm run build   # Build de production
npm start       # Serveur de production
npm run lint    # Linter
```

## 🎨 Fonctionnalités

- ✅ Authentification (inscription, connexion)
- ✅ Catalogue produits avec recherche et filtres
- ✅ Panier d'achat
- ✅ Gestion vendeur
- ✅ Design responsive

## 📄 License

MIT License - JUMIA Tunisia
