# Log Update — 28 July 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Insiden: Poin PIC/Marketing Anjlok Drastis — Root Cause & Perbaikan Darurat

**Tujuan:** User melapor poin marketing "Risqi" anjlok dari ~2000-an ke ~500-an setelah migration `2026_07_27_000001_fix_marketing_points_count_to_sum_formula.php` (lihat log-update-2026-07-27.md #2) di-deploy. Investigasi mendalam lewat diagnosa langsung ke production (bukan cuma baca kode) menemukan bug KEDUA yang jauh lebih serius, sudah ada SEBELUM perubahan kemarin, dan aktif berjalan di production saat ini.

### Root Cause

`MarketingPointReportController::runBulkSync()` dan `PicPointReportController::runBulkSync()` — dipanggil OTOMATIS lewat `TaskPointSettingController::syncTotals()` setiap kali admin menyimpan **pengaturan poin tugas APAPUN** di `/admin/task-point-settings` (tidak harus terkait marketing/PIC secara spesifik) — punya query yang **menimpa ulang `points_earned` pada SEMUA baris riwayat yang SUDAH ADA** supaya sama dengan rate yang berlaku SAAT ITU. Ini menghancurkan nilai poin historis asli setiap kali rate berubah.

Diagnosa production mengonfirmasi:
- Rate poin submit marketing saat ini: **0,25** (sangat kecil, jauh dari desain awal "1 submission = 1 poin").
- **9.014 baris** `marketing_point_histories` di seluruh sistem terbukti pernah ditimpa ulang (`updated_at > created_at`).
- **29.418 baris** `pic_point_histories` di seluruh sistem terbukti pernah ditimpa ulang — bahkan lebih parah.
- Kasus Risqi: 2.245 submission, 2.245 baris riwayat (tidak ada yang hilang), tapi SEMUA baris seragam 0,25 poin (2.245 × 0,25 = 561,25 — persis sama dengan yang tampil di `/marketing/points/rankings`).
- Migration kemarin (#2 di log 27 Juli) memang **memicu munculnya kerusakan yang sudah lama tersembunyi**, bukan penyebab kerusakannya — tapi tetap tanggung jawab saya karena tidak memverifikasi kondisi riwayat production sebelum memutuskan SUM adalah sumber kebenaran.

**Lebih serius lagi:** `PicPointController::index()` (halaman `/pic/points`) dan `Marketing\DashboardController::points()` (halaman `/marketing/points`) **otomatis menghitung ulang & menyimpan `total_points` dari SUM riwayat SETIAP KALI PIC/marketing membuka halaman poin mereka sendiri**. Karena riwayatnya sudah rusak, ini berarti bug ini AKTIF BERJALAN kapan saja seseorang mengecek poinnya sendiri — sama sekali independen dari migration kemarin.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | `runBulkSync()`: hapus query "Update existing records if point value changed" — sekarang HANYA mengisi baris `marketing_point_histories` yang belum ada, tidak pernah lagi menimpa baris yang sudah ada |
| `app/Http/Controllers/Admin/PicPointReportController.php` | `runBulkSync()`: hapus 2 query serupa (untuk step `submit` dan untuk tiap step alur kerja di dalam loop) — sama, hanya backfill baris yang belum ada |
| `database/migrations/2026_07_28_000001_restore_points_corrupted_by_rewrite_bug.php` (baru) | Migration pemulihan data: kembalikan `points_earned` ke nilai standar (1 untuk hampir semua step, 0 untuk `validator`) HANYA untuk baris yang **terbukti pernah ditimpa** (`submission_id` terisi DAN `updated_at > created_at`). Baris yang tidak pernah tersentuh (nilai asli apa pun) TIDAK diubah. Penyesuaian manual admin (`submission_id` NULL) TIDAK pernah disentuh. Hitung ulang `total_points` semua marketing & PIC setelahnya. Mencatat ringkasan lengkap (jumlah baris & ID yang terdampak) ke `storage/logs/laravel.log` |

### Verifikasi (sangat ketat, mengingat insiden sebelumnya)

**Perbaikan kode** — direproduksi persis skenario bug lama untuk Marketing & PIC:
1. Buat baris riwayat asli (points=10/1), ubah rate saat ini jadi angka yang jelas beda (999/777), panggil `runBulkSync()` yang sudah diperbaiki → baris asli **TETAP** tidak berubah (sebelumnya akan ikut tertimpa).
2. Hapus 1 baris (simulasi "belum ada"), panggil lagi → baris itu ter-backfill dengan benar pakai rate saat ini (backfill baris yang BENAR-BENAR belum ada tetap berfungsi, cuma baris yang SUDAH ADA yang sekarang aman).

**Migration pemulihan** — diuji dengan 4 skenario nyata per sistem (Marketing & PIC), dijalankan lewat `php artisan migrate` sungguhan (bukan simulasi):
1. Baris **terbukti rusak** (submission_id terisi, updated_at > created_at) → dikembalikan ke 1 (atau 0 untuk step `validator`).
2. Baris **tidak pernah tersentuh** (updated_at == created_at) → nilai asli (15/20) **tidak berubah sama sekali**.
3. Baris **penyesuaian manual** (submission_id NULL, meski updated_at > created_at) → nilai (999/888) **tidak tersentuh**.
4. Baris `validator` yang rusak → dikembalikan ke 0 poin (bukan 1), sesuai `PicPointHistory::POINT_CONFIG`.

Semua 4 skenario di kedua sistem lolos persis sesuai ekspektasi. Semua data uji dihapus, `total_points` marketing & PIC terkait dikembalikan ke nilai semula, dan pengecekan akhir `SyncController::gatherStats()` mengonfirmasi 0 dari 6 marketing & 0 dari 46 PIC out-of-sync (kondisi sama seperti sebelum pengujian dimulai).

### Catatan Deploy — PENTING

Migration ini **WAJIB** dijalankan di production: `git pull origin master` lalu `php artisan migrate --force`. Setelah itu, **cek `storage/logs/laravel.log`** untuk melihat berapa baris & marketing/PIC mana saja yang terkoreksi (log key: `"Restore poin PIC/Marketing yang rusak akibat bug penimpaan ulang riwayat"`). Poin Risqi diperkirakan akan kembali ke sekitar 2.245 (mendekati "2000-an" yang diingat) setelah migration ini jalan.

**Yang TIDAK bisa dipulihkan dengan sempurna:** rate poin historis yang SEBENARNYA berlaku di setiap submission pada waktunya sudah hilang permanen (tertimpa oleh bug). Migration ini mengembalikan ke nilai STANDAR desain sistem (1 poin/tugas), yang merupakan pendekatan paling wajar & sesuai ekspektasi tim, tapi bukan rekonstruksi persis 1:1 dari sejarah asli.

## 2. 🔄 Update: Fix bulk sync logic to prevent overwriting existing points history and add migration to restore corrupted data

- **Commit:** `ba88937` — 08:09 oleh Gudangsoft
- **File berubah:** 5 file
- `app/Http/Controllers/Admin/MarketingPointReportController.php`
- `app/Http/Controllers/Admin/PicPointReportController.php`
- `database/migrations/2026_07_28_000001_restore_points_corrupted_by_rewrite_bug.php`
- `log-update-2026-07-27.md`
- `log-update-2026-07-28.md`


## 3. 🔄 Update: Lengkapi changelog insiden poin PIC/Marketing 28 Juli

- **Commit:** `c3f097f` — 08:14 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-07-28.md`

## 4. Lanjutan Pemulihan Poin: Baris yang Salah Sejak Backfill + Rate Baru Marketing 0,5/Submission

**Tujuan:** Setelah migration #1 dijalankan di production, poin marketing "Risqi" cuma naik ke 1.547,5 (bukan ~2.245 seperti diperkirakan). Investigasi lanjutan menemukan 930 dari 2.245 baris riwayatnya masih di nilai rusak (0,25) — migration #1 cuma menangkap baris yang **terbukti ditimpa ulang** (`updated_at > created_at`), tapi ada baris lain yang di-**backfill** (diisi otomatis untuk submission lama yang belum punya riwayat) **pada saat rate sudah rusak** — baris ini salah sejak lahir, `created_at` == `updated_at`, lolos dari filter migration #1.

Sekaligus, user memutuskan kebijakan poin submit Marketing ke depan adalah **0,5 poin/submission** (bukan 1 seperti asumsi awal, dan bukan 0,25 yang rusak).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_28_000002_restore_remaining_deflated_points.php` (baru) | Migration lanjutan: perbaiki SEMUA baris riwayat yang terhubung ke submission asli (`submission_id` terisi) yang nilainya bukan standar — **tanpa syarat `updated_at` lagi** (beda dari migration #1). Marketing disamakan ke **0,5** (bukan 1), PIC tetap ke standar lama (1 poin/tugas, 0 untuk `validator`). Juga meng-update `TaskPointSetting` marketing `submit` jadi 0,5 supaya poin BARU ke depan konsisten dengan riwayat yang dipulihkan. Penyesuaian manual admin (`submission_id` NULL) tetap tidak pernah disentuh |

**Catatan penting soal cakupan:** migration ini LEBIH LUAS dari migration #1 — menyamakan SEMUA baris submission ke nilai standar apa pun sebabnya (bukan cuma yang terbukti ditimpa). User sudah dikonfirmasi & menyetujui pendekatan ini sebelum dijalankan, karena sesuai desain "1 submission = 1 poin" (sekarang 0,5) yang berlaku di seluruh sistem — tidak ada mekanisme lain yang sah untuk memberi rate berbeda ke submission tertentu (satu-satunya jalur legal untuk nilai custom adalah penyesuaian manual admin, yang selalu punya `submission_id` NULL dan sudah dilindungi terpisah).

**Diverifikasi lewat migration sungguhan** (dijalankan 2x — pertama dengan target 1, lalu di-rollback & dijalankan ulang dengan target 0,5 setelah keputusan rate final): 7 skenario (baris rusak, baris sudah benar, penyesuaian manual, step `validator` rusak) untuk Marketing & PIC — semua lolos sesuai ekspektasi. `TaskPointSetting` marketing `submit` terkonfirmasi ter-update ke 0,5. Semua data uji dihapus & data referensi lokal (bukan bagian test, riwayat historis lama dengan rate 10 dari awal tahun) dikembalikan ke nilai semula setelah pengujian — pengecekan akhir `SyncController::gatherStats()` menunjukkan 0 dari 6 marketing & 0 dari 46 PIC out-of-sync.

**Catatan deploy:** migration ini **WAJIB** dijalankan di production — `git pull origin master` lalu `php artisan migrate --force`. Setelah itu cek `storage/logs/laravel.log` (log key: `"Restore lanjutan: baris poin yang salah sejak backfill"`) dan pastikan poin Risqi & marketing lain sudah sesuai rate 0,5/submission yang baru.


## 5. 🔄 Update: Lanjutan pemulihan poin: baris salah sejak backfill + rate marketing 0,5

- **Commit:** `f11a32d` — 08:36 oleh Gudangsoft
- **File berubah:** 2 file
- `database/migrations/2026_07_28_000002_restore_remaining_deflated_points.php`
- `log-update-2026-07-28.md`

## 5. Bug Ketiga: Badge Poin di Navbar Marketing Pakai COUNT Submission, Bukan `total_points`

**Tujuan:** User menunjukkan halaman `/marketing/points/rankings` (login sebagai "Rafael") di mana badge poin di navbar menampilkan **2.313**, sementara kartu "Total Point" di halaman yang SAMA menampilkan **1.158** — dua angka berbeda untuk orang yang sama di halaman yang sama. Ditemukan: badge navbar (`resources/views/marketing/layouts/app.blade.php`) punya kode TERPISAH yang menghitung poin dari **COUNT submission mentah** (`auth()->guard('marketing')->user()->submissions()->count()`), di-cache 120 detik — sama sekali tidak terkait dengan `total_points` yang sudah diperbaiki di section #2/#4. Ini bug KETIGA dari kelas yang sama (COUNT submission dianggap = poin), ditemukan terpisah karena letaknya di layout, bukan controller yang sudah diaudit.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/marketing/layouts/app.blade.php` | Badge poin navbar: baca langsung `total_points` (kolom yang sudah benar & konsisten), bukan hitung ulang dari `submissions()->count()`. Cache 120 detik dihapus (tidak perlu lagi, `total_points` sudah kolom biasa yang murah dibaca) |

**Diverifikasi lewat tinker:** render dashboard marketing (Rafael, `total_points=191` di database) lewat controller asli — badge navbar sekarang menampilkan "191 Point", persis sama dengan `total_points`, bukan hasil COUNT submission yang terpisah.

**Catatan:** murni perubahan tampilan, tidak ada perubahan logika poin/migration. Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`. Setelah deploy, badge navbar & kartu "Total Point" di halaman rankings akan selalu menampilkan angka yang sama.

## 6. Dokumentasi Sistem Poin PIC & Marketing

**Tujuan:** User minta dokumentasi lengkap bagaimana sistem poin PIC & Marketing bekerja dan apa saja yang dipengaruhinya, untuk referensi ke depan (terutama setelah rangkaian insiden 27–28 Juli di atas). Dibuat lewat investigasi menyeluruh kode (model, controller, migration) plus rangkuman naratif insiden dari log update sebelumnya.

Isi dokumen mencakup: skema tabel poin, rate per step (tabel `task_point_settings`), alur pemberian poin lewat `awardPoints()`, apa saja yang membaca poin (leaderboard/dashboard, bukan reward/redemption), ringkasan kronologis insiden 27–28 Juli, dan **dua bug residual yang belum diperbaiki** yang ditemukan selama investigasi ini (belum disentuh oleh perbaikan sebelumnya):
- `LaporanKinerjaController` (laporan kinerja PIC) masih menghitung `count × rate saat ini` alih-alih `SUM(points_earned)` — angka historis bisa berubah diam-diam saat rate diubah.
- `SubmissionController::destroy()` masih menimpa `total_points` marketing dengan `COUNT(submissions)` mentah saat sebuah submission dihapus — bug sejenis insiden 27/28 Juli, masih aktif.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `docs/issues/sistem-poin-pic-marketing.md` (baru) | Dokumentasi lengkap sistem poin PIC/Marketing: skema data, rate, alur pemberian poin, apa yang dipengaruhi, riwayat insiden 27–28 Juli, dan 2 bug residual yang belum diperbaiki |

**Catatan:** dokumentasi murni, tidak ada perubahan kode/logika/migration. Dua bug residual yang disebut di atas belum diperbaiki — perlu keputusan/prioritas terpisah dari user sebelum dikerjakan.

## 7. Perbaikan 2 Bug Residual + Bug Baru: Tanggal Penyelesaian Tugas Berubah Saat Sinkronisasi

**Tujuan:** Menindaklanjuti section #6 — user meminta 2 bug residual (`LaporanKinerjaController`, `SubmissionController::destroy()`) diperbaiki, sekaligus melaporkan bug baru: setiap admin melakukan sinkronisasi poin, tanggal penyelesaian tugas (kolom `created_at` di riwayat poin) milik PIC/Marketing ikut berubah ke tanggal sinkronisasi dijalankan, padahal seharusnya tetap sesuai tanggal tugas benar-benar selesai.

Investigasi menemukan akar bug tanggal: 3 jalur sync/backfill menulis riwayat poin baru dengan `created_at = NOW()` (waktu sync) alih-alih tanggal asli tugas selesai:
- `PicPointReportController::runBulkSync()` — backfill step `submit` hardcode `NOW(), NOW()` di raw SQL (step workflow lain di fungsi yang sama sudah benar pakai tanggal `*_validated_at`).
- `PicPointController::syncMyPoints()` (tombol "Sinkronkan Poin Saya" milik PIC) — pakai `PicPointHistory::awardPoints()` tanpa memberi tanggal asli, sehingga Eloquent default ke `now()`.
- `MarketingPointReportController::syncAllPoints()` (tombol sync di `/admin/marketing-points`) — pakai `MarketingPointHistory::create([...])` langsung tanpa `created_at` eksplisit.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/PicPointHistory.php` | `awardPoints()` menerima parameter opsional `$occurredAt` — kalau diisi, `created_at`/`updated_at` riwayat di-set ke tanggal itu, bukan waktu fungsi dipanggil |
| `app/Models/MarketingPointHistory.php` | `awardPoints()` menerima parameter opsional `$occurredAt` yang sama |
| `app/Http/Controllers/Admin/PicPointReportController.php` | `runBulkSync()`: backfill step `submit` pakai `COALESCE(s.created_at, NOW())`, bukan hardcode `NOW()` |
| `app/Http/Controllers/Pic/PicPointController.php` | `syncMyPoints()`: kirim `submission->created_at` (step submit) / `submission->{step}_validated_at` (step workflow) sebagai `$occurredAt` ke `awardPoints()` |
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | `syncAllPoints()`: ganti `MarketingPointHistory::create()` manual jadi `awardPoints(..., $submission->created_at)` |
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | `index()` & `buildData()`: `total_poin` PIC per periode sekarang dari `SUM(points_earned)` riwayat asli (date-filtered), bukan `count × rate saat ini` |
| `app/Http/Controllers/Admin/SubmissionController.php` | `destroy()`: recalculate `total_points` marketing dari `SUM(points_earned)`, bukan `COUNT(submissions)` |
| `docs/issues/sistem-poin-pic-marketing.md` | Section 7 diupdate: 3 bug (2 residual + 1 bug tanggal) ditandai FIXED dengan penjelasan perbaikannya |

**Catatan penting:** perbaikan ini mencegah kerusakan tanggal ke depan saja. Baris riwayat yang tanggalnya sudah kadung berubah jadi tanggal sync di masa lalu (sebelum fix ini) **tidak** ikut diperbaiki — kalau perlu dipulihkan, butuh migration restorasi terpisah (perlu identifikasi baris mana yang tanggalnya terbukti salah, mirip pendekatan migration insiden 27/28 Juli di atas). Jalur pemberian poin **live** (saat validasi benar-benar terjadi) tidak diubah — tetap memakai `now()` seperti sebelumnya karena itu memang saat tugas selesai.

**Verifikasi:** `php -l` pada seluruh file yang diubah — tidak ada syntax error.

## 8. Rapikan File Test/Log/Script di Root Project → `docs/tests/`

**Tujuan:** User minta root project dirapikan — banyak file test/one-off script, changelog lama, file `.sql`, dan script shell/`.ps1` yang menumpuk di root sejak lama. Semua dipindah ke `docs/tests/` (termasuk file yang sedang dibuka user, `update-auto-valid-production.php`).

Sebelum memindah, dicek dulu (grep di seluruh codebase) apakah ada file-file ini direferensikan di tempat lain — tidak ada referensi ke script/`.sql`/shell manapun, aman dipindah. Untuk `log-update*.md` dan `CHANGELOG*.md`, ditemukan SATU referensi: `app/Services/FeatureSettingService.php::changelogs()` yang men-scan pola ini dari `base_path()` (root) untuk ditampilkan di `/admin/feature-management` tab Changelog — path ini diupdate mengikuti lokasi baru supaya fitur itu tidak rusak.

### File yang Dipindahkan (93 file, `git mv` — history tetap terjaga)
- 46 file `log-update-*.md`
- 9 file `CHANGELOG_*.md`
- 9 file `.sql` (script fix/insert data satu kali)
- 7 file `.sh`/`.ps1` (deploy, backup, rollback, fix server/permission)
- 21 file `.php`/`.py` ad hoc (`check-*`, `check_*`, `fix-*`, `patch_*`, `refactor.*`, `sync-*`, `test-*`, `update-*`, `verify-*`, `delete-*`)

**Tidak dipindah:** dokumentasi panduan yang masih aktif dipakai (README.md, DEPLOYMENT.md, INSTALL.md, USER_GUIDE.md, PIC_LOGIN_GUIDE.md, SECURITY_AUDIT.md, dll.) — ini bukan file test/log, jadi dibiarkan di root sesuai lingkup permintaan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Services/FeatureSettingService.php` | `changelogs()`: glob path diubah dari `base_path('log-update*.md')`/`base_path('CHANGELOG*.md')` jadi `base_path('docs/tests/log-update*.md')`/`base_path('docs/tests/CHANGELOG*.md')` |
| `CLAUDE.md` | Aturan wajib log update: lokasi file log diubah dari root project jadi `docs/tests/`; ditambahkan catatan lokasi baru untuk file test/one-off script |

**Verifikasi:** dijalankan `FeatureSettingService::changelogs()` lewat tinker setelah perpindahan — berhasil membaca 55 file (46 log-update + 9 CHANGELOG) dari lokasi baru, fitur Changelog di `/admin/feature-management` tidak rusak.

**Catatan:** mulai sekarang, entry log-update harian (termasuk entry ini sendiri) ditulis ke `docs/tests/log-update-YYYY-MM-DD.md`, bukan lagi ke root project.

## 9. Blokir Akses Publik ke Folder `docs/` (termasuk `docs/tests`)

**Tujuan:** Setelah `log-update*.md`, `CHANGELOG*.md`, dan berbagai script test/one-off dipindah ke `docs/` pada section #8, user minta dipastikan folder ini tidak bisa diakses lewat browser publik.

Investigasi: root project punya `.htaccess` (bukan cuma `public/.htaccess`) yang me-redirect semua request ke `public/$1` KECUALI kalau path yang diminta adalah file/folder yang benar-benar ada secara fisik di root (`RewriteCond %{REQUEST_FILENAME} !-d`/`!-f`). Artinya kalau server di-setup dengan DocumentRoot mengarah ke root project (bukan `public/` seperti yang didokumentasikan di `docs/DEPLOYMENT.md`) — pola umum di hosting bersama (shared hosting) yang tidak bisa ubah DocumentRoot — maka file apapun yang benar-benar ada di `docs/` (termasuk `docs/tests`, isinya sekarang berupa log, changelog, script SQL/shell, dan kredensial default PIC) akan disajikan langsung sebagai file statis, TIDAK ikut ke-redirect ke `public/`.

Kalau DocumentRoot server sudah benar mengarah ke `public/` (sesuai vhost contoh di `docs/DEPLOYMENT.md`), folder ini otomatis sudah tidak bisa diakses sama sekali. Tapi karena tidak bisa dipastikan environment produksi yang sebenarnya dipakai yang mana, ditambahkan lapisan pertahanan langsung di `docs/.htaccess`.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `docs/.htaccess` (baru) | `Require all denied` (Apache 2.4) + `Deny from all` (Apache 2.2 fallback) — blokir semua akses HTTP ke seluruh isi `docs/`, termasuk `docs/tests` dan subfolder lain |

**Verifikasi (bukan cuma baca kode — dites fungsional):** dijalankan instance Apache lokal sungguhan dengan DocumentRoot disetel ke root project (mensimulasikan skenario shared-hosting yang paling berisiko) dan menyalin isi project + `.htaccess` ke luar folder yang dibatasi macOS. Hasil:
- `GET /docs/tests/log-update-2026-07-28.md` → **403 Forbidden** ✅
- `GET /docs/issues/sistem-poin-pic-marketing.md` → **403 Forbidden** ✅ (ikut terlindungi sebagai bonus)
- `GET /README.md` (kontrol negatif, file di root yang memang boleh diakses) → **200 OK** ✅ (tidak ikut ke-block, tidak ada regresi)

**Catatan:** ini melindungi skenario Apache. Kalau server produksi ternyata pakai Nginx dengan `root` yang salah diarahkan ke root project (bukan `public/`), `.htaccess` tidak berlaku sama sekali di Nginx — perlu aturan `location` terpisah di config Nginx (`location ^~ /docs/ { deny all; return 404; }`) yang tidak bisa ditambahkan dari sini karena bukan bagian dari repo. Rekomendasi utama tetap: pastikan DocumentRoot/`root` server produksi mengarah ke `public/`, bukan root project — ini menutup celah untuk SEMUA folder di luar `public/`, bukan cuma `docs/`.

## 10. Fix Error "Data truncated for column 'status'" Saat PIC Submit Pekerjaan

**Tujuan:** User melapor error di `http://127.0.0.1:8000/pic/submissions/13932/submit-work`: `SQLSTATE[01000]: Warning: 1265 Data truncated for column 'status' at row 1` saat mencoba `UPDATE submissions SET status = PRODUCTION_SUBMITTED`.

**Root cause:** Kolom `submissions.status` HARUSNYA sudah `VARCHAR(50)` sejak migration `2026_01_21_000001_add_submitted_status_to_submissions.php` (dibuat khusus supaya status `*_SUBMITTED` bisa dipakai bebas — komentar migration itu sendiri bilang "karena ENUM di MySQL sulit untuk di-alter"). Tapi kolom LIVE-nya ternyata masih ENUM tanpa satu pun nilai `*_SUBMITTED` (`SUBMITTED, EDITOR1_PROCESS, ..., PRODUCTION_PROCESS, VALIDATOR_PROCESS, PUBLISHED, REJECTED`) — tidak ditemukan migration manapun yang mengubahnya balik jadi ENUM (dicek lewat `grep` semua migration), jadi kemungkinan besar ada `ALTER TABLE` manual di luar sistem migration di masa lalu. Akibatnya: PIC yang submit pekerjaan di TAHAP MANAPUN (bukan cuma production) gagal, karena kode (`JournalManagementController::submitWork()`) selalu mengubah status jadi `{STEP}_SUBMITTED` yang tidak ada satupun di ENUM tersebut.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_28_000003_restore_submissions_status_to_varchar.php` (baru) | `ALTER TABLE submissions MODIFY COLUMN status VARCHAR(50)` — mengembalikan ke tipe yang seharusnya sudah berlaku sejak Januari. Aman & idempoten (cuma ubah tipe kolom, isi data tidak berubah) |

**Diverifikasi:**
1. Snapshot checksum seluruh nilai `status` dari 14.185 submission SEBELUM migration, bandingkan SETELAH migration → checksum identik (tidak ada data yang berubah/hilang, cuma tipe kolom).
2. Reproduksi persis skenario error: panggil `submitWork()` sungguhan untuk submission 13932 (status `PRODUCTION_PROCESS`, PIC produksi asli) → SEBELUM fix akan gagal dengan error yang sama; SESUDAH fix berhasil, status berubah jadi `PRODUCTION_SUBMITTED`, redirect 302. Data uji (status submission, baris `submission_histories`) dikembalikan ke semula setelah verifikasi.

**Catatan:** tidak ada perubahan kode PHP, cuma migration schema. Deploy: `git pull origin master` lalu `php artisan migrate --force`.

## 11. Tambah Filter Cepat (Hari Ini/Minggu Ini/Bulan Ini/Tahun Ini) di Halaman Point Saya PIC & Marketing

**Tujuan:** User minta ditambahkan filter cepat berdasarkan periode di setiap halaman "Point Saya" — baik untuk PIC (`/pic/points`) maupun Marketing (`/marketing/points`).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/PicPointController.php` | `index()`: tambah parameter `period` (`today`/`week`/`month`/`year`) yang memfilter query riwayat poin. Kalau `period` diisi, dipakai (mengabaikan filter tanggal manual yang sudah ada); kalau tidak, tetap jatuh ke filter `tanggal_dari`/`tanggal_sampai` manual seperti sebelumnya. Filter `step` tetap bisa dikombinasikan dengan `period` |
| `app/Http/Controllers/Marketing/DashboardController.php` | `points()`: tambah filter `period` yang sama — sebelumnya halaman ini TIDAK punya filter apa pun pada daftar riwayat (cuma menampilkan semua riwayat tanpa filter) |
| `resources/views/pic/points/index.blade.php` | Tambah grup tombol filter cepat (Semua/Hari Ini/Minggu Ini/Bulan Ini/Tahun Ini) di atas form filter tanggal manual yang sudah ada. Tombol aktif ter-highlight (`btn-primary`), lainnya `btn-outline-primary`. Link mempertahankan filter `step` yang sedang aktif |
| `resources/views/marketing/points.blade.php` | Tambah grup tombol filter cepat yang sama di atas tabel riwayat poin |

**Diverifikasi lewat tinker (data real, bukan simulasi):** untuk marketing "Rafael" (2.322 baris riwayat) — filter `period=today` mengembalikan 6 baris, `period=week` 30 baris, tanpa filter 2.322 baris, semua cocok persis dengan hitungan manual langsung ke database. Untuk PIC (5.522 baris riwayat) — `period=year` mengembalikan 5.522, cocok dengan hitungan manual (seluruh riwayatnya memang dari tahun berjalan). Render halaman dicek untuk kedua sistem — tombol filter yang aktif ter-highlight dengan benar, kombinasi `period` + `step` (PIC) bekerja bersamaan tanpa saling mengganggu, dan pagination (`per-page-selector`) sudah otomatis mempertahankan filter lewat `withQueryString()` yang sudah ada sebelumnya.

**Catatan:** murni penambahan fitur filter, tidak ada perubahan logika poin/migration. Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`.

## 12. Tombol Sinkron Data Point di `/admin/laporan-kinerja`

**Tujuan:** User minta ditambahkan cara untuk menyinkronkan data poin sesuai ketentuan terbaru di halaman `/admin/laporan-kinerja`. Halaman ini sendiri sudah menghitung poin langsung dari SUM riwayat (bukan cache), jadi tidak butuh "sync" untuk tampilannya — yang dibutuhkan adalah memastikan riwayat `pic_point_histories`/`marketing_point_histories` sendiri lengkap (ada baris untuk semua tugas yang memenuhi syarat).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah route `POST /admin/laporan-kinerja/sync` → `laporan-kinerja.sync` |
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | Method baru `syncPoints()`: panggil `PicPointReportController::runBulkSync()` & `MarketingPointReportController::runBulkSync()` (keduanya sudah diperbaiki hari ini — cuma mengisi baris yang belum ada, tidak pernah menimpa yang sudah ada), lalu samakan kolom cache `total_points` PIC & Marketing dengan SUM riwayat terbaru. Redirect kembali ke halaman laporan (mempertahankan filter bulan/tahun/rentang tanggal yang sedang aktif) dengan pesan jumlah riwayat baru yang ditambahkan |
| `resources/views/admin/laporan-kinerja/index.blade.php` | Tambah tombol "Sinkron Data Point" (dengan konfirmasi) di sebelah tombol export Excel/PDF, dan tampilan pesan sukses (`session('success')`) yang sebelumnya tidak ada di halaman ini |

**Diverifikasi lewat data real (bukan simulasi):** jalankan `syncPoints()` sungguhan — menemukan & mengisi 7 baris riwayat PIC yang memang belum tercatat (step `production`, tanggal dibackfill sesuai `production_validated_at` asli, bukan waktu sync dijalankan), 0 baris marketing baru (sudah lengkap). `total_points` ke-6 PIC yang terdampak dicek ulang — semua cocok persis dengan SUM riwayat. Jalankan sync KEDUA KALINYA → melaporkan 0/0 (idempoten, aman diklik berkali-kali). Baris yang di-backfill BUKAN data uji — representasi tugas produksi yang benar-benar sudah selesai tapi belum tercatat poinnya, jadi tidak dihapus/dikembalikan.

**Catatan:** tidak ada migration baru. Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`.

## 13. Tampilkan Poin PIC & Marketing dengan 2 Desimal di Semua Halaman

**Tujuan:** User minta penulisan poin PIC & Marketing di dashboard/halaman "Point Saya" pakai format desimal, supaya konsisten dengan kebijakan poin yang sekarang bisa pecahan (mis. rate submit marketing 0,5/submission). Sebelumnya banyak tempat memakai `number_format($x)` tanpa parameter desimal, yang MEMBULATKAN ke bilangan bulat — nilai asli seperti 1.122,5 tampil sebagai "1,123" (salah, membulatkan), padahal seharusnya "1,122.50".

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/author/dashboard.blade.php` | Ranking PIC & Marketing di dashboard PIC: `number_format(..., 2)` |
| `resources/views/pic/partials/sidebar.blade.php` | Badge "Point Saya" di sidebar PIC |
| `resources/views/pic/points/index.blade.php` | Total Point, Point Hari Ini, Point Bulan Ini, breakdown poin per tahap, dan nilai poin per baris riwayat (Total Tugas TIDAK diubah — itu jumlah tugas, bukan poin) |
| `resources/views/pic/points/rankings.blade.php` | Semua kartu & baris tabel poin PIC/Marketing (jumlah PIC/Marketing aktif TIDAK diubah — itu hitungan orang, bukan poin) |
| `resources/views/marketing/dashboard.blade.php` | Total Point di header, ringkasan poin, ranking PIC/Marketing, dan nilai poin per baris riwayat terbaru |
| `resources/views/marketing/layouts/app.blade.php` | Badge poin di navbar (yang baru diperbaiki formulanya sebelumnya, sekarang juga pakai 2 desimal) |
| `resources/views/marketing/point-rankings.blade.php` | Sama seperti rankings PIC |
| `resources/views/marketing/points.blade.php` | Total Point, Point Hari Ini, Point Bulan Ini, dan nilai poin per baris riwayat |

**Diverifikasi lewat render sungguhan (bukan cuma baca kode):** semua 8 file di-render lewat controller asli dengan data real — PIC (total 5.518 poin) tampil "5,518.00" di halaman Point Saya, sidebar, DAN rankings; Marketing (total 1.161 poin) tampil "1,161.00" di halaman Point Saya, navbar, DAN rankings — konsisten di semua tempat. Nilai non-poin (Total Tugas, jumlah PIC/Marketing aktif) dipastikan TIDAK ikut diberi desimal karena itu hitungan bilangan bulat, bukan poin.

**Catatan:** murni perubahan tampilan (format angka), tidak ada perubahan data/migration. Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`.

## 14. Filter Bulan di `/admin/laporan-kinerja` Pakai Periode Cutoff 26–25

**Tujuan:** User minta filter "Bulan" di halaman ini bisa melintasi 2 bulan kalender — contoh diberikan "26 Juli - 25 [Agustus]" — semacam periode cutoff penilaian kinerja (payroll-style), bukan kalender 1–31 biasa. Dikonfirmasi: dropdown Bulan+Tahun yang SUDAH ADA diubah artinya (bukan menambah opsi baru) — pilih "Juli 2026" sekarang berarti periode 26 Juni 2026 s/d 25 Juli 2026.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | Tambah method privat `resolvePeriod()` (dipakai bersama oleh `index()` & `buildData()`, sebelumnya logika periode ter-duplikasi di 2 tempat) — kalau filter tanggal manual (`dari_tanggal`/`sampai_tanggal`) diisi, dipakai apa adanya; kalau tidak (pakai dropdown Bulan/Tahun), periode dihitung sebagai **26 (bulan-1) s/d 25 (bulan)**, termasuk penanganan pergantian tahun (Januari → 26 Desember tahun sebelumnya). Semua query (`whereMonth`/`whereYear` yang lama) diganti seragam jadi `whereDate(...>=...)`/`whereDate(...<=...)` berdasar periode ini. Label periode (`$namaBulan`) sekarang SELALU berupa rentang tanggal eksplisit (mis. "26 Juni 2026 — 25 Juli 2026"), bukan cuma nama bulan, supaya jelas kalau periodenya melintasi 2 bulan kalender |
| `resources/views/admin/laporan-kinerja/index.blade.php` | Tambah ikon info dengan tooltip di label "Bulan" menjelaskan konvensi cutoff 26–25 |

**Diverifikasi lewat tinker (data real, bukan simulasi):**
1. `resolvePeriod()` untuk "Juli 2026" → `2026-06-26` s/d `2026-07-25`, label "26 Juni 2026 — 25 Juli 2026". Untuk "Januari 2026" (uji pergantian tahun) → `2025-12-26` s/d `2026-01-25`, label "26 Desember 2025 — 25 Januari 2026". Untuk rentang tanggal manual → tetap dipakai apa adanya, tidak terpengaruh.
2. `index()` dipanggil sungguhan untuk "Juli 2026" → `totalPicPoin` yang dihasilkan (27.722,65) dicocokkan dengan query manual langsung ke `pic_point_histories` untuk rentang `2026-06-26` s/d `2026-07-25` → **identik persis**.
3. Render halaman penuh & kedua export (`exportExcel`, `exportPdf`) — semua berhasil tanpa error, label periode baru tampil dengan benar di halaman.

**Catatan:** murni perubahan logika filter tampilan, tidak ada perubahan data/migration. Laporan bulan-bulan SEBELUMNYA otomatis ikut memakai definisi periode baru ini kalau diakses ulang (karena datanya dihitung on-the-fly dari riwayat, bukan cache) — ini sesuai maksud user (mendefinisikan ulang arti "1 bulan" untuk laporan kinerja). Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`.

## 15. Koreksi Retroaktif Riwayat Poin PIC ke Rate Per Tahap yang Berlaku Sekarang (Perubahan Besar)

**Tujuan:** User minta cek kesesuaian laporan kinerja dengan `task_point_settings`. Ditemukan: rate poin PIC per tahap SUDAH dikonfigurasi berbeda-beda sejak Mei–Juli 2026 (editor1/author1=0,1, editor2/reviewer1/reviewer2=0,2, submit=0,25, validator=0,33, editor3/author2=0, production=1) — tapi 99%+ riwayat `pic_point_histories` (dari 10 Juni sampai 27 Juli 2026, di HAMPIR SEMUA tahap kecuali production) tetap menunjukkan flat 1 poin/tugas (0 untuk validator).

**Root cause bukan migration hari ini** — dikonfirmasi lewat investigasi: sebaran tanggalnya merata dari 10 Juni s/d 27 Juli (bukan cuma satu waktu), jauh melebihi skala migration pagi ini. ini bug lama pada mekanisme pemberian poin real-time yang membuat rate yang benar tidak pernah terpakai selama lebih dari sebulan — baru mulai benar (0,25 utk submit dst.) sejak sekitar jam 08:49 pagi ini (28 Juli 2026), entah karena perbaikan `runBulkSync()` sebelumnya hari ini atau sebab lain yang belum terpastikan 100%. Kolom `validator` khususnya SEMPAT ikut salah oleh migration saya sebelumnya (section #1) yang salah asumsi validator=0 padahal rate sebenarnya 0,33.

User dikonfirmasi (dengan angka dampak eksplisit ditunjukkan dulu) untuk **mengoreksi riwayat lama secara retroaktif** ke rate saat ini — BUKAN dibiarkan apa adanya seperti prinsip yang dipakai untuk Marketing, karena ini murni bug (bukan perubahan kebijakan yang disengaja untuk periode tertentu).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_28_000004_correct_pic_history_to_current_step_rates.php` (baru) | Ambil rate PIC aktif DINAMIS dari `task_point_settings` (bukan hardcode), lalu untuk tiap tahap, koreksi SEMUA baris `pic_point_histories` yang terhubung ke submission asli (`submission_id` terisi) dan nilainya tidak cocok dengan rate saat ini. Penyesuaian manual admin (`submission_id` NULL, step `adjustment`) otomatis tidak tersentuh (tidak ada entry 'adjustment' di `task_point_settings`). Hitung ulang `total_points` semua PIC setelahnya. Catat ringkasan lengkap (jumlah baris per tahap) ke `storage/logs/laravel.log` |

### Dampak (dikonfirmasi ke user SEBELUM dijalankan)
| Tahap | Baris terkoreksi | Rate |
|---|---|---|
| Editor 1 | 14.214 | 0,1 |
| Author 1 | 14.064 | 0,1 |
| Editor 2 | 14.157 | 0,2 |
| Reviewer 1 | 13.958 | 0,2 |
| Reviewer 2 | 13.986 | 0,2 |
| Editor 3 | 289 | 0 |
| Submit | 13.337 | 0,25 |
| Validator | 1.610 | 0,33 |

**Total poin PIC seluruh sistem turun dari ~97.529 menjadi ~29.678** (data terus bertambah dari aktivitas normal selama investigasi, sehingga total akhir sedikit berbeda dari preview awal ~28.637 — perbedaan wajar, bukan indikasi masalah).

**Diverifikasi lewat migration sungguhan (bukan simulasi):** dijalankan `php artisan migrate` sungguhan di lokal. Baris penyesuaian manual (submission_id NULL) dipastikan TIDAK tersentuh (diuji dengan baris tiruan bernilai 999, tetap 999 setelah migration). Sum tiap tahap setelah migration dicocokkan dengan `jumlah_baris × rate` — semua cocok persis (selisih hanya pembulatan float, <0,01). Pengecekan akhir `SyncController::gatherStats()` — 0 dari 70 PIC, 0 dari 15 marketing, 0 dari 8.343 slot out-of-sync.

**Catatan deploy — PENTING:** migration ini **WAJIB** dijalankan di production — `git pull origin master` lalu `php artisan migrate --force`. Ini mengubah TOTAL POIN SEMUA PIC SECARA SIGNIFIKAN (turun ~70% rata-rata) — pastikan tim terkait sudah diberi tahu sebelum deploy, karena akan langsung terlihat di leaderboard/laporan begitu migration jalan. Cek `storage/logs/laravel.log` (log key: `"Koreksi retroaktif riwayat poin PIC ke rate per tahap yang berlaku sekarang"`) untuk detail lengkap jumlah baris per tahap yang terkoreksi di production.
