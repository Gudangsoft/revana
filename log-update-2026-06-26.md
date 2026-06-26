# Log Update — 26 Juni 2026

## 1. Fix HTTP 500 pada Halaman Marketing Points

**Tujuan:** Memperbaiki error 500 di `/admin/marketing-points` dan bug `submission_id` NULL

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | Sederhanakan query index (hapus `with('submissions')` yang memuat semua data), ganti `withSum` kompleks dengan pendekatan yang lebih aman, fix `adjustPoints()` dengan validation `numeric` dan recalculate total dari sum |
| `resources/views/admin/marketing-points/index.blade.php` | Pindahkan modal Adjust Point ke dalam `@section('content')` (sebelumnya modal berada di luar section, menyebabkan Blade rendering error) |
| `database/migrations/2026_06_26_093326_make_submission_id_nullable_in_marketing_point_histories.php` | Migration baru: ubah `submission_id` menjadi nullable untuk mendukung penyesuaian point manual (tanpa submission) |

## 2. Tambah Fitur Edit Metadata LOA

**Tujuan:** Admin dapat mengedit nama penulis, afiliasi, judul, dan tanggal LOA langsung dari halaman preview LOA tanpa harus masuk ke halaman edit submission penuh

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_26_114409_add_tanggal_loa_to_submissions_table.php` | Migration baru: tambah kolom `tanggal_loa` (date, nullable) ke tabel submissions untuk override tanggal per submission |
| `app/Models/Submission.php` | Tambah `tanggal_loa` ke fillable dan casts sebagai date |
| `app/Http/Controllers/Admin/LoaController.php` | Tambah method `updateMetadata()`, update prioritas tanggal di `show()`, `publicView()`, dan `showMarketing()` (URL param → tanggal_loa → journal default), tambah import `Request` |
| `routes/web.php` | Route baru: `POST /submissions/{submission}/loa-metadata` → `LoaController@updateMetadata` |
| `resources/views/admin/loa/receipt.blade.php` | Ganti link "Edit Afiliasi & Data" dengan tombol modal, tambah modal dark-themed dengan 4 field yang dapat diedit (nama penulis, afiliasi, judul, tanggal LOA), notifikasi sukses via JS |


## 3. Tambah Fitur Login As di Halaman Users

**Tujuan:** Admin dapat masuk/impersonate sebagai akun user (reviewer/non-admin) untuk melihat sistem dari sudut pandang pengguna tersebut

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/UserController.php` | Tambah method `loginAs()` (cek non-admin, simpan sesi, `Auth::login`) dan `returnToAdmin()` (login balik ke admin asli), tambah import `Auth` |
| `routes/web.php` | Route baru: `POST /users/return-to-admin` dan `POST /users/{user}/login-as` (keduanya sebelum resource route agar tidak bentrok wildcard) |
| `resources/views/admin/users/index.blade.php` | Tambah tombol Login As (hijau, ikon person-check) di setiap baris, hanya tampil untuk user non-admin |
| `resources/views/layouts/app.blade.php` | Tambah banner biru "Mode Login As Aktif" dengan tombol "Kembali ke Admin" — tampil saat session `admin_user_impersonating` aktif |

## 4. Tambah Role PIC Reviewer + Dashboard Khusus

**Tujuan:** Role baru `pic_reviewer` dengan akses terbatas ke halaman manajemen reviewer dan jurnal — sidebar & dashboard sendiri, tanpa akses ke menu sensitif admin

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/User.php` | Tambah method `isPicReviewer()` dan `hasAdminAccess()` (admin OR pic_reviewer) |
| `app/Http/Middleware/AdminMiddleware.php` | Gunakan `hasAdminAccess()` agar pic_reviewer bisa masuk admin routes |
| `app/Http/Controllers/Auth/LoginController.php` | Redirect `pic_reviewer` ke `/admin/pic-reviewer/dashboard` setelah login |
| `app/Http/Controllers/Admin/UserController.php` | Tambah `pic_reviewer` ke validasi role, redirect loginAs ke pic-reviewer dashboard |
| `app/Http/Controllers/Admin/PicReviewerDashboardController.php` | Controller baru: dashboard dengan stats reviewer, distribusi submission, menu akses cepat |
| `resources/views/admin/pic-reviewer/dashboard.blade.php` | View dashboard PIC Reviewer: stat cards (reviewer, pending, selesai, permintaan), distribusi submission, grid menu cepat, tabel pending review |
| `resources/views/admin/partials/sidebar-pic-reviewer.blade.php` | Sidebar khusus: Data Jurnal, Jurnal Normal/Fasttrack/BKD/JAFA, Penugasan Review, Daftar Reviewer, Permintaan Review, Papan Peringkat |
| `resources/views/layouts/app.blade.php` | Override sidebar: jika user adalah `pic_reviewer`, selalu gunakan sidebar-pic-reviewer (di semua halaman admin) |
| `resources/views/admin/users/index.blade.php` | Badge ungu untuk role `pic_reviewer`, Login As juga support redirect ke pic-reviewer dashboard |
| `resources/views/admin/users/create.blade.php` | Tambah opsi PIC Reviewer di dropdown role |
| `resources/views/admin/users/edit.blade.php` | Tambah opsi PIC Reviewer di dropdown role |
| `routes/web.php` | Route baru: `GET /admin/pic-reviewer/dashboard` → `PicReviewerDashboardController@index` |

## 5. Tambah Menu PIC Reviewer di Sidebar Admin

**Tujuan:** Admin bisa langsung melihat daftar pengguna PIC Reviewer dari sidebar SDM

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/partials/sidebar.blade.php` | Tambah link "PIC Reviewer" di seksi SDM, setelah PIC |
| `app/Http/Controllers/Admin/UserController.php` | `index()` mendukung filter `?role=` untuk tampilkan pengguna per role |
| `resources/views/admin/users/index.blade.php` | Badge judul saat filter role aktif; search form mempertahankan filter role; Reset muncul saat ada filter role aktif |

## 6. 🔄 Update: meta loa

- **Commit:** `53291f4` — 13:57 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Admin/LoaController.php`
- `app/Models/Submission.php`
- `database/migrations/2026_06_26_114409_add_tanggal_loa_to_submissions_table.php`
- `log-update-2026-06-26.md`
- `resources/views/admin/loa/receipt.blade.php`
- `routes/web.php`


## 6. 🔄 Update: up revierw

- **Commit:** `47e3f71` — 15:24 oleh Gudangsoft
- **File berubah:** 13 file
- `app/Http/Controllers/Admin/PicReviewerDashboardController.php`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Middleware/AdminMiddleware.php`
- `app/Models/User.php`
- `log-update-2026-06-26.md`
- `resources/views/admin/partials/sidebar-pic-reviewer.blade.php`
- `resources/views/admin/pic-reviewer/dashboard.blade.php`
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`


## 7. Fix Dashboard PIC Reviewer (500 Error is_active & ReviewAssignment)

**Tujuan:** Perbaiki error 500 pada `/admin/pic-reviewer/dashboard` akibat query ke kolom tidak ada dan relasi salah

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicReviewerDashboardController.php` | Hapus query `is_active` (kolom tidak ada di tabel users); ganti query berbasis Submission+reviewAssignments (relasi tidak ada) dengan query langsung ke model `ReviewAssignment` menggunakan kolom `status` dan `approved_at`; `recentPending` sekarang berisi `ReviewAssignment` status PENDING |
| `resources/views/admin/pic-reviewer/dashboard.blade.php` | Update tabel "Menunggu Review" agar menampilkan data dari `ReviewAssignment` (judul, jurnal, reviewer, deadline) bukan dari Submission |

## 8. Terapkan Edit Metadata LOA ke Marketing

**Tujuan:** Marketing juga bisa edit nama penulis, afiliasi, judul, dan tanggal LOA langsung dari halaman preview LOA mereka

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah route `POST /submissions/{submission}/loa-metadata` di marketing guard → `marketing.submissions.loa.update-metadata` |
| `app/Http/Controllers/Admin/LoaController.php` | Tambah method `updateMarketingMetadata()` dengan pengecekan ownership marketing; pass `isMarketingView: true` dari `showMarketing()` |
| `resources/views/admin/loa/receipt.blade.php` | Tampilkan tombol & modal jika `isAdminView` ATAU `isMarketingView`; form action dinamis sesuai konteks (admin route vs marketing route) |

## 9. Halaman Submission Khusus PIC Reviewer (Normal/Fasttrack/BKD/JAFA)

**Tujuan:** PIC Reviewer punya halaman submission sendiri yang ringkas dan read-friendly, terpisah dari halaman admin penuh

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicReviewerJournalController.php` | Controller baru: method `submissions($type)` filter berdasarkan tipe (normal/fasttrack/bkd/jafa), mendukung search, filter status, filter jurnal |
| `resources/views/admin/pic-reviewer/submissions.blade.php` | View baru: tabel submission dengan kolom kode, judul, jurnal, penulis, reviewer, status, tanggal — filter bar di atas, pagination di bawah |
| `routes/web.php` | Route baru: `GET /admin/pic-reviewer/submissions/{type}` → `PicReviewerJournalController@submissions` |
| `resources/views/admin/partials/sidebar-pic-reviewer.blade.php` | Link Jurnal Normal/BKD/JAFA → `admin.submissions.monitoring?program=`, Fasttrack → `admin.fasttrack-management.monitoring.index`; tidak perlu controller/view baru karena layout.blade.php sudah override sidebar untuk pic_reviewer |

## 10. 🔄 Update: rev

- **Commit:** `505d53b` — 15:39 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/UserController.php`
- `log-update-2026-06-26.md`
- `resources/views/admin/partials/sidebar.blade.php`
- `resources/views/admin/users/index.blade.php`


## 9. 🔄 Update: a

- **Commit:** `0c541b2` — 15:43 oleh Gudangsoft
- **File berubah:** 2 file
- `database/migrations/2026_06_26_160000_add_pic_reviewer_to_users_role_enum.php`
- `log-update-2026-06-26.md`


## 11. 🔄 Update: uprev

- **Commit:** `20b3959` — 15:46 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/PicReviewerDashboardController.php`
- `log-update-2026-06-26.md`
- `resources/views/admin/pic-reviewer/dashboard.blade.php`


## 13. 🔄 Update: loa mar

- **Commit:** `6b88c2e` — 16:02 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/LoaController.php`
- `log-update-2026-06-26.md`
- `resources/views/admin/loa/receipt.blade.php`
- `routes/web.php`


## 15. 🔄 Update: up

- **Commit:** `a7d6623` — 16:09 oleh Gudangsoft
- **File berubah:** 5 file
- `app/Http/Controllers/Admin/PicReviewerJournalController.php`
- `log-update-2026-06-26.md`
- `resources/views/admin/partials/sidebar-pic-reviewer.blade.php`
- `resources/views/admin/pic-reviewer/submissions.blade.php`
- `routes/web.php`

