#!/bin/bash

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   📊 ICHRI TUNISIA - MONITORING & HEALTH CHECK           ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Check Docker containers
echo "🐳 État des conteneurs Docker:"
docker-compose ps
echo ""

# Check API health
echo "🔌 Vérification API Backend..."
API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/health || echo "000")
if [ "$API_STATUS" = "200" ]; then
    echo "✅ API Backend: ACTIF (200 OK)"
else
    echo "❌ API Backend: INACTIF (HTTP $API_STATUS)"
fi
echo ""

# Check Frontend
echo "🌐 Vérification Frontend..."
FRONTEND_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 || echo "000")
if [ "$FRONTEND_STATUS" = "200" ]; then
    echo "✅ Frontend: ACTIF (200 OK)"
else
    echo "❌ Frontend: INACTIF (HTTP $FRONTEND_STATUS)"
fi
echo ""

# Check Database
echo "🗄️  Vérification Base de Données..."
DB_STATUS=$(docker-compose exec -T mysql mysqladmin ping -h localhost -u root -proot_password 2>&1 | grep -c "mysqld is alive")
if [ "$DB_STATUS" -eq "1" ]; then
    echo "✅ MySQL: ACTIF"
else
    echo "❌ MySQL: INACTIF"
fi
echo ""

# Check Redis
echo "💾 Vérification Redis..."
REDIS_STATUS=$(docker-compose exec -T redis redis-cli ping 2>&1 | grep -c "PONG")
if [ "$REDIS_STATUS" -eq "1" ]; then
    echo "✅ Redis: ACTIF"
else
    echo "❌ Redis: INACTIF"
fi
echo ""

# Disk usage
echo "💿 Utilisation Disque:"
docker-compose exec -T app df -h | grep -E '(Filesystem|/var/www)'
echo ""

# Memory usage
echo "🧠 Utilisation Mémoire:"
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}"
echo ""

# Check logs for errors
echo "📝 Erreurs récentes (dernières 24h):"
ERROR_COUNT=$(docker-compose logs --since 24h 2>&1 | grep -i error | wc -l)
echo "   Nombre d'erreurs: $ERROR_COUNT"
if [ $ERROR_COUNT -gt 0 ]; then
    echo "   ⚠️  Consultez les logs avec: docker-compose logs"
fi
echo ""

# Queue status
echo "📮 État des Queues:"
QUEUE_COUNT=$(docker-compose exec -T app php artisan queue:failed --format=json 2>/dev/null | jq length 2>/dev/null || echo "0")
echo "   Jobs échoués: $QUEUE_COUNT"
echo ""

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   ✅ MONITORING TERMINÉ                                  ║"
echo "╚═══════════════════════════════════════════════════════════╝"
