# 🚀 Panduan Instalasi REVANA

## CARA CEPAT INSTALASI

Ikuti langkah-langkah berikut secara berurutan:

### 1. Persiapan
Pastikan sudah terinstall:
- PHP 8.1+ (https://www.php.net/downloads)
- MySQL 5.7+ (https://dev.mysql.com/downloads/installer/)
- Composer (https://getcomposer.org/download/)

### 2. Install Dependencies
Buka PowerShell/CMD di folder `d:\LPKD-APJI\REVANA`, lalu jalankan:

```powershell
composer install
```

Jika belum ada Composer, download dan install dari https://getcomposer.org/

### 3. Setup Database

#### Buat Database MySQL:
```powershell
# Masuk ke MySQL
mysql -u root -p

# Buat database (dalam MySQL prompt)
CREATE DATABASE dbrevana;
exit;
```

#### Update file .env jika perlu
File `.env` sudah ada. Sesuaikan jika username/password MySQL berbeda:
```
DB_DATABASE=dbrevana
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key
```powershell
php artisan key:generate
```

### 5. Jalankan Migration
```powershell
php artisan migrate
```

Jika ada error, pastikan:
- MySQL service berjalan
- Database `dbrevana` sudah dibuat
- Kredensial di `.env` benar

### 6. Jalankan Seeder (Data Awal)
```powershell
php artisan db:seed
```

Ini akan membuat:
- 1 akun Admin
- 3 akun Reviewer
- 4 Badge
- 5 Reward

### 7. Buat Storage Link
```powershell
php artisan storage:link
```

### 8. Jalankan Server
```powershell
php artisan serve
```

### 9. Akses Aplikasi
Buka browser: **http://localhost:8000**

## 👤 LOGIN

### Admin:
- Email: `admin@revana.com`
- Password: `password`

### Reviewer:
- Email: `ahmad@revana.com` | `siti@revana.com` | `budi@revana.com`
- Password: `password`

---

## 🔧 Troubleshooting

### Error: "composer not found"
**Solusi:** Install Composer dari https://getcomposer.org/download/

### Error: "Access denied for user"
**Solusi:** 
1. Cek MySQL username/password di file `.env`
2. Pastikan MySQL service berjalan
3. Test koneksi: `mysql -u root -p`

### Error: "Base table or view not found"
**Solusi:** Jalankan migration:
```powershell
php artisan migrate:fresh --seed
```

### Error: "Class 'PDO' not found"
**Solusi:** Aktifkan extension PDO di `php.ini`:
```
extension=pdo_mysql
```

### Error: "storage link already exists"
**Solusi:** Hapus link lama:
```powershell
Remove-Item public\storage -Force
php artisan storage:link
```

### Error: "Permission denied" (storage/cache)
**Solusi:** (Hanya untuk Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
```

---

## 📋 Checklist Instalasi

- [ ] PHP 8.1+ terinstall (`php -v`)
- [ ] MySQL terinstall dan berjalan
- [ ] Composer terinstall (`composer -v`)
- [ ] Dependencies terinstall (`composer install`)
- [ ] Database dibuat (`dbrevana`)
- [ ] File `.env` dikonfigurasi
- [ ] Application key di-generate (`php artisan key:generate`)
- [ ] Migration dijalankan (`php artisan migrate`)
- [ ] Seeder dijalankan (`php artisan db:seed`)
- [ ] Storage link dibuat (`php artisan storage:link`)
- [ ] Server berjalan (`php artisan serve`)
- [ ] Bisa login ke aplikasi

---

## 🎯 Testing Flow

### Test sebagai Admin:
1. Login dengan `admin@revana.com`
2. Buat jurnal baru
3. Assign jurnal ke reviewer
4. Monitor status review

### Test sebagai Reviewer:
1. Login dengan `ahmad@revana.com`
2. Accept tugas review
3. Start progress
4. Upload hasil review (file PDF/DOC)
5. Lihat point bertambah setelah approved
6. Tukar point dengan reward

---

## 📞 Butuh Bantuan?

Jika masih ada masalah, cek:
1. Apakah semua service (MySQL, PHP) sudah berjalan?
2. Apakah kredensial database di `.env` sudah benar?
3. Apakah semua dependencies sudah terinstall?

**Happy Coding! 🚀**
