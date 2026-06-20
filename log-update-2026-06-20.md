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

## 4. Field Penulis Tambahan (Co-Authors) di Form Submit

**Tujuan:** Memungkinkan input lebih dari satu penulis per artikel — penulis 2, 3, dst. bisa ditambah/dihapus dinamis lewat tombol + di form create submission.

### File yang Diubah / Dibuat
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_20_000001_add_co_authors_to_submissions.php` | Tambah kolom `co_authors` JSON nullable di tabel `submissions` |
| `app/Models/Submission.php` | Tambah `co_authors` ke `$fillable` dan cast sebagai `array` |
| `resources/views/partials/co-authors-fields.blade.php` | Partial baru — UI dinamis tambah/hapus penulis, repopulate `old()` saat validasi gagal |
| `resources/views/marketing/create-submission.blade.php` | `@include('partials.co-authors-fields')` setelah Data Penulis utama |
| `resources/views/pic/submissions/create.blade.php` | `@include('partials.co-authors-fields')` setelah Data Penulis utama |
| `resources/views/admin/submissions/create.blade.php` | `@include('partials.co-authors-fields')` setelah Data Penulis utama |
| `app/Http/Controllers/Marketing/DashboardController.php` | Validasi + proses `co_authors` di `storeSubmission()` |
| `app/Http/Controllers/Pic/JournalManagementController.php` | Validasi + proses `co_authors` di `submissionsStore()` |
| `app/Http/Controllers/Admin/SubmissionController.php` | Validasi + proses `co_authors` di `store()` |

### Format Simpan
`co_authors` disimpan sebagai JSON array: `[{"nama":"...","no_hp":"...","email":"...","afiliasi":"..."}, ...]`

### Catatan
Jalankan `php artisan migrate` di production untuk membuat kolom `co_authors`.

## 6. Field Afiliasi Penulis Utama di Form Create Submission

**Tujuan:** Tambah input "Afiliasi Penulis" (institusi/universitas) untuk penulis utama di semua form create submission — melengkapi data yang sudah ada di LOA.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/marketing/create-submission.blade.php` | Tambah field `affiliation_penulis` (full width) setelah Email Penulis, sebelum penulis tambahan |
| `resources/views/pic/submissions/create.blade.php` | Tambah field `affiliation_penulis` (full width) setelah Email Penulis |
| `resources/views/admin/submissions/create.blade.php` | Tambah field `affiliation_penulis` (full width) setelah Email Penulis |
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah validasi `affiliation_penulis` + simpan ke `Submission::create()` |
| `app/Http/Controllers/Pic/JournalManagementController.php` | Tambah validasi `affiliation_penulis` (PIC menggunakan `$validated` langsung, sudah tersimpan otomatis) |

---

## 5. Afiliasi Co-Authors + Tampil di LOA

**Tujuan:** Setiap penulis tambahan memiliki field afiliasi (institusi), dan semua penulis beserta afiliasi ditampilkan di LOA.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/partials/co-authors-fields.blade.php` | Tambah field "Afiliasi" (full width, row ke-2) di setiap baris penulis tambahan |
| `app/Http/Controllers/Marketing/DashboardController.php` | Validasi + simpan field `afiliasi` di `co_authors` |
| `app/Http/Controllers/Pic/JournalManagementController.php` | Validasi + simpan field `afiliasi` di `co_authors` |
| `app/Http/Controllers/Admin/SubmissionController.php` | Validasi + simpan field `afiliasi` di `co_authors` |
| `resources/views/admin/loa/receipt.blade.php` | Page 1: tampilkan co-authors + afiliasi di blok alamat. Page 2: tampilkan semua penulis + afiliasi di tabel meta. Bilingual (ID/EN). |

---

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


## 5. 🔄 Update: up

- **Commit:** `bd13a55` — 06:35 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/MarketingController.php`
- `log-update-2026-06-20.md`
- `resources/views/marketing/layouts/app.blade.php`
- `routes/web.php`


## 7. 🔄 Update: co author

- **Commit:** `fb698ad` — 07:14 oleh Gudangsoft
- **File berubah:** 10 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `app/Models/Submission.php`
- `database/migrations/2026_06_20_000001_add_co_authors_to_submissions.php`
- `log-update-2026-06-20.md`
- `resources/views/admin/submissions/create.blade.php`
- `resources/views/marketing/create-submission.blade.php`
- `resources/views/partials/co-authors-fields.blade.php`
- `resources/views/pic/submissions/create.blade.php`


## 9. 🔄 Update: up

- **Commit:** `1f4fe27` — 08:45 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-20.md`
- `resources/views/admin/loa/receipt.blade.php`
- `resources/views/partials/co-authors-fields.blade.php`


## 14. Pembaruan Tampilan Dokumen LOA & Tombol View LOA

**Tujuan:** Perbaikan visual dan konten dokumen LOA sesuai feedback tampilan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | (1) Hilangkan background kuning `.hl`; (2) Format kode artikel: `inisialJurnal_kodeArtikel_kodeSubSIPERA`; (3) Hapus tanda ✓ dari judul; (4) Reviewer Decision selalu nomor 2, ceklis di KANAN; (5) Ganti "Has been Indexed by" dengan logo SINTA (jika akreditasi SINTA) atau kosong; (6) Doc ID dipindah ke baris bawah URL di verified-bar |
| `resources/views/admin/loa-master/index.blade.php` | Tambah tombol "👁 Preview LOA" (ikon mata) di kolom Aksi, buka tab baru |
| `app/Http/Controllers/Admin/LoaMasterController.php` | Tambah method `previewLoa()` — cari submission terbaru dari jurnal, redirect ke LOA-nya |
| `routes/web.php` | Tambah `GET /admin/loa-master/{journalMaster}/preview-loa` |

---

## 13. Upload Gambar Header di LOA Master

**Tujuan:** Admin bisa upload gambar header (JPG/PNG lebar penuh A4) di halaman Setting LOA per jurnal. Jika gambar header diisi, gambar tersebut menggantikan header warna+logo yang dibuat otomatis di dokumen LOA (halaman 1 & 2).

### File yang Diubah / Dibuat
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_20_000002_add_header_image_to_journal_masters.php` | Tambah kolom `header_image_path` string nullable di tabel `journal_masters` |
| `app/Models/JournalMaster.php` | Tambah `header_image_path` ke `$fillable` |
| `app/Http/Controllers/Admin/LoaMasterController.php` | Validasi + upload/remove `header_image` ke `journals/headers` |
| `app/Http/Controllers/Admin/LoaController.php` | Pass `headerImageUrl` ke view receipt (method `publicView` & `show`) |
| `resources/views/admin/loa-master/edit.blade.php` | Tambah card "Gambar Header LOA" dengan upload, preview real-time, dan remove checkbox |
| `resources/views/admin/loa/receipt.blade.php` | Page 1 & Page 2: jika `headerImageUrl` ada, tampil sebagai `<img>` full-width menggantikan `.jrn-header` + `.jrn-subbar` |

### Catatan
Jalankan `php artisan migrate` di production untuk membuat kolom `header_image_path`.

---

## 12. Pindah Field Username/Password Akses Author

**Tujuan:** Username dan Password Akses Author dipindahkan ke tepat di bawah Data Penulis utama (sebelum co-authors), agar pengelompokan data penulis lebih logis.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/marketing/create-submission.blade.php` | Pindah blok username/password ke sebelum `@include('partials.co-authors-fields')` |
| `resources/views/pic/submissions/create.blade.php` | Idem |
| `resources/views/admin/submissions/create.blade.php` | Idem |

---

## 11. 🔄 Update: apiliasi

- **Commit:** `361d136` — 08:52 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-20.md`
- `resources/views/admin/submissions/create.blade.php`
- `resources/views/marketing/create-submission.blade.php`
- `resources/views/pic/submissions/create.blade.php`


## 13. 🔄 Update: up

- **Commit:** `52fd7b2` — 09:01 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-20.md`
- `resources/views/admin/loa/receipt.blade.php`
- `resources/views/admin/submissions/create.blade.php`
- `resources/views/marketing/create-submission.blade.php`
- `resources/views/pic/submissions/create.blade.php`

