# Log Update — 9 July 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Fix Poin Reviewer Tidak Sinkron di Leaderboard (`/admin/leaderboard`)

**Tujuan:** User minta sinkronkan poin reviewer di halaman leaderboard.

**Root cause:** `LeaderboardController::buildLeaderboard()` menghitung `total_points_earned` lewat `withSum('pointHistories as total_points_earned', 'points')` **tanpa filter `type`** — baik transaksi `EARNED` maupun `REDEEMED` sama-sama disimpan sebagai angka `points` positif (lihat `PointManagementController::store()`), jadi keduanya ikut terjumlah bersama. Akibatnya reviewer yang pernah menukar reward (redeem) tampil "total poin"-nya lebih besar dari yang sebenarnya (poin earn + poin redeem, bukan cuma poin earn). `current_points` juga sebelumnya diambil langsung dari kolom cache `users.available_points`, bukan dihitung ulang dari riwayat transaksi.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LeaderboardController.php` | `buildLeaderboard()`: `total_points_earned` sekarang di-filter `type='EARNED'` saja; tambah `total_points_redeemed` (filter `type='REDEEMED'`); `current_points` dihitung `total_points_earned - total_points_redeemed` langsung dari `point_histories` (sumber kebenaran), bukan dari kolom cache `available_points` |

**Diverifikasi:** simulasi lewat tinker — reviewer uji diberi riwayat EARNED 100 + REDEEMED 30. Sebelum fix, `total_points_earned` akan tampil 130 (salah, 100+30 ikut terjumlah). Setelah fix: `total_points_earned` = 100 (benar), `current_points` = 70 (benar, 100-30). Data uji dibersihkan setelah verifikasi.

**Catatan:** halaman leaderboard di-cache 5 menit (`Cache::remember(..., 300, ...)`) per tenant — setelah deploy, hasil baru mungkin baru muncul maksimal 5 menit kemudian, atau langsung kalau cache di-clear manual (`php artisan cache:clear`).

## 2. 🔄 Update: Fix reviewer points synchronization in leaderboard by filtering earned and redeemed points

- **Commit:** `75a5a1d` — 21:05 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/LeaderboardController.php`
- `log-update-2026-07-09.md`

## 3. Root Cause Sebenarnya: Reviewer Pendamping (Reviewer 2-5) Tidak Pernah Dapat Poin

**Tujuan:** Setelah fix #1-2 dideploy, leaderboard TETAP menampilkan 0 poin/0 review untuk reviewer yang ditampilkan. Investigasi lebih lanjut menemukan akar masalah yang sesungguhnya jauh lebih besar dari sekadar bug perhitungan sum.

**Root cause:** `ReviewAssignment::approve()` cuma memberi poin, menambah `completed_reviews`, dan mencatat `PointHistory` ke **reviewer utama** (`reviewer_id`) — reviewer pendamping (`reviewer_2_id` s.d. `reviewer_5_id`) **tidak pernah dapat apa-apa sama sekali**, walau mereka juga ikut mengerjakan review yang sama. Dicek di data lokal: SEMUA review assignment (6/6) punya `reviewer_2_id` terisi — artinya fitur multi-reviewer ini rutin dipakai, dan reviewer yang SELALU jadi reviewer pendamping (bukan reviewer utama) akan permanen menunjukkan 0 poin/0 review di leaderboard, persis seperti yang terlihat di screenshot.

**Temuan tambahan (bug terpisah, ikut mempengaruhi):** `User::checkAndAwardBadges()` query `Badge::where('required_reviews', ...)`, tapi kolom asli di tabel `badges` ternyata `required_points` (migration filenya bilang `required_reviews`, tapi skema database sebenarnya beda — kemungkinan migration diedit setelah pernah dijalankan). Ini bikin `approve()` **selalu throw exception** di baris terakhir — kalau tidak diamankan, ini akan menghentikan proses pemberian poin ke reviewer berikutnya dalam satu assignment yang sama (jadi robek balik potongan fix reviewer pendamping di atas kalau tidak ditangani).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/ReviewAssignment.php` | `approve()` dipecah: hitung poin sekali, lalu loop ke SEMUA reviewer via `assignedReviewerIds()` (reviewer utama + 2-5, dedup, filter null) lewat `awardPointsToAllReviewers()`; `awardPointsTo()` idempoten (skip kalau `PointHistory` untuk reviewer+assignment itu sudah ada) dan sekarang return bool; `checkAndAwardBadges()` dibungkus try/catch supaya kegagalannya (bug kolom `required_reviews`/`required_points`) tidak menghentikan pemberian poin ke reviewer lain |
| `app/Http/Controllers/Admin/LeaderboardController.php` | `total_reviews`/`pending_reviews` sebelumnya cuma hitung assignment di mana user jadi reviewer UTAMA — diganti subquery (`selectSub`) yang cek KELIMA kolom reviewer (`reviewer_id` s.d. `reviewer_5_id`) via `whereColumn` |
| `app/Console/Commands/SyncReviewerPoints.php` (baru) | Command `php artisan reviewers:sync-points` (dengan opsi `--dry-run`) untuk sinkronisasi RETROAKTIF — memberi poin yang terlewat ke reviewer pendamping pada assignment yang SUDAH APPROVED sebelum fix ini ada, tanpa mengubah data yang sudah benar |

**Diverifikasi lewat tinker (skenario nyata, bukan cuma baca kode):**
1. Assignment baru dengan reviewer utama + 1 reviewer pendamping → `approve()` → **keduanya** dapat poin & `completed_reviews` (sebelumnya cuma reviewer utama).
2. Leaderboard: reviewer yang HANYA jadi reviewer pendamping (`reviewer_2_id`) pada assignment APPROVED sekarang tampil `total_reviews = 1` (sebelumnya 0).
3. Simulasi data historis (assignment APPROVED lama, reviewer utama sudah dapat poin dari "bug lama", reviewer pendamping belum) → `reviewers:sync-points --dry-run` benar mendeteksi 1 entri yang kurang → dijalankan tanpa `--dry-run` → reviewer pendamping dapat poinnya → dijalankan lagi → 0 entri baru (idempoten, tidak dobel).

**Perlu dijalankan di production setelah deploy:** `php artisan reviewers:sync-points --dry-run` dulu untuk lihat berapa yang akan disinkronkan, lalu `php artisan reviewers:sync-points` tanpa `--dry-run` untuk benar-benar menyimpannya.

**Belum diperbaiki (di luar cakupan permintaan ini, cuma dimitigasi):** ketidakcocokan kolom `required_reviews` vs `required_points` di tabel `badges` — sudah tidak lagi menggagalkan pemberian poin (dibungkus try/catch + log), tapi badge memang tidak akan pernah otomatis ke-assign sampai ini diperbaiki terpisah. Perlu konfirmasi nama kolom yang benar di production sebelum diperbaiki, karena migration file dan skema aktual ternyata tidak cocok.

## 4. 🔄 Update: Fix reviewer points synchronization by awarding points to all assigned reviewers and updating leaderboard calculations

- **Commit:** `aa21db0` — 21:27 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Console/Commands/SyncReviewerPoints.php`
- `app/Http/Controllers/Admin/LeaderboardController.php`
- `app/Models/ReviewAssignment.php`
- `log-update-2026-07-09.md`


## 5. 🔄 Update: a

- **Commit:** `6d1543b` — 21:29 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-07-09.md`

## 6. Ranking Leaderboard Diubah Jadi Berdasarkan Poin (bukan Tier Reward)

**Tujuan:** User minta sinkronkan "rangking point" di `/admin/leaderboard`. Setelah fix poin di section #3 dijalankan, poin masing-masing reviewer sudah benar, tapi kolom **Rank** tetap tidak nyambung dengan kolom **Points** di baris yang sama — karena rank dihitung dari `tier_score` (poin dari reward yang sudah ditukar: Platinum=1000/Gold=100/Silver=10/Bronze=1), bukan dari poin reviewer itu sendiri. Selama belum ada reviewer yang redeem reward, `tier_score` semua orang = 0, jadi urutan rank jadi acak walau Points-nya beda-beda — ini yang bikin ranking terlihat "belum sinkron". Dikonfirmasi ke user: pilihannya diubah total ke ranking berbasis poin (rank #1 = poin tertinggi), bukan cuma dijadikan tie-breaker.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LeaderboardController.php` | `buildLeaderboard()`: sort diganti dari `sortByDesc('tier_score')` ke `sortByDesc('current_points')`. `tier_score` tetap dihitung (dipakai buat badge jumlah Platinum/Gold/Silver/Bronze di tabel), tapi bukan lagi dasar urutan rank |
| `resources/views/admin/leaderboard/index.blade.php` | Judul card diganti "Peringkat Reviewer Berdasarkan Poin", badge header jadi "Diurutkan berdasarkan poin tertinggi"; card "Cara Perhitungan Rank" ditulis ulang menjelaskan ranking berbasis poin; badge di card "Top 3 Performers" diganti dari jumlah reward jadi jumlah poin (`current_points`), supaya konsisten dengan dasar ranking yang baru |

**Diverifikasi lewat tinker:** 3 reviewer diberi riwayat poin buatan (500, 200-80=120, 50) → hasil `buildLeaderboard()` mengurutkan rank #1/#2/#3 sesuai urutan poin (500 > 120 > 50), bukan tier_score (yang sama-sama 0 untuk ketiganya). Data uji dihapus setelah verifikasi.

**Catatan:** halaman leaderboard di-cache 5 menit (`Cache::remember(..., 300, ...)`) per tenant — setelah deploy, jalankan `php artisan cache:clear` di production supaya hasil baru langsung terlihat, tanpa perlu menunggu 5 menit.

