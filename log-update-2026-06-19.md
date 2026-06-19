# Log Update — 19 Juni 2026

## 1. Fitur LOA (Letter of Acceptance) per Jurnal

**Tujuan:** Admin bisa generate LOA dengan template sesuai identitas masing-masing jurnal (warna header, logo, tanda tangan editor). LOA terdiri dari dua halaman: Receipt for Paper + Paper Evaluation Sheet.

### File yang Diubah / Dibuat

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_19_000001_add_loa_fields_to_journal_masters.php` | Tambah kolom LOA ke tabel `journal_masters`: `kode_singkat`, `e_issn`, `logo_path`, `editor_name`, `editor_title`, `editor_signature_path`, `primary_color`, `secondary_color`, `loa_kota`, `loa_tanggal` |
| `database/migrations/2026_06_19_000002_add_affiliation_to_submissions.php` | Tambah kolom `affiliation_penulis` (nullable) ke tabel `submissions` |
| `app/Http/Controllers/Admin/LoaController.php` | Controller baru: `show()` render LOA printable HTML; helper `loaNumber()`, `loaDate()`, `romanMonth()` |
| `resources/views/admin/loa/receipt.blade.php` | Template LOA dua halaman: (1) Receipt for Paper, (2) Paper Evaluation Sheet. Warna header/aksen dinamis dari data jurnal. Ada tombol Print di layar, hilang saat print. |
| `routes/web.php` | Tambah `GET /admin/submissions/{submission}/loa` → `LoaController@show` |
| `resources/views/admin/submissions/show.blade.php` | Tambah tombol **LOA** (buka di tab baru) di header card detail submission |
| `app/Models/JournalMaster.php` | Tambah semua field LOA ke `$fillable` |
| `app/Models/Submission.php` | Tambah `affiliation_penulis` ke `$fillable` |
| `app/Http/Controllers/Admin/JournalMasterController.php` | `update()`: handle upload logo & tanda tangan editor, validasi field LOA baru |
| `resources/views/admin/journal-masters/edit.blade.php` | Tambah section "Pengaturan LOA" di bawah form: kode singkat, E-ISSN, editor, warna (color picker), tanggal resmi, upload logo & TTD |
| `resources/views/admin/submissions/edit.blade.php` | Tambah field **Afiliasi Penulis** di form edit submission (untuk ditampilkan di LOA) |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah `affiliation_penulis` ke validasi di `update()` |

### Format Nomor LOA
`{id_artikel}/{kode_singkat}/APRKOM/{BULAN_ROMAWI}/{TAHUN}`

Contoh: `PAF001/PAF/APRKOM/III/2026`

### Cara Pakai
1. Buka `/admin/journal-masters` → Edit jurnal → isi bagian **Pengaturan LOA** (kode singkat, E-ISSN, editor, warna, logo, TTD)
2. Di halaman detail submission, isi **Afiliasi Penulis** via tombol Edit
3. Klik tombol **LOA** di header halaman detail submission → terbuka di tab baru
4. Klik **Print / Save PDF** → browser print dialog → Save as PDF

### Catatan Deploy
Jalankan `php artisan migrate` di server untuk menambah kolom baru.

## 2. Fitur Master LOA — Setting Template & Kirim Otomatis

**Tujuan:** Halaman khusus admin untuk setting template LOA per jurnal (identitas, warna, logo, TTD) dan mengaktifkan pengiriman LOA otomatis ke email penulis.

### File yang Dibuat / Diubah

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_19_100001_add_auto_loa_to_journal_masters_and_submissions.php` | Tambah `loa_auto_send` (boolean), `loa_auto_trigger` (string) ke `journal_masters`; tambah `loa_sent_at` (timestamp nullable) ke `submissions` |
| `app/Http/Controllers/Admin/LoaMasterController.php` | Controller baru: `index()` daftar jurnal, `edit/update()` form setting LOA, `resend()` kirim ulang manual, `maybeAutoSend()` hook step validasi, `maybeAutoSendOnPublish()` hook status PUBLISHED, `dispatchLoaEmail()` kirim email + catat waktu |
| `app/Mail/LoaAcceptedMail.php` | Mailable baru untuk email LOA ke penulis |
| `resources/views/emails/loa-accepted.blade.php` | Template HTML email LOA dengan warna jurnal, judul artikel, CTA button link ke LOA publik |
| `resources/views/admin/loa-master/index.blade.php` | Tabel daftar jurnal dengan status kelengkapan (logo, TTD, editor, kode, E-ISSN) dan toggle LOA otomatis |
| `resources/views/admin/loa-master/edit.blade.php` | Form setting LOA per jurnal: live preview header, color picker sync, upload logo/TTD, toggle auto-send + pilih trigger |
| `app/Http/Controllers/Admin/LoaController.php` | Tambah `publicView()` — render LOA tanpa login (untuk link di email penulis) |
| `app/Models/JournalMaster.php` | Tambah `loa_auto_send`, `loa_auto_trigger` ke `$fillable` |
| `app/Models/Submission.php` | Tambah `loa_sent_at` ke `$fillable` dan `$casts` |
| `app/Http/Controllers/Admin/SubmissionController.php` | `toggleValidField()`: tambah hook `LoaMasterController::maybeAutoSend()` setelah save |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah menu **Master LOA** di section Pengaturan |
| `routes/web.php` | Tambah `GET /loa/{kode_loa}` (publik, tanpa auth); tambah 4 route Master LOA di admin |

### Trigger LOA Otomatis
Admin bisa pilih kapan LOA otomatis dikirim:
- **Manual saja** — tidak ada pengiriman otomatis
- **Setelah Production divalidasi** — saat `production_valid = true`
- **Setelah Validator divalidasi** — saat `validator_valid = true`
- **Saat status PUBLISHED** — saat status berubah ke PUBLISHED

### Catatan Deploy
Jalankan `php artisan migrate` di server untuk 2 migration baru sesi ini.

## 3. Halaman Request LOA & Tanggal Per-Request

**Tujuan:** Penulis bisa request LOA sendiri via halaman publik dengan memasukkan kode SIPERA. Tanggal LOA bisa disesuaikan per-request (tidak lagi terpaku di setting jurnal).

### File yang Diubah / Dibuat

| File | Perubahan |
|------|-----------|
| `resources/views/loa/request.blade.php` | Halaman publik baru: form input kode SIPERA + toggle tanggal opsional (default hari ini) |
| `app/Http/Controllers/Admin/LoaController.php` | Tambah `requestForm()`, `requestSubmit()` (cari submission by kode_submit atau kode_loa, redirect ke LOA dengan query param tanggal); update `loaDate()` terima `$dateOverride`; `show()` dan `publicView()` baca `request('tanggal')` |
| `routes/web.php` | Tambah `GET /request-loa` dan `POST /request-loa` |
| `resources/views/admin/journal-masters/edit.blade.php` | Hapus field "Tanggal Resmi LOA" (tanggal kini per-request bukan per-jurnal) |
| `resources/views/admin/loa-master/edit.blade.php` | Hapus field "Tanggal Resmi LOA" |

### Alur Request LOA
1. Penulis buka `/request-loa`
2. Isi kode SIPERA (contoh: `SUB2026060001`)
3. Opsional: aktifkan toggle "Tanggal dipilih" untuk mengubah tanggal (default = hari ini)
4. Submit → redirect ke `/loa/{kode_loa}?tanggal=YYYY-MM-DD` → halaman LOA terbuka
5. Klik Print / Save PDF

### Prioritas Tanggal LOA
1. Query param `?tanggal=` (dari request form atau admin) → dipakai jika ada
2. Tanggal hari ini → fallback default

## 4. Filter & Search — Halaman Master LOA

**Tujuan:** Mempermudah admin menemukan jurnal di halaman Master LOA dengan search, filter chip, dan stat cards.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa-master/index.blade.php` | Tambah: 4 stat cards (Total / Lengkap / Belum Lengkap / Otomatis Aktif), search bar live, filter chip Kelengkapan (Semua / Lengkap / Belum Lengkap), filter chip LOA Otomatis (Semua / Aktif / Manual), row counter, empty-state saat tidak ada hasil, tombol reset filter |
| `app/Http/Controllers/Admin/LoaMasterController.php` | `index()`: hitung `$stats` (total, complete, auto) dan kirim ke view |

### Fitur Filter
- Klik stat card → langsung aktifkan filter terkait
- Search live: cari nama jurnal, kode singkat, editor, E-ISSN
- Filter chip Kelengkapan + LOA Otomatis bisa dikombinasikan
- Row counter menampilkan "X dari Y jurnal" saat ada filter aktif
- Tombol "Reset filter" muncul otomatis saat filter aktif

## 5. QR Code & Watermark pada Dokumen LOA

**Tujuan:** Mempersulit pemalsuan dokumen LOA — setiap dokumen memiliki QR code unik yang mengarah ke LOA asli di server, dan watermark diagonal di seluruh halaman.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Tambah: watermark diagonal semi-transparan berlapis 9 baris (teks jurnal + APRKOM + VERIFIED), QR code SVG di footer setiap halaman mengarah ke URL LOA publik (`/loa/{kode_loa}`), Document ID + URL verifikasi di bawah QR |

### Detail Implementasi
- **Watermark**: rotasi -38°, opacity ~5.5%, teks: `{KODE_SINGKAT} • APRKOM • VERIFIED`, grid 9 baris × 3 kolom → menutupi seluruh halaman A4
- **QR Code**: generate via `qrcode.js` (CDN), output SVG 54×54px, tercetak di verified bar bawah halaman 1 & 2
- **URL QR**: `https://portal.apji.org/loa/{kode_loa}` — siapapun yang scan langsung melihat dokumen LOA asli untuk dibandingkan
- **Document ID**: `kode_loa` tampil sebagai teks di samping QR, verifikasi cepat tanpa scan
- **Fallback**: jika CDN pertama gagal, otomatis coba CDN kedua (cdnjs)

## 6. Portal Penulis Terpadu (`/cek-artikel`)

**Tujuan:** Penulis cukup buka satu halaman, masukkan kode SIPERA satu kali — langsung tampil status artikel + tombol LOA (jika tersedia).

### File yang Dibuat / Diubah

| File | Perubahan |
|------|-----------|
| `resources/views/public/author-portal.blade.php` | Halaman baru: form kode SIPERA, card info artikel, stepper progress 5 tahap, section LOA dengan date picker opsional |
| `app/Http/Controllers/TrackingController.php` | Tambah `authorPortal()` dan `authorPortalSearch()` |
| `routes/web.php` | Tambah `GET /cek-artikel` dan `POST /cek-artikel` |
| `resources/views/public/tracking.blade.php` | Tambah link ke Portal Penulis di bawah form |
| `resources/views/loa/request.blade.php` | Tambah link ke Portal Penulis di bawah card |

### Fitur Portal Penulis
- Kode SIPERA → info artikel + stepper 5 tahap (Submit → Review → Produksi → Validasi → Terbit)
- LOA section muncul otomatis jika `production_valid = true`; date picker opsional (default hari ini)
- LOA terkunci dengan pesan informatif jika artikel belum melewati tahap produksi

## 6. 🔄 Update: loa

- **Commit:** `784089c` — 14:16 oleh Gudangsoft
- **File berubah:** 14 file
- `app/Http/Controllers/Admin/JournalMasterController.php`
- `app/Http/Controllers/Admin/LoaController.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Models/JournalMaster.php`
- `app/Models/Submission.php`
- `database/migrations/2026_06_19_000001_add_loa_fields_to_journal_masters.php`
- `database/migrations/2026_06_19_000002_add_affiliation_to_submissions.php`
- `log-update-2026-06-17.md`
- `log-update-2026-06-19.md`
- `resources/views/admin/journal-masters/edit.blade.php`


## 5. 🔄 Update: loa

- **Commit:** `514a601` — 14:33 oleh Gudangsoft
- **File berubah:** 15 file
- `app/Http/Controllers/Admin/LoaController.php`
- `app/Http/Controllers/Admin/LoaMasterController.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Mail/LoaAcceptedMail.php`
- `app/Models/JournalMaster.php`
- `app/Models/Submission.php`
- `database/migrations/2026_06_19_100001_add_auto_loa_to_journal_masters_and_submissions.php`
- `log-update-2026-06-19.md`
- `resources/views/admin/journal-masters/edit.blade.php`
- `resources/views/admin/loa-master/edit.blade.php`


## 6. 🔄 Update: loa

- **Commit:** `d187ddb` — 14:35 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-06-19.md`


## 8. 🔄 Update: filter loa

- **Commit:** `b7f9cd9` — 14:53 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/LoaMasterController.php`
- `log-update-2026-06-19.md`
- `resources/views/admin/loa-master/index.blade.php`


## 10. 🔄 Update: cek

- **Commit:** `7449b1f` — 15:04 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/TrackingController.php`
- `log-update-2026-06-19.md`
- `resources/views/loa/request.blade.php`
- `resources/views/public/author-portal.blade.php`
- `resources/views/public/tracking.blade.php`
- `routes/web.php`


## 12. 🔄 Update: qr

- **Commit:** `95f40b2` — 15:15 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-19.md`
- `resources/views/admin/loa/receipt.blade.php`

## 16. QR Code Terpisah: Admin URL vs Public URL

**Tujuan:** QR code di dokumen LOA mengarah ke URL yang sesuai konteks — admin view → QR mengarah ke URL admin; public view → QR mengarah ke URL publik.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LoaController.php` | `show()` kirim `$verifyUrl = route('admin.submissions.loa', $submission)` dan `$isAdminView = true`; `publicView()` kirim `$verifyUrl = url('/loa/{kode_loa}')` dan `$isAdminView = false` |
| `resources/views/admin/loa/receipt.blade.php` | Semua referensi URL QR & teks verifikasi pakai `$verifyUrl`; halaman 1 & 2 tampilkan badge "ADMIN" (hijau) jika `$isAdminView`; fallback ke `kode_submit` jika `kode_loa` kosong |

### Perilaku QR Setelah Perubahan
| Akses dari | QR mengarah ke |
|-----------|----------------|
| `/admin/submissions/558/loa` | `https://portal.apji.org/admin/submissions/558/loa` (butuh login) |
| `/loa/{kode_loa}` | `https://portal.apji.org/loa/{kode_loa}` (publik) |

## 15. Unifikasi Penuh URL Portal Penulis

**Tujuan:** `/cek-artikel`, `/request-loa`, dan `/tracking-loa` semuanya menampilkan halaman yang sama — tidak ada redirect, tidak ada halaman terpisah.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `routes/web.php` | Hapus route duplikat; hapus route `/request-loa` lama (LoaController); jadikan `/cek-artikel` dan `/request-loa` alias langsung ke `TrackingController::index/search` — semua URL menuju view yang sama |
| `resources/views/public/author-portal.blade.php` | Hapus link "Request LOA" dari nav (tidak perlu lagi); perbarui hero text; perbaiki footer |
| `resources/views/public/tracking.blade.php` | Update link portal penulis mengarah ke `tracking.index` |

### Peta URL Setelah Perubahan
| URL | Hasil |
|-----|-------|
| `/tracking-loa` | Portal terpadu (GET) |
| `/tracking-loa/search` | Proses pencarian (POST) |
| `/cek-artikel` | Sama persis dengan `/tracking-loa` |
| `/request-loa` | Sama persis dengan `/tracking-loa` |
| `/loa/{kode_loa}` | Halaman cetak LOA (tetap terpisah) |

## 14. Portal Penulis Terpadu — Redesign `/tracking-loa`

**Tujuan:** Gabungkan `/tracking-loa` dan `/cek-artikel` menjadi satu halaman terpusat yang keren dan informatif. Halaman lama `/cek-artikel` otomatis redirect ke `/tracking-loa`.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `resources/views/public/author-portal.blade.php` | Redesign total: hero section + badge APRKOM, search card dengan 3 feature pill (Status / LOA / Tanggal), result card dengan journal header dinamis, article info grid, stepper 5 tahap dengan status label detail, LOA section (available/locked) dengan date picker & verifikasi QR, footer links |
| `app/Http/Controllers/TrackingController.php` | `index()` → return `author-portal` view; `search()` → terima field `kode_loa` atau `kode`, return `author-portal`; `authorPortal/Search()` → redirect ke `tracking.index`; `verifyDirect()` → return `author-portal` |
| `routes/web.php` | `/cek-artikel` GET & POST → redirect 301 ke `/tracking-loa` |

### Fitur Halaman Baru
- **Hero**: badge "Portal Resmi SIPERA · APRKOM", headline, deskripsi
- **Search card**: input kode monospace uppercase, tombol "Cek Sekarang"
- **Feature pills** (saat belum ada hasil): Status Real-time / Unduh LOA / Pilih Tanggal
- **Result card**: header jurnal (warna dinamis, logo/abbr), info artikel, stepper progress 5 tahap
- **Status bar**: label status detail dengan ikon (mis. "Proses Editor 1", "Tahap Produksi")
- **LOA section**: hijau + date picker + tombol "Buka & Cetak LOA" jika tersedia; abu-abu + progress chips jika belum
- Background: radial gradient dekoratif, mobile responsive, CSP-friendly (tidak ada external fetch)

## 13. Tombol Cek LOA di Semua Halaman Monitoring

**Tujuan:** Admin bisa langsung membuka preview LOA dari halaman monitoring tanpa harus masuk ke halaman detail submission terlebih dahulu.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/monitoring.blade.php` | Tambah badge **LOA** di bawah badge Screening di kolom Kode Submit (sticky). Link ke `admin.submissions.loa`. |
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | Tambah badge **LOA** di kolom Kode Submit (sticky). Link ke `admin.submissions.loa`. |
| `resources/views/admin/fasttrack/monitoring.blade.php` | Tambah tombol **LOA** di kolom Aksi. Link ke `admin.submissions.loa`. |
| `resources/views/pic/submissions/monitoring.blade.php` | Tambah badge **LOA** di kolom Kode Submit (sticky). Muncul hanya jika `kode_loa` sudah diset. Link ke LOA publik. |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sama seperti PIC submissions. |
| `resources/views/marketing/submissions-monitoring.blade.php` | Tambah tombol **LOA** di kolom Aksi. Muncul hanya jika `kode_loa` sudah diset. Link ke LOA publik. |
| `resources/views/marketing/fasttrack/monitoring.blade.php` | Sama seperti marketing submissions. |

### Logika Tampil
- **Admin**: tombol selalu muncul (admin bisa preview LOA kapan saja). Hijau jika `production_valid`, abu-abu jika belum.
- **PIC & Marketing**: tombol muncul hanya jika `kode_loa` sudah diisi (LOA pernah digenerate/dikirim). Link ke halaman LOA publik.


## 14. 🔄 Update: monitorung update

- **Commit:** `8dfdef2` — 15:23 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-19.md`
- `resources/views/admin/submissions/monitoring.blade.php`


## 15. 🔄 Update: update loa ditabel monitoring

- **Commit:** `84017df` — 15:29 oleh Gudangsoft
- **File berubah:** 7 file
- `log-update-2026-06-19.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/fasttrack/monitoring.blade.php`
- `resources/views/marketing/fasttrack/monitoring.blade.php`
- `resources/views/marketing/submissions-monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`


## 17. 🔄 Update: tracking loa update

- **Commit:** `70b6a51` — 15:35 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/TrackingController.php`
- `log-update-2026-06-19.md`
- `resources/views/public/author-portal.blade.php`
- `routes/web.php`


## 19. 🔄 Update: track loa update

- **Commit:** `0b93141` — 15:39 oleh Gudangsoft
- **File berubah:** 4 file
- `log-update-2026-06-19.md`
- `resources/views/public/author-portal.blade.php`
- `resources/views/public/tracking.blade.php`
- `routes/web.php`

