# Log Update — 22 July 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Fix Foreign Key Violation Catatan — Root Cause Sebenarnya (`auth()->id()` Jatuh ke Guard `web`)

**Tujuan:** User melapor error FK violation yang SAMA persis masih terjadi di `https://portal.apji.org/marketing/submissions/14206/catatan` walau perbaikan sebelumnya (log-update-2026-07-21.md section #4, commit `2f960b9`) sudah di-deploy — kali ini dengan `user_id=7` (bukan `null` seperti yang diharapkan).

**Root cause:** Perbaikan sebelumnya cuma menghilangkan argumen ke-5 (`$userId`) saat memanggil `logHistory()`, dengan asumsi itu akan default ke `null`. Tapi `logHistory()` memakai `$userId ?? auth()->id()` — operator `??` di PHP **tidak bisa membedakan** "argumen tidak diisi" dari "argumen sengaja diisi `null`", jadi tetap jatuh ke `auth()->id()`. Masalahnya, `auth()->id()` tanpa guard eksplisit selalu cek guard DEFAULT (`web`, dikonfirmasi di `config/auth.php`) — kalau browser yang sama kebetulan juga punya sesi admin aktif di guard `web` (bersamaan dengan sesi marketing/PIC di guard lain), ID admin dari guard `web` itu ikut terpakai walau konteksnya marketing/PIC. Karena ID tersebut tidak selalu valid di konteks itu, insert tetap melanggar FK constraint yang sama.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/Submission.php` | `logHistory()`: default parameter `$userId` diganti dari `null` menjadi sentinel string `'__auto__'`. Sekarang `$resolvedUserId = $userId === '__auto__' ? auth()->id() : $userId;` — argumen yang benar-benar tidak diisi tetap auto-resolve lewat `auth()->id()` (perilaku lama, dipakai semua caller admin), sedangkan argumen yang sengaja diisi `null` sekarang benar-benar jadi `null` tanpa fallback apapun |
| `app/Http/Controllers/Marketing/DashboardController.php` | `updateCatatan()`: argumen ke-5 `logHistory()` sekarang eksplisit `null` (sebelumnya dihilangkan begitu saja, yang ternyata tidak cukup) |
| `app/Http/Controllers/Pic/JournalManagementController.php` | `updateCredential()` dan `requestRevision()`: sama — argumen ke-5 `logHistory()` sekarang eksplisit `null` |

**Diverifikasi lewat tinker (mereproduksi persis skenario production):** login sebagai admin VALID di guard `web` (id=1) BERSAMAAN dengan guard `marketing`/`pic` di sesi yang sama — dikonfirmasi `auth()->id()` memang resolve ke `1` dalam kondisi ini (membuktikan bug-nya nyata), tapi setelah perbaikan, `submission_histories.user_id` tetap `NULL` untuk jalur `Marketing::updateCatatan` maupun `Pic::updateCredential`. Semua pemanggilan `logHistory()` lain yang sudah ada sebelumnya (dari konteks admin, pakai `$adminUser->id` eksplisit) dicek ulang dan dikonfirmasi TIDAK terpengaruh — tetap aman seperti sebelumnya. Semua data uji dikembalikan/dihapus setelah verifikasi.

**Catatan:** tidak ada migration baru (migration enum dari log sebelumnya sudah cukup). Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`, TIDAK perlu `migrate` lagi.


## 2. 🔄 Update: Lengkapi changelog: detail perbaikan FK violation catatan (sentinel __auto__)

- **Commit:** `6eb2eee` — 10:10 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-07-22.md`

## 3. Fasilitas Ganti Reviewer yang Sudah Ditugaskan

**Tujuan:** User melihat halaman `/admin/assignments/52` dan minta ditambahkan fasilitas untuk mengganti reviewer yang sudah ditugaskan di suatu assignment (mis. reviewer tidak responsif/berhalangan), tanpa harus hapus assignment dan buat ulang dari awal.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah route `POST /admin/assignments/{assignment}/change-reviewer` → `assignments.change-reviewer` |
| `app/Http/Controllers/Admin/ReviewAssignmentController.php` | `show()`: tambah data `$reviewers` (semua user role reviewer) untuk dropdown pemilihan. Method baru `changeReviewer()`: validasi slot (1-5) & reviewer baru, tolak kalau reviewer baru sudah dipakai di slot lain pada assignment yang sama, tolak kalau reviewer di slot itu SUDAH mengirim hasil review (supaya hasil review tidak "berpindah pemilik" secara diam-diam), update `reviewer_{n}_id`/`reviewer_{n}_username`/`reviewer_{n}_password`, catat ke `ActivityLog` (event `reviewer_changed`), kirim notifikasi email penugasan ke reviewer baru |
| `resources/views/admin/assignments/show.blade.php` | Tambah tombol "Ganti Reviewer" di setiap blok info reviewer + modal per slot (dropdown reviewer baru, input username/password baru opsional). Kalau reviewer di slot itu sudah submit hasil review, modal menampilkan pesan blokir (tidak ada tombol submit) dan mengarahkan ke "Request Revision" |

**Diverifikasi:**
1. View di-render lewat tinker (dengan `Auth::login()` + `View::share('errors', ...)` supaya layout yang butuh konteks auth/session tetap bisa dirender di luar HTTP request) — berhasil, tombol & modal untuk tiap slot reviewer muncul.
2. Panggil `changeReviewer()` langsung: reviewer baru yang sudah dipakai di slot lain pada assignment yang sama → ditolak, `reviewer_id` tidak berubah.
3. Ganti ke reviewer baru yang valid → `reviewer_id`/`username`/`password` slot ter-update, `ActivityLog` (event `reviewer_changed`) tercatat dengan nama reviewer lama & baru.
4. Assignment yang reviewer-nya (di slot itu) sudah punya `ReviewResult` (hasil review submitted) → percobaan ganti ditolak dengan pesan yang mengarahkan ke "Request Revision".

Semua data uji (reviewer_id/username/password assignment, `ReviewResult` percobaan, `ActivityLog` percobaan) dikembalikan/dihapus setelah verifikasi.

**Catatan:** tidak ada migration baru (semua kolom `reviewer_{n}_id`/`username`/`password` sudah ada). Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`.


## 4. 🔄 Update: Tambah fasilitas ganti reviewer yang sudah ditugaskan

- **Commit:** `fe060a3` — 17:51 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/ReviewAssignmentController.php`
- `log-update-2026-07-22.md`
- `resources/views/admin/assignments/show.blade.php`
- `routes/web.php`

## 5. Pindahkan Menu "Setting Point Reviewer" ke Grup Reviewer

**Tujuan:** User menunjukkan screenshot sidebar grup "REVIEWER" (Penugasan Review, Daftar Reviewer, Permintaan Review, Perpanjangan Waktu, Papan Peringkat) dan minta menu setting point reviewer punya tempat sendiri di situ "biar terkelompok dengan baik" — sebelumnya menu ini ("Pengaturan Point Reviewer") nyempil di accordion "Laporan Point" (menu Laporan), bercampur dengan Point Marketing, Point PIC, dll yang topiknya beda.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/partials/sidebar.blade.php` | Pindahkan link `admin.point-settings.index` (nama ditampilkan jadi "Setting Point Reviewer") dari accordion "Laporan Point" ke grup "Reviewer" (setelah "Papan Peringkat"). Route & controller (`PointSettingController`) tidak berubah sama sekali — cuma posisi menu di sidebar |

**Diverifikasi:** render sidebar lewat tinker — link "Setting Point Reviewer" muncul persis 1 kali di lokasi baru, teks lama "Pengaturan Point Reviewer" sudah tidak ada lagi di manapun (0 kemunculan), route `admin.point-settings.index` tetap resolve dengan benar.

**Catatan:** murni perubahan tampilan sidebar (tidak ada migration, tidak ada perubahan logika).


## 6. 🔄 Update: Pindahkan menu Setting Point Reviewer ke grup Reviewer di sidebar

- **Commit:** `8483ff4` — 18:17 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-07-22.md`
- `resources/views/admin/partials/sidebar.blade.php`

## 7. Ubah Perhitungan Point Reviewer: Flat 10 Poin per Review Selesai (Bukan Lagi per Hari)

**Tujuan:** User minta perhitungan point reviewer di `https://portal.apji.org/admin/point-settings` diubah — sebelumnya berdasarkan lama hari pengerjaan (skala turun: 1 hari=10, 2 hari=8, 3 hari=7, 4 hari=6, 5+ hari=5 poin), sekarang setiap review yang selesai dapat poin yang SAMA yaitu 10, berapa pun lama hari pengerjaannya.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/ReviewAssignment.php` | `awardPointsToAllReviewers()`: poin sekarang diambil flat dari setting `points_per_review` (bukan lagi `PointDaySetting::getPointsByDays()`). Lama hari pengerjaan tetap dihitung tapi cuma untuk teks keterangan di riwayat poin, tidak lagi mempengaruhi jumlah poin |
| `app/Http/Controllers/Admin/PointSettingController.php` | Default `points_per_review` diubah dari 5 jadi 10. `update()`: field `day_points` (skala per-hari) dihapus dari validasi & penyimpanan, diganti validasi `points_per_review` |
| `resources/views/admin/point-settings/index.blade.php` | Bagian "Point Review Berdasarkan Lama Hari Selesai" (form per-bracket 1-5 hari) diganti jadi 1 input tunggal "Point per Review Selesai". Bagian "Hasil Perhitungan (Contoh)" disederhanakan jadi 1 hasil (bukan 3 skenario hari berbeda) |

**Catatan implementasi:** tabel & model `PointDaySetting` (skala lama per-hari) sengaja TIDAK dihapus — sudah tidak dipakai di kode manapun, tapi datanya dibiarkan ada di database (tidak ada migration drop table) supaya tidak ada risiko kehilangan data histori kalau suatu saat dibutuhkan lagi.

**Diverifikasi lewat tinker:**
1. Halaman setting di-render lewat controller asli — form baru "Point / Review" tampil, setting `points_per_review` di database sudah bernilai `10`.
2. `awardPointsToAllReviewers()` dipanggil langsung pada assignment yang sudah berumur 189 hari (jauh di atas 5 hari, dulunya cuma dapat 5 poin) → SEKARANG kedua reviewer (utama & pendamping) tetap dapat **10 poin flat masing-masing**, `completed_reviews` naik 1, `PointHistory` tercatat dengan keterangan "selesai dalam 189 hari" (informasi hari tetap ada, tapi tidak lagi mengurangi poin).

Semua data uji (poin, `completed_reviews`, baris `PointHistory`) dikembalikan ke 0/kosong setelah verifikasi.

**Catatan deploy:** tidak ada migration baru. Deploy cukup `git pull origin master` + `php artisan view:clear`/`cache:clear`.

## 8. Backfill Riwayat Poin Lama ke Flat 10 (Retroaktif)

**Tujuan:** User menunjukkan screenshot "Riwayat Points" di production yang masih memperlihatkan poin lama (+5) untuk review yang sudah selesai SEBELUM section #7 di-deploy — perubahan flat-10 hanya berlaku untuk review yang di-approve SETELAH deploy, riwayat lama tidak otomatis ikut berubah. Diminta (dan dikonfirmasi lewat pilihan eksplisit user): update SEMUA riwayat poin lama ke 10, dan sesuaikan `total_points`/`available_points` reviewer terkait dengan selisihnya.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_22_000001_backfill_flat_points_per_review.php` (baru) | Migration data (bukan perubahan skema): cari semua `point_histories` dengan `type=EARNED`, `review_assignment_id` terisi (khusus poin review, bukan penyesuaian manual admin lewat `PointManagementController`), dan `points != 10` → set `points` jadi 10, tambahkan selisihnya (`10 - poin_lama`) ke `total_points` & `available_points` milik reviewer terkait. Dibungkus 1 transaction, dan mencatat ringkasan (jumlah baris + detail per baris) ke `storage/logs/laravel.log` untuk jejak audit. `down()` sengaja no-op (nilai poin lama per baris tidak disimpan di tempat lain setelah backfill, jadi tidak ada cara aman untuk rollback presisi — migration ini memang dimaksudkan sebagai koreksi satu arah) |

**Diverifikasi lewat migration sungguhan (bukan cuma baca kode):** buat 1 baris `point_histories` tiruan (points=5, `review_assignment_id` terisi, meniru data lama sebelum section #7) + tambahkan manual +5 ke `total_points`/`available_points` reviewer terkait (meniru kondisi SEBELUM backfill) → jalankan `php artisan migrate` sungguhan → baris tersebut jadi `points=10`, `total_points`/`available_points` reviewer bertambah 5 (selisih yang benar), dan log `Backfill flat 10 poin/review selesai` tercatat dengan detail baris yang diubah. Data uji dihapus/dikembalikan (`total_points`/`available_points` kembali ke semula) setelah verifikasi — migration sendiri TIDAK di-rollback (memang tidak reversible by design, dan tabel `migrations` lokal vs production terpisah sehingga status "sudah dijalankan" di lokal tidak mempengaruhi production).

**Catatan deploy:** migration ini **WAJIB** dijalankan di production — `git pull origin master` lalu `php artisan migrate --force`. Cek `storage/logs/laravel.log` setelah migrate untuk melihat ringkasan berapa baris & reviewer yang ter-koreksi.


## 8. 🔄 Update: Ubah perhitungan point reviewer jadi flat 10/review, bukan per hari

- **Commit:** `7f21076` — 18:27 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/PointSettingController.php`
- `app/Models/ReviewAssignment.php`
- `log-update-2026-07-22.md`
- `resources/views/admin/point-settings/index.blade.php`

