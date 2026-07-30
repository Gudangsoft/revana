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

## 3. Fix: Judul Artikel Panjang Meluber Keluar Border Sertifikat

**Tujuan:** User menunjukkan screenshot sertifikat asli (production) — judul artikel panjang ("...ERKEMBANGAN BUDAYA ISLAM KONTEMPORER DAN INOVASI PRODUKSI KONTEN KEAGAMAAN DI RANAH DIGIT...") terpotong di KEDUA sisi kiri & kanan, meluber keluar dari border emas sertifikat.

### Root Cause

Wrapping judul artikel sebelumnya pakai `wordwrap($articleTitle, 100, "\n")` — membagi baris berdasar **jumlah karakter** (100 karakter), bukan lebar piksel sesungguhnya saat dirender. Judul dirender di ukuran font 60 (bold) di atas kanvas selebar 3508px — di ukuran itu, 100 karakter Latin biasa bisa memakan **~3500-4500px**, yaitu SAMA ATAU LEBIH LEBAR dari kanvas sertifikat itu sendiri. Karena teks di-center (`align('center')`), kelebihan lebar itu meluber rata ke KEDUA sisi — persis pola yang dilaporkan (terpotong kiri dan kanan sekaligus, bukan cuma satu sisi).

### Perbaikan

Dibuat `wrapTextByWidth()` — membungkus teks kata-per-kata berdasar **lebar piksel sesungguhnya**, diukur pakai `imagettfbbox()` (fungsi bawaan GD, tidak butuh library tambahan). Baris manapun dijamin tidak akan pernah melebihi batas lebar yang ditentukan (70% lebar kanvas, menyisakan margin untuk border emas), berapa pun panjang judulnya — tidak lagi soal tebak-tebakan jumlah karakter.

Sekalian dibersihkan: kode wrapping lama yang sudah tidak dipakai (`$wrappedTitle`/`$titleLines`, dihitung tapi tidak pernah dipakai untuk render) dihapus.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | Method baru `wrapTextByWidth()` (wrap berbasis lebar piksel via `imagettfbbox()`). Blok render judul artikel diganti pakai method ini (70% lebar kanvas). Hapus kode wrapping lama yang mati/tidak terpakai |
| `tests/Feature/ReviewerCertificateVerifyTest.php` | 2 test baru: judul PERSIS yang dilaporkan overflow sekarang terbagi >1 baris dan setiap barisnya dibuktikan (ukur ulang lewat `imagettfbbox()`) tidak pernah melebihi batas lebar, tanpa ada kata yang hilang; judul pendek tetap 1 baris (tidak berubah perilaku untuk kasus normal) |

### Verifikasi
- **Visual langsung** (bukan cuma unit test): direproduksi persis judul yang dilaporkan user, dirender ke background dummy dengan border emas simulasi di kiri-kanan — SEBELUM fix akan meluber sampai keluar border (dibuktikan lewat wordwrap 100-karakter di font 60 jauh melebihi lebar kanvas); SETELAH fix, judul terbagi rapi 2 baris, tetap di dalam area aman, tidak menyentuh border sama sekali.
- Test baru (2 test, 8 assertion) — PASS.
- Full suite `tests/Feature` — PASS, tidak ada regresi.

**Catatan:** rasio 70% lebar kanvas adalah perkiraan (sama seperti posisi elemen lain di file ini — file template aktif sesungguhnya tidak tersedia untuk dites render langsung). Kalau di template asli border-nya ternyata lebih tebal/tipis dari perkiraan, tinggal sesuaikan `$maxTitleWidthRatio` di `generateCertificate()`.

**Deploy:** murni kode, tidak ada migration. `git pull origin master`.

## 4. Tambah Logo SIPERA di Halaman Verifikasi Sertifikat Publik

**Tujuan:** User minta logo SIPERA ditambahkan di halaman verifikasi publik (`/verify/sertifikat-reviewer/{assignment}/{reviewerId}`, dituju QR code sertifikat) supaya halaman itu terlihat resmi.

**Implementasi:** dipakai pola yang SUDAH ADA di `layouts/app.blade.php` (sidebar admin/reviewer) — logo diambil dari `Setting::get('logo')` (dikonfigurasi admin lewat pengaturan aplikasi), ditampilkan sebagai `<img>`; kalau belum ada logo yang di-upload, otomatis jatuh ke teks bermerek "{app_name}" (default "SIPERA") — sama seperti fallback yang sudah dipakai di tempat lain, bukan pola baru.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | `verify()`: ambil `Setting::get('logo')`/`Setting::get('app_name')`, kirim ke SEMUA hasil view (valid maupun tidak valid) |
| `resources/views/reviewer/certificates/verify.blade.php` | Tambah section "brand" (logo/wordmark) di bagian paling atas kartu, sebelum header status hijau/merah |
| `tests/Feature/ReviewerCertificateVerifyTest.php` | 2 test baru: logo `<img>` muncul kalau `Setting` logo terisi; jatuh ke teks nama aplikasi kalau belum ada logo |

### Verifikasi
Test baru (2 test, 5 assertion) — PASS. Full suite `tests/Feature` — PASS, tidak ada regresi.

**Deploy:** murni kode, tidak ada migration. `git pull origin master`.

## 5. Pencegahan: Nama Reviewer Panjang Juga Dibungkus Berbasis Lebar Piksel

**Tujuan:** Tindak lanjut proaktif (diminta user setelah fix judul artikel #3) — nama reviewer sebelumnya dirender 1 baris TANPA pengaman lebar sama sekali (beda dari judul artikel yang sudah diperbaiki di section #3), jadi nama panjang dengan banyak gelar akademik (mis. "Prof. Dr. H. ... S.Pd., M.Pd., Ph.D.") berisiko meluber keluar border sertifikat persis seperti kasus judul artikel — belum pernah dilaporkan, tapi risikonya sama persis.

### Perbaikan
Nama reviewer sekarang dibungkus pakai `wrapTextByWidth()` yang sama (lihat #3), di ukuran font nama sesungguhnya (80). Ruang vertikal sebelum judul artikel mulai dirender (Y=1500) cukup lega (~380px) untuk menampung nama sampai 2-3 baris tanpa bertabrakan — posisi Y judul artikel TIDAK ikut digeser. Variabel `$maxTitleWidthRatio` (lebar aman 70% kanvas) dipindah jadi dideklarasikan sekali di atas, dipakai bersama oleh nama maupun judul (sebelumnya cuma dideklarasikan di blok judul).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | Blok render nama reviewer: dari `$image->text()` satu baris jadi loop lewat `wrapTextByWidth()`, sama seperti judul artikel |
| `tests/Feature/ReviewerCertificateVerifyTest.php` | Test baru: nama sangat panjang ("Prof. Dr. H. Muhammad Abdurrahman Wahyu Kusuma Wardhana, S.Pd., M.Pd., Ph.D.") terbagi >1 baris, setiap baris dibuktikan tidak melebihi batas lebar |

### Verifikasi
- **Visual langsung:** nama uji di atas (yang di render sungguhan jadi 3 baris) dirender ke background dummy dengan border emas simulasi — tetap rapi di dalam border, tidak tabrakan dengan judul artikel di bawahnya (yang sengaja dibuat pendek untuk tes ini, "Judul Artikel Pendek Saja").
- Test baru (1 test, 3 assertion) — PASS. Full suite `tests/Feature` — PASS, tidak ada regresi.

**Deploy:** murni kode, tidak ada migration. `git pull origin master`.
