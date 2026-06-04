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


## 9. Audit Performa Menyeluruh — Sistem Lebih Ringan

**Tujuan:** Eliminasi semua titik berat yang menyebabkan lambat: query berulang di setiap halaman, N+1 queries, groupBy di PHP memory, query langsung di view, dan kolom DB tanpa index.

### Perubahan per Kategori

#### A. Cache — Menghilangkan Query Berulang per Page Load
| File | Sebelum | Sesudah |
|------|---------|---------|
| `admin/partials/sidebar.blade.php` | `Submission::count()` tiap halaman | Cache 2 menit |
| `pic/partials/sidebar.blade.php` | Loop O(n×m) + get() tanpa batas | SQL COUNT 1 query + cache 60 detik per PIC |
| `app/Providers/ViewServiceProvider.php` | `ReviewRequest::count()` tiap admin view | Cache 5 menit |
| `marketing/layouts/app.blade.php` | `submissions()->count()` tiap navbar | Cache 2 menit per marketing user |

#### B. Query di View — Dihapus
| File | Baris | Fix |
|------|-------|-----|
| `resources/views/admin/submissions/monitoring.blade.php` | 1315, 1383 | Ganti `Pic::where()->get()` → `$pics` (sudah di-pass controller) |

#### C. Aggregasi di DB — Bukan PHP Memory
| File | Sebelum | Sesudah |
|------|---------|---------|
| `LaporanKinerjaController.php` | `->get()->groupBy('pic_id')` memuat ribuan baris | `selectRaw(...)->groupBy()` langsung di DB |

#### D. Optimasi Query
| File | Perubahan |
|------|-----------|
| `SyncController.php` | `Pic::all()` → `Pic::select('id','total_points')->get()` (kurangi memori) |
| `partials/auto-refresh.blade.php` | Default interval 30s → 60s (kurangi 50% frekuensi reload) |

#### E. Database Indexes (17 index baru — migration dijalankan)
| Tabel | Kolom | Dampak |
|-------|-------|--------|
| `submissions` | `marketing_id` | Query marketing & sync |
| `submissions` | `petugas_*_id` (11 kolom) | Filter penugasan PIC |
| `submissions` | `journal_slot_id`, `process_type`, `created_by` | Filter workflow |
| `journal_slots` | `journal_master_id`, `is_active` | Join & filter jurnal |
| `marketing_point_histories` | `marketing_id` | Aggregasi poin marketing |

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/partials/sidebar.blade.php` | Cache pendingValidationCount |
| `resources/views/pic/partials/sidebar.blade.php` | Refactor O(n×m) → SQL COUNT + cache |
| `resources/views/admin/submissions/monitoring.blade.php` | Hapus Pic::where() di view |
| `app/Providers/ViewServiceProvider.php` | Cache ReviewRequest count |
| `resources/views/marketing/layouts/app.blade.php` | Cache submission count navbar |
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | DB-level aggregation |
| `app/Http/Controllers/Admin/SyncController.php` | Select kolom minimal |
| `resources/views/partials/auto-refresh.blade.php` | Default interval 30→60 detik |
| `database/migrations/2026_06_04_203005_add_performance_indexes_to_tables.php` | **[Baru]** 17 index baru, migration dijalankan |

---

## 10. 🔄 Update: a

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


## 11. Fix Lag Admin Fasttrack Monitoring — Lazy-Select Dropdown

**Tujuan:** Halaman admin fasttrack monitoring terasa sangat lambat karena me-render semua opsi dropdown langsung di HTML (50 baris × 9 dropdown × 71 PIC = ~32.000 DOM elements). Diperbaiki dengan menerapkan pola lazy-select.

### Root Cause
Monitoring regular pakai `lazy-select` — opsi dimuat saat hover via JS.
Monitoring fasttrack pakai `@foreach($pics as $pic)` langsung di setiap baris tabel.

### Hasil
- HTML size: dari ~32.000 nodes → **~450 nodes** (50 baris × 9 opsi terpilih)
- Render time turun drastis

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | 9 dropdown per baris: ganti `@foreach($pics)` → lazy-select pattern + JS loader |

---

## 12. 🔄 Update: as

- **Commit:** `808f07a` — 20:19 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-06-04.md`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 12. 🔄 Update: update

- **Commit:** `2295b53` — 20:34 oleh Gudangsoft
- **File berubah:** 10 file
- `app/Http/Controllers/Admin/LaporanKinerjaController.php`
- `app/Http/Controllers/Admin/SyncController.php`
- `app/Providers/ViewServiceProvider.php`
- `database/migrations/2026_06_04_203005_add_performance_indexes_to_tables.php`
- `log-update-2026-06-04.md`
- `resources/views/admin/partials/sidebar.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/marketing/layouts/app.blade.php`
- `resources/views/partials/auto-refresh.blade.php`
- `resources/views/pic/partials/sidebar.blade.php`


## 14. 🔄 Update: as

- **Commit:** `9b40299` — 20:45 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-04.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`


## 15. 🔄 Update: aa

- **Commit:** `b0abba1` — 20:55 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-04.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 16. 🔄 Update: tabel

- **Commit:** `6bdc155` — 21:03 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-04.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 17. 🔄 Update: s

- **Commit:** `e71a071` — 21:07 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-04.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 18. 🔄 Update: sd

- **Commit:** `33a67cb` — 21:12 oleh Gudangsoft
- **File berubah:** 5 file
- `log-update-2026-06-04.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/submissions/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`

