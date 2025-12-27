#!/bin/bash

###############################################################################
# REVANA Rollback Script
# Script untuk rollback deployment
###############################################################################

# Colors untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Konfigurasi
APP_DIR="/var/www/revana"
APP_USER="www-data"
BACKUP_DIR="/var/backups/revana"
PHP_VERSION="8.1"

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

check_status() {
    if [ $? -eq 0 ]; then
        print_success "$1"
    else
        print_error "$1 gagal!"
        exit 1
    fi
}

# Header
echo ""
echo "======================================"
echo "  REVANA Rollback Script"
echo "======================================"
echo ""

# Check parameter
if [ -z "$1" ]; then
    print_error "Usage: ./rollback.sh <backup_timestamp>"
    echo ""
    echo "Available backups:"
    ls -lh $BACKUP_DIR/app_backup_*.tar.gz 2>/dev/null | awk '{print $9}' | sed 's/.*app_backup_/  /' | sed 's/.tar.gz//'
    echo ""
    exit 1
fi

BACKUP_TIMESTAMP=$1
APP_BACKUP="$BACKUP_DIR/app_backup_$BACKUP_TIMESTAMP.tar.gz"
DB_BACKUP="$BACKUP_DIR/db_backup_$BACKUP_TIMESTAMP.sql.gz"

# Check if backup exists
if [ ! -f "$APP_BACKUP" ]; then
    print_error "Backup aplikasi tidak ditemukan: $APP_BACKUP"
    exit 1
fi

# Confirm rollback
echo "Akan rollback ke backup: $BACKUP_TIMESTAMP"
echo "  - Aplikasi: $APP_BACKUP"
if [ -f "$DB_BACKUP" ]; then
    echo "  - Database: $DB_BACKUP"
fi
echo ""
read -p "Apakah Anda yakin ingin rollback? (yes/no): " -r
echo
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]
then
    print_info "Rollback dibatalkan"
    exit 0
fi

print_info "Memulai rollback..."
echo ""

# Enable maintenance mode
print_info "Step 1: Mengaktifkan maintenance mode..."
cd $APP_DIR
php artisan down --message="Sistem sedang dalam rollback" --retry=60
check_status "Maintenance mode aktif"

# Restore database
if [ -f "$DB_BACKUP" ]; then
    print_info "Step 2: Restore database..."
    
    DB_NAME=$(grep DB_DATABASE $APP_DIR/.env | cut -d '=' -f2)
    DB_USER=$(grep DB_USERNAME $APP_DIR/.env | cut -d '=' -f2)
    DB_PASS=$(grep DB_PASSWORD $APP_DIR/.env | cut -d '=' -f2)
    
    if [ ! -z "$DB_NAME" ]; then
        print_info "Restore database: $DB_NAME"
        if [ -z "$DB_PASS" ]; then
            gunzip < $DB_BACKUP | mysql -u $DB_USER $DB_NAME
        else
            gunzip < $DB_BACKUP | mysql -u $DB_USER -p$DB_PASS $DB_NAME
        fi
        check_status "Database restore"
    fi
else
    print_info "Step 2: Database backup tidak ditemukan, skip..."
fi

# Backup current state before restore (safety measure)
print_info "Step 3: Backup current state (safety)..."
SAFETY_BACKUP="$BACKUP_DIR/safety_backup_$(date +%Y%m%d_%H%M%S).tar.gz"
tar -czf $SAFETY_BACKUP -C $APP_DIR .
check_status "Safety backup"

# Restore aplikasi
print_info "Step 4: Restore aplikasi..."
rm -rf $APP_DIR/*
rm -rf $APP_DIR/.[!.]*
tar -xzf $APP_BACKUP -C $APP_DIR
check_status "Aplikasi restore"

# Install dependencies
print_info "Step 5: Install dependencies..."
cd $APP_DIR
composer install --optimize-autoloader --no-dev --no-interaction
check_status "Composer install"

# Clear cache
print_info "Step 6: Clear cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
check_status "Clear cache"

# Optimize
print_info "Step 7: Optimize aplikasi..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
check_status "Optimization"

# Set permissions
print_info "Step 8: Set permissions..."
chown -R $APP_USER:$APP_USER $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
check_status "Permissions"

# Restart services
print_info "Step 9: Restart services..."
if systemctl is-active --quiet php${PHP_VERSION}-fpm; then
    systemctl restart php${PHP_VERSION}-fpm
    check_status "PHP-FPM restart"
fi

if systemctl is-active --quiet nginx; then
    systemctl restart nginx
    check_status "Nginx restart"
fi

if systemctl is-active --quiet apache2; then
    systemctl restart apache2
    check_status "Apache restart"
fi

if systemctl is-active --quiet revana-worker; then
    systemctl restart revana-worker
    check_status "Queue worker restart"
fi

# Disable maintenance mode
print_info "Step 10: Menonaktifkan maintenance mode..."
php artisan up
check_status "Maintenance mode nonaktif"

# Summary
echo ""
echo "======================================"
print_success "Rollback selesai!"
echo "======================================"
echo ""
echo "Aplikasi telah di-rollback ke: $BACKUP_TIMESTAMP"
echo "Safety backup tersimpan di: $SAFETY_BACKUP"
echo ""
