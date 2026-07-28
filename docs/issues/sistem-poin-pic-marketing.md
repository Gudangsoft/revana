# Sistem Poin PIC & Marketing — Cara Kerja, Pengaruh, dan Riwayat Insiden

> Dokumentasi ini menjelaskan sistem poin gamifikasi untuk role **PIC** dan **Marketing**.
> Ditulis 2026-07-28 berdasarkan investigasi kode + `log-update-2026-07-27.md` / `log-update-2026-07-28.md`.

---

## 1. Ada dua sistem poin yang terpisah total

Project ini punya **dua** sistem poin yang tidak saling terhubung sama sekali — jangan tertukar:

| | Sistem PIC/Marketing (dibahas di sini) | Sistem Reviewer (terpisah) |
|---|---|---|
| Tabel poin | `pics.total_points`, `marketings.total_points` | `users.total_points` / `available_points` |
| Riwayat | `pic_point_histories`, `marketing_point_histories` | `point_histories` |
| Rate | `task_point_settings` | `Setting::get('points_per_review')`, dulu `point_day_settings` |
| Fungsi | **Gamifikasi/leaderboard murni** — tidak bisa ditukar apa pun | Bisa **ditukar reward** (`RewardRedemption`, `admin/rewards`) |

Sisa dokumen ini fokus ke sistem PIC/Marketing.

---

## 2. Skema data

### `pics` / `marketings` — kolom `total_points`
`decimal(10,2) default 0` — **kolom cache/agregat**. Ini bukan sumber kebenaran; nilainya harus selalu sama dengan `SUM(points_earned)` dari tabel riwayat masing-masing. Kalau beda, berarti ada bug sinkronisasi (lihat §6).

### `pic_point_histories`
```php
$table->id();
$table->foreignId('pic_id')->constrained('pics')->onDelete('cascade');
$table->foreignId('submission_id')->nullable()->constrained('submissions')->onDelete('set null');
$table->string('step', 50); // editor1, author1, editor2, editor3, author2, production,
                             // reviewer1, reviewer2, validator, submit, adjustment
$table->decimal('points_earned', 8, 2);
$table->text('description')->nullable();
$table->timestamps();
```
⚠️ **Tidak ada unique constraint** di level database untuk `(pic_id, submission_id, step)`. Pencegahan duplikat 100% mengandalkan kode aplikasi (`PicPointHistory::awardPoints()` cek existing row dulu sebelum insert).

### `marketing_point_histories`
```php
$table->id();
$table->foreignId('marketing_id')->constrained('marketings')->onDelete('cascade');
$table->foreignId('submission_id')->nullable()->constrained('submissions')->onDelete('cascade');
$table->decimal('points_earned', 8, 2);
$table->string('description')->nullable();
$table->timestamps();
$table->unique(['marketing_id', 'submission_id']); // guard duplikat DI LEVEL DATABASE
```
Berbeda dari tabel PIC, tabel ini **punya** unique constraint — asimetri struktural yang perlu diingat kalau mau menambah pencegahan duplikat serupa di sisi PIC.

### `task_point_settings` — tabel rate (sumber kebenaran "berapa poin per aksi")
```php
$table->string('user_type');  // 'pic' atau 'marketing'
$table->string('task_key');   // editor1, author1, ..., validator, submit
$table->string('task_label');
$table->decimal('points', 8, 2)->default(1);
$table->boolean('is_active')->default(true);
$table->unique(['user_type', 'task_key']);
```
Rate ini **bisa diubah admin kapan saja** lewat `/admin/task-point-settings`. Tidak ada versioning/snapshot histori rate — begitu rate berubah, tidak ada cara melihat "rate apa yang berlaku bulan lalu" selain dari nilai `points_earned` yang sudah tersimpan di baris riwayat lama. Ini penting: kalau ada bug yang menimpa ulang `points_earned` lama (seperti insiden §6), sinyal rate historis hilang permanen dan tidak bisa direkonstruksi.

---

## 3. Bagaimana poin dihitung & diberikan

### Rate saat ini (per 2026-07-28, bisa berubah kapan saja lewat admin panel)
```
pic/editor1 = 0.1     pic/reviewer1 = 0.2     pic/production = 1
pic/author1 = 0.1     pic/reviewer2 = 0.2     pic/submit     = 0.25
pic/editor2 = 0.2     pic/editor3   = 0       pic/validator  = 0.33
pic/author2 = 0
marketing/submit = 0.5
```
`validator` untuk PIC **sengaja 0 secara default** (langkah "Validator" sendiri tidak menghasilkan poin) — banyak kode secara eksplisit meng-handle `step = 'validator'` sebagai kasus khusus dengan nilai 0.

### Entry point resmi: dua helper idempoten

Semua pemberian poin **harus** lewat helper ini, bukan `Model::create()` langsung — helper ini yang menjamin tidak dobel:

**`app/Models/PicPointHistory.php` → `awardPoints($picId, $submissionId, $step, $description)`**
1. Ambil rate dari `TaskPointSetting::getPicPoints($step)`, fallback ke `POINT_CONFIG` constant kalau DB tidak tersedia.
2. Kalau rate ≤ 0 → tidak buat apa-apa (return null). Ini kenapa `validator` normal tidak menghasilkan baris riwayat.
3. Cek baris `(pic_id, submission_id, step)` sudah ada → kalau sudah, return null (mencegah dobel).
4. Insert baris riwayat + `Pic::increment('total_points', $points)` + hapus cache `rankings.topPics`.

Ada pasangannya, **`revokePoints()`**, dipanggil saat sebuah step di-un-validasi: hapus baris riwayat, kurangi `total_points`, lalu **selalu recalculate ulang dari `SUM(points_earned)`** sebagai safety net terhadap drift.

**`app/Models/MarketingPointHistory.php` → `awardPoints($marketingId, $submissionId, $description)`**
Sama polanya, tapi rate diambil dari `TaskPointSetting::getMarketingPoints('submit')` — marketing hanya punya **satu** task_key (`submit`), tidak ada rate per-langkah seperti PIC.

### Aksi apa saja yang memicu pemberian poin

| Trigger | Lokasi | Penerima | Step |
|---|---|---|---|
| Submission baru dibuat (form admin) | `app/Http/Controllers/Admin/SubmissionController.php:186-208` | Marketing + PIC | `submit` |
| Submission baru dibuat (form PIC self-service) | `app/Http/Controllers/Pic/JournalManagementController.php:536-562` | Marketing + PIC | `submit` |
| Submission fasttrack (admin/PIC) | `SubmissionController.php:1815-1827`, `JournalManagementController.php:1993-2010` | PIC + Marketing | `submit` |
| Toggle validasi step (PIC self-service, AJAX) | `JournalManagementController.php:1508-1525` (`toggleValidation()`) | PIC (per step); Marketing ikut dapat poin saat `production_valid` (step terakhir) di-set | `editor1..production/validator` |
| Toggle validasi step (admin AJAX, dengan revoke saat di-batal) | `SubmissionController.php:1570-1588` | PIC — award saat `true`, `revokePoints()` saat `false` | semua kecuali `reviewer1/2` (reviewer pakai sistem poin terpisah) |

Marketing **hanya** dapat poin dari `submit` — tidak ada skema per-langkah untuk marketing.

---

## 4. Apa yang dipengaruhi oleh poin (hanya ranking, tidak ada penukaran)

Poin PIC/Marketing **murni untuk leaderboard/dashboard**, tidak pernah dipakai untuk transaksi apa pun (tidak ada redeem reward — itu sistem reviewer yang terpisah):

- **Dashboard admin** (`Admin/DashboardController.php:119-132`) — top-10 PIC & Marketing by `total_points`, di-cache sebagai `rankings.topPics`/`rankings.topMarketings`.
- **Dashboard marketing sendiri** (`Marketing/DashboardController.php:167-176`) — cache top-10 yang sama.
- **Halaman ranking PIC** (`Pic/PicPointController.php:160-220`, method `rankings()`) — daftar rank lengkap PIC & Marketing, plus `pointsToNextRank`, `topPercentage`.
- **Blade views**: `resources/views/admin/point-rankings.blade.php`, `resources/views/pic/points/rankings.blade.php`, `resources/views/marketing/point-rankings.blade.php` — semua baca `->total_points` langsung.
- **Laporan Kinerja** (`Admin/LaporanKinerjaController.php`) — laporan performa PDF/Excel (lihat catatan bug residual di §6).
- **`/admin/sync`** (`Admin/SyncController.php`) — halaman diagnostik yang membandingkan `total_points` (cache) vs `SUM(points_earned)` (kebenaran) untuk mendeteksi baris yang "out of sync".

Tidak ada fitur reward/redemption/sertifikat yang mengonsumsi poin PIC/Marketing.

---

## 5. Sinkronisasi otomatis (bulk sync) — cara kerja saat ini

`TaskPointSettingController::syncTotals()` (dipanggil **setiap kali** admin menyimpan/hapus rate task point apa pun di `/admin/task-point-settings`) memanggil dua fungsi:

- `PicPointReportController::runBulkSync()`
- `MarketingPointReportController::runBulkSync()`

**Perilaku saat ini (setelah fix 28 Juli):** fungsi ini **hanya** melakukan:
1. `INSERT` baris riwayat yang **belum ada** untuk submission lama yang belum tercatat (backfill, pakai rate saat ini).
2. `UPDATE total_points = SUM(points_earned)` — recompute agregat dari riwayat.

Fungsi ini **tidak boleh lagi** menimpa `points_earned` pada baris riwayat yang sudah ada — itu justru akar masalah insiden (lihat §6).

Selain trigger admin di atas, ada juga auto-sync yang jalan tiap kali user membuka halaman poin miliknya sendiri:
- `Marketing::syncPoints()` — dipanggil dari `Marketing\DashboardController::dashboard()` dan `::points()` setiap page load.
- `Pic\PicPointController::index()` — recompute dari `SUM(points_earned)` dan langsung `save()` tiap kali PIC buka `/pic/points`.

Keduanya sekarang aman (SUM-based), tapi pola "auto-sync di setiap page view" inilah yang membuat bug lama menyebar cepat begitu logic sync-nya salah — kesalahan langsung ter-write ulang ke DB setiap kali korban membuka halaman poinnya sendiri.

---

## 6. Riwayat insiden 27–28 Juli 2026 (poin PIC/Marketing anjlok/dobel)

Ringkasan kronologis (detail lengkap ada di `log-update-2026-07-27.md` dan `log-update-2026-07-28.md`):

1. **`fb99d2c`** — Poin PIC/Marketing bisa **dobel** kalau PIC meng-unvalid lalu meng-valid ulang suatu step, karena beberapa jalur masih pakai `::create()` langsung, bukan `awardPoints()` yang idempoten. **Fix:** semua jalur pemberian poin diarahkan lewat `awardPoints()`.

2. **`4ff8fd5`** — Poin Marketing bisa **turun diam-diam**: 3 jalur menghitung `total_points` dari `COUNT(submissions)` (asumsi 1 submission = 1 poin, selalu), sementara jalur lain sudah benar pakai `SUM(points_earned)`. Begitu rate submit marketing tidak lagi persis 1, kedua formula itu berbeda hasil — dan karena `Marketing::syncPoints()` jalan otomatis tiap marketing buka `/marketing/points`, poin yang benar bisa tertimpa jadi salah kapan saja tanpa admin melakukan apa pun. **Fix:** semua jalur diseragamkan pakai `SUM(points_earned)`.

3. **`ba88937`** (akar masalah drop drastis) — Root cause: `runBulkSync()` di kedua controller (PIC & Marketing) — yang otomatis terpanggil **setiap kali admin menyimpan rate task point apa pun** — punya query yang menimpa ulang `points_earned` pada **SEMUA baris riwayat yang sudah ada** supaya sama dengan rate yang berlaku saat itu. Contoh nyata: marketing "Risqi" — 2.245 baris riwayat, semuanya seragam jadi 0,25 poin (padahal rate historisnya beragam), sehingga total anjlok ke ~561. **Fix:** hapus semua query "update existing records" dari `runBulkSync()` — sekarang hanya insert baris yang hilang, tidak pernah menimpa baris lama. Migration restorasi mengembalikan baris yang terbukti tertimpa (`updated_at > created_at` dan `submission_id IS NOT NULL`) ke nilai standar.

4. **`f11a32d`** — Setelah restorasi pertama, poin Risqi cuma naik ke 1.547,5 (bukan ~2.245 yang diharapkan) karena 930 baris ternyata **salah sejak lahir** (di-backfill otomatis saat rate sudah rusak, `created_at == updated_at`, lolos dari filter migration pertama). **Fix:** migration lanjutan mengoreksi **semua** baris terkait submission (tanpa syarat `updated_at`), sekaligus menetapkan kebijakan rate submit marketing final: **0,5 poin/submission**.

5. **`7b364a2`** — Bug ketiga dari kelas yang sama, ditemukan terpisah: **badge poin di navbar marketing** masih menghitung `submissions()->count()` (bukan `total_points`), sehingga menampilkan angka berbeda dari kartu "Total Point" di halaman ranking (contoh: 2.313 di navbar vs 1.158 di halaman poin). **Fix:** badge sekarang baca `total_points` langsung, tanpa kalkulasi/cache terpisah.

**Catatan penting yang tidak bisa dipulihkan:** rate historis yang sebenarnya berlaku di setiap submission pada waktunya sudah hilang permanen (tertimpa bug) — restorasi hanya bisa mengembalikan ke nilai standar (1/step, 0/validator, dan rate final marketing), bukan rate asli yang berlaku saat submission dibuat. Karena itu semua migration restorasi ini sengaja **tidak reversible** (`down()` kosong).

### Sistem terkait tapi terpisah: perubahan poin reviewer (22 Juli)
`7f21076`/`9214092` — poin reviewer diubah dari skala menurun per hari (`point_day_settings`) menjadi flat 10 poin/review, dengan backfill retroaktif ke riwayat lama. Sistem ini yang jadi "template" pola flat-rate-per-aksi yang kemudian ditiru di PIC/Marketing — tapi bukan sistem yang rusak pada insiden 27–28 Juli.

---

## 7. Bug residual — sudah diperbaiki 2026-07-28

Tiga bug tambahan ditemukan dan diperbaiki sesudah dokumen ini pertama kali ditulis (semua dari kelas masalah yang sama dengan insiden §6, ditemukan lewat investigasi kode langsung, bukan laporan user):

### a) `LaporanKinerjaController` — laporan kinerja PIC pakai COUNT × rate saat ini (FIXED)
`app/Http/Controllers/Admin/LaporanKinerjaController.php` (method `index()` dan `buildData()`) dulu menghitung `total_poin` PIC per periode sebagai:
```php
$count = $picCounts[$key] ?? 0;                       // jumlah submission tervalidasi di periode itu
$totalPoin += $count * ($pointValues[$key] ?? 0);     // count × rate SAAT INI, bukan SUM(points_earned)
```
Kalau rate sebuah step pernah berubah, angka "total poin" laporan untuk bulan-bulan lama tidak cocok dengan SUM riwayat aslinya, dan berubah diam-diam tiap kali laporan dijalankan ulang setelah rate diubah. **Fix:** `total_poin` sekarang diambil dari `SUM(points_earned)` di `pic_point_histories`, difilter periode yang sama (`created_at`) — sama seperti pola yang sudah dipakai di sisi Marketing pada controller yang sama.

### b) `SubmissionController::destroy()` — hapus submission recalculate poin marketing pakai COUNT (FIXED)
`app/Http/Controllers/Admin/SubmissionController.php:445-467` dulu:
```php
$remainingSubmissions = Submission::where('marketing_id', $submission->marketing_id)
    ->where('id', '!=', $submission->id)->count();
$marketing->total_points = $remainingSubmissions;   // COUNT, bukan SUM(points_earned)
```
Dengan rate 0,5/submission, menghapus submission menimpa `total_points` dengan jumlah submission mentah (mis. 20 tersisa → `total_points` di-set 20, padahal seharusnya SUM `points_earned`, mis. 10). **Fix:** sekarang recalculate dari `MarketingPointHistory::where('marketing_id', ...)->sum('points_earned')`.

### c) Tanggal penyelesaian tugas berubah jadi tanggal sinkronisasi, bukan tanggal tugas selesai (FIXED)
Dilaporkan user: setiap admin melakukan sinkronisasi poin, `created_at` pada baris riwayat yang di-backfill ikut berubah jadi **waktu sinkronisasi dijalankan**, bukan tanggal tugas/submission itu sebenarnya selesai. Ditemukan di 3 tempat:

- `PicPointReportController::runBulkSync()` — backfill step `submit` hardcode `NOW(), NOW()` di raw SQL, bukan `COALESCE(s.created_at, NOW())` seperti yang sudah benar dipakai untuk step workflow lain di fungsi yang sama.
- `PicPointController::syncMyPoints()` (tombol "Sinkronkan Poin Saya" milik PIC) — memanggil `PicPointHistory::awardPoints()` tanpa memberi tahu tanggal asli, sehingga Eloquent memakai `now()` sebagai `created_at`.
- `MarketingPointReportController::syncAllPoints()` (tombol sync di `/admin/marketing-points`) — memakai `MarketingPointHistory::create([...])` langsung tanpa `created_at` eksplisit, jadi Eloquent juga memakai `now()`.

**Fix:** kedua helper `PicPointHistory::awardPoints()` dan `MarketingPointHistory::awardPoints()` sekarang menerima parameter opsional `$occurredAt` — kalau diisi, `created_at`/`updated_at` baris riwayat di-set ke tanggal itu (bukan waktu saat fungsi dipanggil). Semua jalur sync/backfill di atas sekarang mengirim tanggal asli (`submission->created_at` untuk step `submit`, kolom `*_validated_at` submission untuk step workflow lain). Jalur pemberian poin **live** (saat PIC/admin benar-benar meng-klik validasi) tidak diubah — tetap tidak mengirim `$occurredAt`, sehingga tetap memakai `now()` seperti semula (memang benar karena itu momen tugas selesai).

**Catatan:** perbaikan ini mencegah kerusakan tanggal ke depan. Baris riwayat yang tanggalnya sudah kadung berubah jadi tanggal sync di masa lalu **tidak** diperbaiki oleh perubahan ini (butuh migration restorasi terpisah kalau diperlukan, mirip pola di §6).

---

## 8. Referensi file kunci

| Concern | Path |
|---|---|
| Model poin PIC (`awardPoints`/`revokePoints`) | `app/Models/PicPointHistory.php` |
| Model poin Marketing (`awardPoints`) | `app/Models/MarketingPointHistory.php` |
| Tabel rate | `app/Models/TaskPointSetting.php` |
| Model PIC (`total_points`) | `app/Models/Pic.php` |
| Model Marketing (`getActualPoints()`, `syncPoints()`) | `app/Models/Marketing.php:70-86` |
| Admin: setting rate + trigger sync | `app/Http/Controllers/Admin/TaskPointSettingController.php:49-182` |
| Admin: bulk sync/recalc PIC | `app/Http/Controllers/Admin/PicPointReportController.php` |
| Admin: bulk sync/recalc Marketing | `app/Http/Controllers/Admin/MarketingPointReportController.php` |
| Diagnostik out-of-sync | `app/Http/Controllers/Admin/SyncController.php` |
| Halaman poin PIC sendiri | `app/Http/Controllers/Pic/PicPointController.php` |
| Halaman poin Marketing sendiri | `app/Http/Controllers/Marketing/DashboardController.php` |
| Trigger poin dari alur submission | `app/Http/Controllers/Admin/SubmissionController.php`, `app/Http/Controllers/Pic/JournalManagementController.php` |
| Badge navbar marketing | `resources/views/marketing/layouts/app.blade.php` |
| Laporan kinerja (bug residual) | `app/Http/Controllers/Admin/LaporanKinerjaController.php` |
| Migration insiden 27–28 Juli | `database/migrations/2026_07_27_000001_fix_marketing_points_count_to_sum_formula.php`<br>`database/migrations/2026_07_28_000001_restore_points_corrupted_by_rewrite_bug.php`<br>`database/migrations/2026_07_28_000002_restore_remaining_deflated_points.php` |
| Changelog insiden (naratif lengkap) | `log-update-2026-07-27.md`, `log-update-2026-07-28.md` |
