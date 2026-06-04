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

## 6. Optimasi Performa — Eliminasi Query Berlebih per Page Load

**Tujuan:** `countOutOfSync()` dipanggil di setiap halaman admin (sidebar). Dengan 71 PIC, setiap page load memicu 71+ query DB → halaman terasa lambat.

### Root Cause
- `countOutOfSync()` tanpa cache: dipanggil setiap request, menjalankan N query (1 per PIC) untuk menjumlahkan poin
- `$mySteps` di PIC monitoring: 10 query `exists()` terpisah per page load

### Perbaikan
| Sebelum | Sesudah |
|---------|---------|
| `countOutOfSync()` = 71+ query per page load | Cached 5 menit → **0 query** saat cache masih valid |
| PIC sum: N query `WHERE pic_id=X SUM(...)` per PIC | 1 query `GROUP BY pic_id` untuk semua PIC sekaligus |
| `$mySteps`: 10x `exists()` per page | 1 query `MAX(CASE WHEN...)` conditional aggregation |

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SyncController.php` | `countOutOfSync()` → `Cache::remember('sync.out_of_sync_count', 300, ...)` + optimasi PIC aggregation; `clearSyncCache()` dipanggil setiap kali sync berhasil |
| `app/Http/Controllers/Admin/SyncController.php` | `gatherStats()` — PIC sum dari N query → 1 GROUP BY query |
| `app/Http/Controllers/Pic/JournalManagementController.php` | `$mySteps` dari 10x `exists()` → 1 query `MAX(CASE WHEN)` conditional aggregation |

---

## 7. Kolom Mkt Note + Filter Sort By di Semua Halaman Monitoring

**Tujuan:** Terapkan kolom Catatan Marketing (badge oranye) dan filter sortir A→Z/Z→A ke semua halaman monitoring: Admin Normal/BKD/JAFA (sudah ada), Admin Fasttrack, Marketing Normal, Marketing Fasttrack, PIC Normal (sudah ada), PIC Fasttrack.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | `fasttrackMonitoring()` — tambah sort_by match |
| `app/Http/Controllers/Marketing/DashboardController.php` | `submissionsMonitoring()` dan `fasttrackMonitoring()` — tambah sort_by match |
| `app/Http/Controllers/Pic/JournalManagementController.php` | `fasttrackMonitoring()` — tambah sort_by match |
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | Tambah dropdown Urutkan ↑↓ di filter form; tambah kolom Mkt Note |
| `resources/views/marketing/submissions-monitoring.blade.php` | Tambah dropdown Urutkan ↑↓ inline di filter; tambah kolom Catatan Marketing |
| `resources/views/marketing/fasttrack/monitoring.blade.php` | Tambah dropdown Urutkan ↑↓; tambah kolom Catatan Marketing |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Tambah dropdown Urutkan ↑↓; tambah kolom Mkt Note |

---

## 8. 🔄 Update: update

- **Commit:** `3e3ae15` — 19:58 oleh Gudangsoft
- **File berubah:** 11 file
- `app/Exports/PicMonitoringExport.php`
- `app/Exports/SubmissionsExport.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Admin/SyncController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `app/Models/Marketing.php`
- `log-update-2026-06-03.md`
- `log-update-2026-06-04.md`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 9. 🔄 Update: a

- **Commit:** `e5069d2` — 20:14 oleh Gudangsoft
- **File berubah:** 9 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Admin/SyncController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-04.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/marketing/fasttrack/monitoring.blade.php`
- `resources/views/marketing/submissions-monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`

