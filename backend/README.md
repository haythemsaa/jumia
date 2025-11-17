# ICHRI Tunisia - Backend API Documentation

## 🚀 Vue d'ensemble

API REST complète pour la plateforme e-commerce multi-vendeurs ICHRI Tunisia, construite avec Laravel 11.

## 📋 Fonctionnalités Complètes

### ✅ Sprint 1 - Fonctionnalités Critiques
- Paiements en ligne (E-Dinar, Konnect, D17, Cash)
- Notifications multi-canal (Push, Email, SMS)
- Système d'avis avec photos et modération
- Gestion retours & remboursements

### ✅ Sprint 2 - Fonctionnalités Importantes  
- Factures PDF professionnelles
- Comparateur de produits (max 4)
- Chat temps réel Client-Vendeur

### ✅ Sprint 3 - Gamification & Engagement
- Programme fidélité multi-niveaux (Bronze/Silver/Gold/Platinum)
- Flash Sales avec accès par tier
- Système de coupons avancé
- Missions et parrainage

### ✅ Sprint 4 - Optimisations & I18n
- Multi-langues (FR/AR/EN) avec RTL
- Middleware de locale
- Optimisations performances

## 🔧 Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=LoyaltySeeder
php artisan storage:link
php artisan serve
```

## 📡 Documentation API

Voir le fichier complet pour tous les endpoints disponibles.

**Version:** 1.0.0
**Date:** Novembre 2025
