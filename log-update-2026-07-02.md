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

