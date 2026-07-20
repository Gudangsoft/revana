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

## 2. 🔄 Update: Add deadline extension request system for reviewers

- **Commit:** `593f431` — 10:51 oleh Gudangsoft
- **File berubah:** 14 file
- `app/Http/Controllers/Admin/DeadlineExtensionController.php`
- `app/Http/Controllers/Admin/ReviewAssignmentController.php`
- `app/Http/Controllers/Reviewer/DeadlineExtensionController.php`
- `app/Http/Controllers/Reviewer/TaskController.php`
- `app/Models/DeadlineExtensionRequest.php`
- `app/Models/ReviewAssignment.php`
- `app/Providers/ViewServiceProvider.php`
- `database/migrations/2026_07_18_000001_create_deadline_extension_requests_table.php`
- `log-update-2026-07-20.md`
- `resources/views/admin/assignments/show.blade.php`

## 3. Tampilkan Semua Reviewer dengan Deadline Terlewat di Halaman Perpanjangan Waktu

**Tujuan:** User minta halaman `/admin/extension-requests` juga menampilkan SEMUA reviewer yang deadline-nya sudah lewat, baik yang sudah mengajukan request maupun yang belum — sebelumnya halaman ini cuma menampilkan reviewer yang SUDAH mengajukan request, jadi admin tidak punya gambaran reviewer mana saja yang stuck tapi belum/tidak mengajukan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/DeadlineExtensionController.php` | `index()` sekarang juga bangun `$expiredReviewers` — daftar SEMUA reviewer (dari `assignedReviewerIds()`, jadi mencakup reviewer utama + pendamping 2-5) pada assignment yang deadline-nya sudah lewat (`deadline < hari ini`) dan belum selesai (`status` bukan `APPROVED`/`REJECTED`), lengkap dengan status request masing-masing reviewer (`extensionRequestFor()`) — `null` kalau belum pernah mengajukan sama sekali. Paginasi tabel request lama diberi nama page terpisah (`requests_page`) supaya tidak bentrok kalau nanti daftar reviewer expired ini juga perlu paginasi |
| `resources/views/admin/extension-requests/index.blade.php` | Tambah card baru "Reviewer dengan Deadline Terlewat" di atas tabel request yang sudah ada — badge jumlah, per baris menampilkan artikel/reviewer/deadline (EXPIRED)/status request (`Belum mengajukan` kalau `null`, atau badge status kalau sudah ada). Untuk reviewer yang belum mengajukan atau requestnya sudah diproses (bukan PENDING), ada tombol "Perpanjang" langsung (modal, reuse `admin.assignments.extend-deadline`) — dengan catatan penjelas bahwa deadline dipakai bersama semua reviewer di assignment itu, supaya admin tidak salah kira ini cuma memperpanjang untuk 1 reviewer saja |

**Diverifikasi lewat tinker + HTTP request asli:**
1. Assignment dibuat expired sementara (`deadline` di-set ke 3 hari lalu) → halaman menampilkan card baru dengan badge "EXPIRED" dan "Belum mengajukan" untuk reviewer yang belum pernah request.
2. Ditambahkan `DeadlineExtensionRequest` PENDING untuk reviewer yang sama → baris berubah menampilkan badge "Menunggu" dan tombol "Perpanjang" cepat diganti jadi teks "Lihat di tabel bawah" (supaya admin diarahkan approve/reject lewat mekanisme yang benar, bukan bypass langsung).
3. Semua data uji (deadline assignment, request buatan) dikembalikan/dihapus setelah verifikasi.

## 4. 🔄 Update: Show all expired reviewers on the extension-requests page, not just requesters

- **Commit:** `a289395` — 11:06 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/DeadlineExtensionController.php`
- `log-update-2026-07-20.md`
- `resources/views/admin/extension-requests/index.blade.php`

## 4. Isi Otomatis "Tanggal LOA" dengan Hari Ini di Modal Edit Metadata LOA

**Tujuan:** User minta field "Tanggal LOA" di modal Edit Metadata LOA otomatis terisi tanggal hari ini, supaya saat tombol LOA diklik, angka romawi (bulan) di nomor LOA menyesuaikan tanggal yang benar.

**Root cause:** Field `tanggal_loa` di modal sebelumnya dibiarkan KOSONG kalau belum pernah diisi (cuma ada teks bantuan "kosong = tanggal hari ini"). Kalau admin klik "Simpan" tanpa menyentuh field ini, `tanggal_loa` tetap tersimpan `null` — akibatnya nomor LOA (`LoaController::loaNumber()`) dan tanggal tanda tangan (`loaDate()`) tidak pernah benar-benar terkunci ke tanggal LOA diterbitkan, melainkan JATUH KE FALLBACK yang beda-beda: `loaDate()` jatuh ke `now()` (jadi tanggal tanda tangan "berjalan maju" tiap kali halaman LOA dibuka ulang di hari lain), sedangkan `loaNumber()` jatuh ke `slot->bulan`/`slot->tahun` (periode terbitan jurnal, yang formatnya kadang bukan 1 nama bulan bersih, mis. "Januari-Juni/2025" — tidak cocok dipetakan ke 1 angka romawi bulan dengan benar).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Input `tanggal_loa` di modal Edit Metadata LOA sekarang default terisi `now()->toDateString()` (bukan kosong) kalau submission belum punya `tanggal_loa` — jadi begitu admin klik "Simpan" (walau tanpa menyentuh field ini), tanggal hari ini langsung terkunci sebagai `tanggal_loa`, dan nomor LOA + tanggal tanda tangan konsisten memakai tanggal itu selamanya (tidak lagi jatuh ke fallback `now()`/periode slot yang bisa berubah-ubah) |

**Diverifikasi lewat tinker + HTTP request asli:** submission dengan `tanggal_loa` kosong → dibuka modal Edit Metadata (via request HTTP asli, login admin) → value input terisi otomatis `2026-07-20` (tanggal hari ini saat tes), bukan kosong. Disimulasikan submit form persis seperti kalau admin klik Simpan tanpa ubah tanggal → `tanggal_loa` tersimpan `2026-07-20` di database, dan `loaNumber()` menghasilkan angka romawi `VII` (Juli, sesuai tanggal yang tersimpan) — sebelumnya, kalau dibiarkan `null`, nomor LOA akan jatuh ke periode slot jurnal (`Januari-Juni/2025`, bukan bulan tunggal yang valid). Data uji dikembalikan ke `null` setelah verifikasi.


