# Log Update — 15 Juni 2026

## 1. Fix Bug & Performance: Admin PIC Points Page (`/admin/pic-points`)

**Tujuan:** Review dan perbaikan halaman laporan point PIC dari hasil cek kode secara menyeluruh.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Exports/PicPointsExport.php` | Ganti `static $rank = 0` → instance property `$this->rank` (static tidak reset antar instance) |
| `app/Exports/PicPointHistoryExport.php` | Ganti `static $no = 0` → instance property `$this->no` (idem) |
| `app/Models/TaskPointSetting.php` | `getPicPoints()` return type dari `float` ke `?float` (return null jika step tidak ada di DB, bukan 1.0) |
| `app/Models/Pic.php` | `getPointsThisMonthAttribute()` & `getTotalTasksCompletedAttribute()` — cek preloaded value dari `withSum`/`withCount` sebelum query baru |
| `app/Http/Controllers/Admin/PicPointReportController.php` | Tambah `withCount` + `withSum` ke query index; tambah batch pending_tasks_count (8 query per PIC → 8 query untuk semua PIC) |
| `resources/views/admin/pic-points/index.blade.php` | Ganti `$pic->pending_tasks_count` → `$pendingCounts[$pic->id]`; pindahkan modal adjust keluar dari `<tbody>` ke luar `<table>` |

## 2. Fix Timeout 524: Sync-All PIC Points (`/admin/pic-points/sync-all`)

**Tujuan:** Perbaiki error Cloudflare 524 timeout saat menekan tombol "Sinkronkan Point".

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | Rewrite `syncAllPoints()` dan `syncAllAndLogout()` ke bulk SQL; ekstrak logika sync ke `runBulkSync()` private method |

### Detail

**Root cause:** `syncAllPoints()` melakukan loop PHP per submission × per step = ribuan query individual. Dengan data besar (>500 submission), proses melebihi timeout 100 detik Cloudflare → Error 524.

**Fix:** Ganti loop PHP dengan bulk SQL:
- Submit backfill: 1 `INSERT INTO ... SELECT` dengan `NOT EXISTS` subquery (was: 1 query per submission)
- 8 workflow steps: masing-masing 1 `INSERT INTO ... SELECT` + 1 `UPDATE` (was: 2 queries per row per step)
- Recalculate total_points: 1 `UPDATE pics ... LEFT JOIN subquery` (was: N queries per PIC)
- `syncAllAndLogout()`: tidak lagi duplikasi kode, pakai `runBulkSync()` yang sama

**Sebelum:** ~ribuan query, >100 detik untuk data besar
**Sesudah:** ~20-25 query total, selesai dalam hitungan detik

---

### Detail Perbaikan (fix sebelumnya)

**Bug export nomor urut salah:** `static $rank` / `static $no` dalam method `map()` tidak direset jika class diinstansiasi ulang dalam request yang sama. Diganti ke property instance.

**Dead code null-check:** `TaskPointSetting::getPicPoints()` sebelumnya selalu return `float` (default 1.0), sehingga `if ($dbPoints !== null)` di `getPointsForStep()` selalu true dan fallback ke `POINT_CONFIG` tidak pernah terpakai. Sekarang return `null` jika tidak ada di DB, fallback aktif kembali.

**N+1 query parah:** Halaman leaderboard dengan 20 PIC menghasilkan ~200+ extra queries:
- `pending_tasks_count` = 8 queries per PIC → diperbaiki jadi 8 queries untuk semua PIC (batch)
- `points_this_month` & `total_tasks_completed` = 1 query per PIC masing-masing → diperbaiki via `withSum`/`withCount` preloading di controller, accessor memanfaatkan nilai preloaded

**HTML invalid:** Modal `<div>` ditempatkan langsung di dalam `<tbody>` (bukan di dalam `<tr>`). Browser auto-fix tapi tidak valid. Modals dipindahkan ke setelah `</table>` dalam loop `@foreach` terpisah.

## 3. Tambah Field "Email Penulis" ke Semua Menu Submission Fasttrack

**Tujuan:** Terapkan field `email_penulis` ke semua menu submission fasttrack (admin, PIC, marketing) — form create, edit, dan halaman show/detail. Normal/BKD/JAFA sudah memiliki field ini sebelumnya.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `resources/views/admin/fasttrack/create.blade.php` | Tambah input `email_penulis` setelah field `no_hp_penulis` |
| `resources/views/admin/fasttrack/edit.blade.php` | Tambah input `email_penulis` setelah field `no_hp_penulis` |
| `resources/views/admin/fasttrack/show.blade.php` | Tambah baris tampilan `email_penulis` di tabel detail |
| `resources/views/pic/fasttrack/create.blade.php` | Tambah input `email_penulis` + tambah ke JS preview konfirmasi |
| `resources/views/pic/fasttrack/edit.blade.php` | Tambah input `email_penulis` (style `form-group`/`fas fa-envelope`) |
| `resources/views/pic/fasttrack/show.blade.php` | Tambah tampilan `email_penulis` di section detail penulis |
| `resources/views/marketing/fasttrack/create.blade.php` | Tambah input `email_penulis` + tambah ke JS preview rows array |
| `resources/views/marketing/fasttrack/show.blade.php` | Tambah baris tampilan `email_penulis` di tabel detail |
| `app/Http/Controllers/Pic/JournalManagementController.php` | Tambah validasi `email_penulis` di `fasttrackStore()` dan `fasttrackUpdate()`; tambah ke array `$submission->update()` |
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah validasi `email_penulis` di `fasttrackStore()`; tambah ke `Submission::create()` |

## 4. Tambah Variabel `{nama_jurnal}` ke Email Template

**Tujuan:** Tambahkan variabel `{nama_jurnal}` agar bisa disisipkan ke subjek/isi template email, diisi otomatis dengan nama jurnal dari submission.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `resources/views/admin/email-templates/form.blade.php` | Tambah `{nama_jurnal}` ke chip variabel + sample preview JS |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah `'nama_jurnal'` ke dua render() call (assign & validate email) via `$submission->journalSlot?->journalMaster?->nama_jurnal` |
| `app/Http/Controllers/Admin/EmailTemplateController.php` | Tambah `'nama_jurnal'` ke preview vars (sample: 'Jurnal Pendidikan Indonesia') |

## 5. Tambah Variabel `{url_jurnal}`, `{nama_penulis}`, `{username_author}`, `{password_author}` ke Email Template

**Tujuan:** Lengkapi variabel email template untuk mendukung email "Submission Acknowledgement" — nama penulis, credential OJS author, dan URL website jurnal (untuk hyperlink nama jurnal).

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `resources/views/admin/email-templates/form.blade.php` | Tambah 4 chip variabel baru + sample preview JS |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah 4 variabel ke dua render() call (assign & validate) |
| `app/Http/Controllers/Admin/EmailTemplateController.php` | Tambah 4 variabel ke preview vars |

### Variabel Baru

| Variabel | Sumber Data |
|----------|-------------|
| `{url_jurnal}` | `journalSlot → journalMaster → link_jurnal` |
| `{nama_penulis}` | `submission → nama_penulis` |
| `{username_author}` | `submission → username_author` |
| `{password_author}` | `submission → password_author` |

Contoh penggunaan di body template:
```html
Dear {nama_penulis},<br>
Thank you for submitting "<b>{nama_artikel}</b>" to <a href="{url_jurnal}">{nama_jurnal}</a>.<br>
Username: {username_author} | Password: {password_author}
```

## 6. Default Urutan Monitoring Marketing → Terbaru

**Tujuan:** Ubah default urutan halaman Monitoring Artikel marketing dari "Terlama" menjadi "Terbaru".

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Marketing/DashboardController.php` | `submissionsMonitoring()`: default `sort_by` dari `date_asc` → `date_desc`; `date_asc` dijadikan case eksplisit |
| `resources/views/marketing/submissions-monitoring.blade.php` | Option "↓ Terbaru" dipindah ke urutan pertama dan dijadikan default selected; "↑ Terlama" dipindah ke urutan kedua |

## 7. Tambah Trigger `assign_submit` dan `notify_penulis` ke Email Template

**Tujuan:** `assign_submit` ada di controller tapi tidak muncul di UI karena belum ada di `$triggerLabels`. Tambah juga trigger `notify_penulis` untuk email acknowledgement ke penulis saat submission pertama kali masuk.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Models/EmailTemplate.php` | Tambah `notify_penulis` dan `assign_submit` ke `$triggerLabels` |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah private method `sendPenulisEmail()`; panggil di `store()` dan `fasttrackStore()` |

### Detail

**`assign_submit`** — trigger sudah ada di controller (`findActive('assign_submit')`) sejak awal tapi tidak bisa dikonfigurasi karena tidak ada di `$triggerLabels`. Sekarang muncul di dropdown.

**`notify_penulis`** — trigger baru, kirim email ke `email_penulis` saat submission dibuat. Email tidak terkirim jika `email_penulis` kosong atau tidak ada template aktif untuk trigger ini. Variables tersedia: `{nama_penulis}`, `{nama_artikel}`, `{kode_submit}`, `{id_artikel}`, `{nama_jurnal}`, `{url_jurnal}`, `{username_author}`, `{password_author}`, `{tanggal}`, `{app_name}`.

## 8. Laporan Log Pengiriman Email (`/admin/email-logs`)

**Tujuan:** Catat setiap pengiriman email dari sistem template (berhasil/gagal/pending) dan tampilkan laporan di halaman admin.

### File yang Diubah / Dibuat

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_15_120000_create_email_logs_table.php` | Buat tabel `email_logs` (trigger_key, submission_id, recipient, subject, status, error_message, sent_at) |
| `app/Models/EmailLog.php` | Model baru + static method `record()` untuk catat log |
| `app/Http/Controllers/Admin/SubmissionController.php` | Update `sendPenulisEmail()`, assign email block, validate email block — semua catat ke `EmailLog` saat sent/failed |
| `app/Http/Controllers/Admin/EmailTemplateController.php` | Tambah method `logs()` untuk halaman laporan dengan filter & summary |
| `routes/web.php` | Tambah route `GET /email-logs` → `admin.email-logs.index` |
| `resources/views/admin/email-logs/index.blade.php` | View baru: summary cards (terkirim/gagal/pending) + tabel log dengan filter + popover error message |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah link "Log Pengiriman Email" di sidebar, di bawah Template Email |

### Fitur Halaman Log

- **Summary cards:** total Terkirim / Gagal / Pending
- **Filter:** cari email/nama/subjek, filter status, filter trigger, filter rentang tanggal
- **Per baris:** waktu, status badge, nama trigger, penerima (nama + email), subjek, link ke submission
- **Error detail:** klik ikon info untuk lihat pesan error lengkap (popover)

## 9. 🔄 Update: fix sincron

- **Commit:** `d5b709c` — 08:56 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Exports/PicPointHistoryExport.php`
- `app/Exports/PicPointsExport.php`
- `app/Http/Controllers/Admin/PicPointReportController.php`
- `app/Models/Pic.php`
- `app/Models/TaskPointSetting.php`
- `log-update-2026-06-15.md`
- `resources/views/admin/pic-points/index.blade.php`


## 5. 🔄 Update: email

- **Commit:** `edc8015` — 10:05 oleh Gudangsoft
- **File berubah:** 11 file
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-06-15.md`
- `resources/views/admin/fasttrack/create.blade.php`
- `resources/views/admin/fasttrack/edit.blade.php`
- `resources/views/admin/fasttrack/show.blade.php`
- `resources/views/marketing/fasttrack/create.blade.php`
- `resources/views/marketing/fasttrack/show.blade.php`
- `resources/views/pic/fasttrack/create.blade.php`
- `resources/views/pic/fasttrack/edit.blade.php`


## 7. 🔄 Update: emailup

- **Commit:** `4ce538c` — 10:13 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/EmailTemplateController.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `log-update-2026-06-15.md`
- `resources/views/admin/email-templates/form.blade.php`


## 10. 🔄 Update: up

- **Commit:** `234b1c9` — 10:19 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Admin/EmailTemplateController.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `log-update-2026-06-15.md`
- `resources/views/admin/email-templates/form.blade.php`
- `resources/views/marketing/submissions-monitoring.blade.php`


## 12. 🔄 Update: s

- **Commit:** `b4f5658` — 11:08 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Models/EmailTemplate.php`
- `log-update-2026-06-15.md`


## 14. 🔄 Update: report email

- **Commit:** `c550250` — 11:15 oleh Gudangsoft
- **File berubah:** 8 file
- `app/Http/Controllers/Admin/EmailTemplateController.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Models/EmailLog.php`
- `database/migrations/2026_06_15_120000_create_email_logs_table.php`
- `log-update-2026-06-15.md`
- `resources/views/admin/email-logs/index.blade.php`
- `resources/views/admin/partials/sidebar.blade.php`
- `routes/web.php`


## 10. Pengaturan Point Dinamis: Tambah Task & Sync Otomatis (`/admin/task-point-settings`)

**Tujuan:** Jadikan halaman pengaturan point dinamis — admin bisa menambahkan task/step baru untuk PIC dan Marketing, menghapus task yang tidak diperlukan, dan setelah disimpan total poin langsung disinkronkan otomatis.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/TaskPointSettingController.php` | Tambah private `syncTotals()` — 2 bulk SQL untuk recalculate `total_points` pada `pics` dan `marketings`; panggil di `update()`, `store()`, `destroy()` |
| `resources/views/admin/task-point-settings/index.blade.php` | Tombol "Tambah Task" di header tiap card; modal form tambah task PIC/Marketing; kolom Aksi (trash) tiap baris; hidden delete form (JS confirmDelete); tampilkan custom task di bawah default steps |

### Detail Fitur Baru

**Tambah Task Baru:**
- Tombol "+ Tambah Task" di header card PIC (biru) dan Marketing (hijau)
- Modal berisi: Task Key (validasi lowercase+underscore), Label Tugas, Point
- Submit → POST `/admin/task-point-settings` → `store()` → sync → redirect

**Hapus Task:**
- Icon trash di setiap baris (default & custom)
- Konfirmasi JS sebelum hapus (tidak menghapus histori poin)
- Delete → `destroy()` → sync → redirect

**Custom Tasks:**
- Task yang ditambahkan manual (bukan 9 default PIC / 1 default Marketing) tampil di bagian bawah tabel dengan badge "custom" dan warna biru-info
- Bisa diedit (label, point, aktif) dan dihapus sama seperti task default

**Sync Otomatis (`syncTotals()`):**
- Setiap save/tambah/hapus otomatis menjalankan 2 query:
  - `UPDATE pics SET total_points = COALESCE(SUM(pic_point_histories.points_earned), 0)`
  - `UPDATE marketings SET total_points = COUNT(submissions WHERE marketing_id = marketings.id)`
- Tidak mengubah data historis, hanya recalculate total

## 15. 🔄 Update: up

- **Commit:** `3ec594a` — 11:27 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/SubmissionController.php`
- `log-update-2026-06-15.md`

