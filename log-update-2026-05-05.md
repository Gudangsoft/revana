# Update Harian — 5 Mei 2026

## Ringkasan
Perbaikan dan penambahan fitur pada tanggal 5 Mei 2026, mencakup: auto-refresh halaman monitoring, perbaikan logika validasi tahap Validator, pencegahan login admin ganda, dan perbaikan tampilan sidebar.

---

## 1. Auto-Refresh Halaman Monitoring & Fasttrack

**Tujuan:** Halaman PIC dan Marketing langsung menampilkan data terbaru saat ada penugasan baru tanpa perlu refresh manual.

### File yang Ditambahkan
- `resources/views/partials/auto-refresh.blade.php` — Partial reusable untuk countdown auto-refresh

### File yang Diubah
| File | Keterangan |
|------|-----------|
| `resources/views/marketing/fasttrack/index.blade.php` | Auto-refresh 30 detik |
| `resources/views/marketing/submissions-monitoring.blade.php` | Auto-refresh 30 detik |
| `resources/views/pic/my-tasks/index.blade.php` | Auto-refresh 30 detik |
| `resources/views/pic/submissions/index.blade.php` | Auto-refresh 30 detik |
| `resources/views/admin/submissions/monitoring.blade.php` | Auto-refresh 60 detik |

### Fitur Auto-Refresh
- Countdown timer ditampilkan di pojok kiri atas (30s → 0s → reload)
- Otomatis dijeda saat user sedang mengisi input/dropdown
- Tombol Pause/Play untuk jeda manual
- State disimpan di `sessionStorage` (tetap dijeda walau refresh)

---

## 2. Perbaikan Logika Validasi Tahap Validator

**Tujuan:** Tahap Validator tidak perlu menunggu semua proses selesai. Hanya link publikasi yang wajib ada, Editor 3 dan Author 2 boleh dilewati.

### Aturan Baru untuk `validator_valid`
- **Editor 3 dan Author 2 tidak wajib** valid terlebih dahulu (keduanya opsional)
- **Link publikasi wajib diisi** — jika kosong, validasi diblokir dengan pesan error
- Tahap lain (Editor 1, Author 1, Editor 2, Reviewer 1, Reviewer 2, Production) tetap harus valid

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Pic/JournalManagementController.php` | Backend: skip editor3/author2 untuk validator; cek `link_publish` tidak kosong |
| `resources/views/pic/submissions/monitoring.blade.php` | Frontend: skip editor3/author2 di loop; cek `data-has-link-publish` |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Frontend: idem |

---

## 3. Perbaikan False Alert "Link Publikasi Belum Diisi"

**Tujuan:** Alert muncul meski link publikasi sudah diisi, karena JS mencari `<input>` yang tidak ada di baris Validator (link ditampilkan sebagai `<a>` tag).

### Root Cause
`row.querySelector('input[data-field="link_publish"]')` hanya menemukan `<input>` jika user login sebagai petugas Production. Jika login sebagai Validator, link ditampilkan sebagai tag `<a>` sehingga querySelector mengembalikan `null`.

### Solusi
Tambahkan atribut `data-has-link-publish="1/0"` langsung pada elemen `<tr>` (server-side), sehingga JS bisa mengecek ketersediaan link tanpa bergantung pada tipe elemen yang ditampilkan.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/pic/submissions/monitoring.blade.php` | Tambah `data-has-link-publish` pada `<tr>`; perbaiki logika JS |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Idem |

---

## 4. Pencegahan Login Admin Ganda (Single Session)

**Tujuan:** Akun admin tidak bisa digunakan login secara bersamaan dari dua perangkat/browser berbeda.

### Mekanisme
- Saat admin berhasil login: session ID disimpan ke Laravel Cache dengan key `admin_session:{user_id}`
- Jika cache sudah ada session lain yang aktif → login diblokir dengan pesan: *"Akun admin ini sedang aktif di sesi lain."*
- Saat logout: cache dihapus otomatis
- TTL cache mengikuti `session.lifetime` (default 120 menit)

### File yang Diubah
- `app/Http/Controllers/Auth/LoginController.php`
  - Tambah pengecekan cache sebelum redirect dashboard
  - Tambah `Cache::forget()` di method `logout()`

---

## 5. Menu Pengelolaan Jurnal Normal (Admin Sidebar)

**Tujuan:** Menambahkan menu terpisah untuk pengelolaan submission Normal (tanpa parameter program), sejajar dengan BKD dan JAFA.

### Menu Baru
- **Pengelolaan Jurnal Normal** (accordion, icon ungu)
  - Input Data Normal → `admin.submissions.create`
  - Data Submit Normal → `admin.submissions.index`
  - Monitoring Proses → `admin.submissions.monitoring`

### File yang Diubah
- `resources/views/admin/partials/sidebar.blade.php`
  - Tambah variabel `$normalActive`
  - Pisahkan `$journalActive` (hanya untuk Data Jurnal, Slot, Akreditasi, dll)
  - Hapus duplikasi "Data Submit" & "Monitoring Proses" dari accordion "Pengelolaan Jurnal"
  - Tambah accordion baru "Pengelolaan Jurnal Normal"

---

## 6. Menu Pengelolaan Jurnal Normal (PIC Sidebar)

**Tujuan:** Sidebar PIC dirapikan dengan menambahkan section Pengelolaan Jurnal Normal, mengganti link hardcoded lama.

### Perubahan
- Hapus link hardcoded `https://portal.apji.org/pic/submissions`
- Tambah section **Pengelolaan Jurnal Normal** (Input, Data Submit, Monitoring)
- Section Fasttrack dikelompokkan jadi satu (Input + Data + Monitoring)
- Section "Monitoring" lama diganti menjadi "Tugas Saya"

### File yang Diubah
- `resources/views/pic/partials/sidebar.blade.php`

---

## 7. Sidebar Admin: Lebih Lebar + Tombol Hide/Show

**Tujuan:** Memberikan lebih banyak ruang untuk teks menu yang panjang, dan kemampuan menyembunyikan sidebar untuk memperluas area konten.

### Perubahan
- Lebar sidebar: `250px` → `280px`
- Tombol toggle berbentuk lingkaran di tepi kanan sidebar (desktop only ≥ 992px)
- Klik → sidebar slide keluar, konten melebar penuh; klik lagi → sidebar kembali
- State disimpan di `localStorage` (tetap tersembunyi walau refresh halaman)

### File yang Diubah
- `resources/views/layouts/app.blade.php`
  - Update `--sidebar-width` dari 250px ke 280px
  - Tambah CSS `.sidebar-toggle-btn`, `body.sidebar-collapsed`
  - Tambah HTML tombol toggle
  - Update JavaScript dengan fungsi `toggleSidebar()` + localStorage

## 8. 🔄 Update: a

- **Commit:** `e1e259c` — 10:58 oleh Gudangsoft
- **File berubah:** 3 file
- `CLAUDE.md`
- `app/Services/FeatureSettingService.php`
- `log-update-2026-05-05.md`


## 9. 🔄 Update: a

- **Commit:** `c5d8e77` — 10:59 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-05.md`


## 10. 🔄 Update: update

- **Commit:** `63e402b` — 10:59 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-05.md`


## 11. 🔄 Update: perubahan

- **Commit:** `5a12184` — 10:59 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-05.md`


## 12. 🔄 Update: ,

- **Commit:** `4577520` — 11:00 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-05.md`


## 13. 🔄 Update: ip

- **Commit:** `b6c12be` — 11:00 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-05.md`

---

## 14. Ubah Portal Pertama sipera.apji.org ke Halaman Login

**Tujuan:** Sebelumnya akses root `/` di domain `sipera.apji.org` langsung menampilkan form pendaftaran reviewer. Diubah agar halaman login menjadi portal akses pertama, konsisten dengan perilaku domain utama.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Root route `sipera.apji.org`: tamu diarahkan ke `/login` (bukan form registrasi). User terautentikasi diarahkan ke dashboard sesuai role (admin/reviewer). |


## 15. 🔄 Update: login

- **Commit:** `9edbb11` — 18:04 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-05-05.md`
- `routes/web.php`

