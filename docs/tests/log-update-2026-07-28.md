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

## 16. Sembunyikan Tombol "Sinkron Data Point" di `/admin/laporan-kinerja`

**Tujuan:** Setelah dikonfirmasi bahwa laporan sudah menghitung poin langsung dari riwayat (selalu up-to-date tanpa perlu sinkron manual), user minta tombol "Sinkron Data Point" (section #12) disembunyikan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/laporan-kinerja/index.blade.php` | Hapus tombol & form "Sinkron Data Point" dari halaman. Route (`admin.laporan-kinerja.sync`) dan method `LaporanKinerjaController::syncPoints()` TIDAK dihapus — tetap ada kalau suatu saat dibutuhkan lagi sebagai jaring pengaman manual |

**Diverifikasi:** render halaman lewat controller asli — teks "Sinkron Data Point" sudah 0 kemunculan.

**Catatan:** murni perubahan tampilan, tidak ada perubahan logika/route/migration. Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`.

## 17. Fix Data Poin PIC Kembar (Race Condition) + Tambah Unique Constraint

**Tujuan:** User menunjukkan screenshot riwayat poin PIC dengan baris identik berulang (3x "Validasi Production - SUB202607170013" jam 11:53, 2x "Validasi Production - SUB202607170014" jam 11:49, semua +1,00) dan minta dicek alasannya lalu diperbaiki.

**Root cause:** `pic_point_histories` tidak punya batasan UNIK di database untuk kombinasi (pic_id, submission_id, step). Satu-satunya perlindungan dari poin dobel adalah pengecekan "sudah ada atau belum" di `PicPointHistory::awardPoints()` — TIDAK atomik. Kalau 2 permintaan datang hampir bersamaan (klik ganda tombol validasi, atau retry jaringan), keduanya bisa lolos pengecekan di saat yang sama sebelum salah satunya sempat tersimpan, menghasilkan baris kembar. Dicek di database lokal: **1.610 kelompok data kembar, 1.786 baris berlebih** — bug struktural, bukan kasus terisolasi.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_28_000005_deduplicate_and_constrain_pic_point_histories.php` (baru) | Hapus baris kembar (simpan yang id-nya paling kecil/paling awal per kelompok, hapus sisanya) — baris `submission_id` NULL (penyesuaian manual admin) TIDAK terpengaruh. Tambah `UNIQUE INDEX (pic_id, submission_id, step)` supaya database sendiri menolak percobaan kembar ke depan (index unik tidak berlaku untuk NULL, jadi penyesuaian manual tetap bisa berkali-kali). Hitung ulang `total_points` PIC yang terdampak |
| `app/Models/PicPointHistory.php` | `awardPoints()`: bungkus `create()` dengan try/catch — kalau constraint baru menolak (race benar-benar terjadi), tangkap dan kembalikan `null` (sama seperti "sudah pernah diberi"), bukan crash 500 ke pengguna |
| `app/Http/Controllers/Admin/PicPointReportController.php` | `runBulkSync()`: 2 query bulk INSERT (submit & per-tahap alur kerja) diganti jadi `INSERT IGNORE` — defensif terhadap constraint baru, supaya 1 baris yang kebetulan bentrok tidak menggagalkan seluruh batch |

**Diverifikasi lewat migration & simulasi race sungguhan:**
1. Buat 3 baris kembar asli (PIC+submission+step sama) + 2 baris penyesuaian manual (submission_id NULL) untuk PIC yang sama → jalankan migration → kelompok kembar berkurang jadi 1 baris (yang id-nya paling kecil), 2 baris penyesuaian manual TIDAK tersentuh, unique index berhasil dibuat.
2. Coba INSERT langsung baris kembar baru setelah constraint ada → **ditolak database** dengan pesan sesuai nama constraint.
3. Test region normal `awardPoints()`: award baru berhasil, award kedua untuk kombinasi sama mengembalikan `null` (lewat pengecekan biasa, bukan exception) — tidak ada regresi.
4. `runBulkSync()` dijalankan ulang setelah perubahan `INSERT IGNORE` — tetap berjalan normal, backfill & idempoten seperti sebelumnya.
5. Pengecekan akhir: 0 kelompok kembar tersisa, 0 dari 70 PIC/15 marketing/8.343 slot out-of-sync.

**Catatan deploy:** migration ini **WAJIB** dijalankan di production — `git pull origin master` lalu `php artisan migrate --force`. Ini akan MENGHAPUS baris kembar (mengurangi total poin PIC yang punya duplikasi) — cek `storage/logs/laravel.log` (log key: `"Hapus data poin PIC kembar + tambah unique constraint"`) untuk detail jumlah baris yang dihapus per production.

## 18. Bangun Test Suite Otomatis untuk Sistem Poin (PIC & Marketing)

**Tujuan:** Setelah rentetan bug poin hari ini (rate salah, badge navbar tidak sinkron, data kembar), user minta langkah pencegahan jangka panjang. Prioritas pertama yang dipilih: test otomatis, supaya bug serupa (rate ketimpa saat sync, formula SUM vs COUNT ketuker, constraint kembar bolong) langsung ketahuan sebelum sampai production, bukan lewat laporan user.

**Kondisi awal:** PHPUnit 10 sudah ada di `composer.json` tapi scaffolding test 100% belum ada — tidak ada `phpunit.xml`, `tests/TestCase.php`, `.env.testing`, atau database test. Dibangun dari nol.

**Keputusan desain:** pakai database MySQL terpisah sungguhan (`dbrevana_testing`), BUKAN SQLite in-memory — karena kode banyak bergantung pada perilaku spesifik MySQL (`INSERT IGNORE`, unique index yang mengecualikan NULL, `whereRaw('ABS(...) > 0.0001')`, kolom ENUM) yang tidak direplikasi persis oleh SQLite. Pakai `RefreshDatabase` (transaksi per test, rollback otomatis) untuk isolasi cepat.

### File yang Diubah/Ditambah
| File | Perubahan |
|------|-----------|
| `phpunit.xml` (baru) | Konfigurasi standar Laravel 10, testsuite Unit/Feature, koneksi ke `dbrevana_testing` |
| `.env.testing` (baru) | Environment khusus testing — cache/session/mail pakai driver `array`, DB ke `dbrevana_testing` |
| `tests/TestCase.php`, `tests/CreatesApplication.php` (baru) | Base class standar Laravel yang sebelumnya tidak ada |
| `tests/Feature/Points/CreatesPointTestFixtures.php` (baru) | Trait helper bikin data minimal (User→JournalMaster→JournalSlot→Submission, Pic, Marketing) sesuai rantai foreign key asli |
| `tests/Feature/Points/PicPointHistoryAwardTest.php` (baru, 7 test) | `awardPoints()` pakai rate dari `TaskPointSetting` yang aktif; idempoten; DB menolak baris kembar (pic_id+submission_id+step) lewat unique constraint dari section #17; baris penyesuaian manual (submission_id NULL) tidak kena constraint; fallback ke `POINT_CONFIG` saat tidak ada setting aktif |
| `tests/Feature/Points/MarketingPointHistoryAwardTest.php` (baru, 4 test) | Regresi langsung insiden badge navbar (section #2): `getActualPoints()` harus SUM riwayat, bukan COUNT submission — dibuktikan dengan rate lama (10/submission) vs rate baru (0,5) yang kalau salah pakai COUNT akan menghasilkan angka yang jauh beda dari SUM sebenarnya |
| `tests/Feature/Points/RunBulkSyncTest.php` (baru, 6 test) | Jaminan inti `runBulkSync()` PIC & Marketing: **backfill-only, never rewrite** — baris yang sudah ada tidak boleh tertimpa walau rate berubah (regresi langsung insiden section #14, penurunan ~70% poin PIC); baris yang benar-benar hilang tetap terisi dengan rate saat ini; panggilan berulang tidak menghasilkan duplikat; total_points dihitung ulang sebagai SUM bukan COUNT |

**Hasil:** 18 test, 30 assertion, semua **PASS** (`./vendor/bin/phpunit tests/Feature/Points --testdox`).

**Catatan:** ini scaffolding awal, belum menjalankan test di CI (belum ada pipeline CI di project ini) — untuk sekarang dijalankan manual sebelum commit perubahan di area poin. Tidak ada perubahan ke kode aplikasi/production di section ini, murni infrastruktur test lokal + `.env.testing` (tidak ikut ke production, hanya dipakai `php artisan test`/`phpunit` lokal).

## 19. Tambah Total Tugas & Total Point di Hasil Filter "Riwayat Perolehan Point" (PIC & Marketing)

**Tujuan:** User minta ringkasan total tugas dan total point untuk hasil yang sedang difilter (bukan cuma total keseluruhan yang sudah ada di stats card atas), supaya saat filter periode/tanggal/tipe tugas diterapkan, langsung terlihat berapa tugas dan berapa poin dari hasil filter itu tanpa harus menjumlahkan manual dari tabel (terutama kalau hasilnya lebih dari 1 halaman).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/PicPointController.php` | `index()`: tambah `$filteredTotals` — hasil `(clone $query)->selectRaw('COUNT(*) as total_tasks, COALESCE(SUM(points_earned), 0) as total_points')->first()` dari query yang sama (setelah semua filter period/tanggal/step diterapkan, sebelum `paginate()`) — supaya akurat lintas halaman |
| `app/Http/Controllers/Marketing/DashboardController.php` | `points()`: tambah `$filteredTotals` dengan pola sama |
| `resources/views/pic/points/index.blade.php` | Tambah baris ringkasan "Total Tugas" & "Total Point" di atas tabel riwayat, di dalam card yang sama dengan tombol filter periode |
| `resources/views/marketing/points.blade.php` | Tambah baris ringkasan yang sama |

**Diverifikasi:** dicek lewat `php artisan tinker` — hasil `$filteredTotals` (COUNT + SUM lewat clone query) dicocokkan dengan hitungan langsung dari tabel `pic_point_histories` untuk PIC yang punya 3.387 riwayat — cocok persis (3387 tugas, 1593.35 point). Kedua file Blade dicek lolos compile (`Blade::compileString`) tanpa error.

## 20. Tutup Celah Crash 500 di `MarketingPointHistory::awardPoints()` Saat Race Condition

**Tujuan:** Saat membahas rencana deploy fix data kembar PIC (section #17) ke production, dicek juga apakah sisi Marketing punya risiko serupa. Ternyata `marketing_point_histories` **sudah** punya UNIQUE constraint (`marketing_id`, `submission_id`) sejak awal — jadi database tidak pernah menyimpan baris kembar (dikonfirmasi: 0 kelompok duplikat di data lokal). Tapi `MarketingPointHistory::awardPoints()` **belum** dibungkus try/catch seperti `PicPointHistory::awardPoints()` — kalau race condition benar-benar terjadi (klik ganda submit, retry jaringan), `create()` yang kedua akan menabrak constraint dan **crash dengan error 500** ke user, bukan pulang mulus. Bug laten ini sudah ada sejak sebelum sesi ini, ditemukan saat investigasi, langsung diperbaiki agar konsisten dengan penanganan di sisi PIC.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/MarketingPointHistory.php` | `awardPoints()`: bungkus `self::create()` dengan try/catch — kalau `QueryException` dengan pesan mengandung nama constraint `marketing_point_histories_marketing_id_submission_id_unique`, kembalikan `null` (dianggap "sudah pernah diberi"); exception lain tetap dilempar ulang |

**Diverifikasi:** lewat `php artisan tinker` (dalam transaksi yang di-rollback, tidak menyentuh data asli) — dibuat 2 baris `MarketingPointHistory::create()` dengan `marketing_id`+`submission_id` sama persis, baris kedua ditangkap `QueryException` dan pesan errornya dicocokkan persis dengan string yang dicek di `awardPoints()` — **cocok**. Full test suite (`tests/Feature/Points`, 18 test) tetap **PASS** setelah perubahan ini (perubahan tidak mengubah alur normal, hanya menambah jaring pengaman untuk kasus race yang jarang terjadi).

## 21. Fix Tanggal Riwayat Poin Ikut Berubah ke "Hari Ini" Saat Validasi (Bukan Tanggal Kerja Asli)

**Tujuan:** User melaporkan (dengan screenshot) bahwa setelah klik "Refresh Point", riwayat poin PIC untuk tugas yang dikerjakan tanggal 27 Juli malah tercatat tanggal 28 Juli (hari ini) — dan PIC tersebut (contoh: Dila) jadi tidak muncul di Laporan Kinerja periode 27 Juli.

**Root cause (dibuktikan dengan data nyata):** submission `SUB202607170018` — `production_validated_at` = **27 Jul 11:38:47** (tanggal PIC sungguh menyelesaikan tugas), tapi baris `pic_point_histories`-nya tercatat `created_at` = **28 Jul 11:53** (satu hari kemudian). Ditemukan 3 tempat yang mengaward poin secara langsung/real-time saat validasi dicentang, semuanya memanggil `PicPointHistory::awardPoints()` / `MarketingPointHistory::awardPoints()` **tanpa** parameter `$occurredAt` — mengandalkan "now" implisit yang HANYA benar kalau baris poin berhasil tersimpan PAS di detik yang sama dengan validasi. Begitu ada jeda (race condition/retry — pola yang sama dengan insiden section #17), baris poin baru berhasil tersimpan belakangan dengan tanggal "sekarang", bukan tanggal validasi asli.

**Dampak ke Laporan Kinerja:** `LaporanKinerjaController` menghitung kolom "Total Tugas" dari `submissions.{step}_validated_at` (benar) tapi kolom "Total Poin" dari `pic_point_histories.created_at` (bisa salah karena bug ini) — 2 sumber tanggal berbeda untuk baris & periode yang sama, menyebabkan kejanggalan seperti yang dilaporkan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `updateValidation()`: kirim `$occurredAt` (nilai `{field}_validated_at` yang baru saja di-set) ke `PicPointHistory::awardPoints()` dan `MarketingPointHistory::awardPoints()` (bonus production) |
| `app/Http/Controllers/Admin/SubmissionController.php` | `validateStep()` dan `toggleValidField()`: kirim `$occurredAt` (`{step}_validated_at`) ke `PicPointHistory::awardPoints()` |
| `tests/Feature/Points/PicPointHistoryAwardTest.php` | Tambah `test_award_points_backdates_created_at_to_provided_occurred_at` — mengunci kontrak: kalau `$occurredAt` dikirim, `created_at` HARUS mengikutinya |

**Catatan:** 6 pemanggilan `awardPoints()` lain untuk step `submit` (di `DashboardController`, `PicPointController`, `JournalManagementController`, `SubmissionController`) **tidak** diubah — semuanya mengaward poin PAS saat submission dibuat/di-assign, jadi "now" memang tanggal yang benar untuk aksi tersebut (tidak ada field `*_validated_at` terpisah yang perlu dicocokkan).

**Perbaikan data yang sudah telanjur salah tanggal:** logika "betulkan tanggal yang tidak cocok" **sudah ada** di `PicPointReportController::runBulkSync()` (dipakai tombol "Sinkronkan Point" yang **sudah ada dan tetap terlihat** di `/admin/pic-points` — beda dari tombol di `/admin/laporan-kinerja` yang disembunyikan di section #16). Diverifikasi lewat simulasi persis kondisi bug (dalam transaksi yang di-rollback): dibuat riwayat dengan `created_at` 28 Jul tapi `production_validated_at` 27 Jul → jalankan `runBulkSync()` → `created_at` riwayat otomatis terkoreksi jadi 27 Jul. **Tidak perlu kode/fitur baru untuk perbaikan data ini** — admin tinggal klik tombol "Sinkronkan Point" yang sudah ada di `/admin/pic-points` setelah fix kode ini di-deploy.

**Diverifikasi:** full test suite (`tests/Feature/Points`, 19 test, 31 assertion) **PASS**. Simulasi manual lewat `php artisan tinker` (transaksi di-rollback) membuktikan mekanisme backdate & perbaikan bekerja persis seperti dirancang.

## 22. Konsolidasi Tombol "Sinkronkan Point" — dari 7 Tombol Tersebar Jadi 1

**Tujuan:** User minta tombol "Sinkronkan Point" di halaman admin disisakan 1 saja, ditempatkan di lokasi yang strategis. Audit menemukan **7 tombol berbeda** (di 5 halaman) yang semuanya melakukan sinkronisasi poin dengan tingkat kelengkapan tidak konsisten — sebagian cuma menghitung ulang `total_points`, sebagian juga membackfill riwayat hilang, hanya 1 yang juga membetulkan tanggal (section #21). Ini persis rekomendasi #3 dari diskusi strategis di awal sesi ("konsolidasi mekanisme sinkron yang tersebar").

**Lokasi terpilih:** `/admin/sync` ("Sinkronisasi Data") — sudah jadi hub sinkronisasi khusus, sudah ada di sidebar dengan badge penghitung "tidak sinkron", dan sudah menampilkan status per-modul sebelum sinkron.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | Ekstrak isi `syncAllPoints()` jadi `runFullSync(): array` (static, reusable) — backfill + perbaiki tanggal + recompute + hapus orphan. `syncAllPoints()` jadi wrapper tipis |
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | Ekstrak isi `syncAllPoints()` jadi `runFullSync(): array` (static, reusable) — backfill + recompute. `syncAllPoints()` jadi wrapper tipis |
| `app/Http/Controllers/Admin/SyncController.php` | Tambah `syncPoints()` — panggil `PicPointReportController::runFullSync()` + `MarketingPointReportController::runFullSync()` sekaligus (logika PALING lengkap). `syncAll()` diupgrade memakai logika yang sama (dulu cuma recompute-only). Hapus `syncMarketingPoints()`/`syncPicPoints()` (recompute-only, sudah tidak dipakai tombol manapun setelah perubahan ini) |
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | Hapus `syncPoints()` — sudah tidak ada tombol yang memanggilnya sejak section #16, dan sekarang sepenuhnya digantikan oleh tombol terpusat |
| `routes/web.php` | Hapus route `sync.marketing-points`, `sync.pic-points`, `laporan-kinerja.sync`. Tambah `sync.points` → `SyncController::syncPoints()` |
| `resources/views/admin/sync/index.blade.php` | Gabung card "Point Marketing" + "Point PIC" jadi 1 card "Point PIC & Marketing" dengan 1 tombol → `admin.sync.points` |
| `resources/views/admin/pic-points/index.blade.php` | Hapus form/tombol "Sinkronkan Point" + JS handler-nya, ganti jadi link ke `/admin/sync` |
| `resources/views/admin/marketing-points/index.blade.php` | Hapus form/tombol "Sinkronkan Point", ganti jadi link ke `/admin/sync` |
| `resources/views/admin/reports/team-performance.blade.php` | Hapus 2 tombol (PIC/Marketing conditional), ganti jadi 1 link ke `/admin/sync` |
| `resources/views/admin/reports/team-marketing-performance.blade.php` | Hapus tombol "Sinkronisasi Point Marketing", ganti jadi link ke `/admin/sync` |
| `tests/Feature/Points/SyncPageRenderTest.php` (baru, 6 test) | Verifikasi HTTP-level (bukan cuma compile-check): halaman sync render dengan tombol gabungan, tombol berfungsi tanpa error, 4 halaman yang tombolnya dihapus tetap render normal |

**Route yang TIDAK diubah (tetap ada, dipakai internal):** `admin.pic-points.sync-all` dan `admin.marketing-points.sync-all` — controller-nya sekarang jadi wrapper tipis di atas `runFullSync()` yang sama, dipertahankan sebagai kemungkinan pemakaian langsung di masa depan (bukan dead code — logic intinya aktif dipakai).

**Diverifikasi:** full test suite (`tests/Feature/Points`, 25 test, 43 assertion) **PASS**, termasuk test HTTP-level baru yang benar-benar me-render halaman lewat request ter-autentikasi (bukan sekadar cek sintaks Blade) dan mengklik tombol sungguhan untuk memastikan tidak error.

## 23. Verifikasi Ulang: Tombol "Sinkronisasi Data" Terkonsolidasi TIDAK Mengubah Tanggal Pengerjaan

**Tujuan:** User minta dicek ulang — setelah tombol sinkron dikonsolidasi jadi 1 (section #22), apakah tanggal pengerjaan asli tetap terjaga (bukan ikut berubah ke tanggal saat tombol sinkron diklik), sesuai fix section #21.

**Diverifikasi lewat 2 skenario nyata** (simulasi `SyncController::syncPoints()` — persis method di balik tombol "Sinkronisasi Point (PIC & Marketing)"):
1. **Riwayat yang tanggalnya sudah BENAR** (`created_at` sudah cocok dengan `editor1_validated_at`, di-insert langsung lewat query builder supaya bebas dari auto-timestamp Eloquent) → setelah klik sinkron: **`created_at` tetap persis sama**, tidak ikut berubah ke tanggal hari ini.
2. **Riwayat yang tanggalnya TELANJUR SALAH** (skenario insiden section #21 — `created_at` = tanggal sync lama, beda dari `validated_at` sebenarnya) → setelah klik sinkron: **`created_at` diperbaiki ke tanggal validasi asli**, bukan ke tanggal sinkron dijalankan.

Kedua hasil ini **membuktikan tombol terkonsolidasi masih memakai logika yang sama persis** (`PicPointReportController::runFullSync()` → `runBulkSync()`, yang backfill/repair-nya sudah dipastikan pakai `s.{step}_validated_at`, bukan `NOW()`) — konsolidasi tombol di section #22 tidak menghilangkan atau melemahkan perbaikan tanggal dari section #21.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `tests/Feature/Points/RunBulkSyncTest.php` | Tambah 2 test permanen: `test_sync_points_button_does_not_change_created_at_of_already_correct_history` dan `test_sync_points_button_repairs_mismatched_created_at_to_true_validated_at` — mengunci kedua skenario di atas lewat `SyncController::syncPoints()` langsung (bukan cuma `runBulkSync()` mentah), supaya kalau ada perubahan di masa depan yang tidak sengaja menghapus/melemahkan logika ini, test langsung gagal |

**Diverifikasi:** full test suite (`tests/Feature/Points`, 27 test, 45 assertion) **PASS**.

## 24. Uji Nyata: Hapus Semua Riwayat Poin (Lokal) + Sinkron Ulang dari Nol

**Tujuan:** User minta pembuktian nyata (bukan cuma test) — hapus SEMUA riwayat poin PIC & Marketing di database lokal sampai 0, lalu jalankan sinkronisasi penuh, untuk memastikan mekanisme sinkron benar-benar bisa membangun ulang data yang sinkron dari nol. Dilakukan di **database lokal (`dbrevana`) saja** — production TIDAK disentuh (tidak ada akses langsung).

**Langkah:** backup penuh 4 tabel terkait (`pic_point_histories`, `marketing_point_histories`, `pics`, `marketings`) ke `backup_before_full_point_wipe_2026-07-28.sql` (16,6 MB, di scratchpad) → `TRUNCATE` kedua tabel riwayat + reset `total_points` ke 0 semua PIC/Marketing → jalankan `SyncController::syncPoints()` (tombol tunggal hasil konsolidasi section #22) → verifikasi hasil.

**Hasil sinkronisasi:** selesai ~4,4 menit tanpa error. 97.086 riwayat PIC baru + 14.182 riwayat Marketing baru dibuat. Setelah sinkron: **100% sinkron** (70/70 PIC, 15/15 Marketing, 8.343/8.343 slot — `total_points` cocok persis `SUM(riwayat)` di semua baris), **0 kelompok duplikat** di kedua tabel.

**Temuan penting — sinkronisasi dari nol TIDAK sepenuhnya lossless (di luar penyesuaian manual yang sudah diketahui):**
1. **Penyesuaian manual PIC** (194 baris, submission_id NULL, 41,70 poin) — sudah diketahui & disetujui sebelumnya, tidak bisa direkonstruksi karena tidak terkait submission apa pun.
2. **[Temuan baru] Step dengan rate saat ini = 0** — `editor3` rate-nya 0 (aktif tapi 0 poin), jadi backfill (`if ($points > 0)` di `runBulkSync()`) sama sekali tidak membuat baris untuk step ini. **286 baris riwayat "siapa mengerjakan Editor 3" hilang total** (nilai poin 0, tapi jejak siapa yang mengerjakan hilang).
3. **[Temuan baru] "Reassignment drift"** — 36 baris PIC + 22 baris Marketing (total 29,40 poin: 18,40 PIC + 11,00 Marketing) hilang karena field petugas/marketing_id di `submissions` **diubah SETELAH** tugas divalidasi (mis. admin mengoreksi salah assign). Riwayat asli benar mencatat siapa yang SUNGGUH mengerjakan saat itu, tapi backfill hanya bisa merekonstruksi berdasarkan assignment **saat ini** — begitu riwayat dihapus, jejak assignment historis hilang permanen. Dibuktikan lewat query silang riwayat lama (dari backup) vs `submissions.petugas_*_id`/`*_valid` saat ini — jumlah baris yang cocok pola ini PERSIS sama dengan selisih yang ditemukan (36 dan 22).

**Total poin sebelum → sesudah:** PIC 28.079,68 → 28.019,58 (turun 60,10 = 41,70 manual + 18,40 drift). Marketing 7.102,00 → 7.091,00 (turun 11,00 = drift).

**Keputusan user:** setelah diberi tahu temuan lengkap (dan opsi restore dari backup), user memilih **membiarkan kondisi hasil resync** (tidak restore) — tujuan verifikasi sudah tercapai.

**Implikasi untuk production:** temuan ini memperkuat rekomendasi sebelumnya untuk TIDAK PERNAH melakukan hapus-total di production — bukan cuma karena penyesuaian manual hilang, tapi juga karena riwayat "siapa sungguh mengerjakan" bisa hilang kalau ada step berate 0 atau assignment yang pernah dikoreksi. Backup SQL (`backup_before_full_point_wipe_2026-07-28.sql`) masih disimpan di scratchpad kalau sewaktu-waktu dibutuhkan untuk memulihkan 322 baris yang hilang di database lokal.

## 25. Tambah "Reset Semua Point" untuk Marketing (Sebelumnya Cuma Ada di PIC) + Perbaiki Cache Leaderboard

**Tujuan:** User ingin benar-benar mereset poin PIC & Marketing ke 0 (dibiarkan 0, bukan disinkron ulang) di **production** — akan dijalankan sendiri oleh user, bukan oleh saya (tidak ada akses ke server production). Fitur "Reset Semua Point" untuk PIC sudah ada (dengan konfirmasi ketik "RESET"), tapi Marketing belum punya fitur setara — supaya user bisa melakukan ini dengan aman lewat UI yang sudah teruji, bukan lewat SQL/tinker mentah langsung di production.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | Tambah `resetAllPoints(Request $request)` — pola identik dengan punya PIC: wajib ketik "RESET", `DB::transaction` truncate `marketing_point_histories` + set `total_points=0` semua Marketing, hapus cache leaderboard. `index()` tambah variabel `$totalHistories` untuk ditampilkan di modal konfirmasi |
| `app/Http/Controllers/Admin/PicPointReportController.php` | `resetAllPoints()`: **perbaikan bug kecil** — sebelumnya tidak menghapus cache `rankings.topPics`/`sync.out_of_sync_count` setelah reset, jadi leaderboard PIC bisa menampilkan data lama sampai 5 menit setelah reset. Sekarang cache langsung dihapus |
| `routes/web.php` | Tambah `POST /admin/marketing-points/reset-all` → `admin.marketing-points.reset-all` |
| `resources/views/admin/marketing-points/index.blade.php` | Tambah tombol "Reset Semua Point" + modal konfirmasi (ketik "RESET"), identik gaya dengan yang di `/admin/pic-points` |
| `tests/Feature/Points/ResetAllPointsTest.php` (baru, 5 test) | Konfirmasi salah → ditolak, riwayat/total tidak berubah. Konfirmasi "RESET" → riwayat terhapus & total jadi 0 (PIC & Marketing). Kedua halaman menampilkan tombol |

**Diverifikasi:** full test suite (`tests/Feature/Points`, 32 test, 65 assertion) **PASS**.

**Instruksi untuk production (dijalankan sendiri oleh user):**
1. `git pull origin master` (setelah commit) — tidak ada migration baru di section ini, cukup deploy kode.
2. **Sangat disarankan:** backup dulu (`mysqldump` tabel `pic_point_histories`, `marketing_point_histories`, `pics`, `marketings`) sebelum reset — sama seperti yang dilakukan untuk uji coba lokal di section #24. Reset ini **permanen**, tidak ada tombol undo.
3. Buka `/admin/pic-points` → klik "Reset Semua Point" → ketik `RESET` → konfirmasi.
4. Buka `/admin/marketing-points` → klik "Reset Semua Point" (tombol baru) → ketik `RESET` → konfirmasi.
5. Setelah kedua langkah di atas, seluruh leaderboard, riwayat perolehan poin PIC/Marketing, dan laporan terkait poin di admin (`/admin/laporan-kinerja`, `/admin/reports/team-performance`, dll — semuanya membaca dari tabel yang sama) otomatis akan menunjukkan 0, karena semuanya menghitung langsung dari `pic_point_histories`/`marketing_point_histories` yang baru saja dikosongkan — tidak perlu langkah tambahan.

## 26. Fix Crash "There is no active transaction" di Tombol Reset Semua Point

**Tujuan:** User mencoba tombol "Reset Semua Point" PIC di lokal (`/admin/pic-points/reset-all`) untuk uji coba sebelum diterapkan di production, dan mendapat halaman error `PDOException: There is no active transaction`.

**Root cause:** `TRUNCATE TABLE` adalah statement **DDL** di MySQL, dan DDL menyebabkan **implicit commit** — begitu `PicPointHistory::truncate()` jalan di dalam closure `DB::transaction()`, MySQL langsung meng-commit transaksi yang sedang berjalan tanpa sepengetahuan Laravel. Statement berikutnya (`Pic::query()->update(...)`) tetap berhasil (berjalan ter-autocommit sendiri), tapi begitu closure selesai dan Laravel mencoba `COMMIT` transaksi yang menurutnya masih aktif, PDO/MySQL bilang "There is no active transaction" → exception. **Bug ini sudah ada SEBELUM sesi ini** di kode PIC yang asli (section ini cuma pertama kali membuatnya benar-benar terpanggil) — dan otomatis ikut ter-copy ke kode Marketing yang baru dibuat di section #25.

**Efek samping penting:** karena implicit commit terjadi SEBELUM exception dilempar, **kedua statement (truncate + update total_points) sudah benar-benar berhasil dieksekusi** sebelum error muncul — jadi walau user melihat halaman error, data PIC yang di-reset **sungguhan sudah ter-reset ke 0** (dikonfirmasi langsung: `pic_point_histories` 0 baris, `SUM(total_points)` 0 di database lokal user). Errornya murni salah pesan (gagal di langkah commit terakhir), bukan kegagalan operasi.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | `resetAllPoints()`: ganti `PicPointHistory::truncate()` → `PicPointHistory::query()->delete()` (DML biasa, aman di dalam transaksi, tidak memicu implicit commit) |
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | `resetAllPoints()`: perbaikan yang sama |

**Catatan penting soal testing:** ke-5 test di `ResetAllPointsTest.php` (section #25) **LOLOS** walau bug ini masih ada — karena `RefreshDatabase` sendiri membungkus setiap test dalam transaksi luar, membuat `DB::transaction()` di dalam kode yang diuji berjalan sebagai *nested transaction* (pakai SAVEPOINT, bukan BEGIN/COMMIT asli) — sehingga implicit-commit dari TRUNCATE tidak memicu error yang sama seperti saat berjalan asli (top-level transaction sungguhan) di luar test. Ini keterbatasan nyata dari pendekatan testing berbasis `RefreshDatabase`: bug seputar interaksi DDL+transaksi tidak akan pernah tertangkap lewat test semacam ini. Diverifikasi manual lewat `php artisan tinker` langsung ke database nyata (bukan test-wrapped) untuk memastikan fix benar-benar menghilangkan error tsb.

**Diverifikasi:** dipanggil `resetAllPoints()` PIC & Marketing langsung ke database lokal nyata (`dbrevana`, bukan `dbrevana_testing`) setelah fix — keduanya berhasil tanpa error. Full test suite (`tests/Feature/Points`, 32 test, 65 assertion) tetap **PASS**.

**Catatan deploy:** fix ini **WAJIB** ikut di-deploy ke production SEBELUM mencoba tombol "Reset Semua Point" di sana — kalau tidak, akan mengalami crash yang sama seperti yang dialami user di lokal (walau operasinya sendiri kemungkinan besar tetap berhasil di baliknya).

## 27. Tombol "Kembali ke Admin" Belum Terlihat Jelas di Halaman PIC Saat Impersonasi

**Tujuan:** User minta ditambahkan tombol "kembali ke halaman admin" di `/pic/dashboard` saat admin sedang login-as (impersonasi) seorang PIC.

**Temuan:** fitur impersonasi (`PicController::loginAs()`/`returnToAdmin()`) sudah ada, dan sebetulnya sudah ada badge indikator "Mode Admin" yang selalu terlihat di navbar PIC — tapi tombol AKSI "Kembali ke Admin" yang sebenarnya tersembunyi di dalam dropdown profil (harus klik nama dulu, baru terlihat). Dibandingkan dengan layout Marketing (`marketing/layouts/app.blade.php`) yang sudah punya **banner penuh selebar halaman** di bagian paling atas dengan tombol "Kembali ke Admin" langsung terlihat tanpa perlu klik apa pun — PIC belum punya banner setara ini, cuma badge pasif.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/layouts/app.blade.php` | Tambah banner kuning selebar halaman di awal `<body>` (persis pola yang sudah ada di layout Marketing) — muncul kalau `session('admin_impersonating')` ada, berisi nama PIC yang sedang dilihat + tombol "Kembali ke Admin" yang langsung submit ke `admin.pics.return-to-admin`. Badge kecil di navbar (sudah ada sebelumnya) dibiarkan tetap ada untuk konsistensi dengan Marketing |
| `tests/Feature/Points/PicImpersonationBannerTest.php` (baru, 2 test) | Banner + tombol muncul saat `admin_impersonating` di-set di session; tidak muncul untuk sesi PIC normal (bukan hasil impersonasi) |

Berlaku otomatis di SEMUA halaman PIC (bukan cuma dashboard) karena ini bagian dari layout bersama.

**Diverifikasi:** full test suite (`tests/Feature/Points`, 34 test, 71 assertion) **PASS**.

## 28. Format Angka Point/Tugas, Sederhanakan Label Periode Laporan Kinerja, dan "Login As" Buka Tab Baru

**Tujuan:** 3 perbaikan kecil dari feedback user:
1. Card "Total Point"/"Point Hari Ini"/"Point Bulan Ini" di halaman poin PIC & Marketing pakai 1 desimal (`0.0`), bukan 2 (`0.00`). Card "Total Tugas" PIC pakai pemisah ribuan titik, bukan koma default PHP.
2. Label periode di `/admin/laporan-kinerja` yang menampilkan rentang cutoff mentah ("26 Juni 2026 — 25 Juli 2026") membingungkan untuk mode dropdown Bulan+Tahun biasa — disederhanakan jadi "Juli 2026".
3. Tombol "Login As" (PIC/Marketing/Reviewer/User/Reviewer-dari-PIC) membuka tab baru supaya halaman admin yang sedang dibuka tidak hilang.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/points/index.blade.php` | Card Total Point/Point Hari Ini/Point Bulan Ini + baris "Total Point" hasil filter: `number_format(..., 2)` → `1`. Card Total Tugas: tambah pemisah ribuan titik (`, 0, ',', '.'`) |
| `resources/views/marketing/points.blade.php` | Perubahan sama untuk 3 card + baris "Total Point" hasil filter |
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | `resolvePeriod()`: `$namaBulan` sekarang beda tergantung mode — mode custom date range tetap tampilkan rentang eksplisit (berguna di sana), mode dropdown Bulan+Tahun tampilkan label sederhana "Bulan Tahun". **Filter data TIDAK berubah** — `$periodStart`/`$periodEnd` tetap cutoff 26-25 seperti sebelumnya (section #13), cuma labelnya yang disederhanakan |
| `resources/views/admin/pics/index.blade.php`, `admin/marketings/index.blade.php`, `admin/users/index.blade.php`, `admin/reviewers/index.blade.php`, `pic/reviewers/index.blade.php` | Tambah `target="_blank"` ke form tombol "Login As" — supaya halaman asal (mis. daftar PIC di admin) tetap terbuka di tab lama |

**Catatan presisi:** card "Total Point" adalah nilai AGREGAT (jumlah dari banyak riwayat) yang bisa memuat rate pecahan (0.1/0.2/0.25/0.33) — menampilkannya dengan 1 desimal berarti nilai seperti 45.28 akan tampil "45.3" (dibulatkan tampilannya saja, nilai asli di database tidak berubah). Badge poin per-baris riwayat (mis. "+0.25", "+0.33") **TIDAK diubah**, tetap 2 desimal — kalau dibulatkan ke 1 desimal, rate 0.25 dan 0.33 akan sama-sama tampil "0.3", menghilangkan bedanya.

**Diverifikasi:** dicek lewat `php artisan tinker` — `resolvePeriod()` menghasilkan label "Juli 2026" untuk mode dropdown (periode filter tetap 26 Jun–25 Jul, tidak berubah) dan tetap "27 Juni 2026 — 27 Juli 2026" untuk mode custom range. Test HTTP-level (request asli, bukan compile-check) mengonfirmasi teks rentang cutoff sudah tidak muncul dan label sederhana muncul dengan benar. Semua file Blade yang diubah dicek lolos compile. Full test suite (`tests/Feature/Points`, 34 test) tetap **PASS**.

## 29. Tambah Nomor Surat (Kode LOA), Nama Jurnal, dan Publisher di Sertifikat Reviewer

**Tujuan:** User minta 3 info tambahan ditampilkan di sertifikat reviewer (`/reviewer/certificates`): nomor surat (pakai kode LOA), nama jurnal, dan nama publisher.

**Temuan:** sertifikat dibuat dengan menimpakan teks ke atas gambar template statis (`Reviewer\CertificateController::generateCertificate()`, pakai Intervention Image), bukan render HTML/Blade. `ReviewAssignment` (model yang menyimpan data reviewer/artikel untuk sertifikat) **tidak menyimpan** link ke jurnal aslinya — kolom `journal_id` selalu di-set `null` saat dibuat (`ReviewAssignmentController::store()`), dan tabel `journals` kosong (0 baris) di database. Setelah dikonfirmasi ke user: solusinya adalah mencocokkan `submit_link` milik assignment dengan `submissions.link_artikel` — dicek cocok **100% (49 dari 49)** assignment approved yang ada saat ini.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | `generateCertificate()`: cari `Submission` lewat `link_artikel == submit_link`, ambil `kode_loa` (nomor surat), `journalSlot.journalMaster.nama_jurnal`, dan `.publisher`. Tambah 1 baris teks kecil di bawah tanggal pada gambar sertifikat: "No. Surat: ... \| Jurnal: ... \| Publisher: ..." |

**Diverifikasi:** file template AKTIF (`certificates/kTM1Uo9...jpg`) tidak ada di lokal (cuma record database yang tersinkron, bukan file storage), tapi ditemukan file template lain dengan desain identik (`SH3jkSPPS...jpg`) yang dipakai untuk uji render nyata — hasilnya (gambar terlampir ke user) menunjukkan baris info baru pas di bawah tanggal, tidak tumpang tindih dengan logo SIPERA atau border bawah. Data uji pakai assignment sungguhan (id=30) dengan kode_loa/jurnal/publisher lengkap. **Catatan:** posisi taksiran berdasarkan template referensi — kalau template AKTIF di production sedikit berbeda proporsinya, mungkin perlu penyesuaian koordinat Y kecil (sudah diberi komentar jelas di kode).

## 30. `/admin/point-rankings` Tidak Auto-Refresh — Terlihat Seperti "Belum Ter-update Realtime"

**Tujuan:** User melaporkan (dari production, portal.apji.org) bahwa poin di `/admin/point-rankings` tidak ter-update realtime — screenshot menunjukkan PIC dengan Total Point 0 di posisi rank 1.

**Diagnosis:** dicek `DashboardController::pointRankings()` — method ini query `total_points` LANGSUNG dari tabel `pics`/`marketings` setiap request, **tidak ada cache Laravel sama sekali** di endpoint ini. Jadi kalau halaman di-refresh manual, datanya PASTI sudah yang terbaru dari database. Masalah sebenarnya: halaman ini **tidak punya mekanisme auto-refresh** (beda dengan halaman sejenis seperti `/admin/pic-points` dan `/admin/marketing-points` yang sudah auto-reload setiap 30 detik) — jadi admin yang membiarkan tab terbuka akan terus melihat data lama sampai mereka manual tekan F5, terkesan seperti "tidak realtime" padahal datanya sebenarnya sudah benar begitu di-refresh.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/point-rankings.blade.php` | Tambah `@include('partials.auto-refresh', ['interval' => 30, ...])` — halaman otomatis reload tiap 30 detik (dengan indikator countdown & tombol jeda), sama seperti pola yang sudah dipakai di halaman monitoring lain |

**Catatan penting yang TIDAK bisa saya cek:** kalau setelah deploy fix ini admin masih melihat angka lama walau sudah menunggu 30 detik / refresh manual, kemungkinan ada **cache di level server/hosting** (CDN, reverse proxy, page-cache plugin) di depan aplikasi Laravel yang di luar kendali kode — ini perlu dicek langsung di konfigurasi hosting/Cloudflare production, saya tidak punya akses untuk mendiagnosis itu.

**Diverifikasi:** test HTTP-level (request asli) mengonfirmasi widget auto-refresh muncul di halaman. Full test suite (`tests/Feature/Points`, 34 test) tetap **PASS**.

## 31. Fix Akar Masalah: Riwayat Poin Tersimpan Tapi total_points PIC/Marketing Tidak Ikut Bertambah

**Tujuan:** User melaporkan PIC baru ("Eko Siswanto") di production menunjukkan Total Point 0 padahal sudah submit 1 artikel. User eksplisit minta akar masalahnya diperbaiki, BUKAN diselesaikan dengan membiasakan klik tombol "Sinkronisasi Point" di `/admin/sync` — sistem harus otomatis akurat.

**Diagnosis (dikonfirmasi langsung oleh user dari data production):** baris riwayat "Submit" milik PIC tersebut menunjukkan poin yang BENAR (+0,25, sesuai rate submit saat ini), tapi Total Point PIC tetap 0 — membuktikan ini BUKAN soal rate/konfigurasi yang salah, tapi riwayat berhasil tersimpan sementara `total_points` tidak ikut ter-update. Ini persis kelas bug yang sama dengan insiden section #17 (data tidak konsisten), tapi di sisi lain dari operasi yang sama.

**Root cause:** di `PicPointHistory::awardPoints()` dan `MarketingPointHistory::awardPoints()`, urutan operasinya adalah: (1) `create()` riwayat → (2) `forceFill(['created_at' => $occurredAt, ...])->save()` untuk backdate tanggal (ditambahkan section #21) → (3) `increment('total_points', ...)` / recompute SUM. Ketiga langkah ini **TIDAK dibungkus transaksi** — kalau langkah (2) gagal karena alasan apa pun (nilai `$occurredAt` tidak valid, race condition, dll), riwayat di langkah (1) **sudah kadung tersimpan** tapi eksekusi berhenti di situ, langkah (3) tidak pernah jalan — persis gejala yang dilaporkan: riwayat benar, total tidak ikut bertambah, dan tidak akan pernah otomatis benar sampai seseorang manual klik sinkron.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/PicPointHistory.php` | `awardPoints()`: bungkus `create()` + backdate + `increment('total_points', ...)` dalam 1 `DB::transaction()` — kalau ada apa pun yang gagal di tengah, SEMUA batal bersama (rollback), bukan meninggalkan riwayat yatim |
| `app/Models/MarketingPointHistory.php` | `awardPoints()`: perbaikan sama — `create()` + backdate + recompute SUM `total_points` dibungkus 1 transaksi |
| `tests/Feature/Points/PicPointHistoryAwardTest.php`, `MarketingPointHistoryAwardTest.php` | Tambah `test_award_points_rolls_back_history_row_if_backdate_fails` (masing-masing) — sengaja membuat backdate gagal (occurredAt tidak valid) dan membuktikan TIDAK ADA riwayat yatim tersimpan serta `total_points` TIDAK berubah, mengunci jaminan atomik ini |

**Diverifikasi:** direproduksi persis lewat `php artisan tinker` (transaksi di-rollback, tidak menyentuh data asli) — memanggil `awardPoints()` dengan `occurredAt` tidak valid, SEBELUM fix: riwayat tetap tersimpan meski exception dilempar (bukti bug). SETELAH fix: exception dilempar DAN riwayat ikut batal (rollback bersih) — dikonfirmasi count riwayat = 0, total_points = 0 setelahnya. Full test suite (`tests/Feature/Points`, 36 test, 75 assertion) **PASS**.

**Catatan:** perbaikan ini menutup akar masalahnya untuk kejadian BARU ke depannya. PIC "Eko Siswanto" yang datanya SUDAH TERLANJUR desync di production tetap perlu satu kali sinkronisasi manual (`/admin/sync`) untuk membetulkan riwayat yang sudah kadung tidak konsisten — setelah itu, dengan fix ini, seharusnya tidak akan terulang lagi secara otomatis.

## 32. Revert Format 1 Desimal — Balik ke 2 Desimal (Section #28 Menimbulkan Masalah Baru)

**Tujuan:** User menunjukkan screenshot halaman "Point Saya" PIC menampilkan "Total Point 0.3" — tidak cocok dengan nilai sebenarnya (0,25, sesuai rate submit). Ini persis risiko presisi yang sudah diperingatkan saat section #28 dibuat: rate pecahan seperti 0,25/0,33 kalau dibulatkan ke 1 desimal bisa menampilkan angka yang salah/tidak sesuai nilai asli.

**Keputusan:** revert perubahan 1 desimal dari section #28 — balik ke 2 desimal seperti semula. Perubahan "Total Tugas" pakai pemisah ribuan titik (bukan koma) TETAP dipertahankan (tidak terkait masalah presisi ini, dan sudah benar).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/points/index.blade.php` | Card Total Point/Point Hari Ini/Point Bulan Ini + baris "Total Point" hasil filter: `number_format(..., 1)` → `2` (balik ke semula) |
| `resources/views/marketing/points.blade.php` | Perubahan sama untuk 3 card + baris "Total Point" hasil filter |

**Diverifikasi:** dicek tidak ada `number_format(..., 1)` tersisa yang tidak sengaja ikut ter-revert (cuma 4 baris yang memang dimaksud di tiap file, dicek lewat grep sebelum & sesudah). Kedua file lolos compile. Full test suite (`tests/Feature/Points`, 36 test) tetap **PASS**.

## 33. Auto-Sync Berkala (Scheduler) untuk Data PIC/Marketing yang Sudah Terlanjur Desync

**Tujuan:** PIC "Eko Siswanto" di production masih menunjukkan Total Point 0 walau riwayatnya benar (+0,25) — karena data itu sudah kadung rusak SEBELUM perbaikan atomik section #31 di-deploy (perbaikan itu cuma mencegah kejadian BARU, tidak membetulkan data lama). User eksplisit tidak mau bergantung pada klik manual `/admin/sync` sebagai kebiasaan — minta perbaikan otomatis berkala.

**Solusi:** buat command Artisan baru yang menjalankan logika sinkronisasi (`runFullSync()` PIC & Marketing yang sudah ada) secara **otomatis setiap 15 menit** lewat scheduler Laravel — tanpa perlu klik apa pun. Kalau semua data sudah sinkron, command diam saja (tidak menulis log, supaya tidak spam). Kalau ada yang perlu dikoreksi, otomatis dibetulkan + dicatat ke log + cache leaderboard dibersihkan.

### File yang Diubah/Ditambah
| File | Perubahan |
|------|-----------|
| `app/Console/Commands/AutoSyncPicMarketingPoints.php` (baru) | Command `points:auto-sync` — jalankan `PicPointReportController::runFullSync()` + `MarketingPointReportController::runFullSync()`, log hanya kalau ada perubahan nyata |
| `app/Console/Kernel.php` | Daftarkan `points:auto-sync` di scheduler: `->everyFifteenMinutes()->withoutOverlapping()` — pola sama seperti 2 scheduled command lain yang sudah ada (`wa:reviewer-reminders`, `tenants:check-expiry`) |
| `tests/Feature/Points/AutoSyncCommandTest.php` (baru, 3 test) | Simulasi persis kasus Eko Siswanto (riwayat +0,25 benar, total_points 0 salah) untuk PIC & Marketing → jalankan command → `total_points` otomatis terkoreksi. Test ketiga: command diam (tidak melaporkan perubahan) kalau semua memang sudah sinkron |

**PENTING — syarat di server production:** scheduler Laravel **hanya jalan otomatis kalau ada 1 baris cron di server** yang menjalankan `php artisan schedule:run` setiap menit (biasanya sudah disetel via cPanel/SSH: `* * * * * cd /path/ke/project && php artisan schedule:run >> /dev/null 2>&1`). Karena 2 scheduled command lain (`wa:reviewer-reminders` jam 08:00, `tenants:check-expiry` jam 07:00) **sudah ada dan diasumsikan berjalan**, kemungkinan besar cron ini **sudah tersetel** di server — tapi saya tidak punya akses langsung ke production untuk memastikan. Kalau setelah deploy `points:auto-sync` ternyata tidak pernah jalan otomatis (bisa dicek lewat `storage/logs/laravel.log`, cari baris "Auto-sync poin"), berarti cron itu perlu dicek/disetel dulu di server.

**Diverifikasi:** dijalankan langsung (`php artisan points:auto-sync`) — cepat (data lokal sudah sinkron, langsung "tidak ada yang perlu dikoreksi"). Terdaftar dengan benar di `php artisan schedule:list` (jadwal tiap 15 menit). Full test suite (`tests/Feature/Points`, 39 test, 81 assertion) **PASS**.

## 34. PERBAIKAN URGENT: `points:auto-sync` (section #33) Menghidupkan Kembali Data yang Sudah Direset

**Tujuan:** User melaporkan Total Poin & Total Tugas muncul lagi dengan angka besar (mis. 2836 tugas/418,5 poin) di Laporan Kinerja, padahal sudah direset ke 0 sebelumnya. Diminta: kalau ada sinkron, mulai dari data terbaru saja, jangan tarik data lama juga.

**Root cause — bug di section #33 sendiri:** command `points:auto-sync` yang baru dibuat memanggil `runFullSync()` (PIC & Marketing), yang di dalamnya ADA logika backfill (`runBulkSync()`) yang membuat riwayat baru untuk **SEMUA** submission tervalidasi yang belum punya baris riwayat — tanpa peduli tua atau baru. Begitu admin reset semua poin ke 0 (hapus seluruh `pic_point_histories`/`marketing_point_histories`), SEMUA submission lama yang pernah tervalidasi jadi terlihat "belum punya riwayat" di mata logika backfill ini. Karena auto-sync berjalan **otomatis tiap 15 menit**, dalam waktu singkat semua data lama itu **dibangun ulang dari nol** — persis menghidupkan kembali apa yang sengaja dihapus. Ini kesalahan desain saya sendiri saat membuat section #33, tidak mempertimbangkan skenario reset.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Console/Commands/AutoSyncPicMarketingPoints.php` | Tulis ulang total — **TIDAK lagi memanggil `runFullSync()`/`runBulkSync()`** (yang membackfill). Sekarang HANYA menjalankan 1 query `UPDATE ... SET total_points = SUM(riwayat yang SUDAH ADA)` untuk PIC & Marketing — tidak pernah membuat baris riwayat baru dalam kondisi apa pun. Backfill dari submission lama sekarang MURNI tindakan manual lewat tombol "Sinkronisasi Point" di `/admin/sync` |
| `tests/Feature/Points/AutoSyncCommandTest.php` | Tambah `test_auto_sync_command_does_not_resurrect_old_data_after_reset` — mensimulasikan persis kondisi setelah reset (submission tervalidasi ada, riwayat SENGAJA kosong) dan membuktikan auto-sync TIDAK membuat riwayat baru serta `total_points` TETAP 0 |

**Diverifikasi:** 4 test (termasuk test baru di atas) **PASS**. Full test suite (`tests/Feature/Points`, 40 test, 84 assertion) **PASS**.

**TINDAKAN YANG DIPERLUKAN DARI USER:**
1. **Deploy perbaikan ini SEGERA** — kalau section #33 sudah di-deploy dan cron sudah aktif, command yang salah kemungkinan sudah/akan terus membangun ulang data lama tiap 15 menit sampai perbaikan ini menggantikannya.
2. **Data yang sudah kadung "muncul lagi" di production** (mis. 2836 tugas/418,5 poin di screenshot) itu **BENAR secara historis** (bukan data palsu — itu riwayat asli yang tadinya dihapus, sekarang dibangun ulang oleh bug ini) — tapi kalau tujuan Anda tetap "mulai dari 0", perlu klik "Reset Semua Point" SEKALI LAGI setelah perbaikan ini di-deploy, supaya kali ini tetap 0 (auto-sync yang sudah diperbaiki tidak akan menariknya kembali).

## 35. Analisis: Kenapa Widget "Peringkat Point PIC" di Dashboard Admin Tidak Ter-update

**Tujuan:** User minta dicek kenapa PIC "Eko Siswanto" masih Total Point 0 di widget "Peringkat Point PIC" (ternyata ini widget di **halaman Dashboard Admin**, bukan `/admin/point-rankings` — beda halaman, beda sumber data).

**Ditemukan bug cache TAMBAHAN (di luar root cause section #31):** widget ini (`admin/partials/point-rankings.blade.php`, di-include dari `admin/dashboard.blade.php`) mengambil data `$topPics`/`$topMarketings` dari `DashboardController::dashboard()`, yang menyimpannya di cache dengan **key BER-TENANT**: `Cache::remember("rankings.topPics.{$tenantKey}", 300, ...)` (TTL 5 menit). Tapi HAMPIR SEMUA tempat di kode yang seharusnya menghapus cache ini setelah poin berubah (`PicPointHistory::awardPoints()`, `revokePoints()`, `MarketingPointHistory::awardPoints()`, `PicPointReportController::recalculateAllPoints()`, `SyncController::clearSyncCache()`) cuma memanggil `Cache::forget('rankings.topPics')` **TANPA** akhiran tenant — jadi cache yang BENAR-BENAR dipakai widget dashboard **tidak pernah ter-hapus** oleh kode-kode itu, cuma bergantung pada TTL 5 menit kadaluarsa sendiri. Ini bug lama yang sudah ada dari sebelum sesi ini, baru ketahuan sekarang.

### File yang Diubah/Ditambah
| File | Perubahan |
|------|-----------|
| `app/Support/RankingCache.php` (baru) | Helper terpusat `forgetPics()`/`forgetMarketings()` — sekali panggil, menghapus KEDUA variasi key (polos & ber-tenant) sekaligus. Dibuat supaya tidak ada lagi tempat baru yang salah lupa salah satu variasi (persis penyebab bug ini) |
| `app/Models/PicPointHistory.php` | `awardPoints()` & `revokePoints()`: ganti `Cache::forget('rankings.topPics')` → `RankingCache::forgetPics()` |
| `app/Models/MarketingPointHistory.php` | `awardPoints()`: ganti jadi `RankingCache::forgetMarketings()` |
| `app/Http/Controllers/Admin/PicPointReportController.php` | `recalculateAllPoints()` & `resetAllPoints()`: pakai `RankingCache::forgetPics()` |
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | `resetAllPoints()`: pakai `RankingCache::forgetMarketings()` |
| `app/Http/Controllers/Admin/SyncController.php` | `clearSyncCache()`: sebelumnya HANYA menghapus `sync.out_of_sync_count`, sama sekali tidak menyentuh cache ranking — sekarang tambah `RankingCache::forgetPics()` + `forgetMarketings()`. Artinya tombol "Sinkronisasi Point" di `/admin/sync` sekarang juga langsung membersihkan cache dashboard, bukan cuma database |
| `app/Console/Commands/AutoSyncPicMarketingPoints.php` | Pakai `RankingCache` juga, konsisten dengan tempat lain |
| `tests/Feature/Points/RankingCacheTest.php` (baru, 2 test) | Membuktikan `forgetPics()`/`forgetMarketings()` menghapus KEDUA variasi key cache sekaligus |

**Diverifikasi:** full test suite (`tests/Feature/Points`, 42 test, 88 assertion) **PASS**.

**Kesimpulan untuk kasus Eko Siswanto:** kombinasi 2 penyebab — (1) data lama sudah kadung desync dari SEBELUM perbaikan atomik section #31 [belum tentu sudah dibetulkan otomatis kalau auto-sync section #33/34 belum sempat jalan/belum di-deploy], DAN (2) walau datanya sudah benar di database, widget dashboard bisa tetap menampilkan cache lama sampai 5 menit karena bug cache di atas. Setelah perbaikan ini di-deploy, kedua penyebab sudah tertutup — poin baru akan langsung terlihat di dashboard tanpa delay cache yang salah.

## 36. Revert Filter "Bulan" di Laporan Kinerja — Balik ke Kalender Biasa (Bukan Cutoff 26-25)

**Tujuan:** User melaporkan `/admin/laporan-kinerja` dengan filter default "Juli 2026" tidak menampilkan data yang seharusnya ada — data baru (sekitar tanggal 26-28 Juli) tidak muncul padahal harusnya "bulan ini" menampilkan semua data bulan berjalan.

**Root cause:** filter dropdown Bulan+Tahun sejak section #13 diartikan sebagai periode **cutoff 26-25** (pilih "Juli 2026" = 26 Juni s/d 25 Juli), bukan kalender 1-31 biasa — awalnya memang permintaan eksplisit user untuk keperluan periode penilaian kinerja/gajian. Tapi karena section #28 menyederhanakan LABEL dari "26 Juni — 25 Juli 2026" jadi cuma "Juli 2026", tampilannya sekarang terlihat seperti kalender biasa padahal logikanya tetap cutoff — jadi kalau hari ini tanggal 28 Juli, semua aktivitas tanggal 26-28 Juli TIDAK MASUK filter "Juli 2026" (baru muncul kalau pilih "Agustus 2026"). Dikonfirmasi ke user: mereka mau kalender biasa, bukan cutoff.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LaporanKinerjaController.php` | `resolvePeriod()`: mode dropdown Bulan+Tahun sekarang `$periodStart` = awal bulan (`startOfMonth()`), `$periodEnd` = akhir bulan (`endOfMonth()`) — kalender biasa. Mode custom date range (dari_tanggal/sampai_tanggal) tidak berubah |
| `tests/Feature/Points/LaporanKinerjaPeriodTest.php` (baru, 2 test) | Kunci perilaku baru: filter "Juli 2026" harus mencakup 1-31 Juli penuh (bukan 26 Jun-25 Jul), dan data di tanggal 28 pasti termasuk |

**Juga dicek menyeluruh** (permintaan user: "pastikan data poin di admin itu up to date") — halaman lain terkait poin:
- `/admin/pic-points` & `/admin/marketing-points`: sudah benar, pakai `whereMonth`/`whereYear` kalender biasa, TIDAK ada cache Laravel sama sekali di query utamanya — selalu live setiap request.
- Dashboard PIC (`Pic\AuthorController`) & Dashboard Marketing (`Marketing\DashboardController`): pakai cache `rankings.topPics/topMarketings.{tenantKey}` yang SAMA dengan yang sudah diperbaiki lewat `RankingCache` di section #35 — otomatis ikut ter-cover.
- `PicReviewerDashboardController` (cache `pic_reviewer.dashboard_stats`): dicek, tidak terkait data poin PIC/Marketing sama sekali (soal reviewer assignment), tidak perlu diubah.

**Diverifikasi:** `php artisan tinker` mengonfirmasi periode "Juli 2026" sekarang = 2026-07-01 00:00:00 s/d 2026-07-31 23:59:59. Full test suite (`tests/Feature/Points`, 44 test, 94 assertion) **PASS**.

## 37. Indikator "Kapan Auto-Sync Terakhir Berjalan" di `/admin/sync` (Diagnostik Tanpa Perlu SSH)

**Tujuan:** User masih melihat "EKO SISWANTO PIC" Total Point 0 di `/admin/pics` dan `/admin/point-rankings` — 2 halaman yang SUDAH dikonfirmasi tidak pakai cache sama sekali (query langsung ke `pics.total_points`). Ini berarti akar masalahnya BUKAN cache lagi, tapi salah satu dari: (a) cron scheduler server belum aktif sehingga `points:auto-sync` (section #33/34) tidak pernah benar-benar berjalan otomatis, atau (b) riwayat poin PIC tsb memang sudah kosong (mis. ikut terhapus reset kedua) dan belum ada aktivitas baru. User tidak familiar cara cek `storage/logs/laravel.log`/SSH untuk memastikan mana penyebabnya.

**Solusi:** command `points:auto-sync` sekarang mencatat kapan terakhir berjalan + ringkasan hasilnya ke cache, dan halaman `/admin/sync` menampilkannya sebagai indikator visual — bisa dicek lewat browser biasa, tanpa akses server.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Console/Commands/AutoSyncPicMarketingPoints.php` | Setiap kali dijalankan, simpan `points.auto_sync.last_run_at` (timestamp) + `points.auto_sync.last_result` (jumlah PIC/Marketing yang dikoreksi) ke cache (TTL 1 hari) |
| `app/Http/Controllers/Admin/SyncController.php` | `index()`: ambil kedua data cache di atas, kirim ke view |
| `resources/views/admin/sync/index.blade.php` | Tambah card indikator: kalau pernah jalan → tampilkan "X menit yang lalu" + ringkasan (hijau kalau ≤20 menit, kuning + peringatan kalau lebih — mengindikasikan cron mungkin tidak aktif); kalau belum pernah terdeteksi sama sekali → peringatan merah dengan instruksi cek Cron Job di hosting |
| `tests/Feature/Points/SyncPageRenderTest.php` | Tambah 2 test: peringatan muncul kalau belum pernah jalan; "X menit yang lalu" muncul setelah command benar-benar dijalankan |

**Cara pakai untuk user:** buka `/admin/sync` — kalau indikatornya menunjukkan "belum pernah terdeteksi berjalan" atau sudah lebih dari 20 menit, itu tandanya cron scheduler di server (`* * * * * php artisan schedule:run`) belum aktif dan perlu disetel di panel hosting (cPanel biasanya ada menu "Cron Jobs"). Kalau indikatornya sehat (hijau, baru beberapa menit lalu) tapi PIC tertentu masih 0, kemungkinan besar riwayat poinnya memang kosong (bukan bug) — perlu aktivitas baru dari PIC tsb untuk mendapat poin lagi.

**Diverifikasi:** full test suite (`tests/Feature/Points`, 46 test, 100 assertion) **PASS**.

## 38. Hapus Total Tombol Sinkronisasi Poin Manual (Backfill) di `/admin/sync`

**Tujuan:** setelah dikonfirmasi cron scheduler server belum aktif (section #37), user sempat mencoba tombol manual di `/admin/sync` dan itu membangun ulang SEMUA data lama yang sudah direset — user minta fitur ini **dihapus total**, bukan cuma disembunyikan, supaya tidak ada jalan sama sekali (sengaja atau tidak sengaja) untuk membawa kembali data lama.

**Audit sebelum menghapus** (supaya tidak salah hapus logic yang masih dipakai fitur lain):
- `runBulkSync()` (PIC & Marketing) — logika INTI backfill — **TETAP DIPERTAHANKAN**, karena masih dipakai `TaskPointSettingController::syncTotals()` (dipanggil otomatis saat admin menyimpan/menghapus pengaturan rate poin task — fitur terpisah, tidak disebutkan user, tidak diubah) dan `PicPointReportController::syncAllAndLogout()` (dipakai fitur "Sinkronkan & Logout" di modal logout admin — lihat catatan penting di bawah).
- `runFullSync()` (wrapper `runBulkSync()` + recompute total + hapus orphan) — HANYA dipanggil dari `SyncController` dan `syncAllPoints()` (yang keduanya dihapus) — **DIHAPUS**, sudah benar-benar tidak dipakai di mana pun.

### File yang Dihapus/Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SyncController.php` | Hapus method `syncAll()` dan `syncPoints()` sepenuhnya |
| `app/Http/Controllers/Admin/PicPointReportController.php` | Hapus method `syncAllPoints()` dan `runFullSync()` |
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | Hapus method `syncAllPoints()` dan `runFullSync()` |
| `routes/web.php` | Hapus route `admin.sync.all`, `admin.sync.points`, `admin.pic-points.sync-all`, `admin.marketing-points.sync-all` |
| `resources/views/admin/sync/index.blade.php` | Hapus tombol "Sinkronisasi Semua Sekarang" di header + seluruh card "Point PIC & Marketing". Halaman sekarang cuma: indikator auto-sync (section #37), card Slot Jurnal, info box |
| `resources/views/admin/pic-points/index.blade.php`, `admin/marketing-points/index.blade.php`, `admin/reports/team-performance.blade.php`, `admin/reports/team-marketing-performance.blade.php` | Hapus link "Sinkronisasi Point" yang mengarah ke `/admin/sync` (sekarang menyesatkan karena halaman itu tidak lagi punya fungsi itu) |
| `tests/Feature/Points/SyncPageRenderTest.php`, `RunBulkSyncTest.php` | Perbarui test yang sebelumnya menguji tombol/route yang dihapus — 2 test baru membuktikan route benar-benar hilang (`Route::has()` false) |

**Diverifikasi:** full test suite (`tests/Feature/Points`, 46 test, 101 assertion) **PASS**. Semua file Blade yang diubah dicek lolos compile.

## 39. Hapus Modal "Sinkronkan & Logout" — Jalur Lain yang Juga Memicu Backfill Penuh

**Tujuan:** melanjutkan section #38 — ditemukan fitur LAIN yang juga memicu backfill penuh: modal konfirmasi "Sebelum Logout" yang muncul saat admin logout, dengan pilihan "Sinkronkan & Logout" (`admin.sync-and-logout` → `PicPointReportController::syncAllAndLogout()` → `runBulkSync()`). Kemungkinan besar ini **sumber tambahan** kenapa data lama sempat "muncul lagi" setelah direset — kalau siapa pun yang pegang akun admin terbiasa klik itu (bukan "Logout Saja"), backfill penuh terpicu lagi setiap kali logout. User minta dihapus sekalian.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/layouts/app.blade.php` | Hapus modal `#logoutModal` beserta script-nya sepenuhnya. Tombol Logout admin sekarang langsung submit form ke `route('logout')` tanpa modal — konsisten dengan PIC/Marketing/Reviewer yang memang sudah begitu dari awal |
| `app/Http/Controllers/Admin/PicPointReportController.php` | Hapus method `syncAllAndLogout()`. `runBulkSync()` (dipanggil dari method ini sebelumnya) **tetap dipertahankan** — masih dipakai `TaskPointSettingController` |
| `routes/web.php` | Hapus route `admin.sync-and-logout` |
| `tests/Feature/Points/SyncPageRenderTest.php` | Tambah assertion route `admin.sync-and-logout` sudah tidak ada, dan test baru `test_admin_page_renders_without_sync_and_logout_modal` — memastikan modal & teksnya benar-benar hilang dari halaman |

**Diverifikasi:** dicek tidak ada sisa referensi ke `logoutModal`/`sync-and-logout`/`syncAllAndLogout` di seluruh `resources/views`, `app/`, `routes/`. Semua file lolos compile/lint. Full test suite (`tests/Feature/Points`, 47 test, 106 assertion) **PASS**.

**Ringkasan gabungan section #38+#39:** semua jalur yang bisa memicu backfill penuh (membangun ulang riwayat dari submission lama) di admin panel sudah dihapus total — baik tombol manual di `/admin/sync`, link-link yang mengarah ke sana, maupun modal logout. Satu-satunya mekanisme yang tersisa untuk menjaga `total_points` tetap akurat adalah **auto-sync otomatis berkala** (section #33/34/37) yang sengaja dirancang HANYA mengoreksi dari riwayat yang sudah ada, tidak pernah membuat riwayat baru dari data lama.

## 40. Auto-Sync Tanpa Cron — Jaring Pengaman Kalau Server Tidak Bisa/Sulit Setel Cron Job

**Tujuan:** dicek ulang indikator di `/admin/sync` — cron scheduler MASIH belum pernah terdeteksi berjalan (user sudah beberapa kali diberi instruksi menyetel cron di hosting, tapi belum berhasil/belum sempat). User pilih solusi yang tidak butuh akses cron/SSH sama sekali.

**Solusi:** logika inti auto-sync diekstrak ke class `App\Support\PointsAutoSync` (dipakai bersama oleh command terjadwal MAUPUN pemicu baru), lalu `AdminMiddleware` (sudah berjalan di SEMUA route admin) dipasangi trigger tambahan: setiap kali ada admin membuka halaman admin apa pun, sistem cek "sudah 15 menit sejak auto-sync terakhir jalan?" — kalau iya, jalankan sinkronisasi (logika SAMA PERSIS dengan command terjadwal: HANYA recompute dari riwayat yang sudah ada, tidak backfill). Dijalankan lewat `terminate()` (Laravel memanggilnya SETELAH response terkirim ke browser) supaya admin **tidak merasakan delay sama sekali**.

### File yang Ditambah/Diubah
| File | Perubahan |
|------|-----------|
| `app/Support/PointsAutoSync.php` (baru) | `run()` — logika inti (dipindah dari command, TIDAK berubah). `runIfDue()` — pakai `Cache::add()` (atomik) sebagai kunci throttle 15 menit terpisah dari `last_run_at`, supaya request yang datang bersamaan tidak ikut lolos throttle |
| `app/Console/Commands/AutoSyncPicMarketingPoints.php` | Disederhanakan jadi wrapper tipis di atas `PointsAutoSync::run()` — tetap ada untuk cron kalau nanti berhasil diaktifkan |
| `app/Http/Middleware/AdminMiddleware.php` | Tambah method `terminate()` yang memanggil `PointsAutoSync::runIfDue()` |
| `tests/Feature/Points/PointsAutoSyncTest.php` (baru, 4 test) | Sync jalan saat belum pernah dipanggil; throttle diam kalau dipanggil 2x dalam 15 menit; tidak membangun ulang data lama (skenario reset); **test integrasi ujung-ke-ujung** — request HTTP ke halaman admin biasa (bukan `/admin/sync`) memicu sinkronisasi lewat `terminate()` |

**Catatan proses debugging:** test throttle sempat "gagal" saat pertama ditulis — ternyata bukan bug di `PointsAutoSync`, tapi kesalahan di test itu sendiri: `$pic->update([...])` dipanggil pada objek Eloquent yang atributnya masih nilai lama di memori (karena `run()` meng-update database lewat raw SQL, bukan lewat Eloquent), sehingga Eloquent menganggap "tidak ada perubahan" dan diam-diam tidak mengirim query UPDATE meski tetap mengembalikan `true`. Diperbaiki dengan `$pic->refresh()` sebelum update manual di test. Dikonfirmasi lewat debug langsung (`Cache::add()` terbukti mengembalikan `false` dengan benar pada panggilan kedua) sebelum menyimpulkan ini murni masalah test, bukan kode produksi.

**Diverifikasi:** full test suite (`tests/Feature/Points`, 51 test, 115 assertion) **PASS**, termasuk test integrasi HTTP nyata yang membuktikan `AdminMiddleware::terminate()` benar-benar terpanggil oleh Laravel.

**Cara memverifikasi setelah deploy:** cukup buka halaman admin apa saja (tidak harus `/admin/sync`), lalu buka `/admin/sync` — indikator "Auto-Sync Poin Otomatis" seharusnya langsung menunjukkan waktu baru saja, tanpa perlu menyetel apa pun di panel hosting.

## 41. Hapus Pesan Merah "Cron Belum Terdeteksi" di /admin/sync

**Tujuan:** user melihat kotak merah "Auto-Sync Poin Otomatis belum pernah terdeteksi berjalan... cron scheduler belum aktif..." masih tampil di production, lalu minta pesan itu dihapus karena mekanismenya sudah tidak lagi bergantung pada cron (lihat section #40 — fallback `AdminMiddleware::terminate()`). Pesan lama menyalahkan cron padahal cron memang sengaja tidak lagi dibutuhkan, sehingga berpotensi membuat user sibuk mengecek pengaturan Cron Job di hosting padahal tidak perlu.

**Solusi:** kotak peringatan merah (danger) untuk kondisi "belum pernah jalan" diganti jadi info netral (abu-abu) tanpa menyebut cron sama sekali — cukup menjelaskan bahwa auto-sync akan langsung jalan begitu ada admin membuka halaman admin mana pun. Peringatan "⚠ sudah lebih dari 20 menit, cron mungkin tidak aktif" pada kondisi *sudah pernah jalan tapi lama* juga dihapus karena alasan yang sama.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/sync/index.blade.php` | Hapus blok danger merah + peringatan ">20 menit" yang menyebut cron scheduler; ganti jadi info netral yang menjelaskan trigger otomatis lewat `AdminMiddleware` |
| `tests/Feature/Points/SyncPageRenderTest.php` | `test_sync_page_shows_warning_when_auto_sync_never_ran` diganti jadi `test_sync_page_shows_neutral_message_when_auto_sync_never_ran` — assert teks baru "belum pernah berjalan" dan pastikan kata "cron" sudah tidak ada di halaman |

**Catatan proses:** saat mulai mengerjakan ini, `git status` sempat menunjukkan file-file section #40 (`PointsAutoSync.php`, `AdminMiddleware::terminate()`, dll.) sebagai belum ter-commit — sempat dikira berarti belum pernah deploy. Setelah dicek ulang dengan `git log`, ternyata section #40 **sudah ter-commit duluan** di `b13c025` (di luar sesi chat ini, sebelum sesi ini lanjut) sehingga mekanismenya memang sudah aktif — konsisten dengan konfirmasi user bahwa indikator sempat menunjukkan status sehat.

**Diverifikasi:** full test suite `tests/Feature/Points` (13 test terkait, 34 assertion) **PASS** setelah penyesuaian assertion teks. Commit: `c2950b2`.

## 42. Hapus Hook `post-commit` Lama yang Menulis Log ke Root (Bukan `docs/tests/`)

**Tujuan:** commit section #41 memicu error `post-commit` hook ("syntax error in expression"). Ditelusuri, ternyata ada hook lama di `.git/hooks/post-commit` yang otomatis menulis ringkasan tiap commit ke `log-update-{tanggal}.md` di **root project** — bertentangan langsung dengan konvensi CLAUDE.md saat ini (log dipindah ke `docs/tests/` sejak 28 Juli 2026, dan file stub duplikat di root ini bahkan sudah pernah dihapus sekali sebelumnya di commit `daa5d1d` dengan alasan yang sama, tapi hook ini terus membuatnya lagi tiap kali commit).

**Root cause error:** baris `ENTRY_NUM=$(grep -c "^## [0-9]" "$LOG_FILE" 2>/dev/null || echo 0)` — kalau tidak ada baris yang cocok, `grep -c` tetap mencetak `0` ke stdout tapi keluar dengan exit code 1 (dianggap "tidak ditemukan"), sehingga `|| echo 0` ikut jalan juga. Hasilnya `ENTRY_NUM` berisi dua baris ("0\n0"), lalu `$((ENTRY_NUM + 1))` gagal karena bukan angka tunggal yang valid.

**Dampak:** dicek dengan `diff` antara isi file sebelum dan sesudah commit — **tidak ada kerusakan data**, karena error terjadi sebelum baris yang menulis (append) ke file, jadi hook gagal duluan sebelum sempat menulis apa pun. Cuma pesan error yang mengganggu di setiap commit.

**Solusi (dikonfirmasi user):** hapus hook-nya sepenuhnya (bukan diperbaiki) — karena log update sekarang sudah ditulis manual & terstruktur di `docs/tests/log-update-*.md` tiap sesi sesuai CLAUDE.md, mekanisme auto-generate dari commit message ini jadi duplikat yang keliru lokasinya dan sudah tidak diperlukan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `.git/hooks/post-commit` | Dihapus (bukan bagian dari repo/tidak di-*track* git, jadi tidak muncul di `git diff` — perubahan lokal saja) |
| `log-update-2026-07-28.md` (root) | Dihapus lagi — isinya cuma stub kosong tanpa entry berarti (hook selalu gagal sebelum sempat menulis apa pun) |

**Diverifikasi:** `git commit` berikutnya tidak lagi memicu error hook.
