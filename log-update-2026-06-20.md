# Log Update — 20 Juni 2026

## 1. Tambah Nama Bulan di Dropdown Pilih Slot (Marketing)

**Tujuan:** Dropdown "Pilih Slot" di form submit marketing menampilkan nama bulan agar lebih mudah memilih slot yang tepat.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah mapping bulan (1–12) ke nama Indonesia di `getSlotsByJournal()`; label slot dari `Vol. X No. X (2027)` menjadi `Vol. X No. X (Mei 2027)` |

### Contoh Hasil
- Sebelum: `Vol. 4 No. 3 (2026) - Sisa: 14/30 slot`
- Sesudah: `Vol. 4 No. 3 (Maret 2026) - Sisa: 14/30 slot`

## 2. PIC & Marketing Dashboard — Resilient (Fix 500 Error)

**Tujuan:** `/pic/dashboard` dan `/marketing/dashboard` crash 500 karena query DB tanpa try/catch — kolom `tanggal_lahir` / tabel `birthday_wishes` / `laporan_harian` belum ada di tenant DB production.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/AuthorController.php` | Tambah try/catch pada `topPics`, `topMarketings`, semua query `LaporanHarian`, dan seluruh method `todayBirthdayData()` |
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah try/catch pada `syncPoints()`, query `Submission`, `MarketingPointHistory`, `topMarketings`, `topPics`, dan seluruh method `todayBirthdayData()` |

### Root Cause
Tenant DB di production belum menjalankan semua migration → kolom `tanggal_lahir` tidak ada di tabel `pics`/`marketings`, tabel `birthday_wishes` belum ada. Semua query yang menyentuh kolom/tabel ini throw exception → 500.

### Solusi Permanen
Jalankan `php artisan migrate` di server production setelah deploy.

## 3. Banner & Tombol "Kembali ke Admin" saat Login As Marketing

**Tujuan:** Ketika admin menggunakan fitur "Login As" ke marketing, tampilkan banner kuning di atas halaman dan tombol "Kembali ke Admin" di dropdown, sama seperti yang sudah ada di PIC portal.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/MarketingController.php` | Tambah method `returnToAdmin()` — logout marketing guard, hapus session `admin_impersonating`, redirect ke admin dashboard |
| `routes/web.php` | Tambah route `POST /admin/marketings/return-to-admin` → `marketings.return-to-admin` |
| `resources/views/marketing/layouts/app.blade.php` | Tambah banner kuning full-width di atas navbar + badge "Mode Admin" di navbar + tombol "Kembali ke Admin" di dropdown — semua hanya tampil jika `session('admin_impersonating')` ada |

## 3. 🔄 Update: a

- **Commit:** `cd26f39` — 06:31 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/AuthorController.php`
- `log-update-2026-06-20.md`

