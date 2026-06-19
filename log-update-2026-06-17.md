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

## 4. Fix: Poin Tahap Validator Tidak Terhitung (0 poin)

**Tujuan:** AHMAD FEBRIYANTO dan Nabila Qistina menyelesaikan ratusan tugas Validator tapi mendapat 0 poin, karena step "validator" tidak terdaftar di sistem point.

**Root cause:** Step "validator" ada di tabel `submissions` (`petugas_validator_id`, `validator_valid`) tapi tidak diikutsertakan di: (1) `PIC_STEP_ORDER` controller, (2) `runBulkSync()`, (3) `POINT_CONFIG`. Admin membuat custom task "validasi" (beda key dari "validator") mencoba mengatasinya tapi key tidak cocok dengan yang dibaca kode.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/TaskPointSettingController.php` | Tambah `'validator' => 'Validator'` ke `PIC_STEP_ORDER` agar tampil sebagai default step di pengaturan |
| `app/Http/Controllers/Admin/PicPointReportController.php` | Tambah step validator ke `runBulkSync()` dan `pendingTasks()` |
| `app/Models/PicPointHistory.php` | Tambah `'validator'` ke `POINT_CONFIG` (fallback 0 pt, aktual dari DB) |
| `database/migrations/2026_06_17_150000_rename_validasi_to_validator_in_task_point_settings.php` | Rename task_key "validasi" → "validator" di task_point_settings (mempertahankan nilai poin 0,33 yang sudah di-set admin) |

### Catatan Deploy
Jalankan `php artisan migrate` di server untuk menjalankan migration rename.
Setelah migrate, jalankan Sinkronisasi di `/admin/task-point-settings` untuk backfill poin validator.

## 5. Sync Otomatis Nilai Poin Saat Ada Perubahan Setting

**Tujuan:** Jika admin mengubah nilai poin suatu tahap di pengaturan, semua data historis ikut diperbarui saat sinkronisasi dijalankan (bukan hanya data baru saja).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/PicPointReportController.php` | `runBulkSync()`: tambah UPDATE `points_earned` untuk submit dan setiap step bila nilai poin berubah; pisahkan kondisi `$points > 0` dari logika update agar step dengan poin 0 tetap di-update |
| `app/Http/Controllers/Admin/MarketingPointReportController.php` | `runBulkSync()`: tambah UPDATE `points_earned` bila nilai berubah; ubah perhitungan `total_points` dari `COUNT(*)` ke `SUM(points_earned)` agar konsisten dengan perubahan nilai poin |

### Cara Kerja
Setiap kali admin klik **Simpan & Sync** di `/admin/task-point-settings`:
1. Catatan yang belum ada → dibuat baru dengan nilai poin terkini
2. Catatan yang sudah ada tapi nilai berbeda → diperbarui ke nilai terkini
3. Total poin PIC & Marketing dihitung ulang dari jumlah aktual

## 6. 🔄 Update: up

- **Commit:** `1d4c6e2` — 10:27 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Models/Submission.php`
- `database/migrations/2026_06_17_102400_add_catatan_marketing_at_to_submissions_table.php`
- `log-update-2026-06-17.md`
- `resources/views/admin/submissions/show.blade.php`
- `resources/views/marketing/show-submission.blade.php`
- `resources/views/pic/submissions/show.blade.php`


## 5. 🔄 Update: a

- **Commit:** `88ed9bb` — 11:47 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/PicPointReportController.php`
- `log-update-2026-06-17.md`
- `resources/views/admin/pic-points/index.blade.php`


## 7. 🔄 Update: point task

- **Commit:** `aee4eba` — 14:54 oleh Gudangsoft
- **File berubah:** 5 file
- `app/Http/Controllers/Admin/PicPointReportController.php`
- `app/Http/Controllers/Admin/TaskPointSettingController.php`
- `app/Models/PicPointHistory.php`
- `database/migrations/2026_06_17_150000_rename_validasi_to_validator_in_task_point_settings.php`
- `log-update-2026-06-17.md`


## 9. 🔄 Update: a

- **Commit:** `faf043d` — 15:17 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/MarketingPointReportController.php`
- `app/Http/Controllers/Admin/PicPointReportController.php`
- `log-update-2026-06-17.md`

