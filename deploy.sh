#!/bin/bash

###############################################################################
# REVANA Deployment Script
# Script untuk deployment otomatis ke portal.apji.org
###############################################################################

# Colors untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Konfigurasi (sesuaikan dengan server Anda)
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

# Function untuk check command status
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
echo "  REVANA Deployment Script"
echo "  portal.apji.org"
echo "======================================"
echo ""

# Confirm deployment
read -p "Apakah Anda yakin ingin melakukan deployment? (yes/no): " -r
echo
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]
then
    print_info "Deployment dibatalkan"
    exit 0
fi

print_info "Memulai deployment..."
echo ""

# Step 1: Create backup
print_info "Step 1: Membuat backup..."
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
if [ -f "$APP_DIR/.env" ]; then
    DB_NAME=$(grep DB_DATABASE $APP_DIR/.env | cut -d '=' -f2)
    DB_USER=$(grep DB_USERNAME $APP_DIR/.env | cut -d '=' -f2)
    DB_PASS=$(grep DB_PASSWORD $APP_DIR/.env | cut -d '=' -f2)
    
    if [ ! -z "$DB_NAME" ]; then
        print_info "Backup database: $DB_NAME"
        if [ -z "$DB_PASS" ]; then
            mysqldump -u $DB_USER $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz
        else
            mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz
        fi
        check_status "Database backup"
    fi
fi

# Backup aplikasi
print_info "Backup aplikasi..."
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz -C $APP_DIR .
check_status "Aplikasi backup"

# Step 2: Enable maintenance mode
print_info "Step 2: Mengaktifkan maintenance mode..."
cd $APP_DIR
php artisan down --message="Sistem sedang dalam maintenance" --retry=60
check_status "Maintenance mode aktif"

# Step 3: Pull latest code
print_info "Step 3: Pull kode terbaru dari repository..."
git fetch origin
git pull origin main
check_status "Git pull"

# Step 4: Install/Update dependencies
print_info "Step 4: Install dependencies..."

print_info "Install Composer dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction
check_status "Composer install"

if [ -f "package.json" ]; then
    print_info "Install NPM dependencies..."
    npm ci --production
    check_status "NPM install"
    
    print_info "Build assets..."
    npm run build
    check_status "NPM build"
fi

# Step 5: Run migrations
print_info "Step 5: Menjalankan database migrations..."
php artisan migrate --force
check_status "Database migrations"

# Step 6: Clear and optimize cache
print_info "Step 6: Optimasi aplikasi..."

print_info "Clear cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
check_status "Clear cache"

print_info "Optimize aplikasi..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
check_status "Cache optimization"

print_info "Optimize composer autoloader..."
composer dump-autoload --optimize
check_status "Autoloader optimization"

# Step 7: Set correct permissions
print_info "Step 7: Set permissions..."
chown -R $APP_USER:$APP_USER $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
check_status "Permissions"

# Step 8: Create storage link (if not exists)
if [ ! -L "$APP_DIR/public/storage" ]; then
    print_info "Step 8: Membuat storage link..."
    php artisan storage:link
    check_status "Storage link"
else
    print_info "Step 8: Storage link sudah ada, skip..."
fi

# Step 9: Restart services
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

# Restart queue workers jika ada
if systemctl is-active --quiet revana-worker; then
    systemctl restart revana-worker
    check_status "Queue worker restart"
fi

# Step 10: Disable maintenance mode
print_info "Step 10: Menonaktifkan maintenance mode..."
php artisan up
check_status "Maintenance mode nonaktif"

# Step 11: Health check
print_info "Step 11: Health check..."
sleep 2

# Check if application is responding
if command -v curl &> /dev/null; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://portal.apji.org)
    if [ $HTTP_CODE -eq 200 ] || [ $HTTP_CODE -eq 302 ]; then
        print_success "Aplikasi responding dengan HTTP $HTTP_CODE"
    else
        print_error "Aplikasi tidak responding! HTTP Code: $HTTP_CODE"
        print_info "Check logs di storage/logs/laravel.log"
    fi
fi

# Cleanup old backups (keep last 30 days)
print_info "Cleanup old backups..."
find $BACKUP_DIR -type f -mtime +30 -delete
check_status "Cleanup backups"

# Summary
echo ""
echo "======================================"
print_success "Deployment selesai!"
echo "======================================"
echo ""
echo "Backup tersimpan di:"
echo "  - Database: $BACKUP_DIR/db_backup_$DATE.sql.gz"
echo "  - Aplikasi: $BACKUP_DIR/app_backup_$DATE.tar.gz"
echo ""
echo "Untuk rollback, jalankan:"
echo "  ./rollback.sh $DATE"
echo ""
echo "Check logs di:"
echo "  - Laravel: $APP_DIR/storage/logs/laravel.log"
echo "  - Nginx: /var/log/nginx/revana-error.log"
echo ""
