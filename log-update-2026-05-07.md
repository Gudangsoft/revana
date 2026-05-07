# Log Update — 7 Mei 2026

> Sesi pengembangan penuh: perbaikan data, fitur WA otomatis, peningkatan UX, dan hardening teknis/keamanan.

---

## 1. Fix Kode Submit Lama: Prefix SUB → BKD / JAFA

**Tujuan:** Data submission lama yang `program_type = bkd` atau `jafa` masih tersimpan dengan prefix `SUB` pada `kode_submit`. Migration ini memperbaiki data existing tanpa mengubah urutan nomor.

**Contoh:** `SUB202605050017` (bkd) → `BKD202605050017`

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_05_07_173754_fix_kode_submit_prefix_for_bkd_jafa.php` | Migration baru: UPDATE batch untuk BKD dan JAFA; mendukung rollback |

---

## 2. Hapus Prefix Program di Field ID Artikel

**Tujuan:** Field ID Artikel di form submission tidak lagi diisi otomatis dengan prefix program (contoh: `BKD-`). Prefix tersebut kini hanya ada di Kode Submit yang di-generate otomatis — tidak perlu redundan di ID Artikel.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/create.blade.php` | Hapus logika `strtoupper(request('program')) . '-'` dari `value` dan `placeholder` |
| `resources/views/pic/submissions/create.blade.php` | Sama — field ID Artikel kini kosong saat form dibuka |
| `resources/views/marketing/create-submission.blade.php` | Sama — field Nomor Submit kini kosong saat form dibuka |

---

## 3. Notifikasi WhatsApp Otomatis (Fonnte)

**Tujuan:** Mengurangi pekerjaan manual admin/PIC dengan mengirim WA otomatis pada 4 event kunci dalam alur submission.

| Event | Penerima | Isi Pesan |
|-------|----------|-----------|
| Submission baru masuk | Marketing + PIC Submit | Kode, penulis, judul, jurnal, program |
| Reviewer ditugaskan | Reviewer (User) | Detail artikel + kredensial OJS |
| Review selesai divalidasi | PIC Submit | Notif reviewer sudah selesai |
| Reminder harian (>3 hari belum selesai) | Reviewer | Pengingat dengan jumlah hari keterlambatan |

**Cron reminder** berjalan setiap hari pukul 08:00 via Laravel Scheduler.
Gunakan `php artisan wa:reviewer-reminders --days=5` untuk threshold kustom.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Services/WaNotificationService.php` | **Baru** — service terpusat dengan 4 metode notifikasi |
| `app/Console/Commands/SendReviewerDeadlineReminders.php` | **Baru** — artisan command `wa:reviewer-reminders` |
| `app/Console/Kernel.php` | Daftarkan command ke scheduler: `dailyAt('08:00')` |
| `app/Http/Controllers/Admin/SubmissionController.php` | Trigger WA di `store()`, `updateStep()` reviewer1/2, `validateStep()` reviewer1/2 |

> Menggunakan token Fonnte yang sudah dikonfigurasi di `/admin/sms-gateway` — tidak perlu setup tambahan.

---

## 4. Peningkatan UX: Search Global, Progress Stepper, Dashboard Charts

### 4a. Search Global di Navbar

Kotak pencarian muncul di navbar atas (admin only). Mencari lintas field: nama penulis, ID artikel, judul artikel, kode submit, nomor HP, nama jurnal.

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SearchController.php` | **Baru** — query search dengan `orWhere` + `orWhereHas` journal |
| `resources/views/admin/search/results.blade.php` | **Baru** — halaman hasil pencarian responsif |
| `routes/web.php` | Tambah route `GET /admin/search` → `admin.search` |
| `resources/views/layouts/app.blade.php` | Tambah form search di navbar (hanya muncul untuk role admin) |

### 4b. Progress Stepper Horizontal

Mengganti progress bar persentase sederhana dengan stepper 5 tahap yang lebih informatif di halaman detail submission.

```
[✓] Disubmit  →  [✓] Editorial  →  [●] Review  →  [○] Produksi  →  [○] Selesai
```

- Tahap selesai: lingkaran hijau + centang
- Tahap aktif: lingkaran biru + glow + label status spesifik
- Ditolak: lingkaran merah + tanda silang

| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/show.blade.php` | Ganti progress bar dengan stepper 5 tahap + CSS inline |

### 4c. Dashboard Charts (Chart.js 4.4)

Menambahkan dua grafik di atas section Quick Actions pada dashboard admin:

- **Bar + Line chart** — Tren submission per bulan (total / published / rejected) dalam satu grafik gabungan
- **Donut chart** — Status overview: Published, Rejected, In Progress, Submitted + ringkasan angka

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/DashboardController.php` | Tambah `chartLabels`, `chartTotals`, `chartPublished`, `chartRejected` (data per bulan) |
| `resources/views/admin/dashboard.blade.php` | Tambah section 2 chart via `@push('scripts')` + Chart.js CDN |

### 4d. Update Dashboard: Quick Actions, BKD/JAFA Stats, Tabel Submissions

**Quick Actions** diperbarui dengan 7 tombol berwarna: Tambah Jurnal, Kelola Submissions, Submission BKD (+ counter), Submission JAFA (+ counter), Fasttrack, Laporan Kinerja, WA Gateway.

**Informasi BKD & JAFA** — section baru dengan 4 angka per program (Total, Antrian, Published, Ditolak) + progress bar published.

**Submissions yang Sudah Disetujui** — diperbesar ke 30 data terbaru, tambah kolom Program (badge berwarna) dan Marketing, ada tombol detail langsung.

**Submissions Terbaru** — diperbesar ke 15 data, urut dari tanggal submit terbaru, kolom Program + badge warna (BKD=cyan, JAFA=ungu, FT=abu), kolom Marketing/PIC, gunakan `status_label` + `status_badge_class` yang sudah benar.

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/DashboardController.php` | Tambah `bkdStats`, `jafaStats`; recentSubmissions jadi 15 (urut tanggal_submit); completedReviews jadi 30; load relasi marketing + petugasSubmit |
| `resources/views/admin/dashboard.blade.php` | Redesain Quick Actions, tambah BKD/JAFA stats cards, update 2 tabel submissions |

---

## 5. Teknis & Keamanan

### 5a. Query Caching (5 Menit)

Ranking dan data chart di-cache untuk mengurangi beban database pada setiap load dashboard.

| Cache Key | TTL | Isi |
|-----------|-----|-----|
| `rankings.topPics` | 5 menit | Top 10 PIC by total_points |
| `rankings.topMarketings` | 5 menit | Top 10 Marketing by total_points |
| `dashboard.monthlyStats.{year}` | 5 menit | Data chart tren bulanan |

Cache di-invalidate otomatis setiap kali `awardPoints()` dipanggil (pada setiap submit/validasi submission).

| File | Perubahan |
|------|-----------|
| `app/Models/PicPointHistory.php` | `Cache::forget('rankings.topPics')` setelah award |
| `app/Models/MarketingPointHistory.php` | `Cache::forget('rankings.topMarketings')` setelah award |
| `app/Http/Controllers/Admin/DashboardController.php` | Wrap 3 query berat dalam `Cache::remember(..., 300)` |
| `app/Http/Controllers/Pic/AuthorController.php` | Wrap topPics + topMarketings dalam cache |
| `app/Http/Controllers/Marketing/DashboardController.php` | Wrap topPics + topMarketings dalam cache |

### 5b. Activity Log per Submission

Setiap kali admin mengedit data submission, perubahan field dicatat otomatis — termasuk nilai sebelum dan sesudah.

Field yang dilacak: Judul Artikel, Nama Penulis, ID Artikel, Link Submit, No HP Penulis, Status, Marketing, PIC Submit, Slot Jurnal, Program, Catatan.

Riwayat perubahan ditampilkan sebagai timeline di bagian bawah halaman detail submission (`/admin/submissions/{id}`).

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_05_07_220232_create_activity_logs_table.php` | **Baru** — tabel `activity_logs` (subject, causer, event, old/new JSON, IP) |
| `app/Models/ActivityLog.php` | **Baru** — static `record()`, `diff()`, event label/badge |
| `app/Http/Controllers/Admin/SubmissionController.php` | Snapshot before/after di `update()`; load logs di `show()` |
| `resources/views/admin/submissions/show.blade.php` | Tambah section "Riwayat Perubahan" (timeline tabel before/after) |

### 5c. Rate Limiting Login

Login PIC dan Marketing sebelumnya tidak memiliki proteksi brute-force. Sekarang dibatasi **5 percobaan per menit per IP** — sama dengan admin.

| Guard | Route | Sebelum | Sesudah |
|-------|-------|---------|---------|
| Admin | `POST /login` | `throttle:5,1` | Tidak berubah |
| PIC | `POST /pic/login` | ❌ Tidak ada | ✅ `throttle:5,1` |
| Marketing | `POST /marketing/login` | ❌ Tidak ada | ✅ `throttle:5,1` |

Gagal login karena password salah kini juga dicatat di Laravel log (`Log::warning`) dengan email + IP + user agent untuk keperluan audit.

| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah `->middleware('throttle:5,1')` ke 2 route login |
| `app/Http/Controllers/Pic/Auth/LoginController.php` | Tambah `Log::warning` pada password salah |
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah `Log::warning` pada password salah |

## 6. 🔄 Update: update

- **Commit:** `7aa7bb1` — 22:10 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-07.md`


## 7. 🔄 Update: update terbaru

- **Commit:** `83e7034` — 22:11 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-07.md`


## 8. 🔄 Update: update 7 mei

- **Commit:** `f6e54e9` — 22:11 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-07.md`


## 9. 🔄 Update: Update 7 Mei 2026

- **Commit:** `eee46d2` — 22:12 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-07.md`


## 10. 🔄 Update: update info dashbord

- **Commit:** `4216317` — 22:21 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/DashboardController.php`
- `log-update-2026-05-07.md`
- `resources/views/admin/dashboard.blade.php`


## 11. 🔄 Update: update info dashbord

- **Commit:** `7cf9c7b` — 22:21 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-07.md`


## 12. Fix Error Route `admin.fasttrack.monitoring` Tidak Terdefinisi

**Tujuan:** Halaman `/admin/fasttrack` error karena route `admin.fasttrack.monitoring` tidak ada di grup admin fasttrack, meskipun view dan controller method-nya sudah ada.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah `Route::get('/monitoring', ...)→name('monitoring')` ke grup `admin.fasttrack.*` |


## 13. 🔄 Update: update

- **Commit:** `b8412af` — 22:29 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-05-07.md`
- `resources/views/admin/dashboard.blade.php`
- `routes/web.php`


## 14. 🔄 Update: update

- **Commit:** `4999cbc` — 22:29 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-07.md`


## 15. 🔄 Update: fastract

- **Commit:** `9287e2a` — 22:31 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-05-07.md`
- `routes/web.php`


## 16. 🔄 Update: perbaikan grafic

- **Commit:** `ad2ba89` — 22:38 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/DashboardController.php`
- `log-update-2026-05-07.md`
- `resources/views/admin/dashboard.blade.php`

