# REVANA - Review Validation & Analytics

Platform monitoring dan manajemen review jurnal ilmiah dengan sistem penugasan reviewer, point, badge, dan reward.

## 🎯 Fitur Utama

### Admin
- ✅ Manajemen data jurnal (input judul, link, akreditasi)
- ✅ Menugaskan reviewer ke jurnal
- ✅ Monitoring status review real-time
- ✅ Validasi dan approval hasil review
- ✅ Manajemen point, badge, dan reward
- ✅ Dashboard statistik lengkap

### Reviewer
- ✅ Melihat daftar tugas review
- ✅ Accept/Reject tugas yang ditugaskan
- ✅ Upload hasil review jurnal (PDF/DOC)
- ✅ Mendapatkan point & badge otomatis
- ✅ Menukar point menjadi reward (uang/gratis submit)
- ✅ Tracking progress dan history

## 🏆 Sistem Point & Badge

### Point Berdasarkan Akreditasi
- SINTA 1 = 100 point
- SINTA 2 = 80 point
- SINTA 3 = 60 point
- SINTA 4 = 40 point
- SINTA 5 = 20 point
- SINTA 6 = 10 point

### Badge Achievement
- 🌱 **Reviewer Pemula** - 1 jurnal selesai
- ⭐ **Reviewer Aktif** - 10 jurnal selesai
- 🏆 **Reviewer Expert** - 25 jurnal selesai
- 👑 **Reviewer Master** - 50 jurnal selesai

## 📋 Status Review
- `PENDING` - Menunggu response reviewer
- `ACCEPTED` - Tugas diterima reviewer
- `REJECTED` - Tugas ditolak reviewer
- `ON_PROGRESS` - Sedang dikerjakan
- `SUBMITTED` - Hasil sudah disubmit
- `APPROVED` - Disetujui admin (point diberikan)
- `REVISION` - Perlu perbaikan

## 🛠️ Tech Stack
- **Framework**: Laravel 10
- **Database**: MySQL
- **Frontend**: Bootstrap 5 + Bootstrap Icons
- **PHP**: 8.1+

## 📦 Instalasi

### Prasyarat
- PHP 8.1 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Composer
- Web server (Apache/Nginx)

### Langkah Instalasi

1. **Clone atau Download Project**
   ```bash
   cd d:\LPKD-APJI\REVANA
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Setup Environment**
   - File `.env` sudah tersedia
   - Sesuaikan konfigurasi database jika diperlukan:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=dbrevana
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Create Database**
   ```bash
   # Buat database MySQL
   mysql -u root -p
   CREATE DATABASE dbrevana;
   exit;
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Run Seeders (Data Awal)**
   ```bash
   php artisan db:seed
   ```

8. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

9. **Start Development Server**
   ```bash
   php artisan serve
   ```

10. **Akses Aplikasi**
    - URL: http://localhost:8000
    - Login dengan akun demo:

## 👥 Akun Demo

### Admin
- Email: `admin@revana.com`
- Password: `password`

### Reviewer
- Email: `ahmad@revana.com`
- Password: `password`
- Email: `siti@revana.com`
- Password: `password`
- Email: `budi@revana.com`
- Password: `password`

## 📁 Struktur Database

### Tabel Utama
1. **users** - Data admin dan reviewer
2. **journals** - Data jurnal ilmiah
3. **review_assignments** - Penugasan review
4. **review_results** - Hasil upload review
5. **point_histories** - History perolehan/penggunaan point
6. **badges** - Master badge
7. **user_badges** - Badge yang dimiliki user
8. **rewards** - Master reward
9. **reward_redemptions** - Penukaran reward

## 🎨 Template & UI

Aplikasi menggunakan template modern dengan:
- **Bootstrap 5** untuk responsive design
- **Bootstrap Icons** untuk iconography
- **Gradient sidebar** dengan warna indigo/purple
- **Clean card-based layout**
- **Mobile responsive**

## 📖 Alur Kerja

### Flow Admin:
1. Login sebagai admin
2. Input data jurnal baru
3. Assign jurnal ke reviewer
4. Monitor progress review
5. Validasi hasil review yang disubmit
6. Approve/request revision
7. Kelola reward redemptions

### Flow Reviewer:
1. Login sebagai reviewer
2. Lihat tugas baru di dashboard
3. Accept/reject tugas
4. Download jurnal & mulai review
5. Upload hasil review
6. Dapatkan point setelah approved
7. Tukar point dengan reward

## 🔧 Troubleshooting

### Composer tidak ditemukan
```bash
# Download dan install Composer dari https://getcomposer.org/
```

### Error koneksi database
- Pastikan MySQL service berjalan
- Cek kredensial database di file `.env`
- Pastikan database `dbrevana` sudah dibuat

### Storage link error
```bash
# Hapus link lama jika ada
rm public/storage
php artisan storage:link
```

### Permission error
```bash
# Berikan permission ke folder storage dan cache
chmod -R 775 storage bootstrap/cache
```

## 📞 Support

Untuk bantuan lebih lanjut, silakan hubungi tim development.

## 📄 License

Proprietary - LPKD-APJI

---

**REVANA** - Review Validation & Analytics Platform
Versi 1.0.0 - Desember 2025
