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

## 2. Fix: Login Admin Terkunci (Konflik Sesi)

**Tujuan:** Admin sering tidak bisa login karena cache sesi lama tidak terhapus saat browser ditutup tanpa logout.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Auth/LoginController.php` | Deteksi konflik sesi menghasilkan flag `session_conflict`; jika ada `force_login=1` maka cache lama dihapus dan login dilanjutkan |
| `resources/views/auth/login.blade.php` | Tampilkan form "Paksa Login" dengan field password + captcha saat konflik sesi terdeteksi |
| `app/Http/Middleware/AdminMiddleware.php` | Perbarui cache `admin_session:{id}` tiap request sehingga sesi tidak expired saat admin aktif browsing |

## 3. Update: Tambah Field Kutipan & Import Excel ke Referensi Jurnal

**Tujuan:** Menambah kolom `kutipan` dan fitur import massal via Excel ke modul Referensi Jurnal.

### File yang Diubah / Dibuat

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_05_29_100505_add_kutipan_to_referensi_jurnals_table.php` | Migration baru — tambah kolom `kutipan` (nullable text) ke tabel `referensi_jurnals` |
| `app/Models/ReferensiJurnal.php` | Tambah `kutipan` ke `$fillable` |
| `app/Imports/ReferensiJurnalImport.php` | Import class baru — upsert berdasarkan nama_jurnal+tahun, support kolom kutipan |
| `app/Http/Controllers/Admin/ReferensiJurnalController.php` | Tambah method `import()` dan `downloadTemplate()`; update validasi CRUD dengan field kutipan |
| `routes/web.php` | Tambah route `referensi-jurnals/template` dan `referensi-jurnals/import` |
| `resources/views/admin/referensi-jurnals/index.blade.php` | Tambah kolom Kutipan di tabel, tombol Template + Import Excel, dan modal import |
| `resources/views/admin/referensi-jurnals/create.blade.php` | Tambah field Kutipan |
| `resources/views/admin/referensi-jurnals/edit.blade.php` | Tambah field Kutipan |

## 4. 🔄 Update: refrensi

- **Commit:** `9e5332f` — 09:54 oleh Gudangsoft
- **File berubah:** 10 file
- `app/Http/Controllers/Admin/ReferensiJurnalController.php`
- `app/Models/ReferensiJurnal.php`
- `database/migrations/2026_05_29_094714_create_referensi_jurnals_table.php`
- `log-update-2026-05-26.md`
- `log-update-2026-05-29.md`
- `resources/views/admin/partials/sidebar.blade.php`
- `resources/views/admin/referensi-jurnals/create.blade.php`
- `resources/views/admin/referensi-jurnals/edit.blade.php`
- `resources/views/admin/referensi-jurnals/index.blade.php`
- `routes/web.php`

## 5. Redesign: Halaman View Referensi Jurnal

**Tujuan:** Membuat tampilan halaman Referensi Jurnal menjadi lebih menarik, lengkap, dan fungsional.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/ReferensiJurnalController.php` | Tambah data stat cards (total, nasional, internasional, bidang) dan dropdown filter (jenis, bidang, tahun) di method `index()`, `create()`, `edit()` |
| `resources/views/admin/referensi-jurnals/index.blade.php` | Redesign lengkap: 4 stat cards, filter panel dengan active-filter pills, tabel bergaya modern, tombol salin referensi & kutipan, modal detail, modal import dengan file preview & spinner |
| `resources/views/admin/referensi-jurnals/create.blade.php` | Header gradien, datalist autocomplete jenis & bidang, counter karakter, layout lebih rapi |
| `resources/views/admin/referensi-jurnals/edit.blade.php` | Header gradien kuning, info timestamp, tombol hapus inline, datalist autocomplete, counter karakter |

## 6. Fitur: Halaman Publik Daftar Referensi Jurnal

**Tujuan:** Menambah tombol "Daftar Referensi Jurnal" di halaman login yang membuka halaman publik (tanpa login) berisi daftar referensi jurnal lengkap dengan filter.

### File yang Dibuat / Diubah

| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah route publik `GET /referensi-jurnal` → `PublicReferensiJurnalController@index` |
| `app/Http/Controllers/PublicReferensiJurnalController.php` | Controller baru — query dengan filter search/jenis/bidang/tahun, tanpa auth |
| `resources/views/public/referensi-jurnal.blade.php` | Halaman publik full standalone: navbar dengan logo, hero banner dengan stat pills, filter card overlap hero, daftar kartu referensi dengan accent bar warna, tombol salin referensi & kutipan, pagination |
| `resources/views/auth/login.blade.php` | Tambah tombol gradien "Daftar Referensi Jurnal" di bawah tombol Reviewer |

## 7. 🔄 Update: up refrensi

- **Commit:** `e7fa9e1` — 10:16 oleh Gudangsoft
- **File berubah:** 14 file
- `app/Http/Controllers/Admin/ReferensiJurnalController.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Controllers/PublicReferensiJurnalController.php`
- `app/Http/Middleware/AdminMiddleware.php`
- `app/Imports/ReferensiJurnalImport.php`
- `app/Models/ReferensiJurnal.php`
- `database/migrations/2026_05_29_100505_add_kutipan_to_referensi_jurnals_table.php`
- `log-update-2026-05-29.md`
- `resources/views/admin/referensi-jurnals/create.blade.php`
- `resources/views/admin/referensi-jurnals/edit.blade.php`


## 8. 🔄 Update: s

- **Commit:** `08444f3` — 10:20 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-29.md`


## 9. 🔄 Update: refrensi

- **Commit:** `c25938d` — 10:27 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-05-29.md`
- `resources/views/public/referensi-jurnal.blade.php`


## 10. 🔄 Update: style

- **Commit:** `8bde08d` — 10:36 oleh Gudangsoft
- **File berubah:** 8 file
- `app/Http/Controllers/Admin/ReferensiJurnalController.php`
- `app/Models/ReferensiJurnal.php`
- `database/migrations/2026_05_29_103259_add_format_sitasi_to_referensi_jurnals_table.php`
- `log-update-2026-05-29.md`
- `resources/views/admin/referensi-jurnals/_format_sitasi.blade.php`
- `resources/views/admin/referensi-jurnals/create.blade.php`
- `resources/views/admin/referensi-jurnals/edit.blade.php`
- `resources/views/public/referensi-jurnal.blade.php`


## 11. 🔄 Update: perbaikan import

- **Commit:** `7dcbd0d` — 10:47 oleh Gudangsoft
- **File berubah:** 5 file
- `app/Http/Controllers/Admin/ReferensiJurnalController.php`
- `app/Imports/ReferensiJurnalImport.php`
- `log-update-2026-05-29.md`
- `resources/views/admin/referensi-jurnals/index.blade.php`
- `resources/views/public/referensi-jurnal.blade.php`


## 12. 🔄 Update: ref

- **Commit:** `a8cbee8` — 11:41 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Http/Controllers/Admin/ReferensiJurnalController.php`
- `app/Models/ReferensiJurnal.php`
- `database/migrations/2026_05_29_113755_add_metadata_artikel_to_referensi_jurnals_table.php`
- `log-update-2026-05-29.md`
- `resources/views/admin/referensi-jurnals/_metadata_artikel.blade.php`
- `resources/views/admin/referensi-jurnals/create.blade.php`
- `resources/views/admin/referensi-jurnals/edit.blade.php`


## 13. 🔄 Update: up

- **Commit:** `aae5666` — 11:46 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Http/Controllers/Admin/ReferensiJurnalController.php`
- `log-update-2026-05-29.md`
- `resources/views/admin/referensi-jurnals/_fetch_url.blade.php`
- `resources/views/admin/referensi-jurnals/create.blade.php`
- `resources/views/admin/referensi-jurnals/edit.blade.php`
- `resources/views/public/referensi-jurnal.blade.php`
- `routes/web.php`

