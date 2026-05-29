# Log Update — 29 Mei 2026

## 1. Fitur Baru: Referensi Jurnal

**Tujuan:** Menambah menu dan tabel baru untuk input dan manajemen data referensi jurnal (nama jurnal, jenis jurnal, bidang ilmu, tahun, referensi).

### File yang Diubah / Dibuat

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_05_29_094714_create_referensi_jurnals_table.php` | Migration baru — tabel `referensi_jurnals` dengan kolom nama_jurnal, jenis_jurnal, bidang_ilmu, tahun, referensi |
| `app/Models/ReferensiJurnal.php` | Model baru dengan fillable lengkap |
| `app/Http/Controllers/Admin/ReferensiJurnalController.php` | Controller baru — CRUD (index, create, store, edit, update, destroy) dengan fitur pencarian dan filter tahun |
| `resources/views/admin/referensi-jurnals/index.blade.php` | View daftar referensi jurnal dengan filter pencarian dan filter tahun, pagination |
| `resources/views/admin/referensi-jurnals/create.blade.php` | View form tambah referensi jurnal |
| `resources/views/admin/referensi-jurnals/edit.blade.php` | View form edit referensi jurnal |
| `routes/web.php` | Tambah `use ReferensiJurnalController` dan `Route::resource('referensi-jurnals', ...)` |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah menu "Referensi Jurnal" di accordion Data Jurnal; update variabel `$isSharedJournalRoute` |
