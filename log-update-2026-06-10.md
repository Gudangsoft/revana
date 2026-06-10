# Log Update — 10 Juni 2026

## 1. Fix Export Excel Journal Slots

**Tujuan:** Tombol Export Excel di `/admin/journal-slots` tidak berfungsi. Root cause: `JournalSlotsExport` memuat relasi `submissions` yang tidak digunakan (bisa ratusan ribu record → memory exhausted), `static $rowNumber` tidak reset antar request, dan missing null-safe operator yang bisa fatal error jika `journalMaster` null.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Exports/JournalSlotsExport.php` | Hapus `submissions` dari eager load; ubah `static $rowNumber` → `protected int $rowNumber`; tambah null-safe operator `?->` untuk `journalMaster` dan `creator` |

### Detail Fix
- `->with(['journalMaster', 'creator', 'submissions'])` → `->with(['journalMaster', 'creator'])` — hapus relasi tidak terpakai
- `static $rowNumber = 0` → `protected int $rowNumber = 0` (instance property, reset otomatis per request)
- `$slot->journalMaster->nama_jurnal` → `$slot->journalMaster?->nama_jurnal` (dan `publisher`, `accreditation`)
- `$slot->creator->name` → `$slot->creator?->name`

---

## 2. Fix Export Excel Laporan Kinerja — Sinkronkan dengan Tampilan Halaman

**Tujuan:** Export Excel laporan kinerja menggunakan `buildData()` yang masih memakai query lama (`pic_point_histories.created_at`), sehingga angka di Excel berbeda dengan tampilan halaman (yang sudah difix di sesi sebelumnya). Perlu sinkronkan `buildData()` dengan logika baru di `index()`.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | `buildData()`: ganti query `PicPointHistory` lama dengan query `submissions.{step}_validated_at` (sama persis dengan `index()`); tambah `$stepCfg`, `$submissionCounts`, `$pointValues`, `$adjustments` |

### Catatan
- Sebelumnya `index()` sudah benar (query by `validated_at`) tapi `exportExcel` dan `exportPdf` masih pakai `buildData()` lama
- Setelah fix, Export Excel dan PDF kini menampilkan angka yang sama dengan tampilan halaman

## 3. 🔄 Update: export

- **Commit:** `303d6a3` — 11:11 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Exports/JournalSlotsExport.php`
- `app/Http/Controllers/Admin/LaporanKinerjaController.php`
- `log-update-2026-06-09.md`
- `log-update-2026-06-10.md`


## 4. 🔄 Update: update log

- **Commit:** `ad75d9c` — 11:15 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-06-10.md`

---

## 5. Rapikan Tombol Journal Slots + Tambah Export Excel ke Bidang Ilmu & Referensi Jurnal

**Tujuan:** (1) Tombol Kolom di `/admin/journal-slots` tidak rapi karena pakai `btn-group` tanpa `btn-sm`. (2) Halaman `/admin/field-of-studies` dan `/admin/referensi-jurnals` belum ada tombol Export Excel.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/journal-slots/index.blade.php` | Ganti `btn-group` → `d-flex gap-2 flex-wrap`; tambah `btn-sm` ke semua tombol |
| `resources/views/admin/field-of-studies/index.blade.php` | Tambah tombol Export Excel (btn-info) |
| `resources/views/admin/referensi-jurnals/index.blade.php` | Tambah tombol Export Excel (btn-sm btn-info) dengan filter diteruskan |
| `app/Exports/FieldOfStudiesExport.php` | Baru — export: No, Nama, Deskripsi, Urutan, Reviewer, Pendaftar, Status |
| `app/Exports/ReferensiJurnalsExport.php` | Baru — export dengan filter search, jenis, bidang, tahun |
| `app/Http/Controllers/Admin/FieldOfStudyController.php` | Tambah `export()` |
| `app/Http/Controllers/Admin/ReferensiJurnalController.php` | Tambah `export()` |
| `routes/web.php` | Tambah route `field-of-studies-export` dan `referensi-jurnals/export` |


## 6. Export & Import Excel — Kategori dan Jenis Jurnal

**Tujuan:** Halaman `/admin/kategoris` dan `/admin/jenis-jurnals` belum punya fitur Export/Import Excel.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Exports/KategorisExport.php` | Baru — export: No, Nama, Deskripsi, Status |
| `app/Exports/JenisJurnalsExport.php` | Baru — export: No, Nama, Deskripsi, Status |
| `app/Imports/KategoriImport.php` | Baru — import dengan upsert by name, kolom: name, description, is_active |
| `app/Imports/JenisJurnalImport.php` | Baru — sama dengan KategoriImport |
| `app/Http/Controllers/Admin/KategoriController.php` | Tambah `export()`, `import()`, `downloadTemplate()` |
| `app/Http/Controllers/Admin/JenisJurnalController.php` | Sama |
| `routes/web.php` | Tambah 3 route per halaman: export, import, template |
| `resources/views/admin/kategoris/index.blade.php` | Tambah tombol Export/Import/Template + import modal |
| `resources/views/admin/jenis-jurnals/index.blade.php` | Sama |

---

## 7. 🔄 Update: export excel bidang ilmu & referensi jurnal + rapikan tombol journal slots

- **Commit:** `4c8a0ab` — 11:21 oleh Gudangsoft
- **File berubah:** 9 file
- `app/Exports/FieldOfStudiesExport.php`
- `app/Exports/ReferensiJurnalsExport.php`
- `app/Http/Controllers/Admin/FieldOfStudyController.php`
- `app/Http/Controllers/Admin/ReferensiJurnalController.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/field-of-studies/index.blade.php`
- `resources/views/admin/journal-slots/index.blade.php`
- `resources/views/admin/referensi-jurnals/index.blade.php`
- `routes/web.php`


## 8. 🔄 Update: export import excel kategori & jenis jurnal

- **Commit:** `819e2d4` — 11:26 oleh Gudangsoft
- **File berubah:** 10 file
- `app/Exports/JenisJurnalsExport.php`
- `app/Exports/KategorisExport.php`
- `app/Http/Controllers/Admin/JenisJurnalController.php`
- `app/Http/Controllers/Admin/KategoriController.php`
- `app/Imports/JenisJurnalImport.php`
- `app/Imports/KategoriImport.php`
- `log-update-2026-06-10.md`
- `resources/views/admin/jenis-jurnals/index.blade.php`
- `resources/views/admin/kategoris/index.blade.php`
- `routes/web.php`


## 9. 🔄 Update: a

- **Commit:** `6023224` — 11:30 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-06-10.md`


## 10. 🔄 Update: a

- **Commit:** `82d5589` — 11:39 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-06-10.md`

---

## 11. Admin Hak Penuh Edit Production & Validasi di Monitoring

**Tujuan:** Admin tidak bisa mengedit data Production (User Editor, Pass Editor, Link Publish, Valid) dan Validasi (Catatan, Valid) di `/admin/submissions/monitoring`. Semua sel tersebut read-only. Admin harus bisa input dan ubah langsung dari tabel monitoring.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah `link_publish` dan `catatan_validator` ke whitelist `quickUpdateCredential`; naikkan max:255 → max:500 |
| `resources/views/admin/submissions/monitoring.blade.php` | Production USER EDITOR: `<code>` → `<input>` editable; Production PASS EDITOR: `<code>` → `<input>` editable; Production LINK PUBLISH: `<a>` read-only → `<input>` editable; Production VALID: icon statis → toggle button; Validasi CATATAN: teks truncated → `<input>` editable; Validasi VALID: icon statis → toggle button; Tambah fungsi JS `quickToggleValid()` |

### Detail
- `quickToggleValid(btn)` memanggil route `admin.submissions.toggle-valid-field` (sudah ada, sudah allow `production_valid` dan `validator_valid`)
- Toggle button update icon in-place tanpa reload
- Input credential Production menggunakan pola sama dengan editor1/reviewer credential (class `inline-credential-input`, event `onchange`)

