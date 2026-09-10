# Log Update — 10 September 2026

## 1. Halaman Monitoring: Nama Jurnal Tampil Penuh + Volume/Nomor/Bulan/Tahun Lengkap

**Tujuan:** Di semua halaman Monitoring, nama jurnal dipotong `Str::limit(..., 20)` sehingga tampil
seperti "Jurnal Ilmiah Kedokt..." dan info slot cuma menampilkan "Vol.X No.Y" tanpa bulan & tahun.
User minta nama jurnal tampil **penuh** beserta **volume, nomor, bulan, dan tahun lengkap**, berlaku
untuk **semua halaman monitoring** (admin/PIC/marketing, normal & fasttrack).

**Perubahan:** Di sel kompak bawah `kode_submit` (dan kolom "Jurnal" khusus di beberapa view), nama
jurnal tidak lagi dipotong, dan format info slot diseragamkan jadi:
`{nama jurnal penuh}` <br> `Vol.{volume} No.{nomor} · {bulan} {tahun}`
(mis. "Jurnal Ilmiah Kedokteran Universitas Indonesia Raya / Vol.7 No.3 · Maret 2024"). `title`
tooltip yang dulu dipakai untuk melihat nama penuh saat hover dihapus karena sudah tidak perlu.

Filter dropdown "Jurnal" di atas tabel **sengaja dibiarkan** memendekkan nama (`<option>` dengan
nama 50+ karakter untuk ratusan jurnal tidak praktis) — bukan bagian yang diminta user (screenshot
user menunjuk baris data, bukan filter).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | Sel `sticky-first`: nama jurnal penuh (hapus `Str::limit(...,20)`), tambah `Vol.X No.Y · bulan tahun`; hapus `title` tooltip lama. |
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | Sel kompak: nama penuh + `Vol.X No.Y · bulan tahun`. |
| `resources/views/pic/submissions/monitoring.blade.php` | Sel kompak + kolom "Jurnal" terpisah: nama penuh + `Vol.X No.Y · bulan tahun`. |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sel kompak + kolom "Jurnal" terpisah: nama penuh + `Vol.X No.Y · bulan tahun`. |
| `resources/views/marketing/submissions-monitoring.blade.php` | Sel kompak + kolom "Jurnal" terpisah: nama penuh + `Vol.X No.Y · bulan tahun` (sebelumnya kolom Jurnal cuma `bulan/tahun` tanpa volume/nomor). |
| `resources/views/marketing/fasttrack/monitoring.blade.php` | Format info slot diseragamkan dari `Vol.X / No.Y / (bulan/tahun)` jadi `Vol.X No.Y · bulan tahun` (nama sudah penuh sebelumnya). |
| `tests/Feature/MonitoringJournalInfoFullTest.php` (baru) | 7 test: ke-6 halaman monitoring (admin/pic/marketing × normal/fasttrack) menampilkan nama jurnal penuh (string 50 karakter) + "Vol.7 No.3" + "Maret 2024"; plus 1 test khusus memastikan nama penuh muncul di baris data admin (bukan dipotong). |

### Verifikasi
- `php artisan test tests/Feature/MonitoringJournalInfoFullTest.php` → **7 passed (26 assertions)**.
- `php artisan view:clear` dijalankan supaya blade lama tidak ke-cache.
- Full regression suite `php artisan test tests/Feature` → **220 passed (625 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Tidak ada perubahan skema DB, controller, atau route — murni perubahan tampilan di 6 file blade.
- `admin/fasttrack/monitoring.blade.php` (file orphan, tidak dirender controller mana pun — rute
  `admin.fasttrack.monitoring` & `admin.fasttrack-management.monitoring.index` sama-sama mengarah ke
  `SubmissionController@fasttrackMonitoring` yang me-render `admin.fasttrack-management.monitoring.index`)
  **tidak diubah**.
- File monitoring lain yang tidak dipakai (`pic/submissions/monitoring-card.blade.php`,
  `pic/fasttrack/monitoring-backup.blade.php`, `monitoring-old.blade.php`,
  `monitoring.blade.php.backup`) tidak disentuh.

## 2. Halaman /admin/reviewers Diurutkan dari Total Point Tertinggi

**Tujuan:** User minta Daftar Reviewer (`/admin/reviewers`) diurutkan dari total point tertinggi.
Sebelumnya diurutkan `->latest()` (terbaru dibuat lebih dulu).

**Perubahan:** `->latest()` diganti `->orderByDesc('total_points')->orderBy('name')`. Kolom
`total_points` dipilih karena itulah nilai yang ditampilkan di kolom "Total Points" tabel — jadi
urutan baris sesuai dengan angka yang dilihat admin. Nama dipakai sebagai pemecah seri supaya
urutan konsisten antar halaman (list ini paginated, urutan wajib di sisi server). Export Excel
(`ReviewersExport`) ikut disamakan supaya file hasil ekspor urutannya sama dengan layar.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/ReviewerController.php` | `index()`: `->latest()` → `->orderByDesc('total_points')->orderBy('name')`. |
| `app/Exports/ReviewersExport.php` | `collection()`: `->orderBy('name')` → `->orderByDesc('total_points')->orderBy('name')` (samakan dengan halaman). |
| `tests/Feature/AdminReviewersSortByPointsTest.php` (baru) | 4 test: list urut dari poin tertinggi (reviewer poin tertinggi sengaja dibuat paling awal supaya ketahuan kalau masih `->latest()`), seri poin dipecah alfabetis, filter search tetap jalan, koleksi export ikut urut poin tertinggi. |

### Verifikasi
- `php artisan test tests/Feature/AdminReviewersSortByPointsTest.php` → **4 passed (11 assertions)**.
- Full regression suite `php artisan test tests/Feature` → **224 passed (636 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Tidak ada perubahan skema DB atau route — hanya urutan query.
- Catatan: `users.total_points` adalah kolom cache (sumber kebenaran sebenarnya `point_histories`
  EARNED − REDEEMED, lihat leaderboard reviewer 9 Sept). Untuk halaman daftar ini dipakai kolom
  cache-nya langsung supaya urutan = angka yang ditampilkan di kolom yang sama; kalau suatu saat
  angka "Total Points" di halaman ini diubah jadi dihitung dari riwayat, urutannya harus ikut
  disesuaikan.

## 3. Fix: 403 Saat "Kembali ke Admin" Setelah Login As Reviewer

**Tujuan:** User melapor: setelah admin klik "Login As" pada reviewer, lalu klik "Kembali ke Admin",
muncul halaman **403 Akses Ditolak** di `/admin/users/return-to-admin`.

**Akar masalah:** Rute `admin.users.return-to-admin` berada di dalam grup middleware `AdminMiddleware`.
Impersonasi reviewer/user biasa memakai `Auth::login()` di **guard web** (mengganti admin), jadi saat
impersonasi, `auth()->user()` adalah reviewer → `hasAdminAccess()` false → `AdminMiddleware` langsung
`abort(403)`. Jadi rute untuk KELUAR dari impersonasi justru butuh sudah jadi admin lagi — catch-22.
(Impersonasi PIC/Marketing tidak kena masalah ini karena pakai guard terpisah `pic`/`marketing`,
guard web tetap admin.)

Masalah kedua: `ReviewerController::loginAs` menyimpan key session `admin_impersonating`, sedangkan
`UserController::returnToAdmin` cuma membaca `admin_user_impersonating` — dan `layouts/app.blade.php`
cuma menampilkan tombol "Kembali ke Admin" kalau `admin_user_impersonating` ada (jadi lewat pintu
`/admin/reviewers` → Login As, tombolnya malah tidak muncul sama sekali).

**Perbaikan:**
- Rute return dipindah ke **luar grup admin** — `POST /return-to-admin` (name `impersonation.return`),
  hanya butuh middleware `auth`. Rute lama `admin.users.return-to-admin` dihapus (satu-satunya
  pemakainya cuma tombol di layout yang ikut diubah).
- `UserController::returnToAdmin()` sekarang menerima **kedua** key session
  (`admin_user_impersonating` ?? `admin_impersonating`), membersihkan keduanya, memakai
  `hasAdminAccess()` (bukan `isAdmin()`, supaya admin ber-role `pic_reviewer` juga bisa balik), dan
  redirect ke `admin.dashboard`.
- `layouts/app.blade.php`: banner "Mode Login As Aktif" + tombol "Kembali ke Admin" kini tampil kalau
  `admin_user_impersonating` ada **atau** (`admin_impersonating` ada **dan** user web saat ini bukan
  admin — supaya tidak bentrok dengan impersonasi PIC/Marketing yang juga memakai key itu). Tombol
  POST ke `impersonation.return`.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah `POST /return-to-admin` (`impersonation.return`) di grup `auth` (luar grup admin); hapus `admin.users.return-to-admin` dari grup admin. |
| `app/Http/Controllers/Admin/UserController.php` | `returnToAdmin()`: terima 2 key session, bersihkan keduanya, `hasAdminAccess()` gantikan `isAdmin()`, redirect `admin.dashboard`, no-session → redirect `login`. |
| `resources/views/layouts/app.blade.php` | Kondisi banner impersonasi + `action` tombol diarahkan ke `impersonation.return`; deteksi kedua key session. |
| `tests/Feature/ImpersonationReturnToAdminTest.php` (baru) | 6 test: rute return tidak lagi digated AdminMiddleware, jalan lewat kedua pintu (`UserController::loginAs` & `ReviewerController::loginAs`), tombol muncul di halaman reviewer saat impersonasi & mengarah ke rute baru, tanpa session impersonasi → redirect login (tidak crash), session menunjuk ID non-admin → logout, rute lama sudah tidak ada. |

### Verifikasi
- `php artisan test tests/Feature/ImpersonationReturnToAdminTest.php` → **6 passed (25 assertions)**.
- `php artisan route:list` — konfirmasi `impersonation.return` terdaftar sebagai `POST /return-to-admin`
  dan `admin.users.return-to-admin` sudah hilang.
- Full regression suite `php artisan test tests/Feature` → **230 passed (661 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Tidak ada perubahan skema DB.
- Impersonasi PIC & Marketing (`admin.pics.return-to-admin` / `admin.marketings.return-to-admin`)
  **tidak diubah** — sudah berfungsi karena pakai guard terpisah.

## 4. Fix Lanjutan: 405 Method Not Allowed di Path Return Lama

**Tujuan:** Setelah section #3 dideploy, user melapor error berubah jadi **405 Method Not Allowed**
("The POST method is not supported for route admin/users/return-to-admin. Supported methods: GET,
HEAD, PUT, PATCH, DELETE.") — dari tab browser / Blade yang ter-cache di server yang masih submit
form ke path lama `/admin/users/return-to-admin`.

**Akar masalah:** Section #3 menghapus rute `POST /admin/users/return-to-admin` sepenuhnya. Path itu
lalu "jatuh" ke `Route::resource('users')` (pola `users/{user}`, dengan `{user}` = "return-to-admin")
yang punya method GET/HEAD/PUT/PATCH/DELETE tapi **tidak POST** → 405. Klien lama (tab yang belum
di-refresh, atau compiled view di `storage/framework/views` yang belum di-`view:clear`) masih
menembak path itu.

**Perbaikan:** Tambah **alias kompatibilitas** — `POST /admin/users/return-to-admin` didaftarkan
lagi, **di luar grup `AdminMiddleware`** dan **sebelum grup admin** (supaya menang atas
`Route::resource('users')`), menunjuk ke `UserController::returnToAdmin` yang sama. Nama rute lama
`admin.users.return-to-admin` juga dipertahankan supaya compiled view lama yang memanggil
`route('admin.users.return-to-admin')` tidak `RouteNotFoundException` sebelum `view:clear` sempat
jalan saat deploy. View baru tetap pakai rute bersih `impersonation.return` (`POST /return-to-admin`).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah alias `POST /admin/users/return-to-admin` (name `admin.users.return-to-admin`, tanpa AdminMiddleware, sebelum grup admin) di samping `impersonation.return`. |
| `tests/Feature/ImpersonationReturnToAdminTest.php` | `test_old_admin_gated_route_no_longer_exists` diganti `test_both_new_and_legacy_route_names_exist`; tambah `test_legacy_path_still_accepts_post_without_405_or_403` (POST ke path lama persis → redirect dashboard, bukan 405/403). |

### Verifikasi
- `php artisan test tests/Feature/ImpersonationReturnToAdminTest.php` → **7 passed (29 assertions)**.
- `php artisan route:list --path=return-to-admin` → `POST /return-to-admin` (impersonation.return) &
  `POST /admin/users/return-to-admin` (admin.users.return-to-admin) dua-duanya terdaftar.
- Full regression suite `php artisan test tests/Feature` → **231 passed (665 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Tetap **disarankan** jalankan `php artisan view:clear && php artisan route:clear` di server setelah
  deploy, tapi sekarang bukan lagi syarat wajib — path lama sudah aman menerima POST walau ada
  klien/cache yang belum ter-refresh.
