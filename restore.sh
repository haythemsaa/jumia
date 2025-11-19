#!/bin/bash

BACKUP_DIR="./backups"

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   🔄 ICHRI TUNISIA - RESTORATION                         ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# List available backups
echo "📁 Backups disponibles:"
ls -lht $BACKUP_DIR/*.sql.gz 2>/dev/null | head -5
echo ""

read -p "Entrez le nom du fichier backup à restaurer (ex: database_20240101_120000.sql.gz): " BACKUP_FILE

if [ ! -f "$BACKUP_DIR/$BACKUP_FILE" ]; then
    echo "❌ Fichier non trouvé: $BACKUP_DIR/$BACKUP_FILE"
    exit 1
fi

echo ""
read -p "⚠️  Cette action va ÉCRASER la base actuelle. Continuer? (y/N): " CONFIRM

if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "❌ Restoration annulée"
    exit 0
fi

echo ""
echo "🔄 Restoration en cours..."

# Decompress backup
gunzip -c "$BACKUP_DIR/$BACKUP_FILE" | docker-compose exec -T mysql mysql -u ichri_user -pichri_password ichri_tunisia

if [ $? -eq 0 ]; then
    echo "✅ Base de données restaurée avec succès!"
else
    echo "❌ Erreur lors de la restoration"
    exit 1
fi

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   ✅ RESTORATION TERMINÉE !                              ║"
echo "╚═══════════════════════════════════════════════════════════╝"
