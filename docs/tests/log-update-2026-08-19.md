# Log Update — 19 Agustus 2026

## 1. Perbaikan: QR "Scan untuk Verifikasi" di Kwitansi/Invoice Tidak Bisa Discan untuk Judul Artikel Panjang

**Tujuan:** User (screenshot Kwitansi production) minta dicek apakah fungsi barcode/QR sudah berfungsi baik. Diverifikasi langsung dengan generate PDF & mengukur kepadatan modul QR secara aktual (bukan cuma baca kode) — ditemukan bug nyata.

**Root cause:** QR "Scan untuk verifikasi" meng-encode SELURUH parameter pembayaran ke dalam URL, termasuk `keterangan` (= deskripsi otomatis yang memuat judul artikel penuh + nama jurnal). Judul artikel di jurnal akademik rutin 200–250+ karakter. Diuji dengan contoh nyata dari database lokal: URL QR mencapai **721 karakter**, menghasilkan **QR Version 19 (matriks 93×93 modul)** — pada ukuran render 80×80px yang dipakai kode, itu cuma **0.86 piksel per modul**, jauh di bawah ambang minimum kamera HP mana pun bisa scan. QR tetap tampil visual (makanya tidak terlihat "rusak" sekilas), tapi praktis tidak bisa discan.

**Perbaikan:**
1. QR sekarang pakai URL pendek KHUSUS (`qrVerifyUrl()`) yang cuma membawa `jumlah`, `metode_bayar`, `tanggal` — TANPA `nama_pembayar`/`keterangan` yang bisa panjang. Endpoint publik (`kwitansi.public.pdf`/`invoice.public.pdf`) sudah punya fallback yang meregenerasi nama pembayar (dari `submission->nama_penulis`) dan keterangan (teks "Biaya publikasi artikel ..." otomatis) kalau parameter itu tidak dikirim — jadi hasil scan tetap kwitansi/invoice yang lengkap dan benar.
2. Link "Kirim ke Author" (`publicPdfUrl()`, dipakai di link share WA/email/copy-link) **tidak diubah** — tetap membawa semua parameter apa adanya, supaya salinan yang diterima author tetap byte-persis dengan yang dilihat admin (tidak ada regresi di fitur kirim dokumen).
3. Ukuran render QR dinaikkan dari 80px → 160px (di layar via JS, di PDF via `size()`) sebagai margin aman tambahan — hasil akhir untuk kasus judul terpanjang yang diuji: QR Version 6 (41×41 modul) → **3,9 piksel/modul**, jauh lebih layak.

Diverifikasi hasilnya lewat generate PDF nyata (dibaca sebagai gambar) untuk submission berjudul 250+ karakter — QR sekarang terlihat jelas bermodul (bukan noise padat), teks kwitansi tetap menampilkan judul lengkap seperti biasa (cuma URL di dalam QR yang dipendekkan, bukan isi dokumennya).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/KwitansiController.php` | Tambah `qrVerifyUrl()` (URL pendek khusus QR). `show()`/`showMarketing()`: `verifyUrl` pakai method baru (bukan lagi `= $publicPdfUrl`). `generateKwitansiPdf()`: QR di-generate dari `qrVerifyUrl()`, size 80→160. |
| `app/Http/Controllers/Admin/InvoiceController.php` | Sama persis: tambah `qrVerifyUrl()`, `show()`/`showMarketing()`/`generateInvoicePdf()` diarahkan ke method baru, size 80→160. |
| `resources/views/admin/kwitansi/receipt.blade.php` | CSS `.qr-wrap`, `<img>` PDF-mode, dan opsi JS `QRCode()` — semua 80→160px. |
| `resources/views/admin/invoice/receipt.blade.php` | Sama persis. |
| `tests/Feature/QrVerificationDensityTest.php` | Baru, 5 test: QR Kwitansi/Invoice tetap "scannable" (≥2.5 px/modul @160px) untuk submission berjudul 250+ karakter; link share masih membawa nama/keterangan lengkap (regresi fitur kirim ke author); URL pendek yang ada di QR, kalau benar-benar di-fetch (simulasi scan), tetap menghasilkan PDF valid HTTP 200. |

### Verifikasi
- `tests/Feature/QrVerificationDensityTest.php` — 5/5 PASS, 16 assertions.
- Diukur langsung pakai `BaconQrCode\Encoder` (bukan asumsi): kondisi lama 721 karakter → V19/93×93/0.86px per modul @80px; kondisi baru 112 karakter → V6/41×41/3.9px per modul @160px.
- PDF nyata di-generate & dibaca sebagai gambar (submission judul 250+ karakter) — QR baru terlihat jelas bermodul, dokumen tetap menampilkan judul lengkap seperti biasa.
- Simulasi scan end-to-end (`app()->handle()` ke URL pendek tanpa nama_pembayar/keterangan) — HTTP 200, PDF valid, konten ter-regenerasi otomatis dengan benar dari data submission asli.
- Full suite `tests/Feature` — 135 test, 364 assertions, **0 failure**.

### Catatan Deploy
Tidak ada migration — murni perubahan kode (controller + view). Bisa langsung deploy. Dokumen kwitansi/invoice yang SUDAH terlanjur dibagikan (link lama dengan URL panjang di QR-nya) tetap akan tampil sama seperti sebelumnya kalau di-generate ulang lewat link tersebut (endpoint publik tidak berubah, cuma QR yang di-generate untuk halaman/PDF BARU yang berubah) — tidak ada breaking change untuk link yang sudah beredar.
