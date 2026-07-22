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

