# Log Update — 17 Juni 2026

## 1. Timestamp Catatan Marketing di Detail Submission

**Tujuan:** Tambahkan tanggal & waktu penulisan catatan marketing agar tim production bisa memvalidasi bahwa catatan diberikan sebelum artikel diproses terbit.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_17_102400_add_catatan_marketing_at_to_submissions_table.php` | Migration baru: tambah kolom `catatan_marketing_at` (datetime, nullable) di tabel `submissions` |
| `app/Models/Submission.php` | Tambah `catatan_marketing_at` ke `$fillable` dan `$casts` (cast sebagai `datetime`) |
| `app/Http/Controllers/Marketing/DashboardController.php` | `updateCatatan()`: isi `catatan_marketing_at = now()` saat catatan disimpan, null jika catatan dikosongkan |
| `resources/views/admin/submissions/show.blade.php` | Tampilkan timestamp di alert warning catatan marketing |
| `resources/views/pic/submissions/show.blade.php` | Tampilkan timestamp di alert warning catatan marketing |
| `resources/views/marketing/show-submission.blade.php` | Tampilkan info card "Terakhir disimpan" dengan timestamp di atas form edit catatan |

### Detail Tampilan

- **Di halaman Admin & PIC (read-only):** Di dalam alert warning, sebelum teks catatan, muncul baris `🕐 Ditulis: 10/06/2026 14:30`
- **Di halaman Marketing (edit):** Muncul info card dengan ikon jam dan teks "Terakhir disimpan: 10/06/2026 14:30" di atas form textarea
- **Catatan lama** (sebelum fitur ini): tidak menampilkan timestamp (field `catatan_marketing_at` = null)

### Catatan Deploy
Jalankan `php artisan migrate` di server untuk menambah kolom `catatan_marketing_at`.

## 2. Klik Angka "Belum Selesai" di Leaderboard PIC → Modal Detail Tugas

**Tujuan:** Admin bisa lihat detail submission yang belum divalidasi per PIC dari leaderboard tanpa masuk ke halaman detail PIC.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah route `GET /pic-points/{pic}/pending-tasks` |
| `app/Http/Controllers/Admin/PicPointReportController.php` | Tambah `pendingTasks()` — return JSON list submission yang belum divalidasi per PIC |
| `resources/views/admin/pic-points/index.blade.php` | Badge merah jadi button klik; tambah modal + JS fetch; ganti `@section('scripts')` → `@push('scripts')` (fix: layout pakai `@stack`, bukan `@yield`) |


## 3. Kolom Tanggal Penugasan di Modal Tugas Pending PIC

**Tujuan:** Tampilkan tanggal PIC ditugaskan ke setiap submission di modal "Belum Selesai" pada leaderboard, agar admin bisa melihat sudah berapa lama tugas menunggu.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | `pendingTasks()`: tambah field `step` di workflowSteps, LEFT JOIN subquery ke `submission_histories` (action=assigned) untuk ambil `assigned_at`, tambah `tgl_penugasan` ke JSON response |
| `resources/views/admin/pic-points/index.blade.php` | Modal table: tambah kolom header "Tgl Penugasan" dan cell `task.tgl_penugasan` di setiap baris |

## 4. 🔄 Update: up

- **Commit:** `1d4c6e2` — 10:27 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Models/Submission.php`
- `database/migrations/2026_06_17_102400_add_catatan_marketing_at_to_submissions_table.php`
- `log-update-2026-06-17.md`
- `resources/views/admin/submissions/show.blade.php`
- `resources/views/marketing/show-submission.blade.php`
- `resources/views/pic/submissions/show.blade.php`

