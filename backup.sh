#!/bin/bash

BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)
DB_BACKUP="$BACKUP_DIR/database_$DATE.sql"
FILES_BACKUP="$BACKUP_DIR/files_$DATE.tar.gz"

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   💾 ICHRI TUNISIA - BACKUP AUTOMATIQUE                  ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup Database
echo "📦 Sauvegarde de la base de données..."
docker-compose exec -T mysql mysqldump -u ichri_user -pichri_password ichri_tunisia > $DB_BACKUP
if [ $? -eq 0 ]; then
    echo "✅ Base de données sauvegardée: $DB_BACKUP"
    gzip $DB_BACKUP
    echo "   Compressé: ${DB_BACKUP}.gz"
else
    echo "❌ Erreur lors de la sauvegarde de la base"
    exit 1
fi
echo ""

# Backup Files
echo "📦 Sauvegarde des fichiers..."
tar -czf $FILES_BACKUP \
    backend/storage/app \
    backend/.env \
    frontend \
    mobile 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Fichiers sauvegardés: $FILES_BACKUP"
else
    echo "❌ Erreur lors de la sauvegarde des fichiers"
    exit 1
fi
echo ""

# Calculate backup size
TOTAL_SIZE=$(du -sh $BACKUP_DIR | cut -f1)
echo "📊 Taille totale des backups: $TOTAL_SIZE"
echo ""

# Delete old backups (keep last 7 days)
echo "🧹 Nettoyage des anciens backups..."
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
echo "✅ Anciens backups supprimés (>7 jours)"
echo ""

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   ✅ BACKUP TERMINÉ AVEC SUCCÈS !                        ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo "📁 Backups disponibles dans: $BACKUP_DIR"
