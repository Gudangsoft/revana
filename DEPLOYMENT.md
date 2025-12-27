# Panduan Deployment REVANA ke portal.apji.org

## Prasyarat Server

Pastikan server portal.apji.org sudah terinstall:
- PHP >= 8.1 (dengan extension: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL, fileinfo, zip)
- MySQL >= 5.7 atau MariaDB >= 10.3
- Composer
- Git
- Web Server (Apache/Nginx)
- Node.js & NPM (untuk asset compilation)

## Langkah 1: Persiapan Server

### 1.1 Login ke Server
```bash
ssh user@portal.apji.org
```

### 1.2 Buat Direktori Aplikasi
```bash
cd /var/www
sudo mkdir -p revana
sudo chown -R $USER:$USER revana
cd revana
```

## Langkah 2: Clone Repository

### 2.1 Clone dari Git Repository
```bash
# Jika menggunakan Git
git clone <repository-url> .

# Atau upload manual via FTP/SCP
# Jika upload manual, pastikan semua file termasuk .env.example ter-upload
```

### 2.2 Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/revana
sudo chmod -R 755 /var/www/revana
sudo chmod -R 775 /var/www/revana/storage
sudo chmod -R 775 /var/www/revana/bootstrap/cache
```

## Langkah 3: Install Dependencies

### 3.1 Install PHP Dependencies
```bash
cd /var/www/revana
composer install --optimize-autoloader --no-dev
```

### 3.2 Install Node Dependencies (jika ada)
```bash
npm install
npm run build
```

## Langkah 4: Konfigurasi Environment

### 4.1 Copy dan Edit File Environment
```bash
cp .env.example .env
nano .env
```

### 4.2 Konfigurasi .env untuk Production
```env
APP_NAME="REVANA"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://portal.apji.org

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dbrevana_production
DB_USERNAME=dbuser
DB_PASSWORD=secure_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@apji.org"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4.3 Generate Application Key
```bash
php artisan key:generate
```

## Langkah 5: Setup Database

### 5.1 Buat Database
```bash
mysql -u root -p
```

```sql
CREATE DATABASE dbrevana_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dbuser'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON dbrevana_production.* TO 'dbuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5.2 Jalankan Migrasi
```bash
php artisan migrate --force
```

### 5.3 Import Data (jika ada)
```bash
# Export dari local
mysqldump -u root -p dbrevana > dbrevana_backup.sql

# Upload ke server dan import
mysql -u dbuser -p dbrevana_production < dbrevana_backup.sql
```

### 5.4 Jalankan Seeder (jika diperlukan)
```bash
php artisan db:seed --force
```

## Langkah 6: Optimasi Laravel untuk Production

```bash
# Clear dan cache konfigurasi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Create storage link
php artisan storage:link
```

## Langkah 7: Konfigurasi Web Server

### 7.1 Nginx Configuration
Buat file `/etc/nginx/sites-available/revana`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name portal.apji.org www.portal.apji.org;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name portal.apji.org www.portal.apji.org;
    
    root /var/www/revana/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/ssl/certs/portal.apji.org.crt;
    ssl_certificate_key /etc/ssl/private/portal.apji.org.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Logs
    access_log /var/log/nginx/revana-access.log;
    error_log /var/log/nginx/revana-error.log;
    
    # Max upload size
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
```

Aktifkan konfigurasi:
```bash
sudo ln -s /etc/nginx/sites-available/revana /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7.2 Apache Configuration
Buat file `/etc/apache2/sites-available/revana.conf`:

```apache
<VirtualHost *:80>
    ServerName portal.apji.org
    ServerAlias www.portal.apji.org
    
    # Redirect to HTTPS
    Redirect permanent / https://portal.apji.org/
</VirtualHost>

<VirtualHost *:443>
    ServerName portal.apji.org
    ServerAlias www.portal.apji.org
    
    DocumentRoot /var/www/revana/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/portal.apji.org.crt
    SSLCertificateKeyFile /etc/ssl/private/portal.apji.org.key
    
    <Directory /var/www/revana/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/revana-error.log
    CustomLog ${APACHE_LOG_DIR}/revana-access.log combined
    
    # PHP Configuration
    php_value upload_max_filesize 100M
    php_value post_max_size 100M
</VirtualHost>
```

Aktifkan konfigurasi:
```bash
sudo a2enmod rewrite ssl
sudo a2ensite revana.conf
sudo apache2ctl configtest
sudo systemctl restart apache2
```

## Langkah 8: Setup SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Generate SSL Certificate
sudo certbot --nginx -d portal.apji.org -d www.portal.apji.org

# Auto-renewal sudah dikonfigurasi otomatis
# Test renewal
sudo certbot renew --dry-run
```

## Langkah 9: Setup Cron Jobs

Edit crontab:
```bash
sudo crontab -e -u www-data
```

Tambahkan:
```cron
* * * * * cd /var/www/revana && php artisan schedule:run >> /dev/null 2>&1
```

## Langkah 10: Setup Queue Worker (Optional)

Jika menggunakan queue, buat service systemd `/etc/systemd/system/revana-worker.service`:

```ini
[Unit]
Description=REVANA Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=10
ExecStart=/usr/bin/php /var/www/revana/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Aktifkan service:
```bash
sudo systemctl daemon-reload
sudo systemctl enable revana-worker
sudo systemctl start revana-worker
sudo systemctl status revana-worker
```

## Langkah 11: Monitoring dan Maintenance

### 11.1 Monitoring Logs
```bash
# Laravel logs
tail -f /var/www/revana/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/revana-error.log

# Apache logs
tail -f /var/log/apache2/revana-error.log
```

### 11.2 Regular Maintenance
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Update Aplikasi

Ketika ada update kode:

```bash
cd /var/www/revana

# Pull changes dari repository
git pull origin main

# Install dependencies baru
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Jalankan migrasi baru
php artisan migrate --force

# Clear dan rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.1-fpm  # atau apache2
sudo systemctl restart nginx       # jika pakai nginx
```

## Backup Database

Buat script backup `/var/www/revana/backup.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/revana"
DB_NAME="dbrevana_production"
DB_USER="dbuser"
DB_PASS="secure_password_here"

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Backup files (storage folder)
tar -czf $BACKUP_DIR/storage_backup_$DATE.tar.gz /var/www/revana/storage/app

# Hapus backup lama (lebih dari 30 hari)
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Jadwalkan backup harian:
```bash
sudo chmod +x /var/www/revana/backup.sh
sudo crontab -e
```

Tambahkan:
```cron
0 2 * * * /var/www/revana/backup.sh >> /var/log/revana-backup.log 2>&1
```

## Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/revana
sudo chmod -R 755 /var/www/revana
sudo chmod -R 775 /var/www/revana/storage
sudo chmod -R 775 /var/www/revana/bootstrap/cache
```

### 500 Internal Server Error
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check web server logs
tail -f /var/log/nginx/revana-error.log

# Ensure .env is configured
php artisan config:clear
php artisan config:cache
```

### Database Connection Issues
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql
```

### Storage Link Issues
```bash
php artisan storage:link

# If error, manually create symlink
ln -s /var/www/revana/storage/app/public /var/www/revana/public/storage
```

## Security Checklist

- [ ] APP_DEBUG=false di .env
- [ ] APP_ENV=production di .env
- [ ] APP_KEY ter-generate dengan benar
- [ ] Database password kuat dan aman
- [ ] SSL certificate terpasang (HTTPS)
- [ ] Firewall dikonfigurasi (hanya port 22, 80, 443)
- [ ] File .env tidak ter-commit ke Git
- [ ] Directory permissions sudah benar
- [ ] Backup otomatis sudah berjalan
- [ ] Update PHP dan dependencies secara berkala

## Kontak Support

Jika mengalami masalah, hubungi:
- Email: support@apji.org
- Dokumentasi: https://laravel.com/docs

---

**Catatan**: Sesuaikan path, username, password, dan domain sesuai dengan konfigurasi server Anda.
