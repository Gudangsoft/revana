#!/bin/bash

###############################################################################
# REVANA Database Backup Script
# Script untuk backup database secara manual atau otomatis
###############################################################################

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Konfigurasi
APP_DIR="/var/www/revana"
BACKUP_DIR="/var/backups/revana"
RETENTION_DAYS=30

# Function untuk print colored messages
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}→ $1${NC}"
}

# Header
echo ""
echo "======================================"
echo "  REVANA Database Backup"
echo "======================================"
echo ""

# Check if .env exists
if [ ! -f "$APP_DIR/.env" ]; then
    print_error ".env file tidak ditemukan di $APP_DIR"
    exit 1
fi

# Read database config
cd $APP_DIR
DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2)
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)

if [ -z "$DB_NAME" ]; then
    print_error "DB_DATABASE tidak ditemukan di .env"
    exit 1
fi

# Create backup directory
mkdir -p $BACKUP_DIR

# Generate backup filename
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/db_backup_$DATE.sql.gz"

print_info "Backup database: $DB_NAME"
print_info "Target file: $BACKUP_FILE"
echo ""

# Perform backup
if [ -z "$DB_PASS" ]; then
    if [ -z "$DB_HOST" ] || [ "$DB_HOST" == "127.0.0.1" ] || [ "$DB_HOST" == "localhost" ]; then
        mysqldump -u $DB_USER $DB_NAME | gzip > $BACKUP_FILE
    else
        mysqldump -h $DB_HOST -u $DB_USER $DB_NAME | gzip > $BACKUP_FILE
    fi
else
    if [ -z "$DB_HOST" ] || [ "$DB_HOST" == "127.0.0.1" ] || [ "$DB_HOST" == "localhost" ]; then
        mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_FILE
    else
        mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_FILE
    fi
fi

# Check if backup successful
if [ $? -eq 0 ] && [ -f "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h $BACKUP_FILE | cut -f1)
    print_success "Backup berhasil!"
    echo ""
    echo "  File: $BACKUP_FILE"
    echo "  Size: $BACKUP_SIZE"
    echo ""
    
    # Cleanup old backups
    print_info "Cleanup old backups (older than $RETENTION_DAYS days)..."
    DELETED=$(find $BACKUP_DIR -name "db_backup_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete -print | wc -l)
    print_success "Deleted $DELETED old backup(s)"
    echo ""
    
    # Show recent backups
    print_info "Recent backups:"
    ls -lh $BACKUP_DIR/db_backup_*.sql.gz 2>/dev/null | tail -5 | awk '{print "  " $9 " (" $5 ")"}'
    echo ""
    
else
    print_error "Backup gagal!"
    exit 1
fi

# Show restore command
echo "Untuk restore backup ini:"
echo "  gunzip < $BACKUP_FILE | mysql -u $DB_USER -p $DB_NAME"
echo ""
