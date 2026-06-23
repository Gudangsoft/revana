# Log Update — 22 June 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

# Log Update — 22 Juni 2026

## 1. Data Penulis — Layout Panjang + Maksimal 7 Author

**Tujuan:** Nama penulis dan afiliasi membutuhkan ruang penuh (full-width) karena bisa panjang. Sistem perlu mendukung hingga 7 penulis (1 utama + 6 tambahan) dengan data tersimpan lengkap.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/partials/co-authors-fields.blade.php` | Nama & afiliasi full-width (col-12), max 6 co-author, counter "X/6 penulis", tombol disable saat limit |
| `resources/views/admin/submissions/create.blade.php` | Penulis utama: nama & afiliasi full-width, label "Penulis 1 (Utama)" |
| `resources/views/pic/submissions/create.blade.php` | Sama seperti admin create |
| `resources/views/marketing/create-submission.blade.php` | Sama seperti admin create |
| `resources/views/admin/submissions/edit.blade.php` | Redesain + tambah co-authors partial (sebelumnya tidak ada), load data dari `$submission->co_authors` |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah validasi + processing co_authors di update; afiliasi max:500 |
| `app/Http/Controllers/Marketing/DashboardController.php` | co_authors max:6, afiliasi max:500 |
| `app/Http/Controllers/Pic/JournalManagementController.php` | co_authors max:6, afiliasi max:500 |
| `database/migrations/2026_06_22_000001_widen_affiliation_penulis_to_text.php` | Ubah kolom affiliation_penulis dari varchar(255) ke text |

### Deploy
```bash
php artisan migrate
php artisan view:clear
```

## 2. 🔄 Update: Data Penulis: layout full-width + max 7 author + migration afiliasi ke text

- **Commit:** `60d23a0` — 20:28 oleh Gudangsoft
- **File berubah:** 10 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `database/migrations/2026_06_22_000001_widen_affiliation_penulis_to_text.php`
- `log-update-2026-06-22.md`
- `resources/views/admin/submissions/create.blade.php`
- `resources/views/admin/submissions/edit.blade.php`
- `resources/views/marketing/create-submission.blade.php`
- `resources/views/partials/co-authors-fields.blade.php`
- `resources/views/pic/submissions/create.blade.php`


## 2. Hapus Co-Authors — Nama Penulis Pakai Koma dalam 1 Field

**Tujuan:** Co-authors section dihapus. Semua nama penulis ditulis dalam 1 field dipisahkan koma (misal: Ekosiswanto, Maulidiah Zulfa, Zaenal). Field dilengkapi counter karakter dengan warna kuning saat mendekati limit.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/create.blade.php` | Hapus co-authors include, tambah counter karakter di nama_penulis |
| `resources/views/admin/submissions/edit.blade.php` | Hapus co-authors include, tambah counter karakter |
| `resources/views/pic/submissions/create.blade.php` | Hapus co-authors include, tambah counter karakter |
| `resources/views/marketing/create-submission.blade.php` | Hapus co-authors include, tambah counter karakter |

### Perilaku Counter
- Normal: abu-abu `"X / 500 karakter"`
- ≥ 80% (400+ karakter): **kuning** (`text-warning fw-semibold`)
- ≥ 95% (475+ karakter): **merah** (`text-danger fw-semibold`)

## 4. 🔄 Update: Hapus co-authors section — nama penulis koma dalam 1 field + counter karakter

- **Commit:** `f5a6e35` — 20:35 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-22.md`
- `resources/views/admin/submissions/create.blade.php`
- `resources/views/admin/submissions/edit.blade.php`
- `resources/views/marketing/create-submission.blade.php`
- `resources/views/pic/submissions/create.blade.php`


## 5. 🔄 Update: Migration nama_penulis varchar(255) → text + hapus max:255 di semua controller

- **Commit:** `0c6cf20` — 20:39 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `database/migrations/2026_06_22_000002_change_nama_penulis_to_text.php`


## 6. 🔄 Update: Terapkan nama penulis koma + counter di semua form fasttrack (admin/PIC/marketing)

- **Commit:** `bcde967` — 20:44 oleh Gudangsoft
- **File berubah:** 5 file
- `resources/views/admin/fasttrack/create.blade.php`
- `resources/views/admin/fasttrack/edit.blade.php`
- `resources/views/marketing/fasttrack/create.blade.php`
- `resources/views/pic/fasttrack/create.blade.php`
- `resources/views/pic/fasttrack/edit.blade.php`


## 7. 🔄 Update: Sembunyikan Master LOA dari marketing + tambah afiliasi ke semua form fasttrack

- **Commit:** `18d5311` — 20:50 oleh Gudangsoft
- **File berubah:** 9 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `resources/views/admin/fasttrack/create.blade.php`
- `resources/views/admin/fasttrack/edit.blade.php`
- `resources/views/marketing/fasttrack/create.blade.php`
- `resources/views/marketing/layouts/app.blade.php`
- `resources/views/pic/fasttrack/create.blade.php`
- `resources/views/pic/fasttrack/edit.blade.php`


## 8. 🔄 Update: Fix counter karakter tidak berfungsi — ganti IIFE menjadi DOMContentLoaded

- **Commit:** `0dbabf3` — 20:58 oleh Gudangsoft
- **File berubah:** 9 file
- `resources/views/admin/fasttrack/create.blade.php`
- `resources/views/admin/fasttrack/edit.blade.php`
- `resources/views/admin/submissions/create.blade.php`
- `resources/views/admin/submissions/edit.blade.php`
- `resources/views/marketing/create-submission.blade.php`
- `resources/views/marketing/fasttrack/create.blade.php`
- `resources/views/pic/fasttrack/create.blade.php`
- `resources/views/pic/fasttrack/edit.blade.php`
- `resources/views/pic/submissions/create.blade.php`


## 9. 🔄 Update: Fix counter tidak jalan di admin fasttrack — @section → @push

- **Commit:** `af39ee0` — 21:04 oleh Gudangsoft
- **File berubah:** 2 file
- `resources/views/admin/fasttrack/create.blade.php`
- `resources/views/admin/fasttrack/edit.blade.php`


## 10. 🔄 Update: Tambah inputan logo SINTA pada master akreditasi

- **Commit:** `5ebd550` — 21:22 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Admin/AccreditationController.php`
- `app/Models/Accreditation.php`
- `database/migrations/2026_06_22_000003_add_logo_sinta_to_accreditations.php`
- `resources/views/admin/accreditations/create.blade.php`
- `resources/views/admin/accreditations/edit.blade.php`
- `resources/views/admin/accreditations/index.blade.php`


## 11. 🔄 Update: Logo akreditasi LOA master otomatis dari data master akreditasi

- **Commit:** `99bf228` — 21:27 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/LoaMasterController.php`
- `resources/views/admin/loa-master/edit.blade.php`


## 12. 🔄 Update: Tambah panel logo akreditasi di halaman Master LOA index

- **Commit:** `ef46116` — 21:41 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/LoaMasterController.php`
- `resources/views/admin/loa-master/index.blade.php`


## 13. 🔄 Update: Hapus input manual logo akreditasi LOA — otomatis dari master akreditasi

- **Commit:** `56db1ef` — 21:43 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/LoaMasterController.php`
- `resources/views/admin/loa-master/edit.blade.php`


## 14. 🔄 Update: LOA receipt: logo akreditasi langsung dari master Accreditation

- **Commit:** `8aa7636` — 21:51 oleh Gudangsoft
- **File berubah:** 1 file
- `app/Http/Controllers/Admin/LoaController.php`


## 15. 🔄 Update: Hapus panel Logo Akreditasi dari halaman Master LOA index

- **Commit:** `0e073e7` — 21:53 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/LoaMasterController.php`
- `resources/views/admin/loa-master/index.blade.php`


## 16. 🔄 Update: Tambah input link SK akreditasi di LOA Master, logo dapat diklik

- **Commit:** `83725ca` — 21:56 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Admin/LoaController.php`
- `app/Http/Controllers/Admin/LoaMasterController.php`
- `app/Models/JournalMaster.php`
- `database/migrations/2026_06_22_000004_add_link_sk_akreditasi_to_journal_masters.php`
- `resources/views/admin/loa-master/edit.blade.php`
- `resources/views/admin/loa/receipt.blade.php`

