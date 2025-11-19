# 🔧 GUIDE DE MAINTENANCE - ICHRI TUNISIA

Guide complet pour maintenir votre marketplace en production.

## 📊 Monitoring

### Vérification Quotidienne
```bash
./monitor.sh
```

Ce script vérifie :
- ✅ État des conteneurs Docker
- ✅ API Backend (http://localhost:8000)
- ✅ Frontend (http://localhost:8080)
- ✅ Base de données MySQL
- ✅ Cache Redis
- ✅ Utilisation disque/mémoire
- ✅ Erreurs récentes
- ✅ État des queues

### Logs en Temps Réel
```bash
# Tous les services
docker-compose logs -f

# Backend uniquement
docker-compose logs -f app

# Base de données
docker-compose logs -f mysql
```

## 💾 Sauvegardes

### Backup Automatique
```bash
./backup.sh
```

Sauvegarde automatique :
- ✅ Base de données MySQL (compressée)
- ✅ Fichiers uploads
- ✅ Configuration (.env)
- ✅ Frontend & Mobile

**Stockage** : `./backups/`
**Rétention** : 7 jours

### Backup Planifié (Cron)
```bash
# Editer crontab
crontab -e

# Ajouter (backup quotidien à 2h du matin)
0 2 * * * cd /path/to/jumia && ./backup.sh >> /var/log/ichri-backup.log 2>&1
```

### Restoration
```bash
./restore.sh
```

Suivre les instructions pour restaurer depuis un backup.

## ⚡ Optimisations

### Optimisation Manuelle
```bash
./optimize.sh
```

Ce script effectue :
- ✅ Nettoyage caches Laravel
- ✅ Optimisation configuration/routes/views
- ✅ Optimisation Composer autoload
- ✅ Nettoyage Redis
- ✅ Optimisation base de données
- ✅ Nettoyage Docker

### Optimisation Automatique (Cron)
```bash
# Optimisation hebdomadaire (dimanche 3h)
0 3 * * 0 cd /path/to/jumia && ./optimize.sh >> /var/log/ichri-optimize.log 2>&1
```

## 🗄️ Base de Données

### Maintenance MySQL
```bash
# Vérifier la base
docker-compose exec mysql mysqlcheck -u ichri_user -pichri_password ichri_tunisia

# Optimiser les tables
docker-compose exec mysql mysqlcheck -u ichri_user -pichri_password --optimize ichri_tunisia

# Réparer les tables
docker-compose exec mysql mysqlcheck -u ichri_user -pichri_password --repair ichri_tunisia
```

### Réinitialiser les Données
```bash
# ⚠️  ATTENTION: Supprime toutes les données
docker-compose exec app php artisan migrate:fresh --seed
```

## 🔄 Mises à Jour

### Update Code
```bash
# Pull latest code
git pull origin main

# Update dependencies
docker-compose exec app composer install
cd frontend && npm install
cd mobile && npm install

# Run migrations
docker-compose exec app php artisan migrate

# Clear caches
./optimize.sh

# Restart services
docker-compose restart
```

### Update Docker Images
```bash
# Pull latest images
docker-compose pull

# Rebuild containers
docker-compose up -d --build

# Remove old images
docker image prune -f
```

## 📮 Queue Workers

### Vérifier les Queues
```bash
# Failed jobs
docker-compose exec app php artisan queue:failed

# Retry failed jobs
docker-compose exec app php artisan queue:retry all

# Flush failed jobs
docker-compose exec app php artisan queue:flush
```

### Restart Queue Workers
```bash
docker-compose restart queue
```

## 🔐 Sécurité

### Mettre à Jour les Clés
```bash
# Nouvelle APP_KEY
docker-compose exec app php artisan key:generate

# ⚠️  Attention: Déconnectera tous les utilisateurs
```

### Audit de Sécurité
```bash
# Check dependencies vulnerabilities
docker-compose exec app composer audit

# Update packages
docker-compose exec app composer update --with-all-dependencies
```

## 💥 Dépannage

### Reset Complet
```bash
# Stop all
docker-compose down

# Remove volumes (⚠️  Supprime les données)
docker-compose down -v

# Rebuild fresh
docker-compose up -d --build

# Restore from backup
./restore.sh
```

### Problèmes Courants

#### Site Lent
```bash
# Check resources
docker stats

# Optimize
./optimize.sh

# Check queue workers
docker-compose logs queue
```

#### Erreur 500
```bash
# Check logs
docker-compose logs app | tail -50

# Clear caches
docker-compose exec app php artisan cache:clear

# Check permissions
docker-compose exec app chown -R www-data:www-data storage
```

#### Base de données inaccessible
```bash
# Restart MySQL
docker-compose restart mysql

# Check status
docker-compose exec mysql mysqladmin ping
```

## 📈 Performance

### Monitoring Performance
```bash
# API response time
curl -w "@curl-format.txt" -o /dev/null -s http://localhost:8000/api/products

# Database queries
docker-compose exec app php artisan debugbar:clear
```

### Optimisations Production

**Laravel**
- ✅ Config cache activé
- ✅ Route cache activé
- ✅ View cache activé
- ✅ OPcache activé
- ✅ Queue workers actifs

**Redis**
- ✅ Cache activé
- ✅ Session storage Redis
- ✅ Queue driver Redis

**MySQL**
- ✅ Indexes optimisés
- ✅ Query cache activé

## 📊 Rapports

### Génération Rapports
```bash
# Sales report
docker-compose exec app php artisan reports:sales --month=current

# Users report
docker-compose exec app php artisan reports:users

# Products report
docker-compose exec app php artisan reports:products
```

## 🔔 Alertes

### Email Alerts
Configurer dans `.env` :
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=alerts@ichri.tn
MAIL_FROM_ADDRESS=alerts@ichri.tn
```

### Webhook Notifications
```bash
# Configure webhook URL in .env
WEBHOOK_URL=https://hooks.slack.com/services/...
```

## 📝 Logs

### Localisation Logs
```
Backend: backend/storage/logs/laravel.log
Nginx: docker logs ichri_nginx
MySQL: docker logs ichri_mysql
Redis: docker logs ichri_redis
```

### Rotation Logs
Les logs sont automatiquement rotatés tous les 7 jours.

## 🎯 Checklist Maintenance

### Quotidien
- [ ] ./monitor.sh (vérifier état)
- [ ] Vérifier erreurs logs
- [ ] Vérifier queue workers

### Hebdomadaire
- [ ] ./backup.sh (sauvegarde)
- [ ] ./optimize.sh (optimisation)
- [ ] Vérifier espace disque
- [ ] Review failed jobs

### Mensuel
- [ ] Update dependencies
- [ ] Security audit
- [ ] Performance review
- [ ] Database optimization
- [ ] Clean old backups

### Annuel
- [ ] SSL certificate renewal
- [ ] Full security audit
- [ ] Update Docker images
- [ ] Review architecture

---

**Maintenance régulière = Application stable** ⚡

---

ICHRI Tunisia - Production Maintenance Guide 🇹🇳
