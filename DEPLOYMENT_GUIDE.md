# Quick Deployment Guide - REVANA

## 🚀 Cara Deploy

Ada 3 metode deployment yang tersedia:

### Metode 1: Deploy Otomatis dari Server (Recommended)

Jika kode sudah ada di Git repository:

```bash
# Login ke server
ssh user@portal.apji.org

# Clone atau pull latest code
cd /var/www/revana
git pull origin main

# Jalankan deployment script
chmod +x deploy.sh
sudo ./deploy.sh
```

### Metode 2: Deploy dari Windows/Local

```powershell
# Dari direktori project di Windows
.\deploy-from-local.ps1 -ServerUser "root" -ServerHost "portal.apji.org"

# Tanpa upload database
.\deploy-from-local.ps1 -ServerUser "root" -ServerHost "portal.apji.org" -SkipDatabase
```

### Metode 3: Manual Upload (Tradisional)

1. **Upload files via FTP/FileZilla** ke `/var/www/revana`
2. **Login SSH** ke server
3. **Jalankan setup manual:**

```bash
cd /var/www/revana
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

## 📋 Persiapan Sebelum Deploy

### 1. Konfigurasi .env di Server

Copy dan edit file `.env.production` menjadi `.env` di server:

```bash
# Di server
cd /var/www/revana
cp .env.production .env
nano .env
```

Update nilai-nilai berikut:
- `APP_KEY` (generate dengan: `php artisan key:generate`)
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_USERNAME`, `MAIL_PASSWORD` (untuk email notifications)

### 2. Setup Database di Server

```bash
mysql -u root -p
```

```sql
CREATE DATABASE dbrevana_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dbuser'@'localhost' IDENTIFIED BY 'your_password_here';
GRANT ALL PRIVILEGES ON dbrevana_production.* TO 'dbuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Install Prerequisites di Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1 dan extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-mbstring \
    php8.1-xml php8.1-bcmath php8.1-curl php8.1-zip php8.1-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js & NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install MySQL/MariaDB
sudo apt install -y mysql-server
```

## 🔄 Rollback

Jika terjadi masalah setelah deployment:

```bash
# Di server
cd /var/www/revana

# Lihat daftar backup
ls -lh /var/backups/revana/

# Rollback ke backup tertentu
sudo ./rollback.sh 20251227_143000
```

## 💾 Backup Database

### Manual Backup
```bash
# Di server
cd /var/www/revana
sudo ./backup-database.sh
```

### Setup Automatic Daily Backup

```bash
# Edit crontab
sudo crontab -e

# Tambahkan baris ini (backup setiap hari jam 2 pagi)
0 2 * * * /var/www/revana/backup-database.sh >> /var/log/revana-backup.log 2>&1
```

## 🔧 Troubleshooting

### Error 500 - Internal Server Error

```bash
# Check Laravel logs
tail -f /var/www/revana/storage/logs/laravel.log

# Check web server logs
tail -f /var/log/nginx/error.log

# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Permission Errors

```bash
sudo chown -R www-data:www-data /var/www/revana
sudo chmod -R 755 /var/www/revana
sudo chmod -R 775 /var/www/revana/storage
sudo chmod -R 775 /var/www/revana/bootstrap/cache
```

### Database Connection Error

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql

# Reset config cache
php artisan config:clear
php artisan config:cache
```

### Storage Link Missing

```bash
php artisan storage:link

# Or manually
ln -s /var/www/revana/storage/app/public /var/www/revana/public/storage
```

## 📊 Monitoring

### Check Application Status

```bash
# Check web server
sudo systemctl status nginx

# Check PHP-FPM
sudo systemctl status php8.1-fpm

# Check disk space
df -h

# Check recent logs
tail -f storage/logs/laravel.log
```

### Performance Monitoring

```bash
# Check active connections
sudo netstat -tulpn | grep :80

# Check PHP processes
ps aux | grep php-fpm

# Check memory usage
free -h
```

## 📝 Checklist Deploy

- [ ] Backup database lokal
- [ ] Commit dan push semua perubahan ke Git
- [ ] Update `.env.production` dengan nilai yang benar
- [ ] Test deployment di staging (jika ada)
- [ ] Informasikan user tentang maintenance
- [ ] Jalankan deployment script
- [ ] Verify aplikasi berjalan normal
- [ ] Test fitur-fitur utama
- [ ] Monitor logs selama 15 menit pertama
- [ ] Informasikan user bahwa deployment selesai

## 🆘 Emergency Contacts

- **Developer**: [Your contact]
- **Server Admin**: [Server admin contact]
- **Database Admin**: [DBA contact]

## 📚 Dokumentasi Lengkap

Lihat [DEPLOYMENT.md](DEPLOYMENT.md) untuk dokumentasi lengkap dan detail konfigurasi web server.

---

**Last Updated**: December 27, 2025
