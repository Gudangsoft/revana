# Update Password Default PIC - 22 Januari 2026

## Perubahan yang Dilakukan

### 1. Update Import PIC ([PicsImport.php](app/Imports/PicsImport.php))
- Semua PIC yang diimport (baru atau update) akan otomatis mendapat password default: `pic@apjikom.or.id`
- Password di-hash menggunakan `bcrypt()` untuk keamanan

### 2. Script Update Password Massal
- Dibuat script `update-pic-passwords.php` untuk update password semua PIC yang sudah ada
- **Sudah dijalankan**: 7 PIC berhasil diupdate passwordnya

## Password Default PIC

**Username**: email atau username PIC  
**Password**: `pic@apjikom.or.id`

## Cara Login PIC

### URL Login
```
http://portal.apji.org/login/pic
atau
http://127.0.0.1:8000/login/pic (development)
```

### Kredensial Login
- **Username**: Gunakan email atau username PIC
- **Password**: `pic@apjikom.or.id`

### Contoh Login
```
Email: sintadewinugraheni@portal.apji.org
Password: pic@apjikom.or.id
```

## Daftar PIC yang Sudah Diupdate

Berdasarkan hasil running script, berikut PIC yang passwordnya sudah diset:

1. ✓ Eko
2. ✓ Nilam (Nilam@apji.org)
3. ✓ Siti Rahayu (siti.rahayu@apji.org)
4. ✓ Budi Santoso (budi.santoso@apji.org)
5. ✓ Dewi Lestari (dewi.lestari@apji.org)
6. ✓ Ahmad Fauzi (ahmad.fauzi@apji.org)
7. ✓ Rina Wati (rina.wati@apji.org)

## Import PIC Baru

Setiap kali melakukan import PIC (baik data baru atau update), password akan otomatis diset ke `pic@apjikom.or.id`.

**Tidak perlu setup manual lagi** - sistem akan otomatis:
1. Set password untuk PIC baru
2. Reset password untuk PIC yang diupdate via import

## Script yang Tersedia

### 1. Update Password Semua PIC (Manual)
```bash
php update-pic-passwords.php
```
Digunakan untuk reset password semua PIC yang sudah ada di database.

### 2. Import PIC dari Excel/CSV
Melalui menu Admin → Data PIC → Import
- Password otomatis diset saat import

## Keamanan

- Password di-hash menggunakan bcrypt (Laravel default)
- Password default ini sebaiknya diganti oleh PIC setelah login pertama kali
- **Rekomendasi**: Tambahkan fitur "Ganti Password" dan "Force Change Password" pada login pertama

## Testing

Untuk test login PIC:
1. Buka `/login/pic`
2. Gunakan email PIC (contoh: `sintadewinugraheni@portal.apji.org`)
3. Password: `pic@apjikom.or.id`
4. Klik Login

## File yang Diubah/Dibuat

1. ✏️ `app/Imports/PicsImport.php` - Tambah password default saat import
2. ➕ `update-pic-passwords.php` - Script update password massal
3. ➕ `PIC_DEFAULT_PASSWORD.md` - Dokumentasi ini

## Catatan Penting

⚠️ **Password default ini bersifat sementara**  
Sebaiknya:
- Beritahu PIC untuk mengganti password setelah login pertama
- Implementasikan fitur "Force Password Change"
- Kirim email notifikasi ke PIC dengan kredensial login
