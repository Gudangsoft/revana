# Log Update — 2 Juli 2026

## 1. Tambah Tombol Kirim Email/WA ke Author di Halaman LOA

**Tujuan:** Admin dan marketing dapat langsung mengirim link LOA ke penulis via WhatsApp atau Email dari halaman LOA tanpa perlu menyalin link secara manual

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Tambah tombol "📤 Kirim ke Author" di print-bar; tambah modal dengan info penulis (nama, HP, email), input salin link LOA, tombol WhatsApp (`wa.me/{noHp}?text=...`) dan tombol Email (`mailto:` dengan subject dan body terisi otomatis) |

## 2. 🔄 Update: send loa

- **Commit:** `2ae525b` — 11:19 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-07-01.md`
- `log-update-2026-07-02.md`
- `resources/views/admin/loa/receipt.blade.php`


## 3. 🔄 Update: tambah input email/HP di modal kirim jika kosong, fix html escaping

- **Commit:** `da9c3c2` — 11:38 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/LoaController.php`
- `resources/views/admin/loa/receipt.blade.php`


## 4. 🔄 Update: up

- **Commit:** `d17a6b0` — 13:24 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-07-02.md`
- `resources/views/admin/loa/receipt.blade.php`

## 5. Kembalikan Modal "Edit Metadata LOA" & "Kirim ke Author" yang Terhapus Tidak Sengaja

**Tujuan:** Commit `d17a6b0` (perapian spacing LOA) tidak sengaja ikut menghapus tombol dan modal "Edit Metadata LOA" serta modal "Kirim ke Author" (WA/Email), digantikan dengan link "Edit Afiliasi & Data" yang mengarah ke halaman edit submission penuh. User melaporkan tombol edit metadata hilang setelah deploy — dikembalikan ke perilaku semula.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Restore tombol "✏ Edit Metadata LOA" dan "📤 Kirim ke Author" di print-bar beserta modal `#modal-meta-loa` (form nama/afiliasi/judul/tanggal LOA) dan `#modal-send-loa` (kirim link LOA via WhatsApp/Email); hapus link "Edit Afiliasi & Data" yang menggantikannya sementara. Perubahan spacing/tabel `to-block` dari commit sebelumnya tetap dipertahankan. |


## 6. 🔄 Update: rec

- **Commit:** `9825a1a` — 14:41 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-07-02.md`
- `resources/views/admin/loa/receipt.blade.php`

## 7. Fix Bug LOA Auto-Send Terkirim Terlalu Dini pada Trigger "PUBLISHED"

**Tujuan:** User minta cek apakah fungsi email otomatis LOA sudah berfungsi benar. Ditemukan bug: untuk jurnal dengan `loa_auto_trigger = published` ("Saat status berubah ke PUBLISHED"), LOA ke penulis terkirim sejak tahap validasi PERTAMA (editor1/author1/dst) — bukan menunggu status submission benar-benar PUBLISHED. Penyebab: kondisi guard di `maybeAutoSend()` selalu lolos untuk trigger `published` terlepas dari step yang divalidasi, sehingga bentrok dengan `maybeAutoSendOnPublish()` yang sudah benar mengecek status PUBLISHED.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LoaMasterController.php` | `maybeAutoSend()`: hapus bypass `$trigger !== 'published'` dari kondisi guard, sehingga trigger `published` sepenuhnya ditangani oleh `maybeAutoSendOnPublish()` (gated status PUBLISHED), bukan ikut lolos di setiap step validasi |

## 8. Tombol "Kirim via Email" di Modal LOA Kirim Langsung dari Sistem (Bukan mailto:)

**Tujuan:** Tombol "✉ Kirim via Email" sebelumnya memakai link `mailto:` yang membuka aplikasi email admin/marketing sendiri (perlu login ke akun email pribadi). User minta email dikirim langsung dari sistem (SMTP server aplikasi) memakai mailable `LoaAcceptedMail` yang sudah ada, jadi admin/marketing tinggal klik kirim tanpa perlu buka email sendiri.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Ganti `<a href="mailto:...">` jadi `<form>` POST ke route resend LOA (dengan konfirmasi JS sebelum submit); hapus variabel `$emailSubject`/`$emailBody` yang sudah tidak terpakai |
| `routes/web.php` | Tambah route `POST /marketing/loa-master/{submission}/resend` (`marketing.loa-master.resend`) — sebelumnya hanya ada untuk admin (`admin.loa-master.resend`) |
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah method `loaMasterResend()`: cek kepemilikan submission oleh marketing yang login, lalu panggil `LoaMasterController::dispatchLoaEmail()` untuk kirim LOA via SMTP server |

## 9. Counter "Berhasil Dikirim" untuk Email & WhatsApp di Modal Kirim LOA

**Tujuan:** User minta counter berapa kali LOA berhasil dikirim via Email/WA per submission, ditampilkan di modal "Kirim LOA ke Author". Email dihitung dari pengiriman sukses lewat sistem (SMTP); WA dihitung dari klik tombol "Kirim via WhatsApp" karena server tidak bisa memverifikasi apakah pesan WA benar-benar terkirim (WA membuka wa.me di sisi klien).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_02_150000_add_loa_send_counters_to_submissions_table.php` | Migration baru: tambah kolom `loa_email_sent_count` dan `loa_wa_sent_count` (unsignedInteger, default 0) ke tabel `submissions` |
| `app/Models/Submission.php` | Tambah `loa_email_sent_count` dan `loa_wa_sent_count` ke `$fillable` |
| `app/Http/Controllers/Admin/LoaMasterController.php` | `dispatchLoaEmail()`: increment `loa_email_sent_count` setelah kirim sukses; tambah method `logWaClick()` (increment `loa_wa_sent_count`) dan endpoint `waClick()` untuk AJAX |
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah method `loaMasterWaClick()`: cek kepemilikan submission, lalu panggil `LoaMasterController::logWaClick()` |
| `routes/web.php` | Tambah route `POST /admin/loa-master/{submission}/wa-click` dan `POST /marketing/loa-master/{submission}/wa-click` |
| `resources/views/admin/loa/receipt.blade.php` | Tambah meta `csrf-token` di `<head>`; tampilkan caption "Terkirim Nx" (email) dan "Diklik Nx" (WA) di bawah masing-masing tombol; tambah JS `logWaClick()` yang mengirim fetch POST (keepalive) ke endpoint wa-click saat tombol WhatsApp diklik, tanpa mengganggu navigasi ke wa.me |

## 10. Form Kontak Author di Modal Kirim LOA Selalu Bisa Diedit (untuk Testing)

**Tujuan:** Sebelumnya form email/No HP di modal "Kirim LOA ke Author" hanya muncul kalau salah satu kosong (`@if(!$emailPenulis || !$submission->no_hp_penulis)`). User perlu bisa mengubah email/No HP kapan saja untuk keperluan testing (misal ganti ke email/nomor sendiri), bukan hanya saat kosong.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Hapus kondisi `@if` yang menyembunyikan form kontak saat email/HP sudah terisi; kedua input sekarang selalu tampil ter-edit dengan value saat ini (bukan hidden input); teks keterangan menyesuaikan ("Lengkapi kontak..." jika kosong, "Ubah kontak author bila perlu..." jika sudah terisi) |

## 11. Tombol "Kirim via WhatsApp" di Modal LOA Kirim Otomatis Lewat Fonnte (Bukan Buka wa.me)

**Tujuan:** Tombol "Kirim via WhatsApp" sebelumnya hanya membuka link `wa.me` di tab baru — admin/marketing masih harus menekan tombol kirim sendiri di WhatsApp. User minta pengiriman WA benar-benar otomatis dari sistem tanpa membuka WhatsApp lagi, sama seperti email yang sudah dibuat sistem-kirim sebelumnya. Project ternyata sudah punya integrasi WhatsApp gateway (Fonnte, `app/Services/FonnteService.php`) yang dipakai di fitur lain (notifikasi kredensial OJS) — dipakai ulang di sini.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LoaMasterController.php` | Tambah `dispatchLoaWa()`: kirim pesan LOA via `FonnteService`, increment `loa_wa_sent_count` hanya jika sukses; tambah endpoint `resendWa()`; hapus `waClick()`/`logWaClick()` (tidak relevan lagi karena sekarang benar-benar terkirim, bukan sekadar diklik) |
| `app/Http/Controllers/Marketing/DashboardController.php` | Ganti `loaMasterWaClick()` jadi `loaMasterResendWa()` yang memanggil `dispatchLoaWa()` |
| `routes/web.php` | Ganti route `POST .../wa-click` jadi `POST .../resend-wa` untuk admin & marketing |
| `resources/views/admin/loa/receipt.blade.php` | Tombol WhatsApp jadi `<form>` POST ke `resendWaRoute` (dengan konfirmasi JS) alih-alih `<a href="wa.me/...">`; caption "Diklik Nx" jadi "Terkirim Nx"; hapus JS `logWaClick()`/variabel `$waMsg` yang tidak terpakai; tambah handler `session('error')` untuk toast merah kalau Fonnte gagal/belum dikonfigurasi |

## 12. Counter "Terkirim" Dibuat Lebih Mencolok (Badge Pill)

**Tujuan:** Counter "Terkirim Nx" sebelumnya cuma teks abu-abu kecil, kurang terlihat. User minta tampilan lebih mencolok.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Counter Email/WA diubah jadi badge pill berwarna (hijau untuk WA, biru untuk email) dengan border, ikon centang, dan teks bold — alih-alih teks polos abu-abu |

## 13. LOA Dikirim Sebagai Lampiran PDF di Email (Bukan Cuma Link)

**Tujuan:** User tanya apakah email bisa langsung melampirkan file PDF LOA, bukan cuma link. Project sudah punya `barryvdh/laravel-dompdf` untuk generate PDF, tapi QR code di halaman LOA dibuat oleh JavaScript (`qrcode.min.js`) sehingga tidak ikut ter-render kalau PDF dibuat apa adanya (dompdf tidak menjalankan JS). Solusi: generate QR sebagai SVG di sisi server (`simplesoftwareio/simple-qrcode`, berbasis `bacon/bacon-qr-code`, tidak butuh ekstensi Imagick) lalu suntikkan sebagai `<img>` khusus mode PDF.

Sudah diverifikasi: PDF hasil generate valid (`%PDF-1.7` header), dan QR SVG terbukti ter-render sebagai vector path asli di dalam content stream PDF (dicek lewat isolated test — bukan sekadar asumsi). Pipeline attachment Mailable juga sudah dites lewat `Mail::fake()` tanpa error. **Belum dites kirim email sungguhan dengan lampiran** — disarankan coba kirim ke email sendiri dulu sebelum dipakai ke author asli.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `composer.json` / `composer.lock` | Tambah `simplesoftwareio/simple-qrcode` (+ `bacon/bacon-qr-code`, `dasprid/enum`) |
| `app/Http/Controllers/Admin/LoaController.php` | Refactor: ekstrak `buildViewData()` dipakai bareng oleh `show()`, `publicView()`, `showMarketing()` (sebelumnya duplikat 3x); tambah `generateLoaPdf()` yang generate QR SVG server-side lalu render view jadi PDF via Dompdf dengan `defaultMediaType=print` |
| `resources/views/admin/loa/receipt.blade.php` | Tambah dukungan `$pdfMode`/`$qrDataUri`: saat mode PDF, tampilkan `<img>` QR dari data URI SVG alih-alih div kosong yang biasanya diisi JS |
| `app/Mail/LoaAcceptedMail.php` | Tambah `attachments()`: lampirkan PDF hasil `LoaController::generateLoaPdf()` dengan nama `LOA-{kode}.pdf` |

## 14. Fix PDF LOA yang Terkirim Berantakan (Gambar Hilang, Header/Footer Tidak Sejajar)

**Tujuan:** User melaporkan PDF LOA yang dikirim ke email tampil berantakan dibanding versi asli — header image tampil sebagai teks alt kosong, dan sinta-bar/verified-bar (footer dengan badge SINTA & logo akreditasi) hilang. Ditemukan 2 akar masalah:
1. **dompdf `enable_remote` default `false`** — semua gambar yang di-fetch lewat URL (`Storage::url()`/`asset()`) gagal dimuat sama sekali di PDF (baik relative maupun absolute URL), karena dompdf tidak diizinkan fetch remote sama sekali secara default. QR code sebelumnya "kebetulan" tetap muncul karena base64 data URI tidak lewat jalur fetch remote.
2. **dompdf tidak mendukung `display:flex` sama sekali** — header (`.jrn-header`), subbar, sinta-bar, dan verified-bar semua pakai flexbox untuk layout horizontal, sehingga elemen anak jadi bertumpuk/hilang di PDF.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LoaController.php` | `generateLoaPdf()`: resolve `logoUrl`/`signUrl`/`headerImageUrl`/`accreditationLogoUrl` ke **path file lokal absolut** (`Storage::disk('public')->path(...)`) alih-alih URL, supaya dompdf baca langsung dari disk tanpa perlu `enable_remote` |
| `resources/views/admin/loa/receipt.blade.php` | Tambah blok CSS `@if($pdfMode)` yang mengganti `display:flex` jadi `display:table`/`table-cell` untuk `.jrn-header`, `.jrn-subbar`, `.sinta-bar`, `.verified-bar` (dompdf-compatible); ketemu bug tersembunyi: `.verified-bar` punya `display:flex !important` di rule asli sehingga override tanpa `!important` tidak menang — ditambahkan `!important` juga supaya benar-benar jadi `display:table` |

**Cara verifikasi (tanpa kirim email sungguhan):** generate PDF langsung lewat `generateLoaPdf()` untuk 2 skenario — (a) jurnal dengan `header_image_path` custom, (b) jurnal dengan logo+subbar biasa — keduanya sempat gagal dengan error dompdf "Parent table not found for table cell" sebelum fix `!important`, dan gambar terverifikasi ter-embed (`/Subtype /Image` count > 0) setelah fix path lokal.
