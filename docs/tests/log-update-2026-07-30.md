# Log Update — 30 Juli 2026

## 1. Fix: Script Halaman "Pengaturan Point" Tidak Pernah Ter-render (Total PIC Selalu "—")

**Tujuan:** User menunjukkan kartu "Total point PIC untuk alur lengkap" di `/admin/task-point-settings` menampilkan `— pt` (dash), bertanya apakah ini memang seharusnya kosong.

### Root Cause

**Bukan disengaja — bug rendering Blade.** File ini pakai `@section('scripts') ... @endsection` untuk blok `<script>`-nya, tapi `resources/views/layouts/app.blade.php` (layout yang di-`@extends`) cuma menyediakan `@stack('scripts')` di bagian bawah, bukan `@yield('scripts')`. `@section`/`@yield` dan `@push`/`@stack` adalah 2 mekanisme Blade yang **terpisah dan tidak saling terhubung** — konten yang ditulis lewat `@section('scripts')` di halaman ini tidak pernah ditangkap oleh `@stack('scripts')` milik layout, sehingga seluruh JavaScript halaman ini **tidak pernah ter-render ke HTML sama sekali**.

Konvensi yang benar di project ini (dipakai 5+ halaman admin lain seperti `pic-points/index.blade.php`, `marketings/index.blade.php`) adalah `@push('scripts')`/`@endpush`.

**Dampak lebih luas dari sekadar "Total point" kosong:**
- Fungsi `recalc()` (penjumlah total rate PIC) tidak pernah jalan → kartu selalu `—`.
- Fungsi `syncRowStyle()` (styling baris saat toggle aktif/nonaktif diklik) tidak pernah jalan.
- Fungsi `confirmDelete()` (dipanggil `onclick` tombol hapus task) **tidak pernah terdefinisi** — klik tombol hapus kemungkinan besar cuma memicu error JavaScript diam-diam di console, dialog konfirmasi tidak pernah muncul, dan task tidak pernah benar-benar terhapus lewat tombol itu.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/task-point-settings/index.blade.php` | `@section('scripts')`/`@endsection` → `@push('scripts')`/`@endpush`, sesuai konvensi halaman admin lain |
| `tests/Feature/Points/PointsDisplayAuditTest.php` | Test baru: render halaman lewat HTTP request asli, assert `function recalc()` dan `picTableTotal` benar-benar muncul di HTML (sebelumnya tidak muncul sama sekali) |

### Verifikasi
- Direproduksi & dibuktikan langsung lewat `app()->handle()` (bukan cuma baca kode): SEBELUM fix, `recalc()`/`picTableTotal` tidak ada di HTML respons; SETELAH fix, keduanya muncul.
- Test baru — PASS.
- Full regression suite `tests/Feature/Points` — PASS, tidak ada regresi.

**Catatan:** murni perubahan tampilan/JS, tidak ada perubahan data/migration. Deploy: `git pull origin master` + `php artisan view:clear`. Setelah deploy, kartu "Total point PIC untuk alur lengkap" akan langsung terisi angka (bukan lagi `—`), dan tombol hapus task akan kembali berfungsi normal.

## 2. Fitur Baru: QR Code Verifikasi di Sertifikat Reviewer

**Tujuan:** User menunjukkan contoh render Sertifikat Reviewer (yang sebelumnya sudah punya info Nama Jurnal, Publisher, dan No. Surat/kode submit — ternyata sudah diimplementasikan di sesi lain sebelumnya) dan minta ditambahkan **Barcode/QR** sebagai bukti verifikasi apakah review itu benar terjadi atau tidak.

### Kendala Teknis & Solusinya

Paket QR yang sudah terpasang (`simplesoftwareio/simple-qrcode` v4.2, wrapper untuk `bacon/bacon-qr-code`) hanya punya 3 format output: `svg`, `eps`, dan `png` — tapi **`png` HANYA didukung lewat backend Imagick**, dan **server ini tidak punya ekstensi `imagick` terpasang** (dikonfirmasi langsung: `format('png')` melempar *"You need to install the imagick extension"*). Sertifikat sendiri dirender sebagai JPEG raster lewat Intervention Image (driver GD) — SVG tidak bisa langsung ditempel ke situ (GD tidak bisa decode SVG).

**Solusi:** ambil matrix QR mentah langsung dari `bacon/bacon-qr-code` (`Encoder::encode()` → `ByteMatrix`, cuma grid angka 0/1 per modul QR — tidak butuh backend gambar apa pun), lalu gambar sendiri modul-per-modul pakai GD murni (`imagefilledrectangle` per modul hitam). Hasilnya PNG asli yang valid, dibuktikan bisa didekode ulang oleh GD dan discan sebagai QR code yang benar.

### Fitur Baru
- **QR code** dicetak di pojok kiri bawah sertifikat, dengan label "Scan untuk verifikasi".
- QR mengarah ke halaman publik baru (**tidak perlu login**) yang menampilkan status keaslian review: nama reviewer, peran (Reviewer 1/2), judul artikel, nama jurnal, publisher, no. surat, dan tanggal disetujui — atau pesan "Tidak Ditemukan/Belum Valid" kalau kombinasi assignment+reviewer tidak cocok dengan review yang APPROVED di sistem.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Route publik baru `GET /verify/sertifikat-reviewer/{assignment}/{reviewerId}` → `reviewer-certificate.verify` |
| `app/Http/Controllers/Reviewer/CertificateController.php` | Method baru `verify()` (halaman publik) dan `renderQrPng()` (rasterisasi QR murni GD, lihat penjelasan teknis di atas). `generateCertificate()`: tambah overlay QR + caption "Scan untuk verifikasi" di pojok kiri bawah, sebelum blok "Save file" |
| `resources/views/reviewer/certificates/verify.blade.php` (baru) | Halaman publik hasil verifikasi — kartu hijau/emas "Terverifikasi" dengan detail review, atau kartu merah "Tidak Ditemukan" |
| `tests/Feature/ReviewerCertificateVerifyTest.php` (baru, 7 test) | Halaman verify menampilkan detail benar untuk Reviewer 1 & Reviewer 2; menolak reviewer yang tidak terkait assignment; menolak assignment yang belum APPROVED; menolak assignment yang tidak ada; `renderQrPng()` menghasilkan PNG valid yang bisa didekode ulang; `generateCertificate()` end-to-end (pakai background dummy, karena file template aktif sesungguhnya tidak tersedia di lokal) berhasil tanpa error dan menghasilkan file gambar |

### Verifikasi
- **Visual, bukan cuma unit test:** dijalankan `generateCertificate()` sungguhan (lewat reflection ke method private, background dummy) dan hasil gambarnya dilihat langsung — QR, teks reviewer, judul artikel, tanggal, dan baris No. Surat/Jurnal/Publisher semua muncul dengan benar, QR tidak tumpang tindih elemen lain.
- Halaman verifikasi diuji lewat HTTP request sungguhan untuk 5 skenario (reviewer 1 valid, reviewer 2 valid, reviewer tidak terkait, assignment belum approved, assignment tidak ada) — semua PASS.
- Test baru (7 test, 19 assertion) — PASS. Full suite `tests/Feature` (Points + fitur baru) — PASS, tidak ada regresi silang.

### Catatan Penting — Posisi QR Belum Dicek di Template Asli
Sama seperti catatan lama soal posisi teks "No. Surat/Jurnal/Publisher": **file template sertifikat yang aktif di production tidak tersedia untuk dites render langsung di lokal** (cuma ada record `Certificate` di database, file gambarnya tidak ikut ter-sync). Koordinat QR (`$qrX = 150`, `$qrY = $height - 480`, ukuran 260×260px) adalah perkiraan berdasarkan pola desain di layout referensi. **Mohon cek visual hasil sertifikat asli setelah deploy** — kalau QR tumpang tindih dengan elemen desain lain (logo, watermark, border), tinggal geser nilai `$qrX`/`$qrY` di `CertificateController::generateCertificate()`.

### Catatan Deploy
Tidak ada migration. `git pull origin master`. Tidak perlu instalasi ekstensi PHP tambahan (justru dirancang supaya TIDAK butuh imagick). Setelah deploy, coba buka halaman "Sertifikat" sebagai reviewer (review yang sudah APPROVED) dan cek preview/download — QR harus muncul dan bisa di-scan menuju halaman verifikasi.
