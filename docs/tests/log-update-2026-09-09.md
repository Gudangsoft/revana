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
