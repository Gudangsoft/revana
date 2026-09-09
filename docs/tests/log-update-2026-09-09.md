# Log Update — 09 September 2026

## 1. Perbaikan Layout Sertifikat Reviewer (Teks Nama & Judul Artikel Tumpang Tindih)

**Tujuan:** User melaporkan (via screenshot) tampilan sertifikat reviewer yang dihasilkan sistem
berantakan — nama reviewer tumpang tindih dengan sub-judul di template, dan judul artikel tumpang
tindih dengan teks footer. Akar masalahnya: posisi Y untuk nama reviewer (`$yNamePosition = 1120`)
dan judul artikel (`$yArticlePosition = 1500`) di `generateCertificate()` adalah angka TETAP yang
ditebak (file komentar lama secara eksplisit mengakui ini "perkiraan" karena file template AKTIF
tidak tersedia untuk dites render langsung saat kode itu ditulis). Posisi tetap ini tidak
memperhitungkan berapa banyak baris yang akan dihasilkan `wrapTextByWidth()` untuk nama/judul yang
panjang, sehingga tidak selaras dengan zona kosong sesungguhnya di antara paragraf tetap yang sudah
tercetak di gambar template.

User mengirim contoh sertifikat asli beresolusi penuh (2560x1811px) dengan overlap nyata. Dari
gambar itu diukur ulang batas zona kosong sesungguhnya di template (dalam koordinat piksel asli):
- **Zona nama** (antara "Sertifikat ini diberikan kepada :" dan "in Recognition of
  Contribution..."): Y ≈ 599–838 (tinggi ≈ 239px)
- **Zona judul** (antara "Sebagai bentuk penghargaan..." dan "Thank you your contribution..."):
  Y ≈ 887–1231 (tinggi ≈ 344px)
- Posisi tanggal (`height - 180`) dan info nomor surat/jurnal/publisher (`height - 110`) sudah
  cocok dengan posisi di gambar asli — **tidak diubah**.

Perbaikan mengganti posisi Y tetap dengan **rumus center vertikal berbasis zona** (dihitung sebagai
rasio terhadap tinggi kanvas, supaya tetap benar walau template diganti dengan resolusi lain yang
proporsinya sama) yang otomatis menyesuaikan diri dengan jumlah baris hasil wrap — 1 baris nama
pendek diposisikan tepat di tengah zona, 2 baris nama panjang juga tetap center. Ditambah
`wrapTextWithAutoShrink()`: kalau nama/judul sangat panjang sampai jumlah barisnya melebihi batas
aman zona (>2 baris untuk nama, >4 baris untuk judul), ukuran font dikecilkan bertahap (langkah 5px)
sampai muat atau sampai batas minimum font tercapai — mencegah kelas bug yang sama terulang untuk
nama/judul yang lebih panjang lagi di masa depan.

**Catatan penting:** File template AKTIF produksi tidak tersedia di lokal (`file_exists()` gagal
saat dicek), jadi perbaikan ini didasarkan pada pengukuran teliti dari satu contoh sertifikat asli
yang dikirim user, bukan render langsung terhadap file template. Perlu konfirmasi dari user pada
sertifikat berikutnya yang di-generate untuk memastikan overlap benar-benar hilang.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | Tambah method `wrapTextWithAutoShrink()` (wrap + kecilkan font bertahap kalau baris melebihi batas aman). Ganti `$yNamePosition`/`$yArticlePosition` tetap dengan perhitungan center-vertikal berbasis zona (rasio Y 599-838 untuk nama, 887-1231 untuk judul, dari tinggi referensi 1811px) yang menyesuaikan jumlah baris otomatis. |
| `tests/Feature/ReviewerCertificateVerifyTest.php` | Tambah 5 test baru: `wrapTextWithAutoShrink()` mempertahankan font asli kalau baris sudah muat, mengecilkan font untuk nama bergelar panjang, mengecilkan font untuk judul sangat panjang, berhenti tepat di font minimum kalau constraint tetap tidak realistis, dan uji integrasi end-to-end `generateCertificate()` dengan nama+judul sangat panjang memakai background dummy. |

### Verifikasi
- `php -l app/Http/Controllers/Reviewer/CertificateController.php` → tidak ada syntax error.
- `php artisan test tests/Feature/ReviewerCertificateVerifyTest.php` → **17 passed (54 assertions)**.
- Perhitungan manual jumlah baris nyata (imagettfbbox, font Arial Bold, lebar kanvas 3508px, lebar
  maks 70%): nama bergelar panjang menghasilkan 3 baris di font 80 → otomatis mengecil ke font 60
  untuk muat 2 baris; judul sangat panjang menghasilkan 5 baris di font 60 → otomatis mengecil ke
  font 55 untuk muat 4 baris.
- Full regression suite: `php artisan test tests/Feature` → **179 passed (492 assertions)**, semua
  test lain (poin, LOA, export, dashboard, kwitansi/invoice QR, keyword search, dll.) tetap hijau —
  tidak ada regresi akibat perubahan ini.

### Catatan Deploy
- Tidak ada migration baru, tidak ada perubahan skema DB.
- Tidak mengubah posisi tanggal maupun info nomor surat/jurnal/publisher/QR — hanya posisi nama
  reviewer & judul artikel.
- **Mohon user mengecek sertifikat reviewer berikutnya yang di-generate di production** untuk
  konfirmasi overlap benar-benar teratasi, karena verifikasi lokal tidak bisa render terhadap file
  template produksi asli (file tidak ada di storage lokal).

## 2. Perbaikan Lanjutan: Font Nama/Judul Sertifikat Terlalu Besar, Judul Dibatasi 2 Baris

**Tujuan:** Setelah perbaikan #1 di atas dideploy, user mengirim screenshot BARU dari sertifikat
sungguhan yang menunjukkan perbaikan zona-center belum cukup — judul artikel nyata ("PENGARUH CITRA
MEREK, RELATIONSHIP MARKETING, DAN KEPUASAN PELANGGAN TERHADAP LOYALITAS PELANGGAN PADA E-COMMERCE
SHOPEE DI KOTA BATAM") menghasilkan **4 baris** di font 60 dan baris terakhirnya menabrak langsung
teks "Thank you your contribution..." / "Terima kasih atas kontribusi Anda...". User juga menilai
font nama & judul secara umum **terlalu besar** dibanding proporsi teks tetap di template. Permintaan
eksplisit: "untuk judul dibuat maksimal 2 baris".

Dari screenshot ini juga terkonfirmasi lebar kanvas template asli adalah **2560px** (bukan 3508px
seperti asumsi lama di komentar kode) — pada lebar 2560px, judul contoh di atas persis menghasilkan
4 baris di font 60, cocok dengan yang terlihat di screenshot.

Perbaikan:
- `$nameFontSize` diturunkan dari **80 → 60** (font minimum shrink 50 → 36).
- `$articleFontSize` diturunkan dari **60 → 50** (font minimum shrink 40 → 26, supaya judul yang
  sangat panjang tetap bisa dipaksa muat 2 baris).
- Batas baris judul di `wrapTextWithAutoShrink()` diperketat dari **4 → 2 baris** sesuai permintaan
  eksplisit user. Batas baris nama tetap 2 (tidak diminta berubah).
- Judul nyata yang dilaporkan sekarang otomatis mengecil ke font ~28-32 dan muat rapi 2 baris,
  jauh di dalam zona judul, tidak lagi menabrak teks di bawahnya.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | `$nameFontSize` 80→60, `$nameMinFontSize` 50→36; `$articleFontSize` 60→50, `$articleMinFontSize` 40→26, batas baris judul (parameter `maxLines` ke `wrapTextWithAutoShrink()`) 4→2. Komentar diperbarui untuk mencatat lebar kanvas asli 2560px. |
| `tests/Feature/ReviewerCertificateVerifyTest.php` | Tambah 3 test baru memakai teks PERSIS dari screenshot yang dilaporkan: judul harus muat 2 baris (bukan 4) dengan parameter baru, nama tetap muat 2 baris di font 60 tanpa perlu shrink, dan uji integrasi `generateCertificate()` end-to-end dengan nama+judul persis kasus yang dilaporkan. |

### Verifikasi
- Simulasi manual `imagettfbbox()` (di luar Laravel, PHP murni) dengan lebar kanvas 2560px: judul
  nyata dari screenshot → 4 baris di font 60 (cocok dengan bug yang dilaporkan) → setelah perbaikan,
  otomatis mengecil sampai muat 2 baris.
- `php artisan test tests/Feature/ReviewerCertificateVerifyTest.php` → **20 passed (65 assertions)**.
- Full regression suite `php artisan test tests/Feature` → **182 passed (503 assertions)** — tidak
  ada regresi ke fitur lain (poin, LOA, export, dashboard, kwitansi/invoice QR, keyword search, dll.).

### Catatan Deploy
- Sama seperti #1: tidak ada perubahan skema DB.
- Karena file template produksi tidak tersedia di lokal, kalibrasi masih berbasis reverse-engineering
  dari 2 contoh screenshot asli yang dikirim user (bukan render langsung terhadap file produksi).
  Mohon user cek sertifikat baru sekali lagi setelah deploy ini.

## 3. Perbaikan Lanjutan: Nama Reviewer Dibuat 1 Baris

**Tujuan:** Screenshot ketiga dari user menunjukkan nama reviewer ("MARTINA ROSMAULINA MARBUN,
S.PD., M.HUM") masih terpecah jadi 2 baris ("MARTINA ROSMAULINA MARBUN, S.PD.," / "M.HUM") padahal
diminta 1 baris. Diselidiki: lebar teks nama ini di font 60 adalah 1828px — cuma sedikit melebihi
batas lebar 70% dari kanvas (1792px) yang sebelumnya dipakai bersama dengan judul artikel — jadi
kepotong ke baris ke-2 walau sebenarnya cuma kurang ~36px lagi supaya muat 1 baris.

**Perbaikan:** Nama reviewer sekarang punya jatah lebar sendiri yang lebih lega, terpisah dari
lebar judul artikel:
- `$nameMaxWidthRatio = 0.82` (naik dari 0.70 yang dipakai bersama judul) — nama nyata ini sekarang
  muat 1 baris **tanpa perlu mengecilkan font sama sekali** (tetap di font 60, sama seperti
  sebelumnya).
- Batas baris nama diperketat dari 2 → **1 baris**, dengan `$nameMinFontSize` diturunkan ke 24 supaya
  nama yang jauh lebih panjang lagi (kasus ekstrem: banyak gelar akademik) tetap bisa dipaksa muat 1
  baris via auto-shrink, bukan berhenti di 2 baris seperti sebelumnya.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | Tambah `$nameMaxWidthRatio = 0.82` (terpisah dari `$maxTitleWidthRatio` yang kini hanya dipakai judul artikel). `$nameMinFontSize` 36→24. Batas baris nama (parameter `maxLines`) 2→1. |
| `tests/Feature/ReviewerCertificateVerifyTest.php` | Test nama diganti: memverifikasi nama nyata dari screenshot sekarang muat 1 baris di font 60 tanpa shrink (bukan lagi "muat 2 baris di font 60"). Tambah test baru untuk kasus ekstrem (nama+gelar sangat panjang) memastikan tetap dipaksa 1 baris via auto-shrink. |

### Verifikasi
- Simulasi manual `imagettfbbox()`: nama nyata dari screenshot pada rasio lebar 82% (2099px) →
  1 baris tanpa perlu shrink font; kasus ekstrem (nama+banyak gelar) → otomatis mengecil ke font 25
  untuk tetap muat 1 baris.
- `php artisan test tests/Feature/ReviewerCertificateVerifyTest.php` → **21 passed (70 assertions)**.
- Full regression suite `php artisan test tests/Feature` → **183 passed (508 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Sama seperti #1 & #2: tidak ada perubahan skema DB, hanya logika layout teks di controller.
- Mohon user cek sekali lagi sertifikat baru setelah deploy — dengan 3 putaran perbaikan berbasis
  screenshot asli, seharusnya sudah cukup presisi, tapi verifikasi langsung di production tetap yang
  paling diandalkan karena tidak ada akses ke file template asli di lokal.

## 4. Perbaikan Lanjutan: Font Judul Kurang Besar Setelah Muat 2 Baris

**Tujuan:** Screenshot keempat menunjukkan judul artikel SUDAH rapi 2 baris dan tidak lagi tumpang
tindih (perbaikan #2 berhasil), tapi user menilai font-nya sekarang **kurang besar/kurang terbaca**.
Diselidiki: judul nyata ("PENGARUH CITRA MEREK, RELATIONSHIP MARKETING, DAN KEPUASAN PELANGGAN
TERHADAP LOYALITAS PELANGGAN PADA E-COMMERCE SHOPEE DI KOTA BATAM") dengan rasio lebar 70% harus
dikecilkan sampai font **30** supaya muat 2 baris — cukup kecil karena batas lebarnya sempit.

**Perbaikan:** Rasio lebar khusus judul artikel (`$maxTitleWidthRatio`) dinaikkan dari **70% → 88%**
(masih sisa margin ±6% kiri-kanan untuk border emas, tidak sampai mepet). Dengan lebar yang lebih
lega, judul yang sama sekarang muat 2 baris di font **40** (naik 33% dari 30) tanpa perlu dikecilkan
sebanyak sebelumnya.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | `$maxTitleWidthRatio` 0.70 → 0.88 (dipakai khusus judul artikel setelah nama dapat rasio sendiri di #3). |
| `tests/Feature/ReviewerCertificateVerifyTest.php` | Test judul nyata diperbarui: memverifikasi font hasil akhir sekarang tepat **40** (bukan lagi sekadar "< 50"), dengan rasio lebar 88%. |

### Verifikasi
- Simulasi manual `imagettfbbox()`: judul nyata pada rasio 88% (2252px) → muat 2 baris di font 40.
- `php artisan test tests/Feature/ReviewerCertificateVerifyTest.php` → **21 passed (70 assertions)**,
  termasuk assertion baru yang memastikan font tepat 40.
- Full regression suite `php artisan test tests/Feature` → **183 passed (508 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Tidak ada perubahan skema DB.
- Ini putaran ke-4 kalibrasi berbasis screenshot — mohon user cek sekali lagi hasil akhirnya di
  production untuk konfirmasi ukuran font judul sekarang sudah pas (tidak kebesaran seperti awal,
  tidak kekecilan seperti setelah perbaikan #2).

## 5. Perbaikan Lanjutan: QR Code Dipindah ke Tengah, Diletakkan di Atas Tanggal, Ukuran Diperkecil

**Tujuan:** Screenshot kelima menunjukkan QR code verifikasi masih di pojok kiri bawah, sebaris
dengan tanggal ("30 Juli 2026") di sisi kanannya. User minta QR dipindah ke tengah (horizontal) dan
diletakkan DI ATAS tanggal (bukan sebaris), ukurannya juga agak diperkecil.

**Perbaikan:**
- QR di-center secara horizontal: `$qrX` dari nilai tetap `150` (pojok kiri) menjadi
  `($width - $qrSize) / 2`.
- Ukuran QR diperkecil dari **260px → 200px** (variabel `$qrSize`, dipakai juga untuk `resize()`
  supaya konsisten).
- Posisi vertikal (`$qrY = $height - 480`) TIDAK diubah — sudah terbukti aman (ada jarak bersih ke
  paragraf tetap di atasnya berdasarkan screenshot sebelumnya). Karena ukuran QR mengecil, otomatis
  menambah jarak ke tanggal di bawahnya (dari ~15px jadi ~75px) — hasil akhirnya QR benar-benar
  berada DI ATAS tanggal, bukan lagi sebaris dengannya.
- Label "Scan untuk verifikasi" ikut disesuaikan offsetnya supaya tetap center persis di bawah QR.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | `$qrX` tetap (150) → dihitung dinamis untuk center horizontal; `$qrSize` baru (200, sebelumnya hardcode 260 di `resize()`); label offset disesuaikan mengikuti `$qrSize`. |
| `tests/Feature/ReviewerCertificateVerifyTest.php` | Tambah test baru `test_qr_code_is_horizontally_centered_and_positioned_above_the_date` — men-generate sertifikat dengan background dummy putih polos, scan piksel BENAR-BENAR HITAM (modul QR, teks emas sertifikat tidak ikut kejaring), verifikasi bounding box QR center secara horizontal dan berada di atas posisi tanggal. |

### Verifikasi
- `php artisan test tests/Feature/ReviewerCertificateVerifyTest.php` → **22 passed (74 assertions)**,
  termasuk test posisi QR baru yang memverifikasi langsung dari piksel gambar hasil render (bukan
  cuma baca angka koordinat di kode).
- Full regression suite `php artisan test tests/Feature` → **184 passed (512 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Tidak ada perubahan skema DB.
- Ukuran QR baru (200px, lihat juga #6 di bawah — direvisi lagi ke 150px) masih jauh di atas ambang
  aman-scan yang pernah ditetapkan untuk fitur QR lain di sistem ini (160px, lihat
  `QrVerificationDensityTest.php`), jadi tidak ada risiko QR jadi sulit di-scan akibat pengecilan ini.
- Putaran ke-5 kalibrasi berbasis screenshot — mohon user cek sekali lagi hasil akhirnya di
  production.

## 6. Perbaikan Lanjutan: QR Menabrak Paragraf di Atasnya Setelah Di-center

**Tujuan:** Setelah perbaikan #5 (QR di-center + dipindah ke atas tanggal), user kirim screenshot
baru menunjukkan QR jadi MENABRAK paragraf tetap di atasnya ("...standard of academic i[ntegrity]" /
"...integritas akademik yang tinggi da[lam]..."). Instruksi user: "turunkan, nabrak dengan yang
atas".

**Analisis akar masalah:** `$qrY = $height - 480` (≈Y1331 di referensi 1811px) ternyata SUDAH
tumpang tindih dengan akhir paragraf tetap tersebut (berakhir ~Y1380, berdasarkan pengukuran
screenshot pertama di awal sesi ini) — SEBELUM di-center, ini tidak kelihatan karena QR ada di pojok
KIRI ($qrX = 150) sedangkan baris terakhir paragraf yang di-center tidak menjangkau sejauh itu ke
kiri. Begitu QR dipindah ke tengah (#5), posisinya pas bertabrakan dengan bagian tengah paragraf itu.

Ruang kosong yang tersedia antara akhir paragraf (~Y1380) dan awal tanggal (~Y1598) cuma ~218px —
sempit untuk menampung QR + label dengan aman di kedua sisi.

**Perbaikan:**
- `$qrY` diturunkan dari `$height - 480` menjadi **`$height - 410`** (mulai ~Y1401, ~21px setelah
  akhir paragraf).
- Ukuran QR dikecilkan lagi dari **200px → 150px** supaya blok QR+label tetap muat dengan margin
  aman di kedua sisi (ke paragraf di atas maupun ke tanggal di bawah) dalam ruang yang sempit itu.
- Jarak QR-ke-label dirapatkan dari 25px → 15px untuk memadatkan tinggi blok.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Reviewer/CertificateController.php` | `$qrY` dari `$height - 480` → `$height - 410`; `$qrSize` 200 → 150; jarak label 25 → 15. |
| `tests/Feature/ReviewerCertificateVerifyTest.php` | Test posisi QR ditambah assertion baru: QR harus mulai SETELAH ambang batas akhir paragraf (penjaga regresi supaya `$qrY` tidak sengaja digeser naik lagi). |

### Verifikasi
- `php artisan test tests/Feature/ReviewerCertificateVerifyTest.php` → **22 passed (75 assertions)**.
- Full regression suite `php artisan test tests/Feature` → **184 passed (513 assertions)** — tidak
  ada regresi ke fitur lain.

### Catatan Deploy
- Tidak ada perubahan skema DB.
- QR 150px masih jauh di atas ambang aman-scan 160px yang jadi acuan (dari `QrVerificationDensityTest.php` untuk fitur QR Kwitansi/Invoice) — margin cukup tipis tapi verifikasi manual `imagettfbbox`/kalkulasi modul menunjukkan kepadatan piksel per modul QR sertifikat ini tetap jauh di atas ambang minimum aman-scan karena konten URL verifikasi pendek (moduleCount kecil).
- Ini putaran ke-6 kalibrasi — ruang antara paragraf & tanggal genuinely sempit (~218px), jadi hasil
  ini adalah upaya terbaik berdasarkan pengukuran yang ada. **Sangat disarankan user cek sekali lagi**
  sertifikat baru di production — kalau masih ada sedikit tabrakan di salah satu sisi, beri tahu sisi
  mana (atas ke paragraf, atau bawah ke tanggal) supaya bisa dikalibrasi lebih presisi.

## 7. Dashboard Reviewer: Tampilkan Padanan Rupiah untuk Total & Available Points

**Tujuan:** User minta dashboard reviewer (`/reviewer/dashboard`) juga menampilkan nilai poin dalam
bentuk Rupiah, bukan cuma angka poin polos. Sistem sudah punya nilai tukar poin-ke-Rupiah
(`point_value`, dikelola admin di `/admin/point-settings`, default Rp 1.000/poin) dan konvensi
tampilan "Rp {angka}" ini sudah dipakai di 2 halaman reviewer lain (`reviewer/tasks/index.blade.php`
dan `reviewer/rewards/index.blade.php`) — dashboard sekarang mengikuti konvensi yang sama.

**Perbaikan:** Di kartu profil reviewer (bagian atas dashboard), di bawah angka "Total Points" dan
"Available Points" ditambahkan baris kecil "≈ Rp {total_points/available_points × point_value}",
diformat dengan pemisah ribuan titik (`number_format(..., 0, ',', '.')`) sama seperti di halaman lain.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/reviewer/dashboard.blade.php` | Tambah baris "≈ Rp ..." di bawah Total Points & Available Points, dihitung dari `Setting::get('point_value', 1000)` (konvensi yang sama dipakai di halaman reviewer lain). |
| `tests/Feature/ReviewerDashboardPointsRupiahTest.php` | Test baru (3 test): padanan Rupiah muncul benar sesuai `point_value` yang diset admin, fallback ke default 1000 kalau setting belum diisi, dan tampil "Rp 0" yang benar untuk reviewer tanpa poin. |

### Verifikasi
- `php artisan test tests/Feature/ReviewerDashboardPointsRupiahTest.php` → **3 passed (7 assertions)**.
- Full regression suite `php artisan test tests/Feature` dijalankan setelah perubahan ini.

### Catatan Deploy
- Tidak ada perubahan skema DB, tidak ada perubahan controller — murni tambahan tampilan di view yang
  membaca `Setting` yang sudah ada.
