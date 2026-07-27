# Log Update — 27 July 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Fix Poin PIC/Marketing Dobel Saat Validasi Diulang

**Tujuan:** User menanyakan apakah poin PIC & Marketing di `https://portal.apji.org/pic/points` bisa auto-update tanpa perlu klik sinkron (sinkron tetap ada khusus admin). Investigasi menemukan poin SUDAH otomatis ter-update saat pekerjaan selesai — sinkron memang sudah cuma jaring pengaman, bukan satu-satunya jalur. Tapi ditemukan celah nyata: beberapa jalur pemberian poin masih pakai `PicPointHistory::create()`/`MarketingPointHistory::create()` langsung, bukan helper idempoten `awardPoints()` yang sudah ada — kalau PIC meng-unvalid lalu meng-valid ulang suatu tahap (mis. membetulkan kesalahan klik), poin bisa dobel karena guard `$oldValue != true` di `toggleValidation()` tidak melindungi skenario ini (oldValue kembali jadi false setelah di-unvalid).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | `toggleValidation()`: ganti `PicPointHistory::create()` + update manual `total_points` jadi `PicPointHistory::awardPoints()` (idempoten). Bagian poin Marketing (production_valid) ganti dari `firstOrCreate()` jadi `MarketingPointHistory::awardPoints()` (fungsinya sama, konsisten 1 helper). PIC punya `fasttrackStore()`: pola yang sama diganti jadi `PicPointHistory::awardPoints()` |
| `app/Http/Controllers/Admin/SubmissionController.php` | Admin punya `fasttrackStore()`: ganti `PicPointHistory::create()` & `MarketingPointHistory::create()` jadi versi `awardPoints()` masing-masing |

**Diverifikasi lewat tinker (reproduksi skenario dobel-poin sungguhan):** validasi `editor1` sebuah submission → poin PIC +1, 1 baris riwayat. Un-validate lalu validate ulang → SEBELUM fix akan dobel poin; SESUDAH fix, poin PIC TETAP sama, tetap 1 baris riwayat. Test terpisah untuk `MarketingPointHistory::awardPoints()`: panggil 2x berturut-turut untuk pasangan marketing+submission yang sama → panggilan ke-2 return `null`, tidak nambah poin. Semua data uji dikembalikan ke semula setelah verifikasi.

**Catatan:** tidak ada migration baru untuk perbaikan ini. Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`.

## 2. Fix Poin Marketing Bisa Turun Diam-Diam (Formula COUNT Submission vs SUM Riwayat)

**Tujuan:** User minta dicek ulang apakah poin PIC & Marketing sudah benar-benar up-to-date & konsisten di semua laporan admin terkait poin. Audit menyeluruh (bukan cuma baca kode — dicek langsung ke database) menemukan: poin PIC SUDAH konsisten di semua jalur (semua pakai SUM `pic_point_histories`, cuma 1 sisa drift historis di 1 PIC — sudah dikoreksi lewat tombol Sinkron yang sudah ada). Poin Marketing TIDAK konsisten — 3 jalur (`MarketingPointHistory::awardPoints()`, `Marketing::syncPoints()`, tombol Sinkron di `/admin/marketing-points`) menghitung ulang `total_points` dari **COUNT submission** (asumsi "1 submission = 1 poin" selalu berlaku), sementara 2 jalur lain (`runBulkSync()`, `adjustPoints()`) sudah benar pakai **SUM riwayat** `marketing_point_histories`. Kedua formula cuma cocok kalau rate poin per submission (`TaskPointSetting`) tidak pernah berubah — tapi rate itu TERBUKTI pernah berubah (marketing "Wandi": 2 submission bernilai 20 poin di riwayat, karena rate-nya dulu 10/submission, bukan 2). Yang lebih serius: `Marketing::syncPoints()` otomatis jalan SETIAP KALI marketing buka halaman poin sendiri (`/marketing/points`) — artinya bug ini bisa diam-diam menimpa poin yang benar jadi lebih rendah kapan saja tanpa perlu klik apa pun.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/Marketing.php` | `getActualPoints()`/`syncPoints()`: ganti dari `submissions()->count()` jadi `pointHistories()->sum('points_earned')` |
| `app/Models/MarketingPointHistory.php` | `awardPoints()`: baris sync `total_points` diganti dari COUNT submission jadi SUM riwayat |
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | `syncAllPoints()`: total_points dihitung dari SUM riwayat (bukan COUNT), backfill riwayat yang hilang pakai rate `TaskPointSetting` saat ini (bukan hardcode 1). `index()`: urutan leaderboard diubah dari `submissions_count` jadi `total_points` (kolom yang sama yang ditampilkan — sebelumnya bisa beda urutan dari angka yang ditampilkan), dan tile "Total Points" dihitung dari SUM riwayat asli (bukan disamakan dengan jumlah submission) |
| `app/Http/Controllers/Admin/SyncController.php` | `gatherStats()`, `syncMarketingPoints()`, `syncAll()`, `countOutOfSync()`: semua bagian marketing diganti dari COUNT submission jadi SUM `marketing_point_histories` |
| `app/Http/Controllers/Marketing/DashboardController.php` | Perbarui komentar yang sudah tidak akurat soal "1 submission = 1 point" |
| `database/migrations/2026_07_27_000001_fix_marketing_points_count_to_sum_formula.php` (baru) | Migration data: koreksi `total_points` semua marketing SEKARANG dari SUM riwayat yang sebenarnya (supaya production langsung benar begitu deploy, tidak bergantung admin ingat klik Sinkron). Mencatat ringkasan ke `storage/logs/laravel.log`. `down()` sengaja no-op (nilai lama memang salah, tidak ada gunanya dikembalikan) |

**Diverifikasi lewat data nyata (bukan cuma baca kode):** cek SEMUA 46 PIC & 6 marketing terhadap SUM riwayat masing-masing sebelum ubah kode — ditemukan Wandi (marketing_id=1): cached=20 vs formula-COUNT=2 vs SUM-riwayat=20 (formula COUNT akan salah kalau di-sync). Setelah fix: `getActualPoints()`/`syncPoints()`/`syncMarketingPoints()`/`syncAllPoints()` semua mengembalikan 20 (bukan 2) untuk Wandi. Jalankan sync ulang untuk SEMUA marketing & PIC lewat method controller asli → 0 dari 6 marketing & 0 dari 46 PIC out-of-sync (dicek lewat `SyncController::gatherStats()`). Migration diuji dengan reproduksi persis bug lama (paksa `total_points` marketing_id=1 jadi 2, simulasi hasil formula COUNT) → migration mengoreksi balik ke 20, log tercatat dengan detail baris yang diubah.

**Catatan deploy:** migration ini **WAJIB** dijalankan di production — `git pull origin master` lalu `php artisan migrate --force`. Cek `storage/logs/laravel.log` untuk ringkasan marketing mana saja yang terkoreksi. Setelah ini, poin PIC & Marketing sudah benar-benar konsisten di semua laporan admin (`/admin/pic-points`, `/admin/marketing-points`, `/admin/sync`, `/admin/point-rankings`) dan tidak akan lagi diam-diam turun.



## 3. 🔄 Update: Fix poin Marketing bisa turun diam-diam (formula COUNT vs SUM riwayat)

- **Commit:** `4ff8fd5` — 19:33 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Http/Controllers/Admin/MarketingPointReportController.php`
- `app/Http/Controllers/Admin/SyncController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Models/Marketing.php`
- `app/Models/MarketingPointHistory.php`
- `database/migrations/2026_07_27_000001_fix_marketing_points_count_to_sum_formula.php`
- `log-update-2026-07-27.md`

## 4. Sembunyikan Tombol "Sync Point" di Navbar PIC

**Tujuan:** User menunjukkan screenshot tombol "Sync Point" yang selalu tampil di navbar atas setiap halaman PIC (`/pic/dashboard` dst.) dan minta tombol itu disembunyikan, dengan syarat poin PIC tetap benar-benar ter-update otomatis tanpa perlu klik apa pun. Berdasarkan perbaikan sebelumnya (section #1 hari ini) yang sudah memastikan poin PIC ter-update otomatis & aman dari dobel-poin persis saat tugas diselesaikan, tombol sync di navbar (yang selalu terlihat di SEMUA halaman) tidak lagi diperlukan sebagai jalur utama — cuma jadi jaring pengaman untuk kasus langka.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/layouts/app.blade.php` | Hapus tombol "Sync Point" dari navbar (tampil di semua halaman PIC) beserta JS/CSS animasi loading-nya yang jadi tidak terpakai. Tombol "Sync & Logout" di modal logout dan tombol "Refresh Point" di halaman `/pic/points` sendiri TIDAK dihapus — tetap tersedia sebagai jalur manual kalau PIC butuh, cuma tidak lagi dipaksakan tampil di setiap halaman |

**Diverifikasi lewat tinker:** render halaman `/pic/dashboard` lewat controller asli — teks "Sync Point" sudah 0 kemunculan (navbar), halaman tetap render normal. Render halaman `/pic/points` — tombol "Refresh Point" masih ada (1 kemunculan), tidak terpengaruh perubahan ini.

**Catatan:** murni perubahan tampilan (navbar), tidak ada perubahan logika/route/migration. Rute `pic.points.sync` & controllernya tidak disentuh sama sekali — cuma tidak lagi ditaruh di navbar.

**Temuan tambahan, sudah ditindaklanjuti di section #5 di bawah:** ada widget terpisah "Pengingat Sinkronisasi Point" yang muncul sebagai banner sekali sehari di dashboard admin/marketing/PIC — user konfirmasi untuk dihapus juga di dashboard marketing & PIC.

## 5. Hapus Banner "Pengingat Sinkronisasi Point" di Dashboard Marketing & PIC

**Tujuan:** Lanjutan dari temuan section #4 — banner "Pengingat Sinkronisasi Point" (muncul sekali sehari, dismissable) di dashboard marketing & PIC menyarankan "lakukan sinkronisasi secara rutin agar data akurat", padahal poin sudah auto-update & idempoten sejak perbaikan hari ini. User konfirmasi untuk dihapus.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/author/dashboard.blade.php` | Hapus `@include('partials.sync-point-reminder', ...)` yang menyarankan PIC sync poin sendiri secara rutin |
| `resources/views/marketing/dashboard.blade.php` | Hapus `@include('partials.sync-point-reminder', ...)` yang sama untuk marketing |

**Sengaja TIDAK dihapus:** versi di `resources/views/admin/dashboard.blade.php` — banner itu cuma mengarahkan admin ke halaman laporan poin PIC (`admin.pic-points.index`), bukan mengklaim data admin sendiri "tidak akurat kalau tidak disinkronkan", jadi tetap relevan sebagai pengingat admin untuk sesekali meninjau laporan. Partial `resources/views/partials/sync-point-reminder.blade.php` sendiri tidak dihapus karena masih dipakai admin.

**Diverifikasi lewat tinker:** render dashboard PIC & Marketing lewat controller asli — teks "Pengingat Sinkronisasi Point" sudah 0 kemunculan di keduanya. Render dashboard admin — banner itu masih ada (1 kemunculan), tidak terpengaruh.

**Catatan:** murni perubahan tampilan, tidak ada perubahan logika/route/migration.




## 4. 🔄 Update: Update log to reflect fix for Marketing points calculation (COUNT vs SUM)

- **Commit:** `3bc0693` — 20:12 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-07-27.md`


## 6. 🔄 Update: Sembunyikan tombol Sync Point di navbar PIC

- **Commit:** `0b63e2c` — 20:29 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-07-27.md`
- `resources/views/pic/layouts/app.blade.php`

