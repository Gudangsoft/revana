# Fitur Edit Limit - Fasttrack Submission

## Deskripsi
Fitur pembatasan edit submission fasttrack dengan maksimal 3x edit dan konfirmasi sebelum menyimpan.

## Perubahan yang Dilakukan

### 1. Database Migration
**File:** `database/migrations/2026_02_02_add_edit_count_to_submissions_table.php`
- Menambahkan kolom `edit_count` (integer, default 0) ke tabel `submissions`
- Untuk tracking berapa kali submission sudah diedit

**Cara Menjalankan:**
```bash
php artisan migrate --path=database/migrations/2026_02_02_add_edit_count_to_submissions_table.php
```

### 2. Model Submission
**File:** `app/Models/Submission.php`
- Menambahkan `edit_count` ke dalam `$fillable`

### 3. Controller - JournalManagementController
**File:** `app/Http/Controllers/Pic/JournalManagementController.php`

#### Method `fasttrackEdit()`
- Mengecek apakah `edit_count >= 3`
- Jika sudah mencapai batas, redirect ke show page dengan error message
- User tidak bisa mengakses form edit

#### Method `fasttrackUpdate()`
- Increment `edit_count` setiap kali update berhasil
- Log history dengan mencatat edit ke berapa
- Track apakah ada perubahan slot

### 4. View - Edit Form
**File:** `resources/views/pic/fasttrack/edit.blade.php`

#### Alert Warning
- Muncul jika edit_count >= 1
- Menampilkan berapa kali sudah diedit
- Menampilkan sisa kesempatan edit
- Warna: Warning (kuning) untuk sisa 2x, Danger (merah) untuk sisa 1x

#### Konfirmasi JavaScript
Sebelum form di-submit, akan muncul dialog konfirmasi:
```
⚠️ KONFIRMASI PERUBAHAN ⚠️

Apakah Anda yakin data yang diinput sudah BENAR dan SESUAI?

📝 Pastikan:
✓ Jurnal & Slot sudah benar
✓ Judul artikel sudah benar
✓ Nama penulis sudah sesuai
✓ Link publish sudah dicek

⚠️ PERHATIAN: Ini edit ke-X, sisa kesempatan: Xx
```

### 5. View - Show Page
**File:** `resources/views/pic/fasttrack/show.blade.php`

#### Badge Edit Count
- Menampilkan berapa kali submission sudah diedit
- Warna badge berubah sesuai jumlah edit

#### Tombol Edit
- Menampilkan sisa kesempatan edit: "Edit (3x tersisa)"
- Jika sudah 3x edit, tombol disabled dengan label "Edit Terkunci"

## Fitur Utama

### 1. Pembatasan Edit (Max 3x)
- Submission hanya bisa diedit maksimal 3x
- Setelah 3x edit, tombol edit akan disabled
- User tidak bisa mengakses form edit lagi

### 2. Warning System
- **Edit ke-1**: Tidak ada warning
- **Edit ke-2**: Alert warning kuning, sisa 1x
- **Edit ke-3**: Alert danger merah, ini edit terakhir

### 3. Konfirmasi Sebelum Save
- Dialog konfirmasi muncul setiap kali user klik tombol Update
- Berisi checklist data yang harus dipastikan benar
- Menampilkan informasi jumlah edit dan sisa kesempatan
- User bisa Cancel untuk memeriksa kembali

### 4. Tracking & History
- Setiap edit dicatat di history
- Log mencatat edit ke berapa
- Log mencatat apakah ada perubahan slot

## Testing Checklist

1. ✅ Migration berhasil dijalankan
2. ✅ Kolom edit_count ditambahkan ke database
3. ✅ Edit pertama berjalan normal
4. ✅ Edit kedua muncul warning kuning
5. ✅ Edit ketiga muncul warning merah
6. ✅ Setelah 3x edit, tombol disabled
7. ✅ Tidak bisa akses form edit setelah 3x
8. ✅ Konfirmasi muncul sebelum save
9. ✅ History tercatat dengan benar
10. ✅ Slot counter update dengan benar

## Keamanan

- Backend validation: Cek di controller sebelum akses form
- Frontend protection: Tombol disabled di UI
- History tracking: Semua edit tercatat
- Confirmation: User harus confirm sebelum save

## Catatan Penting

⚠️ **PERHATIAN:**
- Fitur ini hanya berlaku untuk submission fasttrack
- Edit count tidak bisa di-reset kecuali manual via database
- Jika diperlukan edit lebih dari 3x, harus ada approval khusus

## Rollback (Jika Diperlukan)

Untuk rollback migration:
```bash
php artisan migrate:rollback --step=1
```

Atau hapus kolom manual:
```sql
ALTER TABLE submissions DROP COLUMN edit_count;
```
