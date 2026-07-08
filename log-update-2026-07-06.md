# Log Update — 6 July 2026

## Ringkasan
Log perubahan otomatis dari git commits.

---

## 1. Nomor Telepon Marketing Bisa Lebih dari Satu

**Tujuan:** User minta tombol (+) di form Marketing supaya bisa menambahkan lebih dari satu nomor telepon per marketing (nomor utama sudah opsional sejak awal; sekarang ditambah dukungan nomor cadangan).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_07_06_120000_add_additional_phones_to_marketings_table.php` | Migration baru: tambah kolom `additional_phones` (JSON, nullable) ke tabel `marketings` |
| `app/Models/Marketing.php` | Tambah `additional_phones` ke `$fillable`, cast sebagai `array` |
| `app/Http/Controllers/Admin/MarketingController.php` | `store()`/`update()`: validasi `additional_phones` (array of nullable string, max 20), buang entri kosong lewat helper `cleanPhones()` sebelum disimpan |
| `resources/views/partials/marketing-phone-fields.blade.php` | Partial baru: input nomor utama + tombol (+) untuk menambah baris nomor tambahan secara dinamis (JS, dengan tombol hapus per baris), dipakai bareng oleh form create & edit |
| `resources/views/admin/marketings/create.blade.php` | Ganti input telepon polos dengan `@include('partials.marketing-phone-fields')` |
| `resources/views/admin/marketings/edit.blade.php` | Sama, sambil oper data `additional_phones` yang sudah tersimpan supaya muncul terisi saat edit |
| `resources/views/admin/marketings/index.blade.php` | Tampilkan nomor tambahan (kalau ada) sebagai teks kecil di bawah nomor utama pada tabel daftar marketing |

**Diverifikasi:** migration jalan bersih di lokal; test lewat tinker — submit `store()` dengan 3 nomor (1 kosong) menghasilkan `additional_phones` tersimpan sebagai `["082...","083..."]` (nomor kosong otomatis terbuang); render form edit dengan data nomor tambahan berhasil tanpa error dan kedua nomor tampil di form.

## 2. 🔄 Update: update nomor wa marketting

- **Commit:** `7ceacef` — 11:25 oleh Gudangsoft
- **File berubah:** 8 file
- `app/Http/Controllers/Admin/MarketingController.php`
- `app/Models/Marketing.php`
- `database/migrations/2026_07_06_120000_add_additional_phones_to_marketings_table.php`
- `log-update-2026-07-06.md`
- `resources/views/admin/marketings/create.blade.php`
- `resources/views/admin/marketings/edit.blade.php`
- `resources/views/admin/marketings/index.blade.php`
- `resources/views/partials/marketing-phone-fields.blade.php`

## 3. Samakan Struktur Format LOA Bahasa Inggris dengan Bahasa Indonesia

**Tujuan:** User minta format LOA formal Bahasa Inggris disamakan strukturnya dengan Bahasa Indonesia. Sebelumnya kedua bahasa punya layout yang berbeda jauh — versi Indonesia sudah pakai tabel No/Hal di atas, tabel "Kepada Yth./di", tabel detail Judul-Kode naskah, dan blok "DITERIMA", sementara versi Inggris masih pakai struktur lama berbasis paragraf (`<p>` salutation, judul artikel dicetak miring di tengah, tanpa tabel No/Subject maupun tabel detail manuskrip).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Tulis ulang blok "FORMAT ENGLISH" di halaman 1 supaya strukturnya identik dengan "FORMAT INDONESIA": tabel No/Subject ("Manuscript Accepted"), tabel "To,/at" (pengganti paragraf salutation lama), tabel detail "Manuscript title/Manuscript code", blok "ACCEPTED" besar di tengah, lalu `jrn-info-block` dan 2 paragraf penutup — hanya beda teks (Inggris vs Indonesia), layout & class CSS sama persis |

**Diverifikasi:** render `admin.loa.receipt` dengan `loa_language=en` lewat tinker — berhasil tanpa error, mengandung "ACCEPTED", "Manuscript title", dan baris "Subject" sesuai struktur baru; generate PDF untuk submission yang sama di kedua bahasa (`en` dan `id`) juga berhasil tanpa error dompdf.

## 4. 🔄 Update: Align English LOA format structure with Indonesian version for consistency

- **Commit:** `653460b` — 11:40 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-07-06.md`
- `resources/views/admin/loa/receipt.blade.php`

## 5. Fix Pengaturan SMTP di /admin/email-settings Hilang Setelah Disimpan

**Tujuan:** User melaporkan isian form Konfigurasi SMTP (Host, Username, Password, dll) langsung hilang/balik ke placeholder setelah disimpan, padahal seharusnya tetap tampil supaya tidak perlu buka `.env` manual tiap mau lihat/ubah setting yang aktif.

**Root cause:** Fitur ini sebelumnya 100% bergantung pada tulis-langsung ke file `.env` di server (`file_put_contents`), yang rawan gagal-diam di banyak setup hosting (permission, ownership, proses lain yang menimpa `.env`, dsb) — walau kode sudah antisipasi beberapa kasus itu, penulisan file tetap jauh lebih rapuh dibanding penyimpanan ke database yang sudah dipakai reliable di seluruh bagian aplikasi lain (lewat model `Setting`, sama seperti yang dipakai untuk token Fonnte).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/EmailSettingController.php` | `index()`/`update()`/`testEmail()`: ganti sumber utama data dari parsing `.env` jadi `Setting::get()/set()` (tabel `settings`, key `mail_host`, `mail_port`, dst). `.env` sekarang cuma fallback awal (kalau belum pernah disimpan lewat form ini) dan tetap ditulis best-effort untuk sinkronisasi, tapi kegagalannya tidak lagi menggagalkan penyimpanan |
| `app/Providers/AppServiceProvider.php` | Tambah `applyStoredMailSettings()` di `boot()`: override `config('mail...')` dari `Setting` di setiap request kalau `mail_host` sudah pernah disimpan, supaya pengiriman email sungguhan (LOA, notifikasi template) juga ikut pakai setting dari form ini — bukan cuma tombol Test Email saja |

**Diverifikasi:** simulasi penuh lewat tinker — `update()` dengan data SMTP baru → `index()` (simulasi reload halaman) menampilkan kembali semua nilai yang baru disimpan (tidak hilang); `AppServiceProvider::boot()` di-trigger ulang dan `config('mail.mailers.smtp.host')` dkk terbukti ter-override sesuai data di database.

## 6. 🔄 Update: Fix SMTP settings persistence by switching from .env file to database storage

- **Commit:** `6707afa` — 11:51 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/EmailSettingController.php`
- `app/Providers/AppServiceProvider.php`
- `log-update-2026-07-06.md`


## 7. 🔄 Update: a

- **Commit:** `1d475ab` — 11:52 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-07-06.md`


## 8. 🔄 Update: Update deployment instructions to use 'master' branch instead of 'main'

- **Commit:** `a7a2d74` — 12:03 oleh Gudangsoft
- **File berubah:** 4 file
- `DEPLOYMENT.md`
- `DEPLOYMENT_GUIDE.md`
- `deploy.sh`
- `log-update-2026-07-06.md`


## 9. 🔄 Update: a

- **Commit:** `8fc2f24` — 12:03 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-07-06.md`


## 10. 🔄 Update: a

- **Commit:** `85c5dc0` — 12:08 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-07-06.md`

## 11. Root Cause Sebenarnya Ditemukan: Variabel `$settings` Bentrok dengan Global View Composer

**Tujuan:** Setelah fix #5 (pindah ke database) ternyata TETAP tidak muncul walau sudah dipastikan deploy benar (branch `master`) dan PHP-FPM sudah di-restart. Ini memaksa investigasi lebih dalam sampai ketemu akar masalah sesungguhnya — sama sekali bukan soal `.env`, cache, atau deploy.

**Root cause sebenarnya:** `AppServiceProvider::boot()` men-share variabel bernama **`settings`** ke **SEMUA view** lewat `View::composer('*', function ($view) { ... $view->with('settings', $settings); })` — isinya branding aplikasi (`app_name`, `logo`, dst). Composer ini jalan saat view di-render, **setelah** controller mengisi data lewat `compact('settings')`, sehingga composer diam-diam **menimpa** data SMTP yang sudah benar dengan array branding yang sama sekali beda struktur — hasilnya field `$settings['mail_host']` di Blade selalu `null`/kosong meski data di database/session/cache sudah benar. Ini kenapa fix sebelumnya (ganti .env ke DB) tidak berpengaruh sama sekali: datanya sudah benar sampai baris terakhir sebelum render, lalu ditimpa di detik terakhir.

Dibuktikan langsung lewat tinker: render `admin.email-settings.index` dengan `compact('settings')` berisi nilai uji `PROOF-VALUE-12345` → nilai itu **hilang** dari HTML (terbukti timpa-menimpa). Setelah variabel diganti nama, nilai yang sama muncul normal.

**Temuan tambahan:** `SmsGatewayController` (yang tadinya jadi acuan "sudah bisa muncul") ternyata **memakai variabel nama sama** (`compact('settings')`) dan **terbukti kena bug yang sama** saat dites dengan cara yang sama — kemungkinan belum ketahuan karena field-nya jarang dicek ulang setelah simpan, bukan berarti benar-benar bebas bug.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/EmailSettingController.php` | `index()`/`update()`: ganti nama variabel `$settings` → `$emailSettings` (dan `compact('settings')` → `compact('emailSettings')`) supaya tidak lagi ditimpa oleh view composer global |
| `resources/views/admin/email-settings/index.blade.php` | Semua referensi `$settings['...']` diganti jadi `$emailSettings['...']` |

**Diverifikasi:** render langsung dengan data uji lewat tinker — sebelum fix nilai hilang ("CLOBBERED"), setelah fix nilai tampil benar ("FIXED — value now shows correctly"); dites juga lewat pemanggilan `EmailSettingController::index()` yang sesungguhnya — `value="mail.apji.org"` muncul benar di HTML akhir.

**Catatan:** `SmsGatewayController`/`admin.sms-gateway.index` kemungkinan besar punya bug identik (variabel `$settings` sama) — belum diperbaiki karena di luar permintaan awal, menunggu konfirmasi user.

## 12. Fix Bug Sama di SMS Gateway (Fonnte) — Variabel `$settings` Bentrok

**Tujuan:** User konfirmasi untuk sekalian perbaiki bug identik (temuan #11) di halaman SMS Gateway.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SmsGatewayController.php` | `index()`/`update()`: ganti nama variabel `$settings` → `$smsSettings` (dan `compact('settings')` → `compact('smsSettings')`) |
| `resources/views/admin/sms-gateway/index.blade.php` | Semua referensi `$settings['...']` (24 tempat) diganti jadi `$smsSettings['...']` |

**Diverifikasi:** sama seperti Email Settings — render dengan data uji `PROOF-SMS-99999` lewat tinker sebelum fix hilang dari HTML, setelah fix muncul benar; dites juga lewat `SmsGatewayController::index()` sesungguhnya dengan token asli tersimpan di DB — `value="REAL-TEST-TOKEN"` muncul benar di HTML akhir.

## 13. Fix Bug Sama di Halaman Pengaturan Umum (SettingController) — Variabel `$settings` Bentrok

**Tujuan:** Ditemukan instance ketiga bug yang sama saat menyisir kode: `SettingController` (halaman "Pengaturan Umum" — nama app, URL, tagline, alamat, kontak, logo, favicon, bahasa) juga pakai `compact('settings')`, sehingga field-fieldnya juga ditimpa oleh view composer global. User konfirmasi untuk sekalian diperbaiki.

Catatan tambahan: controller ini juga memvalidasi & menyimpan `mail_from_address`/`mail_from_name` ke `.env` lewat regex, tapi field itu **tidak ada sama sekali di view**-nya (tidak pernah dibaca balik ke form) — kode vestigial dari iterasi form sebelumnya. Dibiarkan apa adanya karena tidak berhubungan dengan bug yang sedang diperbaiki dan tidak terlihat/dipakai user.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SettingController.php` | `index()`: ganti nama variabel `$settings` → `$generalSettings` (dan `compact('settings')` → `compact('generalSettings')`) — juga menghindari nama `appSettings` yang sama-sama dipakai composer global |
| `resources/views/admin/settings/index.blade.php` | Semua referensi `$settings['...']` diganti jadi `$generalSettings['...']` |

**Diverifikasi:** render `SettingController::index()` sesungguhnya lewat tinker — field `app_url` sebelumnya akan kosong (clobbered), setelah fix `value="http://127.0.0.1:8000"` (nilai APP_URL asli) muncul benar di HTML.

## 14. 🔄 Update: smpt

- **Commit:** `35c754c` — 12:40 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Http/Controllers/Admin/EmailSettingController.php`
- `app/Http/Controllers/Admin/SettingController.php`
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-07-06.md`
- `resources/views/admin/email-settings/index.blade.php`
- `resources/views/admin/settings/index.blade.php`
- `resources/views/admin/sms-gateway/index.blade.php`

## 15. Tambah Variabel Nomor WA Marketing di Template Notifikasi Kredensial

**Tujuan:** User minta variabel nomor WA marketing (nomor 1, 2, dst — mengacu ke fitur multi-nomor marketing yang dibuat sebelumnya) tersedia di template notifikasi kredensial penulis, supaya pesan WA ke penulis bisa mencantumkan kontak marketing yang menangani submission-nya.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | `buildWhatsAppMessage()`: load relasi `marketing` dari submission, gabungkan nomor utama (`phone`) + nomor tambahan (`additional_phones`) jadi satu daftar, lalu tambahkan variabel `{noWaMarketing1}` s.d. `{noWaMarketing5}` (`MAX_MARKETING_WA_VARS = 5`) ke proses `str_replace` template — nomor yang tidak ada diisi `-` |
| `resources/views/admin/sms-gateway/index.blade.php` | Dokumentasikan variabel baru di kotak info "Variabel" pada bagian Template Notifikasi Kredensial Penulis |

**Diverifikasi:** lewat tinker — buat submission dengan marketing yang punya 3 nomor (1 utama + 2 tambahan), set template custom berisi `{noWaMarketing1}` s.d. `{noWaMarketing4}`, hasil render menampilkan 3 nomor asli dengan benar dan slot ke-4 yang tidak ada tampil `-`.

## 16. Tambah Variabel Nomor WA Marketing di Email Template (`/admin/email-templates/{id}/edit`)

**Tujuan:** User minta variabel nomor WA marketing yang sama (#15) juga tersedia di sistem Email Template (`assign_*`, `validate_*`, `notify_penulis`) — bukan cuma template WA kredensial.

Karena sistem ini pakai konvensi snake_case (`nama_artikel`, `kode_submit`, dst, beda dari WA yang camelCase), variabel dinamai `{no_wa_marketing_1}` s.d. `{no_wa_marketing_5}` mengikuti konvensi yang sudah ada di sistem ini.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah helper `marketingWaVars(Submission $submission): array` (dipakai bareng, hindari duplikasi 3x) yang menghasilkan `no_wa_marketing_1` s.d. `no_wa_marketing_5`; di-merge (`...$this->marketingWaVars($submission)`) ke ketiga titik pemanggilan `$tpl->render([...])`: notifikasi assign PIC (quick assign), notifikasi validasi tahap, dan `sendPenulisEmail()` (notify_penulis) |
| `resources/views/admin/email-templates/form.blade.php` | Tambah 5 variabel baru ke daftar chip variabel yang bisa diklik; tambah nilai contoh di JS `livePreview()` supaya preview template ikut menampilkan nomor sample |

**Diverifikasi:** lewat tinker — submission dengan marketing 2 nomor, template `notify_penulis` custom berisi `{no_wa_marketing_1}` s.d. `{no_wa_marketing_3}`, hasil render: 2 nomor asli tampil benar, slot ke-3 tampil `-`.

## 17. Fix Submission Lama Tidak Muncul di Dropdown "Pilih Submission" (`/admin/assignments/create`)

**Tujuan:** User melaporkan submission tanggal 1 Juli tidak muncul di dropdown pilihan artikel saat membuat penugasan reviewer.

**Root cause:** Dropdown "Pilih Submission" di-preload dari query `Submission::whereNotNull('id_artikel')->orderBy('created_at','desc')->limit(500)->get()` — HANYA 500 submission TERBARU yang dikirim ke browser. Kotak pencarian di atas dropdown cuma filter client-side (`option.style.display='none'`) atas opsi yang SUDAH dimuat — jadi begitu total submission (dengan `id_artikel` terisi) melebihi 500, submission yang lebih lama (spt. tanggal 1 Juli) otomatis terpotong dari daftar dan **tidak mungkin ditemukan** lewat pencarian apa pun, karena datanya memang tidak pernah sampai ke browser.

Dibuktikan langsung lewat tinker: submission tertua di database dicek — TIDAK termasuk dalam 30 (atau 500) data preload terbaru, sehingga sebelum fix ini dijamin tidak akan muncul di dropdown.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/ReviewAssignmentController.php` | `create()`: kurangi preload jadi 30 submission terbaru saja (bukan 500) — cuma untuk tampilan awal. Tambah `searchSubmissions()`: endpoint AJAX baru yang query LANGSUNG ke database (`id_artikel`/`judul_artikel`/nama jurnal, `LIKE %q%`) tanpa batas berdasarkan usia data, return JSON max 50 hasil |
| `routes/web.php` | Tambah route `GET admin/assignments/search-submissions` → `searchSubmissions` |
| `resources/views/admin/assignments/create.blade.php` | JS pencarian submission diganti dari filter client-side ke AJAX `fetch()` (debounce 300ms) ke endpoint baru — dropdown di-render ulang dari hasil pencarian server, bukan cuma sembunyikan/tampilkan opsi yang sudah ada. Update teks bantuan supaya jelas bahwa mengetik akan mencari ke seluruh data |

**Diverifikasi:** lewat tinker — cari submission tertua di database (yang terbukti TIDAK ada di 30 data preload terbaru) lewat `searchSubmissions()` langsung → ditemukan dengan benar. Endpoint juga dites pencarian berdasarkan `id_artikel` — hasil sesuai termasuk nama jurnal.

## 18. Tambah Filter Tanggal di Pencarian Submission (`/admin/assignments/create`)

**Tujuan:** User minta filter tanggal ditambahkan ke fitur pencarian submission yang baru saja diperbaiki (#17), supaya bisa mempersempit hasil pencarian ke rentang tanggal submit tertentu.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/ReviewAssignmentController.php` | `searchSubmissions()`: tambah filter `tanggal_dari`/`tanggal_sampai` (`whereDate('tanggal_submit', ...)`) — konvensi nama parameter sama dengan filter tanggal yang sudah dipakai di seluruh `SubmissionController`; tambahkan `tanggal_submit` ke hasil JSON |
| `resources/views/admin/assignments/create.blade.php` | Tambah 2 input tanggal (dari/sampai) + tombol "Hapus filter tanggal" di atas dropdown pencarian; JS di-refactor jadi `performSubmissionSearch()` yang dipakai bareng oleh input teks maupun input tanggal; tanggal submit tiap submission ditampilkan di teks opsi dropdown |

**Diverifikasi:** lewat tinker — filter dengan rentang tanggal yang mencakup `tanggal_submit` submission uji → submission tersebut muncul di hasil; filter dengan tanggal jauh di masa depan (di luar rentang manapun) → hasil kosong (0), membuktikan filter benar-benar diterapkan di query.

## 19. WA Pengiriman LOA Bisa Pakai API Fonnte Terpisah

**Tujuan:** User minta pengiriman WA khusus LOA (tombol "Kirim via WhatsApp" di halaman LOA) bisa memakai API/device Fonnte yang berbeda dari nomor WA utama yang dipakai untuk notifikasi kredensial/SMS lainnya.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SmsGatewayController.php` | Tambah key `fonnte_api_token_loa` ke `$keys`, `buildSettings()`, validasi `update()`, dan logika "pertahankan token lama kalau dikosongkan" (mengikuti pola yang sudah ada untuk `fonnte_api_token`) |
| `resources/views/admin/sms-gateway/index.blade.php` | Tambah section baru "WA Pengiriman LOA (Terpisah)": input token opsional + tombol hapus, penjelasan bahwa kosong = pakai token utama; token baru ditambahkan ke `TEXT_FIELDS` supaya ikut ter-backup di localStorage seperti field lain |
| `app/Http/Controllers/Admin/LoaMasterController.php` | `dispatchLoaWa()`: baca `Setting::get('fonnte_api_token_loa')` — kalau diisi, dipakai sebagai token eksplisit ke `FonnteService::send()` (parameter `token`); kalau kosong, tetap pakai token utama seperti sebelumnya. Guard "belum dikonfigurasi" disesuaikan supaya tidak salah blokir kalau yang diisi cuma token LOA (bukan token utama) |

**Diverifikasi:** lewat tinker dengan `FonnteService` di-mock (tanpa panggilan API sungguhan, untuk hindari kirim WA asli saat testing) — tanpa token LOA diisi, token yang diteruskan ke `send()` adalah `null` (pakai default/token utama); dengan token LOA diisi, token yang diteruskan persis token LOA yang tersimpan. Round-trip simpan→baca `fonnte_api_token_loa` lewat `SmsGatewayController::update()`/`index()` juga dites dan sesuai.

## 20. Tombol "Cek Status Koneksi" Terpisah untuk Token LOA

**Tujuan:** User mengisi token LOA lalu klik "Cek Status Koneksi" — tapi tombol itu ternyata mengecek token **utama** (kosong), bukan token LOA, jadi selalu tampil "Masukkan API Token terlebih dahulu" walau token LOA sudah diisi dan sudah bekerja dengan benar di background. User minta pengecekan status token LOA benar-benar mandiri, tidak tergantung token utama sama sekali.

**Catatan:** logika pengiriman WA LOA (`dispatchLoaWa()`, #19) sebenarnya SUDAH mandiri sejak awal — cuma tombol "Cek Status Koneksi" di UI yang belum punya versi khusus LOA, jadi terlihat seperti belum berfungsi.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/sms-gateway/index.blade.php` | Tambah tombol "Cek Status Koneksi (LOA)" + area hasil terpisah (`checkStatusBtnLoa`/`statusResultLoa`) di section token LOA — memakai endpoint `check-status` yang sama tapi mengirim nilai `fonnte_api_token_loa` secara eksplisit (endpoint ini sudah mendukung override token dari body request sejak awal, jadi tidak perlu ubah backend) |

**Diverifikasi:** render halaman — tombol dan area hasil baru muncul di HTML dengan benar; dikonfirmasi lewat baca kode `checkStatus()` bahwa endpoint memprioritaskan token dari request body (bukan `Setting::get('fonnte_api_token')`) sehingga pengecekan token LOA benar-benar independen dari token utama.

## 21. Lampirkan File PDF LOA di Pengiriman WhatsApp

**Tujuan:** User melaporkan pengiriman WA LOA belum melampirkan file PDF LOA — cuma kirim teks + link ke halaman LOA, padahal versi email sudah melampirkan PDF sejak fitur #13-14.

**Pendekatan:** Fonnte mengirim lampiran dengan cara fetch file dari URL publik yang diberikan (parameter `url`/`filename` di payload `send`), bukan upload binary langsung. Jadi dibuatkan endpoint publik baru yang men-generate PDF LOA secara on-demand (pakai `LoaController::generateLoaPdf()` yang sudah ada dari fitur email), lalu URL itu diteruskan ke Fonnte saat kirim WA.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah route publik `GET /loa/{kode_loa}/pdf` → `loa.public.pdf` (tanpa login, sama seperti `loa.public` — diperlukan supaya server Fonnte bisa fetch filenya) |
| `app/Http/Controllers/Admin/LoaController.php` | Tambah `publicPdf()`: generate PDF via `generateLoaPdf()` yang sudah ada, return sebagai response `application/pdf` |
| `app/Http/Controllers/Admin/LoaMasterController.php` | `dispatchLoaWa()`: tambah `url` (link ke `loa.public.pdf`) dan `filename` (`LOA-{kode}.pdf`) ke `options` yang dikirim ke `FonnteService::send()`, supaya Fonnte melampirkan PDF-nya di pesan WA |

**Diverifikasi:** endpoint `publicPdf()` dites langsung — return HTTP 200, `Content-Type: application/pdf`, isi file valid (diawali `%PDF`). `dispatchLoaWa()` juga dites dengan `FonnteService` di-mock (tanpa kirim WA sungguhan) — `options` yang diteruskan ke `send()` terbukti berisi `url` yang mengarah ke endpoint PDF yang benar dan `filename` yang sesuai.

## 22. Hapus Teks "[SUBJECT TO REVISION BY THE REVIEWER]" di Format Inggris

**Tujuan:** User minta teks `[SUBJECT TO REVISION BY THE REVIEWER]` di sebelah judul "EVALUATION CRITERIA" dan "REVIEWER'S DECISION" (halaman 2 LOA, format Bahasa Inggris) dihapus — versi Bahasa Indonesia sudah tidak menampilkan teks setara ini sejak commit sebelumnya (`d17a6b0`), tapi versi Inggris terlewat.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | `criteria_note` dan `decision_note` pada kamus teks Inggris (`$L`) dikosongkan, menyamakan dengan versi Indonesia yang sudah kosong lebih dulu |

**Diverifikasi:** render `admin.loa.receipt` dengan `loa_language=en` lewat tinker — teks "SUBJECT TO REVISION" tidak lagi muncul di HTML.

## 23. Tambah Pencarian di Halaman Manajemen Bidang Ilmu (`/admin/field-of-studies`)

**Tujuan:** User melaporkan halaman Manajemen Bidang Ilmu belum ada fitur pencarian — sebelumnya cuma paginasi tanpa filter apa pun.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/FieldOfStudyController.php` | `index()`: tambah filter `search` (cari di kolom `name` dan `description`) dan `status` (aktif/nonaktif), pakai `withQueryString()` supaya filter tetap terbawa saat ganti halaman/per-page |
| `resources/views/admin/field-of-studies/index.blade.php` | Tambah form pencarian (GET) di atas tabel: input teks cari + dropdown status + tombol Cari/Reset, mengikuti gaya filter yang sudah ada di halaman admin lain |

**Diverifikasi:** lewat tinker — query filter `name LIKE`/`description LIKE` diuji langsung terhadap data uji: pencarian yang cocok mengembalikan hasil yang benar, pencarian yang tidak cocok mengembalikan 0 hasil. Render halaman (dengan data dummy untuk menghindari isu skema `reviewer_registrations` yang sudah ada sebelumnya di lokal, tidak terkait perubahan ini) berhasil menampilkan input pencarian dengan benar.

## 24. Fix 404 Saat Hapus Bidang Ilmu

**Tujuan:** User melaporkan klik "Hapus" pada bidang ilmu (mis. dari hasil pencarian #23) berujung ke halaman 404 (`/admin/field-of-studies/640`) alih-alih terhapus dan kembali ke daftar.

**Root cause (dugaan kuat berdasarkan pengujian):** tombol Hapus (dan bulk-delete) memakai form `method="POST"` dengan `@method('DELETE')` — Laravel men-spoof method lewat field `_method` tersembunyi. Route `admin.field-of-studies.destroy` (dari `Route::resource(...)`) cuma terdaftar untuk verb `DELETE`, TIDAK ada route untuk `POST` biasa ke URI yang sama. Kalau proses method-override ini gagal diteruskan dengan benar di production (banyak kemungkinan penyebab: proxy/CDN yang strip field tertentu, cache, dsb — tombol "Nonaktifkan" yang pakai `Route::post()` biasa terbukti tetap jalan normal), request akhirnya jatuh sebagai POST murni ke `/field-of-studies/{id}` yang tidak match route manapun (resource route tidak mendaftarkan POST untuk member URI) → 404.

**Fix:** hilangkan ketergantungan pada method-spoofing untuk aksi hapus — pakai route `POST` biasa (sama seperti tombol Aktifkan/Nonaktifkan yang sudah terbukti bekerja), tanpa `@method('DELETE')` sama sekali.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Tambah `POST /field-of-studies/{fieldOfStudy}/remove` (`admin.field-of-studies.remove`) dan `POST /field-of-studies-bulk-remove` (`admin.field-of-studies.bulk-remove`), keduanya mengarah ke controller method yang sama (`destroy`/`bulkDelete`) — route `DELETE` lama tetap ada (tidak dihapus) untuk kompatibilitas |
| `resources/views/admin/field-of-studies/index.blade.php` | Form hapus per-baris dan bulk-delete diubah memakai route POST baru tanpa `@method('DELETE')` |

**Diverifikasi:** `php artisan route:list` mengonfirmasi kedua route POST baru terdaftar dengan benar; render halaman mengonfirmasi form Hapus sekarang mengarah ke route `.../remove` (bukan lagi resource `destroy` yang di-spoof) dan tidak ada lagi `@method('DELETE')` tersisa di halaman.
