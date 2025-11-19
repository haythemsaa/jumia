#!/bin/bash

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   🇹🇳 ICHRI TUNISIA - INSTALLATION AUTOMATIQUE 🚀         ║"
echo "║   Démarrage en 1 clic - Production Ready                  ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Vérifier Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé. Installez Docker d'abord."
    echo "   Visitez: https://docs.docker.com/get-docker/"
    exit 1
fi

echo "✅ Docker détecté"
echo ""

# Démarrer les conteneurs
echo "📦 Démarrage des conteneurs Docker..."
docker-compose up -d

# Attendre que MySQL soit prêt
echo "⏳ Attente de MySQL..."
sleep 10

# Installer les dépendances backend
echo "📚 Installation des dépendances Laravel..."
docker-compose exec -T app composer install --no-interaction

# Configuration Laravel
echo "⚙️  Configuration de Laravel..."
docker-compose exec -T app cp .env.example .env
docker-compose exec -T app php artisan key:generate
docker-compose exec -T app php artisan storage:link

# Migrations et seeders
echo "🗄️  Création de la base de données..."
docker-compose exec -T app php artisan migrate:fresh --seed

# Cache et optimisations
echo "⚡ Optimisation de l'application..."
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

# Permissions
echo "🔐 Configuration des permissions..."
docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec -T app chmod -R 775 storage bootstrap/cache

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║              ✅ INSTALLATION TERMINÉE !                   ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo "🌐 Accès aux applications:"
echo ""
echo "   Frontend:     http://localhost:8080"
echo "   API Backend:  http://localhost:8000"
echo "   PhpMyAdmin:   http://localhost:8081"
echo ""
echo "👤 Comptes de test créés:"
echo ""
echo "   ADMIN:"
echo "   Email:    admin@ichri.tn"
echo "   Password: password"
echo ""
echo "   CLIENT:"
echo "   Email:    client@ichri.tn"
echo "   Password: password"
echo ""
echo "   VENDEUR:"
echo "   Email:    vendor@ichri.tn"
echo "   Password: password"
echo ""
echo "📚 Documentation:"
echo "   - README.md (vue d'ensemble)"
echo "   - PROJECT_COMPLETE.md (détails complets)"
echo "   - docs/DEPLOYMENT.md (guide déploiement)"
echo ""
echo "🎉 Votre marketplace est prête à l'emploi!"
echo ""
