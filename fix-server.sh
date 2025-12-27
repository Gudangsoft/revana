#!/bin/bash

###############################################################################
# REVANA Fix Apache Configuration
# Script untuk memperbaiki konfigurasi Apache otomatis
###############################################################################

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${YELLOW}→ $1${NC}"; }
print_header() { echo -e "${BLUE}$1${NC}"; }

clear
echo ""
print_header "======================================"
print_header "  REVANA Server Troubleshooting"
print_header "  portal.apji.org"
print_header "======================================"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    print_error "Script ini harus dijalankan sebagai root"
    echo "Gunakan: sudo ./fix-server.sh"
    exit 1
fi

APP_DIR="/var/www/revana"
DOMAIN="portal.apji.org"

# Step 1: Check if application directory exists
print_header "Step 1: Checking Application Directory"
if [ -d "$APP_DIR" ]; then
    print_success "Application directory exists: $APP_DIR"
    
    if [ -f "$APP_DIR/artisan" ]; then
        print_success "Laravel detected"
    else
        print_error "Laravel artisan not found!"
        echo "Deploy aplikasi terlebih dahulu"
        exit 1
    fi
else
    print_error "Application directory not found: $APP_DIR"
    print_info "Creating directory..."
    mkdir -p $APP_DIR
    print_info "Silakan clone repository terlebih dahulu:"
    echo "  cd $APP_DIR"
    echo "  git clone <repository-url> ."
    exit 1
fi
echo ""

# Step 2: Check web server
print_header "Step 2: Detecting Web Server"
if systemctl is-active --quiet apache2; then
    print_success "Apache detected and running"
    WEB_SERVER="apache"
elif systemctl is-active --quiet nginx; then
    print_success "Nginx detected and running"
    WEB_SERVER="nginx"
else
    print_error "No web server detected!"
    echo "Install Apache atau Nginx terlebih dahulu"
    exit 1
fi
echo ""

# Step 3: Fix Apache Configuration
if [ "$WEB_SERVER" = "apache" ]; then
    print_header "Step 3: Fixing Apache Configuration"
    
    # Check if site config exists
    SITE_CONFIG="/etc/apache2/sites-available/${DOMAIN}.conf"
    
    print_info "Creating Apache configuration..."
    
    cat > $SITE_CONFIG << 'EOF'
<VirtualHost *:443>
    ServerName portal.apji.org
    ServerAlias www.portal.apji.org
    
    DocumentRoot /var/www/revana/public
    
    <Directory /var/www/revana/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
        
        # Enable rewrite
        RewriteEngine On
        
        # Handle Front Controller...
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [L]
    </Directory>
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/portal.apji.org/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/portal.apji.org/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/revana-error.log
    CustomLog ${APACHE_LOG_DIR}/revana-access.log combined
    
    # PHP Configuration
    php_value upload_max_filesize 100M
    php_value post_max_size 100M
</VirtualHost>

<VirtualHost *:80>
    ServerName portal.apji.org
    ServerAlias www.portal.apji.org
    
    # Redirect HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>
EOF
    
    print_success "Apache configuration created"
    
    # Enable required modules
    print_info "Enabling Apache modules..."
    a2enmod rewrite >/dev/null 2>&1
    a2enmod ssl >/dev/null 2>&1
    a2enmod headers >/dev/null 2>&1
    print_success "Apache modules enabled"
    
    # Disable default site if enabled
    if [ -L "/etc/apache2/sites-enabled/000-default.conf" ]; then
        print_info "Disabling default site..."
        a2dissite 000-default >/dev/null 2>&1
    fi
    
    if [ -L "/etc/apache2/sites-enabled/000-default-le-ssl.conf" ]; then
        a2dissite 000-default-le-ssl >/dev/null 2>&1
    fi
    
    # Enable our site
    print_info "Enabling site configuration..."
    a2ensite ${DOMAIN}.conf >/dev/null 2>&1
    print_success "Site enabled"
    
    # Test configuration
    print_info "Testing Apache configuration..."
    if apache2ctl configtest >/dev/null 2>&1; then
        print_success "Apache configuration is valid"
    else
        print_error "Apache configuration has errors"
        apache2ctl configtest
        exit 1
    fi
    
fi

# Step 4: Fix Nginx Configuration
if [ "$WEB_SERVER" = "nginx" ]; then
    print_header "Step 3: Fixing Nginx Configuration"
    
    SITE_CONFIG="/etc/nginx/sites-available/${DOMAIN}"
    
    print_info "Creating Nginx configuration..."
    
    cat > $SITE_CONFIG << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name portal.apji.org www.portal.apji.org;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name portal.apji.org www.portal.apji.org;
    
    root /var/www/revana/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/portal.apji.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/portal.apji.org/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Logs
    access_log /var/log/nginx/revana-access.log;
    error_log /var/log/nginx/revana-error.log;
    
    # Max upload
    client_max_body_size 100M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
    
    print_success "Nginx configuration created"
    
    # Enable site
    print_info "Enabling site..."
    ln -sf $SITE_CONFIG /etc/nginx/sites-enabled/${DOMAIN}
    
    # Remove default if exists
    if [ -L "/etc/nginx/sites-enabled/default" ]; then
        rm /etc/nginx/sites-enabled/default
    fi
    
    # Test configuration
    print_info "Testing Nginx configuration..."
    if nginx -t >/dev/null 2>&1; then
        print_success "Nginx configuration is valid"
    else
        print_error "Nginx configuration has errors"
        nginx -t
        exit 1
    fi
fi

echo ""

# Step 5: Set correct permissions
print_header "Step 4: Setting Permissions"
print_info "Setting ownership..."
chown -R www-data:www-data $APP_DIR
print_success "Owner set to www-data"

print_info "Setting directory permissions..."
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
print_success "Permissions set"

echo ""

# Step 6: Check .env file
print_header "Step 5: Checking Configuration"
if [ -f "$APP_DIR/.env" ]; then
    print_success ".env file exists"
    
    # Check APP_KEY
    APP_KEY=$(grep "^APP_KEY=" $APP_DIR/.env | cut -d'=' -f2)
    if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
        print_info "Generating APP_KEY..."
        cd $APP_DIR
        php artisan key:generate --force
        print_success "APP_KEY generated"
    else
        print_success "APP_KEY configured"
    fi
else
    print_error ".env file not found!"
    print_info "Creating from .env.example..."
    if [ -f "$APP_DIR/.env.example" ]; then
        cp $APP_DIR/.env.example $APP_DIR/.env
        cd $APP_DIR
        php artisan key:generate --force
        print_success ".env created"
        echo ""
        print_info "PENTING: Edit .env untuk konfigurasi database:"
        echo "  nano $APP_DIR/.env"
    else
        print_error ".env.example not found!"
    fi
fi

echo ""

# Step 7: Create storage link
print_header "Step 6: Creating Storage Link"
cd $APP_DIR
if [ ! -L "$APP_DIR/public/storage" ]; then
    php artisan storage:link >/dev/null 2>&1
    print_success "Storage link created"
else
    print_success "Storage link already exists"
fi

echo ""

# Step 8: Clear and cache
print_header "Step 7: Clearing Cache"
cd $APP_DIR
php artisan config:clear >/dev/null 2>&1
php artisan route:clear >/dev/null 2>&1
php artisan view:clear >/dev/null 2>&1
php artisan cache:clear >/dev/null 2>&1
print_success "Cache cleared"

print_info "Optimizing application..."
php artisan config:cache >/dev/null 2>&1
php artisan route:cache >/dev/null 2>&1
php artisan view:cache >/dev/null 2>&1
print_success "Application optimized"

echo ""

# Step 9: Restart web server
print_header "Step 8: Restarting Services"
if [ "$WEB_SERVER" = "apache" ]; then
    systemctl restart apache2
    print_success "Apache restarted"
elif [ "$WEB_SERVER" = "nginx" ]; then
    systemctl restart nginx
    systemctl restart php8.1-fpm
    print_success "Nginx and PHP-FPM restarted"
fi

echo ""

# Step 10: Test
print_header "Step 9: Testing Application"
sleep 2

HTTP_CODE=$(curl -k -s -o /dev/null -w "%{http_code}" https://$DOMAIN)
if [ $HTTP_CODE -eq 200 ] || [ $HTTP_CODE -eq 302 ]; then
    print_success "Application responding! HTTP $HTTP_CODE"
    echo ""
    print_success "======================================"
    print_success "  Setup berhasil!"
    print_success "======================================"
    echo ""
    echo "Akses aplikasi di: https://$DOMAIN"
    echo ""
else
    print_error "Application not responding! HTTP Code: $HTTP_CODE"
    echo ""
    print_info "Check logs:"
    if [ "$WEB_SERVER" = "apache" ]; then
        echo "  tail -f /var/log/apache2/revana-error.log"
    else
        echo "  tail -f /var/log/nginx/revana-error.log"
    fi
    echo "  tail -f $APP_DIR/storage/logs/laravel.log"
fi

echo ""
print_info "Catatan:"
echo "  - Pastikan database sudah dibuat dan dikonfigurasi di .env"
echo "  - Jalankan migrasi: cd $APP_DIR && php artisan migrate --force"
echo "  - Check logs jika ada masalah"
echo ""
