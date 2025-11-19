#!/bin/bash

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   ⚡ ICHRI TUNISIA - OPTIMISATIONS                       ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Clear Laravel caches
echo "🧹 Nettoyage des caches Laravel..."
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
echo "✅ Caches nettoyés"
echo ""

# Optimize Laravel
echo "⚡ Optimisation Laravel..."
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
echo "✅ Laravel optimisé"
echo ""

# Optimize Composer autoload
echo "📦 Optimisation Composer..."
docker-compose exec app composer dump-autoload -o
echo "✅ Composer optimisé"
echo ""

# Clear Redis cache
echo "💾 Nettoyage Redis..."
docker-compose exec redis redis-cli FLUSHDB
echo "✅ Redis nettoyé"
echo ""

# Optimize Database
echo "🗄️  Optimisation Base de Données..."
docker-compose exec mysql mysqlcheck -u ichri_user -pichri_password --optimize ichri_tunisia 2>/dev/null
echo "✅ Base de données optimisée"
echo ""

# Docker cleanup
echo "🐳 Nettoyage Docker..."
docker system prune -f
echo "✅ Docker nettoyé"
echo ""

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   ✅ OPTIMISATIONS TERMINÉES !                           ║"
echo "╚═══════════════════════════════════════════════════════════╝"
