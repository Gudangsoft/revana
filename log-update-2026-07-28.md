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

