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
