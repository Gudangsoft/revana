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
