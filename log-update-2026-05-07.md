# Log Update — 7 May 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Hapus Prefix Program di Field ID Artikel

**Tujuan:** Field ID Artikel tidak lagi perlu diisi dengan prefix program (BKD-, JAFA-, dll) karena prefix tersebut sudah otomatis masuk ke Kode Submit.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/create.blade.php` | Hapus logika prefix `strtoupper(request('program')) . '-'` dari value dan placeholder field id_artikel |
| `resources/views/pic/submissions/create.blade.php` | Sama: hapus prefix dari value dan placeholder field id_artikel |
| `resources/views/marketing/create-submission.blade.php` | Sama: hapus prefix dari value dan placeholder field Nomor Submit |


## 2. 🔄 Update: up

- **Commit:** `e450978` — 17:34 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Models/Submission.php`
- `log-update-2026-05-07.md`
- `resources/views/admin/submissions/create.blade.php`
- `resources/views/marketing/create-submission.blade.php`
- `resources/views/pic/submissions/create.blade.php`

