# Log Update — 04 Juni 2026

## 1. Filter Sort By (A→Z / Z→A) pada Monitoring Submissions

**Tujuan:** Menambahkan opsi pengurutan data pada halaman monitoring submissions untuk admin dan PIC, agar pengguna dapat menyortir data berdasarkan judul artikel (A→Z / Z→A) maupun tanggal (terbaru/terlama).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah logika sort berdasarkan parameter `sort_by` (title_asc, title_desc, date_asc, date_desc) pada method `monitoring()` |
| `app/Http/Controllers/Pic/JournalManagementController.php` | Tambah logika sort berdasarkan parameter `sort_by` pada method `submissionsMonitoring()` |
| `resources/views/admin/submissions/monitoring.blade.php` | Tambah dropdown "Urutkan" (Terbaru / Terlama / Judul A→Z / Judul Z→A) di form filter; restrukturisasi tombol filter menjadi compact icon buttons |
| `resources/views/pic/submissions/monitoring.blade.php` | Tambah dropdown "Urutkan" (Terbaru / Terlama / Judul A→Z / Judul Z→A) di form filter |

---

## 2. Sinkronasi Poin PIC Per Tahap Otomatis

**Tujuan:** Poin PIC dicatat otomatis ketika PIC menyerahkan pekerjaan (`submitWork`), bukan menunggu selesainya keseluruhan proses editor–validator. Juga perbaiki reviewer1/reviewer2 yang sebelumnya tidak mendapat poin saat divalidasi admin.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `submitWork()` — tambah `PicPointHistory::awardPoints()` per tahap; `getStepFromStatus()` — tambah reviewer1 & reviewer2 |
| `app/Http/Controllers/Admin/SubmissionController.php` | `validateStep()` — tambah reviewer1 & reviewer2 ke `$stepToPetugasField` agar poin juga bisa diberikan saat validasi admin |

---

## 3. Catatan Marketing Ditampilkan di Tabel Monitoring

**Tujuan:** Kolom "Mkt Note" dengan badge oranye ditampilkan di tabel monitoring (admin & PIC) agar catatan marketing langsung terlihat tanpa harus buka detail submission. Hover untuk lihat teks lengkap.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | Tambah kolom `catatan_marketing` (badge oranye) setelah kolom Judul |
| `resources/views/pic/submissions/monitoring.blade.php` | Tambah kolom `catatan_marketing` (badge oranye) setelah kolom Judul |
| `app/Exports/SubmissionsExport.php` | Tambah kolom Catatan Marketing, Petugas Validator, Validator Valid ke export Excel |

---

## 4. Tabel PIC Hanya Tampilkan Kolom Tahap yang Ditugaskan

**Tujuan:** Di halaman monitoring PIC, kolom workflow (Editor 1, Author 1, Editor 2, dst.) hanya ditampilkan untuk tahap-tahap yang benar-benar ditugaskan ke PIC tersebut. Tabel menjadi lebih ringkas dan fokus.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `submissionsMonitoring()` — hitung `$mySteps` (step apa saja yang dimiliki PIC ini di seluruh database); tambah method `submissionsMonitoringExport()` |
| `resources/views/pic/submissions/monitoring.blade.php` | Wrap setiap group kolom step dengan `@if(in_array('step', $mySteps))`; tambah tombol Export Excel |
| `app/Exports/PicMonitoringExport.php` | **[Baru]** Export class khusus PIC monitoring dengan kolom lengkap termasuk catatan marketing dan peran PIC per submission |
| `routes/web.php` | Tambah route `GET /pic/submissions/monitoring/export` |

---

## 5. Perbaikan Sinkronisasi Data — False Positive "Tidak Sinkron"

**Tujuan:** Halaman `/admin/sync` selalu menampilkan ratusan item "tidak sinkron" bahkan setelah sync berhasil dijalankan. Ini disebabkan bug perbandingan tipe data (float vs int strict `!==`).

### Root Cause
Model `Pic` dan `Marketing` memiliki `total_points` di-cast sebagai `float`. Semua perbandingan menggunakan `!==` (strict equality). PHP: `(float)5.0 !== (int)5` → `true`, sehingga SEMUA item selalu terdeteksi "tidak sinkron" meskipun nilainya sama.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SyncController.php` | `gatherStats()`, `syncMarketingPoints()`, `syncPicPoints()`, `countOutOfSync()` — ganti strict `!==` dengan `round(..., 4) !== round(...)` untuk float-safe comparison |
| `app/Http/Controllers/Admin/SyncController.php` | `syncAll()` — PIC total_points di-cast ke float agar konsisten dengan model cast |
| `app/Models/Marketing.php` | `syncPoints()` — ganti `!==` dengan `(int) round()` comparison |
