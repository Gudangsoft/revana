# Log Update — 10 May 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 2. Fitur Validasi & Detail Catatan Kinerja Harian (Admin)

**Tujuan:** Admin bisa membuka detail setiap catatan kinerja PIC, memberikan catatan/feedback, dan menandai status validasi (sudah/belum divalidasi). PIC bisa melihat status validasi dan catatan admin di riwayat mereka.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_05_10_000001_add_validation_to_laporan_harian_table.php` | Migrasi baru: tambah kolom `validated_at`, `validated_by`, `catatan_admin` |
| `app/Models/LaporanHarian.php` | Tambah fillable + casts + accessor `is_validated` + relasi `validator()` |
| `app/Http/Controllers/Admin/LaporanHarianController.php` | Tambah method `show()` dan `validate()` |
| `routes/web.php` | Tambah route `admin.laporan-harian.show` dan `admin.laporan-harian.validate` |
| `resources/views/admin/laporan-harian/show.blade.php` | View baru: detail catatan + form validasi + catatan admin |
| `resources/views/admin/laporan-harian/index.blade.php` | Tambah kolom Status validasi + tombol Detail; update summary cards |
| `resources/views/pic/laporan-harian/index.blade.php` | Tampilkan badge status validasi & catatan admin di riwayat PIC |

---

## 1. Badge "New" + Titik Notifikasi di Menu Sidebar

**Tujuan:** Menandai menu baru dengan badge "New" dan titik merah berkedip selama 7 hari sejak menu ditambahkan, agar pengguna mudah mengenali fitur baru.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/partials/sidebar.blade.php` | Tambah badge "New" + titik blink pada menu Catatan Kinerja Harian; tambah CSS `@keyframes sidebarBlink` |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah badge "New" + titik blink pada menu Catatan Kinerja Harian; tambah `<style>` block dengan animasi |


## 3. 🔄 Update: update /laporan-harian

- **Commit:** `8f08dd1` — 18:22 oleh Gudangsoft
- **File berubah:** 10 file
- `app/Http/Controllers/Admin/LaporanHarianController.php`
- `app/Models/LaporanHarian.php`
- `database/migrations/2026_05_10_000001_add_validation_to_laporan_harian_table.php`
- `log-update-2026-05-10.md`
- `resources/views/admin/laporan-harian/index.blade.php`
- `resources/views/admin/laporan-harian/show.blade.php`
- `resources/views/admin/partials/sidebar.blade.php`
- `resources/views/pic/laporan-harian/index.blade.php`
- `resources/views/pic/partials/sidebar.blade.php`
- `routes/web.php`


## 4. 🔄 Update: up

- **Commit:** `b493f30` — 18:24 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/LaporanHarianController.php`
- `routes/web.php`

