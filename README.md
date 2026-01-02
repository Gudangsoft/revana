# SIPERA - Sistem Informasi Peer Review Artikel

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-yellow.svg)](LICENSE)

Sistem manajemen peer review artikel jurnal berbasis web yang memfasilitasi proses review artikel, gamifikasi dengan sistem poin dan reward, serta manajemen reviewer secara terintegrasi.

## 🚀 Fitur Utama

### 👨‍💼 Admin
- **Dashboard Analitik**: Statistik lengkap review, reviewer, dan poin dengan export Excel
- **Manajemen Penugasan Review**: Assign artikel ke reviewer dengan tracking status dan deadline
- **Manajemen Reviewer**: Kelola data reviewer, histori review, dan preferensi bahasa
- **Pendaftaran Reviewer**: Verifikasi dan approve pendaftaran reviewer baru dengan integrasi WhatsApp
- **Sistem Poin**: Kelola penambahan/pengurangan poin reviewer dengan history lengkap
- **Manajemen Reward**: CRUD reward dengan sistem tier (Bronze, Silver, Gold, Platinum)
- **Penukaran Reward**: Approve/reject/complete redemption dari reviewer
- **Papan Peringkat**: Leaderboard berdasarkan poin dan badge reviewer
- **Marketing & PIC**: Kelola data marketing dan Person In Charge
- **Pengelolaan Pengguna**: CRUD users dengan multi-role (admin/reviewer/pic)
- **Setting Web**: Konfigurasi logo, favicon, nama aplikasi, tagline, alamat, dan kontak secara real-time

### 👨‍🔬 Reviewer
- **Dashboard Personal**: Overview tugas, statistik review, dan poin terkini
- **Tugas Review**: List assignments dengan status dan filter
- **Submit Review**: Upload hasil review dengan file dan link sertifikat
- **Katalog Reward**: Browse dan tukar poin dengan reward berdasarkan tier
- **Leaderboard**: Lihat peringkat dan kompetisi antar reviewer
- **Edit Profile**: Update data personal, preferensi bahasa artikel (Indonesia/English)

### 📝 PIC (Person In Charge)
- **Dashboard**: Monitoring artikel yang di-submit untuk direview
- **Submit Artikel**: Upload artikel jurnal untuk direview

## 🏆 Sistem Gamifikasi

### Point System
- Point diberikan setelah review diapprove admin
- Point dapat ditukar dengan reward
- History point tercatat lengkap

### Badge Achievement
- 🌱 **Reviewer Pemula** - Review pertama selesai
- ⭐ **Reviewer Aktif** - 10 review selesai
- 🏆 **Reviewer Expert** - 25 review selesai
- 👑 **Reviewer Master** - 50 review selesai

### Reward Tiers
- 🥉 **Bronze** - Reward entry level
- 🥈 **Silver** - Reward menengah
- 🥇 **Gold** - Reward premium
- 💎 **Platinum** - Reward eksklusif

## 📋 Status Review
- `PENDING` - Menunggu response reviewer
- `ACCEPTED` - Tugas diterima reviewer
- `REJECTED` - Tugas ditolak reviewer
- `ON_PROGRESS` - Sedang dikerjakan
- `SUBMITTED` - Hasil sudah disubmit
- `APPROVED` - Disetujui admin (point diberikan)
- `REVISION` - Perlu perbaikan

## 💻 Tech Stack
- **Framework**: Laravel 10.x
- **Database**: MySQL/PostgreSQL
- **Frontend**: Bootstrap 5.3.0 + Bootstrap Icons
- **Authentication**: Laravel Multi-Auth (Admin, Reviewer, PIC)
- **File Storage**: Laravel Storage (public disk)
- **Export**: Maatwebsite/Laravel-Excel
- **PHP**: 8.1+

## 📦 Instalasi

### Prerequisites
- PHP 8.1 atau lebih tinggi
- Composer
- MySQL 5.7+ atau PostgreSQL 12+
- Web Server (Apache/Nginx)
- Node.js & NPM (opsional, untuk asset compilation)

### Langkah Instalasi

#### 1. Clone Repository
```bash
git clone <repository-url>
cd REVANA
```

#### 2. Install Dependencies
```bash
composer install
```

#### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` sesuai konfigurasi Anda:
```env
APP_NAME=SIPERA
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### 4. Create Database
```sql
# MySQL
CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 5. Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

Seeder akan membuat:
- Default admin account
- Setting default (app_name, dll)
- Sample badges
- Sample data untuk testing (opsional)

#### 6. Create Storage Link
```bash
php artisan storage:link
```

#### 7. Set Permissions (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Windows: Pastikan folder `storage` dan `bootstrap/cache` dapat ditulis.

#### 8. Start Development Server
```bash
php artisan serve
```

Aplikasi berjalan di: **http://127.0.0.1:8000**

## 👥 Default Credentials

Setelah menjalankan seeder, gunakan kredensial berikut:

### Admin
- **Email**: `admin@example.com`
- **Password**: `password`

### Reviewer (jika ada sample seeder)
- **Email**: `reviewer@example.com`
- **Password**: `password`

### PIC
- Buat manual dari admin panel
- Akses via: `/pic/login`

> ⚠️ **PENTING**: Segera ubah password default setelah login pertama kali!

## 📁 Struktur Project

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Admin controllers
│   │   │   ├── Reviewer/           # Reviewer controllers
│   │   │   └── Pic/                # PIC controllers
│   │   └── Middleware/             # Custom middleware
│   ├── Models/                     # Eloquent models
│   └── Exports/                    # Export classes
├── database/
│   ├── migrations/                 # Database migrations
│   └── seeders/                    # Database seeders
├── resources/
│   └── views/
│       ├── admin/                  # Admin views
│       ├── reviewer/               # Reviewer views
│       ├── pic/                    # PIC views
│       ├── auth/                   # Auth views
│       └── layouts/                # Layout templates
├── routes/
│   ├── web.php                     # Web routes
│   ├── api.php                     # API routes
│   └── console.php                 # Console routes
├── storage/
│   ├── app/public/                 # User uploads (linked to public/storage)
│   ├── framework/                  # Framework cache
│   └── logs/                       # Application logs
└── public/
    ├── storage/                    # Symlink to storage/app/public
    └── index.php                   # Entry point
```

## 📊 Database Schema

### Tabel Utama
1. **users** - Data admin, reviewer, dan PIC
2. **review_assignments** - Penugasan review artikel
3. **review_results** - Hasil submit review dengan file
4. **point_histories** - History perolehan/penggunaan poin
5. **badges** - Master badge achievement
6. **user_badges** - Badge yang dimiliki user
7. **rewards** - Master reward dengan tier
8. **reward_redemptions** - Penukaran reward
9. **pics** - Data Person In Charge
10. **marketings** - Data marketing
11. **reviewer_registrations** - Pendaftaran reviewer baru
12. **settings** - Konfigurasi aplikasi (key-value)

## 🎨 UI/UX Features

### Design System
- **Bootstrap 5.3.0** - Modern responsive framework
- **Bootstrap Icons** - 1,800+ icon library
- **Custom Gradient Sidebar** - Indigo/purple theme
- **Card-based Layout** - Clean and organized
- **Mobile Responsive** - Optimized for all devices
  - Collapsible sidebar untuk mobile
  - Hamburger menu
  - Responsive tables dengan horizontal scroll

### Branding & Customization
- Logo upload (200x50px recommended)
- Favicon upload (32x32px recommended)
- Real-time app name update dari database
- Customizable tagline, address, contact

## 🔄 Workflow

### Admin Workflow:
1. Login ke admin panel
2. Buat assignment review baru (judul, deskripsi, poin, deadline)
3. Assign reviewer dari dropdown
4. Monitor status review di dashboard
5. Review hasil yang disubmit reviewer
6. Approve (poin diberikan) atau minta revision
7. Kelola redemption reward dari reviewer

### Reviewer Workflow:
1. Login ke reviewer panel
2. Lihat tugas baru di dashboard
3. Accept/reject assignment
4. Start progress review
5. Submit hasil review (file + link sertifikat)
6. Dapatkan poin setelah approved
7. Browse katalog reward
8. Redeem reward dengan poin
9. Track status redemption

### PIC Workflow:
1. Login via `/pic/login` dengan username
2. View dashboard artikel
3. Submit artikel baru untuk direview
4. Monitor status artikel

## 📱 WhatsApp Integration

Setelah reviewer mendaftar, sistem otomatis redirect ke WhatsApp admin dengan pesan:
```
Halo Admin, 

Saya [NAMA] telah mendaftar sebagai reviewer di [APP_NAME].

Email: [EMAIL]
Afiliasi: [AFILIASI]
Scopus ID: [SCOPUS_ID]

Mohon untuk diverifikasi. Terima kasih!
```

## 🔐 Role-Based Access Control

| Fitur | Admin | Reviewer | PIC |
|-------|-------|----------|-----|
| Dashboard | ✅ | ✅ | ✅ |
| Manajemen Review | ✅ | ❌ | ❌ |
| Submit Review | ❌ | ✅ | ❌ |
| Manajemen Poin | ✅ | ❌ | ❌ |
| Reward Catalog | ✅ | ✅ | ❌ |
| Redeem Reward | ❌ | ✅ | ❌ |
| Leaderboard | ✅ | ✅ | ❌ |
| Submit Artikel | ❌ | ❌ | ✅ |
| User Management | ✅ | ❌ | ❌ |
| Settings | ✅ | ❌ | ❌ |

## 🌐 Deployment Production

### Quick Deploy Script
Project sudah dilengkapi dengan script deployment:
```bash
# Deploy dari local ke server
.\deploy-from-local.ps1

# Deploy di server
./deploy.sh

# Rollback jika ada masalah
./rollback.sh
```

Lihat [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) untuk panduan lengkap.

### Manual Deployment

#### 1. Upload Files
```bash
# Via rsync (Linux/Mac)
rsync -avz --exclude 'node_modules' --exclude '.git' ./ user@server:/path/to/app

# Via FTP/SFTP
# Upload semua file kecuali: node_modules, .git, .env
```

#### 2. Server Configuration
```bash
cd /path/to/app

# Install dependencies (production)
composer install --optimize-autoloader --no-dev

# Setup environment
cp .env.example .env
php artisan key:generate

# Edit .env untuk production settings
nano .env

# Run migrations
php artisan migrate --force

# Seed initial settings
php artisan db:seed --class=SettingSeeder

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 3. Web Server Setup

**Nginx Example:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache Example (.htaccess sudah include di public/):**
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/app/public

    <Directory /path/to/app/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### 4. SSL Setup (Recommended)
```bash
# Using Certbot (Let's Encrypt)
sudo certbot --nginx -d your-domain.com
```

## 🔧 Configuration

### App Settings
Edit via **Admin > Setting Web** atau langsung di database:
```sql
-- Update app name
UPDATE settings SET value='Nama Aplikasi Baru' WHERE key='app_name';

-- Update tagline
UPDATE settings SET value='Tagline Baru' WHERE key='tagline';
```

Kemudian clear cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Environment Variables
Key environment variables di `.env`:
```env
APP_NAME=SIPERA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
```

## 📈 Performance Optimization

### Caching
```bash
# Cache config, routes, views
php artisan optimize

# Clear all caches
php artisan optimize:clear
```

### Database Optimization
```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_user_role ON users(role);
CREATE INDEX idx_assignment_status ON review_assignments(status);
CREATE INDEX idx_redemption_status ON reward_redemptions(status);
```

### File Storage
- Gunakan CDN untuk static assets
- Compress images sebelum upload
- Implement lazy loading untuk images

## 🔍 Monitoring & Logs

### Application Logs
```bash
# View latest logs
tail -f storage/logs/laravel.log

# Clear old logs
> storage/logs/laravel.log
```

### Database Backup
```bash
# Backup database
./backup-database.sh

# Manual backup
mysqldump -u username -p database_name > backup.sql
```

## � Troubleshooting

### Installation Issues

**Composer tidak ditemukan**
```bash
# Download dari https://getcomposer.org/
```

**Error: "Please provide a valid cache path"**
```bash
# Set permissions
chmod -R 775 storage/framework/{cache,views,sessions}
```

**Error: "No application encryption key has been specified"**
```bash
php artisan key:generate
```

### Database Issues

**Error koneksi database**
- Pastikan MySQL/PostgreSQL service berjalan
- Verifikasi kredensial di `.env`
- Cek database sudah dibuat
```bash
# Test koneksi
php artisan tinker
>>> DB::connection()->getPdo();
```

**Migration failed**
```bash
# Reset migrations (CAUTION: deletes all data)
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback
```

### File/Permission Issues

**Storage link error**
```bash
# Remove old link
rm public/storage

# Create new link
php artisan storage:link
```

**Logo/Favicon tidak muncul**
```bash
# Check storage link
ls -la public/storage

# Set permissions (Linux/Mac)
chmod -R 775 storage/app/public

# Windows: Set folder writable via Properties
```

**Permission error (Linux/Mac)**
```bash
# Set correct ownership
sudo chown -R www-data:www-data storage bootstrap/cache

# Set permissions
chmod -R 775 storage bootstrap/cache
```

### Application Issues

**App name tidak berubah setelah update settings**
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Verify database has setting
php artisan tinker
>>> App\Models\Setting::where('key', 'app_name')->first();
```

**Login redirect loop**
- Clear browser cache dan cookies
- Check `.env` APP_URL matches your domain
- Verify middleware configuration

**File upload gagal**
- Check `php.ini`: `upload_max_filesize` dan `post_max_size`
- Verify storage permissions
- Check disk space

## 📚 Documentation

- [User Guide](USER_GUIDE.md) - Panduan penggunaan aplikasi
- [PIC Login Guide](PIC_LOGIN_GUIDE.md) - Panduan login untuk PIC
- [Deployment Guide](DEPLOYMENT_GUIDE.md) - Panduan deployment lengkap
- [Project Summary](PROJECT_SUMMARY.md) - Ringkasan proyek
- [Installation Guide](INSTALL.md) - Panduan instalasi detail
- [Composer Installation](INSTALL_COMPOSER.md) - Cara install Composer

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ReviewAssignmentTest.php

# Run with coverage
php artisan test --coverage
```

### Manual Testing Checklist
- [ ] Admin dapat login dan akses dashboard
- [ ] Admin dapat create/edit/delete assignments
- [ ] Reviewer dapat accept/reject assignments
- [ ] Reviewer dapat submit hasil review
- [ ] Admin dapat approve/revision hasil review
- [ ] Point otomatis diberikan setelah approval
- [ ] Reviewer dapat redeem reward
- [ ] Admin dapat manage redemptions
- [ ] Settings web dapat diupdate real-time
- [ ] File upload berfungsi (review results, logo, favicon)
- [ ] WhatsApp redirect setelah pendaftaran

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create feature branch
   ```bash
   git checkout -b feature/AmazingFeature
   ```
3. Commit changes
   ```bash
   git commit -m 'Add some AmazingFeature'
   ```
4. Push to branch
   ```bash
   git push origin feature/AmazingFeature
   ```
5. Open Pull Request

### Coding Standards
- Follow PSR-12 coding standard
- Write descriptive commit messages
- Add comments for complex logic
- Update documentation for new features

## 🔒 Security

### Security Best Practices
- ✅ CSRF protection enabled
- ✅ XSS protection via Blade escaping
- ✅ SQL injection prevention via Eloquent
- ✅ Password hashing with bcrypt
- ✅ Role-based access control
- ✅ File upload validation

### Reporting Security Issues
Jika menemukan celah keamanan, harap laporkan ke: **security@apji.org**

**JANGAN** buat public issue untuk masalah keamanan.

## 📞 Support & Contact

### Technical Support
- **Email**: support@apji.org
- **Website**: https://portal.apji.org

### Development Team
**LPKD APJI** - Lembaga Pengembangan Kajian Dakwah APJI

### Issues & Bugs
Report issues via GitHub Issues atau email ke support.

## 📄 License

This project is proprietary software developed for LPKD APJI.

**Copyright © 2026 LPKD APJI. All rights reserved.**

Unauthorized copying, modification, distribution, or use of this software is strictly prohibited.

## 🙏 Acknowledgments

- Laravel Framework - [https://laravel.com](https://laravel.com)
- Bootstrap - [https://getbootstrap.com](https://getbootstrap.com)
- Bootstrap Icons - [https://icons.getbootstrap.com](https://icons.getbootstrap.com)

## 📋 Changelog

### Version 1.0.0 (January 2026)
- ✨ Initial release
- ✅ Multi-role authentication (Admin, Reviewer, PIC)
- ✅ Review assignment management
- ✅ Gamification system (points, badges, rewards)
- ✅ Reviewer registration with WhatsApp integration
- ✅ Real-time settings management
- ✅ Responsive design
- ✅ Export to Excel functionality
- ✅ File upload for review results
- ✅ Certificate link for approved reviews
- ✅ Multi-language article preference (Indonesia/English)
- ✅ User management system

---

<div align="center">

**SIPERA** - Sistem Informasi Peer Review Artikel

Version 1.0.0 | January 2026

Made with ❤️ by LPKD APJI Development Team

[Website](https://portal.apji.org) • [Documentation](USER_GUIDE.md) • [Support](mailto:support@apji.org)

</div>
