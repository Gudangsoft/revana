# Log Update — 17 May 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Tenant Creation Wizard — Setup Lebih Mudah

**Tujuan:** Menyederhanakan proses pembuatan tenant agar admin tidak perlu SSH/edit .env manual, dan dapat melihat progress tiap langkah secara real-time.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/TenantController.php` | Tambah: `storeAjax()`, `systemCheck()`, `testDbAdmin()`, `saveDbAdmin()`, refactor `validateTenantRequest()` |
| `app/Services/TenantManager.php` | Tambah method `createWithSteps()` — membuat tenant per langkah dan mengembalikan status tiap langkah |
| `routes/web.php` | Tambah 4 route baru: `system-check`, `store-ajax`, `test-db-admin`, `save-db-admin` |
| `resources/views/admin/tenants/create.blade.php` | Tulis ulang lengkap — form lebih ringkas, auto-subdomain dari nama institusi, system check otomatis, setup DB admin via modal, progress wizard AJAX |

### Fitur Baru
- **System check otomatis** saat halaman dibuka — tampil badge hijau (siap) atau kuning (perlu setup)
- **Modal "Setup Database Admin"** — input username/password MySQL root, test koneksi, simpan ke `.env` langsung dari browser tanpa SSH
- **Progress wizard AJAX** — klik "Buat Tenant" → modal progress menampilkan tiap langkah (Simpan data → Buat DB → Migrasi → Admin → WA) dengan ikon ✅/❌ per langkah
- **Auto-subdomain** — subdomain otomatis terisi saat mengetik nama institusi
- **Form lebih ringkas** — bagian Admin Default di-collapse by default

---

