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
