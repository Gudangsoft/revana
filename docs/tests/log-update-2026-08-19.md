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

---

## 2. Widget Baru: Monitoring Tren Submission di Dashboard Admin

**Tujuan:** User minta grafik monitoring tren submission di `/admin/dashboard`, khusus total submission (bukan dipecah status), dengan filter granularitas per tahun/bulan/hari. Dashboard sudah punya chart "Tren Submission {tahun}" (Total+Published+Rejected, fixed ke tahun berjalan, tanpa filter) — dibiarkan tidak berubah, widget baru ini ditambahkan terpisah supaya tidak mengganggu apa yang sudah ada.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/DashboardController.php` | `index()`: tambah `$submissionYears` (daftar tahun yang benar-benar punya data submission, cache 5 menit, dipakai isi dropdown filter tahun). Method baru `submissionTrend(Request $request)`: endpoint JSON, terima `period` (year/month/day, default month kalau nilai tidak dikenal), `year`, `month`; agregasi `COUNT(*) GROUP BY` YEAR/MONTH/DAY sesuai `created_at`, kembalikan `{labels, data, total}`. |
| `routes/web.php` | Route baru `GET /admin/dashboard/submission-trend` → `admin.dashboard.submission-trend`. |
| `resources/views/admin/dashboard.blade.php` | Card baru "Monitoring Tren Submission" (di bawah row chart yang sudah ada): dropdown Per Tahun/Bulan/Hari + dropdown Tahun (disembunyikan kalau period=year) + dropdown Bulan (cuma tampil kalau period=day), canvas Chart.js (bar, satu dataset "Total Submission"), teks ringkasan total. JS: `fetch()` ke endpoint baru tiap filter berubah, update chart tanpa reload halaman. |
| `tests/Feature/SubmissionTrendTest.php` | Baru, 5 test: period month mengembalikan 12 label dengan hitungan benar per bulan (dan tidak ikut menghitung data tahun lain); period day mengembalikan jumlah hari sesuai bulan terpilih (termasuk kasus Februari 28 hari) dengan hitungan benar per tanggal; period year mengelompokkan lintas semua tahun yang ada; period tidak dikenal jatuh ke default month; halaman dashboard menampilkan widget & elemen filter baru. |

### Verifikasi
- `tests/Feature/SubmissionTrendTest.php` — 5/5 PASS, 20 assertions.
- Smoke test manual `app()->handle()` dengan data lokal riil (14.691 submission, semua tahun 2026): period=year → 1 label/total 14.691; period=month (2026) → 12 label/total 14.691 (konsisten); period=day (Juni 2026) → 30 label/total 3.201 (cuma bulan itu). Halaman dashboard HTTP 200, widget baru terkonfirmasi muncul di HTML.
- Full suite `tests/Feature` — 140 test, 384 assertions, **0 failure**.

### Catatan Deploy
Tidak ada migration. Murni penambahan (route + controller method + view) — tidak mengubah widget/chart dashboard yang sudah ada.

**Revisi (masih tanggal sama): Tambah Filter Kategori (Semua/Normal/Fasttrack/BKD/JAFA)**

User (screenshot widget yang sudah jalan) minta tambahan filter kategori submission: "semua - normal - bkd - ft dll". Ditambahkan dropdown kategori memakai definisi yang SAMA PERSIS dengan yang sudah dipakai stat card dashboard (`$regularSubmissions`/`$fasttrackSubmissions` berbasis `process_type`, `$bkdStats`/`$jafaStats` berbasis `program_type`) — supaya angkanya konsisten dengan bagian dashboard lain, bukan definisi baru. Kategori SENGAJA tidak saling eksklusif (mengikuti kombinasi data nyata: ada submission yang `process_type` normal TAPI `program_type` bkd, jadi ikut terhitung di kategori "Normal" maupun "BKD" sekaligus) — dikonfirmasi lewat 77 baris kombinasi "normal+bkd" yang benar-benar ada di database.

File tambahan yang diubah: `DashboardController.php` (property `$trendCategoryOptions`, method `applyTrendCategory()` sebagai satu sumber kebenaran filter, param `kategori` di `submissionTrend()`, response tambah `kategori_label`; `index()` kirim `$trendCategoryOptions` ke view), `dashboard.blade.php` (dropdown kategori baru di kiri dropdown Per Tahun/Bulan/Hari, JS kirim param `kategori` + update label dataset chart sesuai kategori terpilih), `SubmissionTrendTest.php` (+5 test: filter normal/fasttrack/bkd/semua menghitung benar, kategori tidak dikenal jatuh ke "Semua", widget dashboard menampilkan dropdown & opsi kategori baru).

Verifikasi tambahan: 10/10 test PASS (37 assertions). Smoke test data riil — semua (14.691), normal (11.837), fasttrack (2.854), bkd (78), jafa (11) — angka bkd/fasttrack lebih besar dari total masing-masing kategori "murni" karena overlap yang memang disengaja. Full suite akhir `tests/Feature` — **145 test, 401 assertions, 0 failure**.

**Revisi lagi (masih tanggal sama): Rincian Kategori di Tooltip Chart**

User (screenshot hover chart, cuma menampilkan "Total Submission (Semua): 176") minta tooltip menampilkan rincian per kategori juga — "di detail lagi / Normal / Fasttrack / BKD / JAFA berapa, gitu". Ditulis ulang `submissionTrend()`: sekarang SATU query per request menghitung total DAN keempat kategori sekaligus (pakai `SUM(CASE WHEN ...)` conditional per kategori, bukan 4 query terpisah), lalu `data` (tinggi bar) tetap ikut kategori yang dipilih di dropdown seperti sebelumnya, tapi response juga menyertakan `breakdown` (array Normal/Fasttrack/BKD/JAFA per titik) yang SELALU lengkap terlepas dari kategori yang diminta. `applyTrendCategory()` (query-filter pendekatan lama) dihapus, digantikan pendekatan conditional-aggregate ini yang lebih efisien (1 query, bukan N).

Frontend (`dashboard.blade.php`): tooltip Chart.js diberi `callbacks.afterBody` yang membaca variabel `trendBreakdown` (diisi ulang tiap `loadTrend()` dari `json.breakdown`) dan menambahkan baris "Normal: X", "Fasttrack: Y", "BKD: Z", "JAFA: W" di bawah baris total yang sudah ada — muncul apa pun kategori yang sedang aktif di dropdown.

File yang diubah: `DashboardController.php` (constant `TREND_CATEGORY_SQL`, `submissionTrend()` ditulis ulang total), `dashboard.blade.php` (tooltip callback + variabel `trendBreakdown`), `SubmissionTrendTest.php` (+1 test: response tetap mengembalikan breakdown 4 kategori lengkap & benar meski `kategori=fasttrack` diminta secara eksplisit; `data` chart tetap ikut kategori yang diminta, bukan breakdown-nya).

Verifikasi: 11/11 test PASS (47 assertions). Smoke test data riil (17 Juli 2026, kategori=semua): total hari itu 75, breakdown Normal=58/Fasttrack=17/BKD=1/JAFA=0 — Normal+Fasttrack=75 (cocok persis dgn total, karena kedua kategori itu memang saling eksklusif di dimensi `process_type`). Full suite akhir `tests/Feature` — **146 test, 411 assertions, 0 failure**.

---

## 3. Tambah Dropdown "Tipe Proses" (Normal/Fasttrack) di Edit Submission

**Tujuan:** User (screenshot dropdown "Program": Normal/BKD/JAFA di `/admin/submissions/{id}/edit`) minta "tambahkan fasttrack" ke situ. Dicek dulu: "Program" (`program_type`) dan "Fasttrack" (`process_type`) ternyata dua kolom BERBEDA dan independen di model `Submission` (dikonfirmasi ada kombinasi nyata "normal+bkd" dan "fasttrack+bkd" di database, dari investigasi widget dashboard sebelumnya) — `process_type` dipakai luas oleh dashboard, `PicController`, dan menu Fasttrack (`admin.fasttrack-management`, dll), sedangkan `program_type` cuma menentukan prefix Kode Submit (SUB/BKD/JAF).

Ditanyakan ke user: tambahkan "Fasttrack" sebagai opsi ke-4 di dropdown Program yang sama (berisiko data tidak konsisten — tersimpan ke field yang salah, tidak terhitung Fasttrack di tempat lain) vs dropdown terpisah untuk `process_type`. User pilih **dropdown terpisah**.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/submissions/edit.blade.php` | Dropdown baru "Tipe Proses" (Normal/Fasttrack) di sebelah dropdown "Program" yang sudah ada, `name="process_type"`. |
| `app/Http/Controllers/Admin/SubmissionController.php` | `update()`: tambah validasi `process_type` (`nullable`, `Rule::in(['normal','fasttrack'])`) — TIDAK menyentuh logika sync prefix kode_submit (itu cuma utk `program_type`, `process_type` memang tidak memengaruhi prefix). |
| `app/Models/ActivityLog.php` | Tambah `process_type` ke `SUBMISSION_TRACKED_FIELDS` (label "Tipe Proses") supaya perubahan tercatat di log aktivitas, konsisten dengan `program_type`. |
| `tests/Feature/SubmissionProcessTypeEditTest.php` | Baru, 6 test: submission normal bisa ditandai Fasttrack via form edit (dan `isFasttrack()` ikut benar), bisa dikembalikan ke Normal, mengubah `process_type` TIDAK mengubah prefix `kode_submit` (beda dgn `program_type`), `process_type` dan `program_type` benar-benar independen (bisa kombinasi bkd+fasttrack), nilai tidak valid ditolak validasi, halaman edit menampilkan dropdown & field baru. |

### Verifikasi
- `tests/Feature/SubmissionProcessTypeEditTest.php` — 6/6 PASS, 13 assertions.
- Smoke test manual `app()->handle()` dengan data lokal riil: halaman edit submission asli (`SUB202602020001`) HTTP 200, dropdown "Tipe Proses" terkonfirmasi muncul di HTML.
- Full suite `tests/Feature` — 152 test, 424 assertions, **0 failure**.

### Catatan Deploy
Tidak ada migration (kolom `process_type` sudah ada sejak awal, cuma belum ada UI utk mengubahnya di halaman edit ini). Murni penambahan form field + validasi — tidak mengubah perilaku default (submission lama tetap `process_type` apa adanya, cuma sekarang bisa diubah manual lewat form ini juga selain lewat alur Fasttrack khusus yang sudah ada).

---

## 4. Sembunyikan Rincian Kategori di Tooltip Saat Kategori Spesifik Dipilih

**Tujuan:** User (screenshot tooltip dgn kategori "Normal" terpilih) melaporkan baris rincian jadi redundan — tooltip menampilkan "Total Submission (Normal): 98" DAN "Normal: 98" sekaligus (angka yang sama dua kali). Rincian per kategori (section #2 revisi ke-2) cuma berguna saat melihat "Semua" — begitu kategori spesifik (Normal/Fasttrack/BKD/JAFA) sudah dipilih, baris total di atas sudah menampilkan angka itu, jadi rincian jadi tidak perlu.

Diperbaiki murni di JS (`dashboard.blade.php`): callback `afterBody` sekarang cek `kategoriSel.value !== 'semua'` dan mengembalikan array kosong (tidak ada baris tambahan) kalau kategori spesifik sedang dipilih — rincian 4 kategori cuma muncul saat dropdown kategori = "Semua". Tidak ada perubahan backend (`submissionTrend()` tetap mengirim `breakdown` apa adanya; hanya tampilannya yang disaring di sisi client).

### Verifikasi
- Smoke test manual `app()->handle()` — halaman dashboard HTTP 200, logika baru terkonfirmasi ada di HTML yang dirender.
- Full suite `tests/Feature` — 152 test, 424 assertions, **0 failure** (tidak ada test PHP baru karena ini murni perilaku tampilan tooltip JS, tidak ada logika backend yang berubah).

### Catatan Deploy
Tidak ada migration, tidak ada perubahan backend — murni edit satu file view.

---

## 5. Pencarian Keyword Bebas di Halaman Data Submit & Monitoring

**Tujuan:** User (screenshot sidebar "Data Submit"/"Monitoring") minta sistem pencarian ditambahkan, bisa diketik keyword apapun. Dicek dulu: sistem SUDAH punya pencarian global di navbar atas (`admin.search`, cari nama penulis/ID/judul/kode submit/HP/nama jurnal — 6 field), tapi itu buka halaman hasil TERPISAH. Di halaman "Data Submit" (`admin.submissions.index`) dan "Monitoring" (`admin.submissions.monitoring`) sendiri, filter yang ada cuma tanggal/status/nama jurnal — TIDAK ada kolom cari bebas berdasarkan nama penulis/ID/judul/kode submit langsung di halaman itu.

Ditambahkan `Submission::scopeSearch()` (SATU sumber kebenaran, field cakupan sama persis dengan `SearchController` — nama_penulis, id_artikel, judul_artikel, kode_submit, no_hp_penulis, nama_jurnal via relasi) dipakai bareng oleh kedua halaman, supaya "keyword apa pun" konsisten dengan pencarian global yang sudah ada, bukan definisi baru yang beda-beda.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Models/Submission.php` | `scopeSearch($query, $keyword)` baru — LIKE di 5 kolom + relasi jurnal, kosongkan filter kalau keyword kosong/null. |
| `app/Http/Controllers/Admin/SubmissionController.php` | `index()`: panggil `$query->search($request->input('keyword'))`, tambah `->withQueryString()` ke paginate (sebelumnya TIDAK ada — filter/keyword hilang saat pindah halaman, ikut diperbaiki karena jadi prasyarat fitur ini bekerja benar lintas halaman). `monitoring()`: panggil scope yang sama (paginate di sini sudah lebih dulu pakai `withQueryString()`). |
| `resources/views/admin/submissions/index.blade.php` | Input teks "Cari" baru di filter form (kolom pertama), placeholder menyebutkan field yang dicakup. |
| `resources/views/admin/submissions/monitoring.blade.php` | Baris filter baru (di atas baris tanggal/status/reviewer yang sudah penuh 6 kolom) berisi input "Cari". |
| `tests/Feature/SubmissionKeywordSearchTest.php` | Baru, 9 test: scope cocok di nama penulis/kode submit/judul artikel/nama jurnal (via relasi) masing-masing, scope mengembalikan semua baris kalau keyword kosong/null, halaman Data Submit & Monitoring benar-benar menyaring hasil sesuai keyword lewat HTTP request, kedua halaman menampilkan input field baru. |

### Verifikasi
- `tests/Feature/SubmissionKeywordSearchTest.php` — 9/9 PASS, 18 assertions.
- Smoke test manual `app()->handle()` dengan data lokal riil: cari nama penulis asli ("Desiana") di kedua halaman, HTTP 200, hasil mengandung kode_submit yang benar.
- Full suite `tests/Feature` — 161 test, 442 assertions, **0 failure**.

### Catatan Deploy
Tidak ada migration. Murni penambahan scope + filter + input field — filter/URL lama yang sudah dipakai (tanggal, status, journal_search, dll.) tetap berfungsi sama seperti sebelumnya, `keyword` cuma parameter tambahan yang opsional.

---

## 6. Perluas Pencarian Keyword ke SEMUA Halaman Data Submit & Monitoring

**Tujuan:** User minta section #5 di atas "diterapkan untuk semua submit dan monitoring" — bukan cuma halaman Normal (`admin.submissions.index`/`monitoring`) yang sudah dikerjakan. Ditelusuri seluruh sidebar (Admin, PIC, Marketing): total ada 4 pasang "Data Submit"/"Monitoring" di Admin (Normal, Fasttrack, BKD, JAFA) dan pola serupa di PIC & Marketing — Normal/BKD/JAFA semuanya sudah otomatis ikut ter-cover di section #5 karena berbagi controller & view yang sama (cuma beda query param `program`), tersisa **8 endpoint terpisah** yang masing-masing punya controller/view sendiri: Fasttrack di ketiga role (Admin/PIC/Marketing) × (Data Submit + Monitoring) = 6, plus Data Submit & Monitoring "biasa" milik PIC dan Marketing (yang ternyata pakai controller sendiri-sendiri, bukan berbagi dgn Admin) = ditambah beberapa lagi.

Ditemukan: hampir semua 8 endpoint ini SUDAH punya pencarian sendiri-sendiri (param `search`, bukan `keyword`), tapi kebanyakan cuma cocok di 3 field (`kode_submit`/`judul_artikel`/`nama_penulis`) — beberapa malah tidak ada input pencarian di view-nya sama sekali walau controller-nya sudah siap membaca parameternya. Semua diseragamkan pakai `Submission::scopeSearch()` yang sama (dari section #5) — param nama `search` DIPERTAHANKAN di endpoint-endpoint ini (bukan diganti ke `keyword`) supaya URL yang sudah dipakai/dibookmark tetap jalan, cuma cakupan field pencocokannya yang diperluas jadi 6 field.

### File yang Diubah
| Area | File Controller | Endpoint | File View |
|------|-----------------|----------|-----------|
| Admin Fasttrack | `SubmissionController.php` | `fasttrackSubmissions()` | `admin/fasttrack-management/submissions/index.blade.php` |
| Admin Fasttrack | `SubmissionController.php` | `fasttrackMonitoring()` | `admin/fasttrack-management/monitoring/index.blade.php` (input baru — sebelumnya tidak ada) |
| PIC Normal/BKD/JAFA | `Pic/JournalManagementController.php` | `submissionsIndex()` | `pic/submissions/index.blade.php` |
| PIC Normal/BKD/JAFA | `Pic/JournalManagementController.php` | `submissionsMonitoring()` | `pic/submissions/monitoring.blade.php` (input baru — sebelumnya tidak ada) |
| PIC Fasttrack | `Pic/JournalManagementController.php` | `fasttrackIndex()` | `pic/fasttrack/index.blade.php` |
| PIC Fasttrack | `Pic/JournalManagementController.php` | `fasttrackMonitoring()` | `pic/fasttrack/monitoring.blade.php` (input baru — sebelumnya tidak ada) |
| Marketing Normal/BKD/JAFA | `Marketing/DashboardController.php` | `submissions()` | `marketing/submissions.blade.php` |
| Marketing Normal/BKD/JAFA | `Marketing/DashboardController.php` | `submissionsMonitoring()` | `marketing/submissions-monitoring.blade.php` |
| Marketing Fasttrack | `Marketing/DashboardController.php` | `fasttrackIndex()` | `marketing/fasttrack/index.blade.php` |
| Marketing Fasttrack | `Marketing/DashboardController.php` | `fasttrackMonitoring()` | `marketing/fasttrack/monitoring.blade.php` |

Semua 10 method di atas: blok pencarian manual (`where(kode_submit LIKE ... OR judul_artikel LIKE ... OR nama_penulis LIKE ...)`, 3-4 field) diganti satu baris `$query->search($request->input('search'));`. Juga ikut diperbaiki 2 bug kecil yang ditemukan di jalan: `PicJournalController::submissionsIndex()` dan `SubmissionController::index()` (section #5) tidak pakai `->withQueryString()` di `paginate()` — filter/search hilang begitu pindah halaman.

### Verifikasi
- `tests/Feature/SubmissionKeywordSearchAllPagesTest.php` — Baru, 10 test (1 per endpoint tambahan), semua PASS, 30 assertions — tiap test bikin 2 submission dgn nama beda, pastikan cari 1 nama cuma memunculkan yang cocok.
- Smoke test manual `app()->handle()` dengan data lokal riil, login sesuai guard masing-masing (admin/pic/marketing), cari pakai `kode_submit` asli (unik) di halaman Admin Fasttrack Data Submit, PIC Data Submit, Marketing Data Submit — semua HTTP 200 dan hasil mengandung kode yang dicari.
- Full suite `tests/Feature` — 171 test, 472 assertions, **0 failure**.

### Catatan Deploy
Tidak ada migration. Semua perubahan backward-compatible — parameter `search` yang sudah ada tetap dibaca dengan nama yang sama, cuma cakupan pencariannya diperluas.

---

## 7. Perbaikan: Export Excel `/admin/submissions/export` 500 Error (Memori Habis)

**Tujuan:** User melaporkan (screenshot) `https://portal.apji.org/admin/submissions/export` menampilkan **HTTP ERROR 500**. Direproduksi lokal dengan dataset produksi asli (14.691 submission) — betul crash:

```
Fatal error: Allowed memory size of 536870912 bytes exhausted (tried to allocate 17028928 bytes)
at vendor/maennchen/zipstream-php/src/File.php:345
```

**Root cause:** BUKAN dari perubahan sesi ini — murni masalah skala data. `SubmissionsExport` (44 kolom × 14.691 baris = ±646 ribu sel) sudah benar memakai `WithChunkReading` (chunk 500 baris), tapi itu cuma mengurangi beban QUERY ke database — PhpSpreadsheet tetap menyimpan SELURUH sel hasil akhir di memori PHP saat menulis file XLSX (proses kompresi ZIP-nya yang di-trace di error di atas). Dataset sudah tumbuh melebihi `memory_limit` default server (512M).

**Perbaikan:** `ini_set('memory_limit', '2048M')` + `set_time_limit(300)` di awal method export — HANYA berlaku untuk request export ini sendiri (PHP `ini_set()` per-request, tidak memengaruhi request lain / tidak perlu ubah `php.ini` server). Ini rekomendasi resmi Laravel Excel untuk error "Allowed memory size exhausted" pada dataset besar. Fix yang sama juga diterapkan ke `PicJournalController::submissionsMonitoringExport()` (export monitoring milik PIC) sebagai pencegahan, karena polanya identik (`Excel::download()` tanpa penyesuaian memori).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | `export()`: tambah `ini_set('memory_limit', '2048M')` + `set_time_limit(300)` di awal method, sebelum `Excel::download()`. |
| `app/Http/Controllers/Pic/JournalManagementController.php` | `submissionsMonitoringExport()`: perbaikan pencegahan yang sama (dataset per-PIC lebih kecil, tapi bisa besar juga utk PIC dengan tugas sangat banyak). |
| `tests/Feature/SubmissionExportMemoryTest.php` | Baru, 3 test: endpoint export tetap 200 tanpa filter, tetap 200 dengan filter status, `memory_limit` benar-benar naik ke 2048M setelah endpoint diakses (dikembalikan lagi di akhir test supaya tidak bocor ke test lain). |

### Verifikasi
- **Direproduksi & diverifikasi dengan dataset PRODUKSI ASLI** (bukan cuma data sintetis): sebelum fix — crash persis seperti laporan user (memory exhausted @512M); setelah fix — HTTP 200, file 4,6 MB berhasil dibuat penuh dalam 62 detik, peak memory ~524MB (di bawah ceiling baru 2048M, di atas ceiling lama 512M — persis menjelaskan kenapa dulu crash & sekarang tidak).
- File hasil export dibaca ulang pakai PhpSpreadsheet: 14.692 baris (14.691 data + 1 header, cocok persis dengan jumlah submission), 44 kolom, isi baris pertama & terakhir benar.
- `tests/Feature/SubmissionExportMemoryTest.php` — 3/3 PASS, 5 assertions.
- Full suite `tests/Feature` — 174 test, 477 assertions, **0 failure**.

### Catatan Deploy
Tidak ada migration. Murni perubahan kode (2 baris tambahan per method) — bisa langsung deploy, akan langsung memperbaiki error 500 di production begitu ter-pull. Tidak berisiko terhadap request lain karena `ini_set`/`set_time_limit` di sini cuma berlaku untuk request export itu sendiri. **Catatan skalabilitas**: ini fix yang efektif untuk skala data saat ini (~15rb baris), tapi kalau dataset terus tumbuh signifikan (mis. 100rb+ baris), pertimbangkan solusi jangka panjang seperti export via queue (`->queue()`) atau membatasi export per-rentang-tanggal.
