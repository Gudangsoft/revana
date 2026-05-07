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


## 2. Notifikasi WhatsApp Otomatis via Fonnte

**Tujuan:** Kirim WA otomatis ke Marketing/PIC saat submission baru masuk, ke reviewer saat ditugaskan beserta kredensial OJS, ke PIC saat review selesai, dan reminder harian ke reviewer yang belum selesai lebih dari 3 hari.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Services/WaNotificationService.php` | Service baru: 4 metode notifikasi (newSubmission, reviewerAssigned, reviewCompleted, deadlineReminder) |
| `app/Console/Commands/SendReviewerDeadlineReminders.php` | Artisan command `wa:reviewer-reminders` untuk reminder harian |
| `app/Console/Kernel.php` | Daftarkan command `wa:reviewer-reminders` jalan tiap hari jam 08:00 |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah trigger WA di store(), updateStep() reviewer1/2, validateStep() reviewer1/2 |

---

## 3. Peningkatan UX: Search Global, Stepper, Dashboard Charts

**Tujuan:** (1) Kotak pencarian di navbar untuk admin mencari submission lintas halaman. (2) Progress stepper horizontal di detail submission menggantikan progress bar sederhana. (3) Grafik tren bulanan + donut status di dashboard admin.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SearchController.php` | Controller baru: global search submission (nama, ID, judul, kode, jurnal) |
| `resources/views/admin/search/results.blade.php` | View baru: halaman hasil pencarian dengan tabel responsif |
| `routes/web.php` | Tambah route `GET /admin/search` → `admin.search` |
| `resources/views/layouts/app.blade.php` | Tambah search box di navbar (hanya tampil untuk admin) |
| `resources/views/admin/submissions/show.blade.php` | Ganti progress bar dengan horizontal stepper 5 tahap (Submit → Editorial → Review → Produksi → Selesai) |
| `app/Http/Controllers/Admin/DashboardController.php` | Tambah data chart: chartLabels, chartTotals, chartPublished, chartRejected (per bulan) |
| `resources/views/admin/dashboard.blade.php` | Tambah section chart: bar chart tren bulanan + donut status overview (Chart.js 4.4) |

---

## 4. Teknis & Keamanan: Caching, Activity Log, Rate Limiting

**Tujuan:** (1) Cache query ranking/poin 5 menit dengan invalidasi otomatis saat poin berubah. (2) Activity log untuk submission — catat siapa mengubah field apa sebelum/sesudah. (3) Rate limiting 5x/menit pada login PIC dan Marketing (admin sudah ada).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/PicPointHistory.php` | Tambah `Cache::forget('rankings.topPics')` setelah award points |
| `app/Models/MarketingPointHistory.php` | Tambah `Cache::forget('rankings.topMarketings')` setelah award points |
| `app/Http/Controllers/Admin/DashboardController.php` | Wrap topPics, topMarketings, dan monthly chart data dalam `Cache::remember(..., 300)` |
| `app/Http/Controllers/Pic/AuthorController.php` | Wrap topPics, topMarketings dalam `Cache::remember` |
| `app/Http/Controllers/Marketing/DashboardController.php` | Wrap topPics, topMarketings dalam `Cache::remember` + failed login logging |
| `database/migrations/2026_05_07_220232_create_activity_logs_table.php` | Migration baru tabel activity_logs |
| `app/Models/ActivityLog.php` | Model baru: static `record()`, `diff()`, badge helpers, daftar field tracking |
| `app/Http/Controllers/Admin/SubmissionController.php` | Snapshot before/after di update(), catat ActivityLog jika ada field berubah; load activityLogs di show() |
| `resources/views/admin/submissions/show.blade.php` | Tambah section "Riwayat Perubahan" di bawah detail submission |
| `routes/web.php` | Tambah `throttle:5,1` ke POST /pic/login dan POST /marketing/login |
| `app/Http/Controllers/Pic/Auth/LoginController.php` | Tambah Log::warning pada password salah |

---

## 5. 🔄 Update: up

- **Commit:** `e450978` — 17:34 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Models/Submission.php`
- `log-update-2026-05-07.md`
- `resources/views/admin/submissions/create.blade.php`
- `resources/views/marketing/create-submission.blade.php`
- `resources/views/pic/submissions/create.blade.php`

