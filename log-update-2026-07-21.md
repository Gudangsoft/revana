# Log Update — 21 July 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Fix Bug "Minta Revisi" PIC Selalu Gagal (Kolom `revision_notes` Tidak Ada)

**Tujuan:** Ditemukan saat investigasi sistem catatan (lihat section #2) — bukan bagian dari laporan awal user, tapi bug nyata & aktif yang wajib segera diperbaiki.

**Root cause:** `JournalManagementController::requestRevision()` (dipanggil dari tombol "Minta Revisi" di `pic/submissions/process.blade.php`) menulis `$submission->revision_notes = $request->revision_notes;` lalu `$submission->save();` — tapi kolom `revision_notes` **tidak pernah ada** di tabel `submissions` manapun (dicek migration & `$fillable`, tidak ditemukan sama sekali). Baris ini pasti membuat `save()` gagal dengan `QueryException: Unknown column 'revision_notes'`, artinya **setiap** PIC yang klik "Minta Revisi" akan mendapat error database, bukan berhasil — fitur ini sudah rusak total sebelum perbaikan ini.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | Baris `$submission->revision_notes = $request->revision_notes;` dihapus — catatan revisi memang sudah tercatat lewat `catatan_validator` (kalau status VALIDATOR_PROCESS) dan lewat `submission_histories` (action `revision_request`), jadi kolom `revision_notes` itu tidak pernah dibutuhkan sama sekali |

**Diverifikasi lewat tinker:** simulasi `requestRevision()` langsung (bukan cuma baca kode) — SEBELUM fix akan throw `QueryException`; SESUDAH fix berhasil tanpa exception, redirect 302, dan catatan tetap tercatat dengan benar di `catatan_validator` & `submission_histories`. Data uji (status, catatan) dikembalikan seperti semula setelah verifikasi.

## 2. Amankan Sistem Catatan — Riwayat Otomatis Sebelum Ditimpa

**Tujuan:** User melapor banyak yang bingung dengan sistem catatan (screenshot: catatan muncul di tabel PIC tapi sempat dikira hilang di detail). Investigasi menyeluruh (bukan cuma 1 kasus) menemukan akar masalah sebenarnya: sistem punya 5 kolom catatan terpisah (`notes`, `catatan_reviewer1`, `catatan_reviewer2`, `catatan_validator`, `catatan_marketing`) yang masing-masing cuma menyimpan **1 nilai terakhir** — beberapa di antaranya bisa ditimpa lewat 5 jalur tulis berbeda TANPA pernah tercatat ke riwayat (`submission_histories`) sama sekali, jadi catatan lama hilang tanpa jejak begitu ada yang menulis catatan baru. Disepakati dengan user: scope perbaikan kali ini fokus mengamankan data dulu (bukan redesain tampilan) — setiap catatan sekarang otomatis tercatat ke riwayat SEBELUM ditimpa.

**Detail temuan (dari audit menyeluruh):**
- `catatan_marketing`: 1 satu-satunya jalur tulis (`Marketing\DashboardController::updateCatatan`), **nol riwayat sama sekali** sebelum perbaikan ini.
- `catatan_reviewer1`/`catatan_reviewer2`: 3 jalur tulis — 1 sudah aman (`updateReviewerNotes`, sudah ada sebelumnya), 2 lainnya (form edit admin, AJAX inline-edit admin & PIC) **tidak logging sama sekali**.
- `catatan_validator`: sama seperti reviewer1/2, ditambah 1 jalur lagi lewat `requestRevision()` (lihat section #1) yang juga tidak logging catatan lama.
- Marketing juga ditemukan buta total terhadap `catatan_validator` dan `catatan_reviewer1/2` di halaman utama mereka (di luar scope perbaikan kali ini, dicatat untuk keputusan lanjutan kalau diperlukan).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_21_000001_add_marketing_to_submission_histories_step.php` (baru) | Tambah nilai `'marketing'` ke enum kolom `submission_histories.step` — sebelumnya cuma ada `submit/editor1/author1/editor2/reviewer1/reviewer2/editor3/author2/production/fasttrack/validator`, belum ada slot untuk catatan marketing |
| `app/Http/Controllers/Marketing/DashboardController.php` | `updateCatatan()`: catatan LAMA (yang akan ditimpa) dicatat ke `submission_histories` (step `marketing`, action `note_added`) sebelum kolom `catatan_marketing` ditimpa — cuma kalau nilainya benar-benar berubah |
| `app/Http/Controllers/Admin/SubmissionController.php` | `update()` (form edit submission): tambah logging sebelum `catatan_reviewer1`/`catatan_reviewer2` ditimpa. `quickUpdateCredential()` (AJAX inline-edit di tabel monitoring): tambah logging untuk `catatan_reviewer1`/`catatan_reviewer2`/`catatan_validator` sebelum ditimpa |
| `app/Http/Controllers/Pic/JournalManagementController.php` | `updateCredential()` (AJAX inline-edit PIC): tambah logging sama seperti admin, plus `requestRevision()`: tambah logging untuk `catatan_validator` sebelum ditimpa (lihat juga section #1 untuk bug lain di method yang sama) |

**Pola yang dipakai (konsisten di semua tempat, mengikuti pola `updateReviewerNotes()` yang sudah ada sebelumnya):** kalau nilai catatan baru BERBEDA dari nilai saat ini dan tidak kosong, catat nilai BARU itu ke `submission_histories` (action `note_added`) SEBELUM kolom ditimpa — supaya setiap catatan yang pernah ditulis (termasuk yang pertama kali) punya jejak permanen di riwayat, kolom `catatan_*` sendiri cuma berfungsi sebagai cache "nilai terakhir".

**Diverifikasi lewat tinker (5 skenario, semua lewat pemanggilan controller sungguhan, bukan cuma baca kode):**
1. Marketing tulis catatan pertama kali → tercatat 1 baris riwayat. Tulis catatan KEDUA (beda teks) → baris riwayat jadi 2 (catatan pertama tetap ada, tidak hilang). Tulis catatan yang SAMA persis lagi → jumlah riwayat TIDAK bertambah (tidak ada duplikat untuk nilai yang tidak berubah).
2. Admin AJAX quick-update `catatan_reviewer1` → riwayat bertambah 1 baris dengan step `reviewer1`.
3. PIC AJAX inline-update `catatan_reviewer1` (lewat jalur editor2) → riwayat bertambah 1 baris.
4. PIC `requestRevision()` dengan status dipaksa `VALIDATOR_PROCESS` → `catatan_validator` berubah DAN riwayat `note_added` step `validator` tercatat.

Semua data uji (nilai catatan, status, riwayat buatan) dikembalikan/dihapus sepenuhnya setelah setiap verifikasi.

**Catatan:** perlu `php artisan migrate --force` di production setelah deploy (ada migration baru, menambah nilai enum). Perbaikan ini murni soal KEAMANAN DATA (tidak ada lagi catatan yang hilang tanpa jejak) — tidak ada perubahan tampilan/halaman apapun di scope ini. Temuan lain dari audit (marketing buta terhadap catatan_validator/reviewer1/2, dan field editor1-3/author1-2/production yang tidak punya kolom catatan permanen sama sekali) sengaja belum disentuh, menunggu keputusan scope lanjutan dari user.
