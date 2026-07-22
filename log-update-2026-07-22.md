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

