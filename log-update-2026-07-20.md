# Log Update — 20 Juli 2026

## 1. Fitur Baru: Request Perpanjangan Waktu Review

**Tujuan:** User minta reviewer bisa mengajukan request perpanjangan waktu (deadline sudah lewat, lihat screenshot `/reviewer/tasks/51` dengan badge EXPIRED), dibatasi maksimal 1 kali per reviewer per assignment. Admin perlu bisa approve/tolak request tersebut lewat menu tersendiri, dan admin juga harus bisa memperpanjang deadline langsung tanpa perlu ada request dari reviewer.

### File Baru
| File | Keterangan |
|------|-----------|
| `database/migrations/2026_07_18_000001_create_deadline_extension_requests_table.php` | Tabel `deadline_extension_requests`: `review_assignment_id`, `reviewer_id`, `reason`, `requested_deadline` (opsional), `status` (PENDING/APPROVED/REJECTED), `admin_note`, `responded_by`, `responded_at`. Unique index `(review_assignment_id, reviewer_id)` — jaring pengaman DB-level supaya 1 reviewer tidak bisa punya 2 request untuk assignment yang sama, di luar validasi level aplikasi |
| `app/Models/DeadlineExtensionRequest.php` | Model + relasi `reviewAssignment()`, `reviewer()`, `respondedBy()` |
| `app/Http/Controllers/Reviewer/DeadlineExtensionController.php` | `store()`: cek reviewer memang ditugaskan di assignment ini (`assignedReviewerIds()`), cek belum pernah mengajukan (`extensionRequestFor()`), validasi `reason` (wajib) + `requested_deadline` (opsional, harus setelah hari ini), simpan status `PENDING` |
| `app/Http/Controllers/Admin/DeadlineExtensionController.php` | `index()`: daftar semua request (pending duluan). `approve()`: validasi `new_deadline` wajib, update status jadi `APPROVED` **dan langsung update `deadline` di `review_assignments`**. `reject()`: update status jadi `REJECTED` + catatan admin. Keduanya menolak kalau request sudah pernah diproses sebelumnya (`status !== PENDING`) |
| `resources/views/admin/extension-requests/index.blade.php` | Tabel daftar request + modal Setujui (input tanggal deadline baru, default dari `requested_deadline` reviewer) dan modal Tolak (catatan opsional) |

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/ReviewAssignment.php` | Tambah relasi `extensionRequests()` (hasMany) dan helper `extensionRequestFor(int $reviewerId)` untuk cek apakah reviewer tertentu sudah pernah mengajukan |
| `app/Http/Controllers/Admin/ReviewAssignmentController.php` | Tambah `extendDeadline()` — admin memperpanjang deadline **langsung tanpa perlu ada request** dari reviewer manapun, dicatat ke `ActivityLog` (event `deadline_extended`) untuk audit. `show()` sekarang eager-load `extensionRequests.reviewer` |
| `app/Http/Controllers/Reviewer/TaskController.php` | `show()` sekarang load `extensionRequests` dan kirim `myExtensionRequest` (request milik reviewer yang login, kalau ada) ke view |
| `app/Providers/ViewServiceProvider.php` | Tambah `$pendingExtensionRequests` (cached 5 menit, pola sama seperti `$pendingReviewRequests` yang sudah ada) untuk badge di sidebar |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah menu "Perpanjangan Waktu" dengan badge jumlah pending, di bawah "Permintaan Review" |
| `resources/views/admin/assignments/show.blade.php` | Tombol "Perpanjang" di sebelah badge Deadline (modal input tanggal baru + catatan, langsung POST ke `extendDeadline()` — tanpa perlu request reviewer). Tambah blok "Riwayat Perpanjangan" menampilkan semua request (dari reviewer manapun) untuk assignment ini beserta statusnya |
| `resources/views/reviewer/tasks/show.blade.php` | Tombol "Request Perpanjangan Waktu" di card Aksi — tampil selama status belum `APPROVED`/`REJECTED` (termasuk saat sudah **EXPIRED**, justru itu skenario utamanya) DAN reviewer belum pernah mengajukan. Kalau sudah pernah mengajukan, tombol diganti kartu status (Menunggu/Disetujui dengan deadline baru/Ditolak dengan catatan admin). Modal pengajuan: alasan (wajib) + tanggal yang diminta (opsional, admin yang tentukan final kalau dikosongkan) |

**Diverifikasi lewat tinker + HTTP request asli (login sungguhan sebagai reviewer & admin, request lewat `app()->handle()`):**
1. Reviewer buka halaman task → tombol "Request Perpanjangan Waktu" muncul.
2. Reviewer submit request → tersimpan status `PENDING`, redirect dengan pesan sukses.
3. Reviewer submit request KEDUA untuk assignment yang sama → **ditolak** dengan pesan "sudah pernah mengajukan", tetap cuma 1 baris di database (dikonfirmasi via count).
4. Admin buka `/admin/extension-requests` → daftar & alasan request muncul dengan benar.
5. Admin approve (dengan tanggal baru) → status jadi `APPROVED`, dan `deadline` di `review_assignments` **langsung ikut berubah** ke tanggal yang disetujui (dicek before/after, benar berubah).
6. Reviewer buka lagi halaman task-nya → tombol request sudah hilang (dicek elemen tombol persis, bukan cuma teks — modal-nya sendiri memang masih ada di HTML tapi tidak bisa dibuka tanpa tombol pemicu), status "Disetujui" + deadline baru tampil dengan benar.
7. Admin reject request lain → status jadi `REJECTED` + catatan tersimpan.
8. Admin coba approve/reject request yang **sudah diproses** → ditolak dengan pesan "sudah pernah diproses" (tidak bisa diproses dua kali).
9. Admin pakai fitur "Perpanjang" langsung di halaman detail assignment (**tanpa ada request reviewer sama sekali**) → deadline berubah, tercatat di `ActivityLog` dengan nilai sebelum/sesudah.
10. Sidebar admin → badge jumlah pending di menu "Perpanjangan Waktu" muncul dan sesuai jumlah data pending.
11. Ditemukan sekali collision "Duplicate entry" saat testing (2 percobaan tidak sengaja memakai kombinasi assignment+reviewer yang sama) — ini justru **mengonfirmasi unique index di database bekerja sebagai jaring pengaman** seperti yang dirancang.

Semua data uji (request, perubahan deadline, activity log) dihapus/dikembalikan ke nilai semula setelah verifikasi selesai.

**Catatan:** perlu `php artisan migrate --force` di production setelah deploy (ada migration baru, tabel `deadline_extension_requests`).
