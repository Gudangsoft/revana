# ⚠️ LANGKAH INSTALASI COMPOSER

## Composer Belum Terinstall!

Untuk menjalankan aplikasi Laravel REVANA, Composer WAJIB diinstall terlebih dahulu.

## 🔧 Cara Install Composer (Windows):

### Metode 1: Download Installer (RECOMMENDED)

1. **Download Composer Installer**
   - Buka: https://getcomposer.org/Composer-Setup.exe
   - Atau kunjungi: https://getcomposer.org/download/

2. **Jalankan Installer**
   - Double click file `Composer-Setup.exe` yang sudah didownload
   - Installer akan otomatis mendeteksi PHP
   - Ikuti wizard instalasi (Next, Next, Install)
   - Tunggu sampai selesai

3. **Verifikasi Instalasi**
   Buka PowerShell BARU (restart PowerShell), lalu jalankan:
   ```powershell
   composer --version
   ```
   
   Jika berhasil, akan muncul versi Composer

---

### Metode 2: Manual Install via PowerShell

Jika tidak bisa download installer, jalankan di PowerShell:

```powershell
# Download installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Install Composer
php composer-setup.php

# Hapus installer
php -r "unlink('composer-setup.php');"

# Pindahkan composer.phar ke lokasi global (optional)
Move-Item composer.phar C:\composer\composer.phar

# Buat batch file untuk menjalankan composer
# (atau tambahkan C:\composer ke PATH)
```

---

## 🚀 Setelah Composer Terinstall

Buka PowerShell BARU di folder REVANA, lalu jalankan:

```powershell
# 1. Install dependencies
composer install

# 2. Generate application key
php artisan key:generate

# 3. Buat database MySQL
# Buka MySQL dan jalankan:
# CREATE DATABASE dbrevana;

# 4. Jalankan migration & seeder
php artisan migrate --seed

# 5. Buat storage link
php artisan storage:link

# 6. Jalankan server
php artisan serve
```

Akses: http://localhost:8000

Login:
- Admin: admin@revana.com / password
- Reviewer: ahmad@revana.com / password

---

## ❓ Troubleshooting

### "composer not found" setelah install
**Solusi:** Restart PowerShell atau restart komputer

### "php not found" saat install composer
**Solusi:** Install PHP terlebih dahulu dari https://windows.php.net/download/

### Instalasi gagal
**Solusi:** 
1. Jalankan PowerShell sebagai Administrator
2. Pastikan koneksi internet stabil
3. Disable antivirus sementara saat install

---

## 📞 Butuh Bantuan?

Jika masih kesulitan:
1. Pastikan PHP sudah terinstall (cek: `php -v`)
2. Download installer dari link resmi
3. Restart PowerShell setelah install Composer
4. Jalankan `composer --version` untuk verifikasi

**PENTING:** Composer adalah dependency manager yang WAJIB untuk Laravel!
