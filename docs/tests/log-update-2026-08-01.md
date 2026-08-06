# Log Update — 01 Agustus 2026

## 1. Toggle Opsional "Tampilkan Tanda Tangan & Nama Editor" di LOA

**Tujuan:** User (screenshot LOA jurnal ABDIMAS-45) meminta agar tanda tangan dan nama lengkap editor bisa ditambahkan di dokumen LOA, tapi sifatnya **opsional** — admin bisa memilih untuk menampilkan atau menyembunyikannya. Investigasi menemukan field `editor_name`/`editor_signature_path` sudah ada di `JournalMaster` dan sudah dipakai LOA (diedit lewat menu "Info Jurnal", bukan menu Master LOA) — kalau kosong, otomatis tidak tampil. Yang belum ada: kontrol eksplisit untuk menyembunyikannya SEKALIPUN datanya sudah terisi.

Pola diambil dari fitur `loa_language` yang sudah ada (setting tetap per-jurnal di menu Master LOA, bukan pilihan per-generate dokumen) — konsisten dengan konsep yang sudah berjalan, sesuai arahan CLAUDE.md untuk selalu reuse pola yang ada.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_08_01_000001_add_loa_show_signature_to_journal_masters.php` | Baru. Kolom `loa_show_signature` boolean, default `true` (supaya jurnal yang sudah punya data tetap tampil seperti sebelumnya). |
| `app/Models/JournalMaster.php` | Tambah `loa_show_signature` ke `$fillable` dan `$casts` (boolean). |
| `app/Http/Controllers/Admin/LoaMasterController.php` | `update()`: simpan `loa_show_signature` via `$request->boolean()`, pola sama seperti `loa_auto_send`. |
| `resources/views/admin/loa-master/edit.blade.php` | Card baru "Tanda Tangan & Nama Editor" dengan switch on/off (gaya sama seperti card "LOA Otomatis"), **plus input langsung** untuk `editor_name` (text) dan `editor_signature` (file upload dengan preview gambar existing + checkbox "Hapus TTD") — awalnya hanya berupa link ke menu Info Jurnal, tapi user minta inputnya langsung ada di sini juga (lihat catatan revisi di bawah). JS kecil untuk update label "Ditampilkan"/"Disembunyikan" saat switch diklik. |
| `app/Http/Controllers/Admin/LoaController.php` | `buildViewData()` dan `generateLoaPdf()`: `signUrl` digate dengan `($journal?->loa_show_signature ?? true)` — TTD tetap ada di storage, cuma tidak ikut ditampilkan/dilampirkan kalau toggle mati. |
| `resources/views/admin/loa/receipt.blade.php` | `$editorName` digate dengan toggle yang sama (dihitung di blade, konsisten dengan pola lama field ini). |
| `resources/views/emails/loa-accepted.blade.php` | Baris nama editor di footer email digate dengan toggle yang sama, supaya konsisten di seluruh pengalaman LOA (halaman, PDF, email). |
| `tests/Feature/LoaShowSignatureTest.php` | Baru, 6 test: simpan toggle ON (checkbox dicentang) dan OFF (checkbox tidak dikirim = unchecked), TTD+nama editor tampil default (toggle ON), TTD+nama editor disembunyikan saat toggle OFF, halaman edit menampilkan input `editor_name`/`editor_signature`, field lain (nama jurnal) tetap tampil normal saat toggle OFF. |

**Revisi (masih tanggal sama):** `LoaMasterController::update()` ternyata SUDAH lengkap menangani validasi & penyimpanan `editor_name`/`editor_signature` (upload, hapus file lama saat diganti, checkbox `remove_signature`) — cuma form di `loa-master/edit.blade.php` belum pernah menyertakan input untuk keduanya (field-nya cuma ada di form terpisah, menu Info Jurnal). User melaporkan "belum ada inputan Nama lengkap editor dan ttd" setelah lihat card toggle yang awal — diperbaiki dengan menambahkan input tersebut langsung ke card yang sama, meniru persis pola upload TTD di `journal-masters/edit.blade.php`. Tidak ada perubahan controller untuk revisi ini, murni tambahan field di view.

### Verifikasi
- `tests/Feature/LoaShowSignatureTest.php` — 6/6 PASS, 16 assertions.
- Smoke test manual `app()->handle()` dengan data lokal riil: halaman `/admin/loa-master/{id}/edit` HTTP 200, card toggle, checkbox `swShowSignature`, dan input `editor_name`/`editor_signature` terkonfirmasi muncul di HTML.
- Full suite `tests/Feature` — 113 test, 303 assertions, **0 failure**.

### Catatan Deploy
Migration baru (`2026_08_01_000001_*`) perlu `php artisan migrate --force` di production. Tidak ada perubahan pada data yang sudah ada — semua jurnal otomatis dapat `loa_show_signature = true` (default), jadi tampilan LOA tidak berubah untuk siapa pun sampai admin sengaja mematikan toggle-nya per jurnal.

---

## 2. Template Email ke Penulis (Author) Tidak Bisa Dibuat Lewat UI

**Tujuan:** User (screenshot halaman Template Email Monitoring) melaporkan "template email yang dikirimkan ke author belum ada". Investigasi menemukan mekanismenya SUDAH ADA di kode sejak lama — `SubmissionController::sendPenulisEmail()` mencari `EmailTemplate::findActive('notify_penulis')` dan mengirim email begitu submission baru dibuat (dipanggil dari `store()` dan alur Fasttrack/BKD) — tapi trigger key `notify_penulis` **tidak pernah bisa dipilih lewat UI**: baik grid tombol "+" di halaman index maupun dropdown di halaman "Buat Template" cuma menyaring trigger berawalan `assign_`/`validate_`, jadi `notify_penulis` (dan `screening_*`, yang ternyata tidak pernah dipakai kode sama sekali — dibiarkan tidak ditampilkan) selalu tersembunyi tanpa cara untuk membuatnya.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/email-templates/index.blade.php` | Tambah kelompok baru "Ke Penulis (Author)" (filter prefix `notify_`) di atas kelompok "Saat PIC Ditugaskan", pola badge/tombol "+" identik dengan dua kelompok yang sudah ada. |
| `resources/views/admin/email-templates/form.blade.php` | Tambah `<optgroup label="Ke Penulis (Author)">` berisi trigger `notify_*` yang tersedia, di halaman "Buat Template". |
| `database/migrations/2026_08_01_000002_seed_notify_penulis_email_template.php` | Baru. `firstOrCreate` template default untuk `notify_penulis` — subjek & isi HTML memakai semua variabel yang sudah didukung kode (`nama_penulis`, `nama_artikel`, `kode_submit`, `id_artikel`, `nama_jurnal`, `url_jurnal`, `username_author`, `password_author`, `app_name`, `tanggal`). **`is_active = false`** SENGAJA — supaya admin meninjau dulu redaksi kalimatnya sebelum sistem mulai otomatis mengirim email ke penulis asli di production. `down()` menghapus baris ini lagi. |
| `tests/Feature/EmailTemplateNotifyPenulisTest.php` | Baru, 6 test: migration seed non-aktif, `render()` mengganti semua variabel dengan benar, index menampilkan grup "Ke Penulis" beserta badge saat template ada, index menampilkan link "+" saat template dihapus, dropdown "Buat Template" menawarkan `notify_penulis` saat tersedia, template baru benar-benar bisa dibuat lewat `store()`. |

### Verifikasi
- `tests/Feature/EmailTemplateNotifyPenulisTest.php` — 6/6 PASS, 16 assertions.
- Smoke test manual `app()->handle()` dengan data lokal riil: `/admin/email-templates` HTTP 200, section "Ke Penulis (Author)" dan badge "Notifikasi ke Penulis" terkonfirmasi muncul; template hasil seed terkonfirmasi `is_active=false`.
- Full suite `tests/Feature` — 119 test, 319 assertions, **0 failure**.

### Catatan Deploy
Migration baru (`2026_08_01_000002_*`) perlu `php artisan migrate --force` di production — akan membuat 1 baris template baru (non-aktif, tidak mengubah perilaku pengiriman email apa pun sampai admin sengaja meninjau isi & menekan tombol aktifkan di `/admin/email-templates`). **Sebelum diaktifkan, admin disarankan meninjau/menyunting dulu redaksi kalimat & memastikan `username_author`/`password_author` memang relevan ditampilkan ke penulis pada momen submission baru dibuat** — kalau tidak relevan, edit templatenya lewat UI (tombol pensil) sebelum diaktifkan.

---

## 3. Halaman Baru: Monitoring Akreditasi Jurnal

**Tujuan:** User minta "monitoring proses jurnal yang akreditasi agar team mempersiapkan" — permintaan awalnya umum, diklarifikasi lewat AskUserQuestion menjadi: peringatan jurnal yang masa berlaku akreditasinya (SINTA) mendekati kedaluwarsa, supaya tim mulai siapkan dokumen reakreditasi dari jauh-jauh hari. Sebelumnya masa berlaku akreditasi cuma tersimpan sebagai teks bebas di `loa_status` (format tidak konsisten antar jurnal, mis. "...sampai Volume 6 Nomor 1 Tahun 2027"), tidak bisa dipakai untuk hitung mundur otomatis.

Keputusan yang dikonfirmasi user: (1) tanggal berakhir akreditasi disimpan sebagai tanggal kalender penuh (bukan cuma tahun), (2) ambang "Perlu Bersiap" = 12 bulan sebelum kedaluwarsa, (3) cukup halaman monitoring dulu, belum perlu notifikasi email otomatis (bisa menyusul kalau memang dibutuhkan).

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_08_01_000003_add_accreditation_expires_at_to_journal_masters.php` | Baru. Kolom `accreditation_expires_at` (date, nullable) di `journal_masters`. |
| `app/Models/JournalMaster.php` | Tambah `accreditation_expires_at` ke `$fillable` dan `$casts` (`date`). |
| `app/Http/Controllers/Admin/LoaMasterController.php` | `update()`: validasi & simpan `accreditation_expires_at` (`nullable\|date`), pola sama seperti `loa_status`. |
| `resources/views/admin/loa-master/edit.blade.php` | Input tanggal baru "Akreditasi Berakhir Tanggal" di card "Identitas Jurnal (untuk LOA)", dengan link ke halaman Monitoring Akreditasi. |
| `app/Http/Controllers/Admin/MonitoringAkreditasiController.php` | Baru. `index()`: ambil jurnal aktif yang `accreditation` terisi, hitung status (`expired`/`warning`/`unknown`/`safe`) berdasarkan `accreditation_expires_at` vs hari ini + ambang 12 bulan, urutkan prioritas (kedaluwarsa → perlu bersiap [terdekat dulu] → belum diisi → aman). |
| `resources/views/admin/monitoring-akreditasi/index.blade.php` | Baru. 4 kartu ringkasan (Kedaluwarsa/Perlu Bersiap/Belum Diisi/Aman) + tabel per jurnal dengan sisa waktu & link langsung ke form isi tanggal. |
| `routes/web.php` | Route baru `GET /admin/monitoring-akreditasi` → `admin.monitoring-akreditasi.index`. |
| `resources/views/admin/partials/sidebar.blade.php` | Link baru "Monitoring Akreditasi" di accordion "Data Jurnal" (setelah "Akreditasi"), plus tambahan kondisi active-state. |
| `tests/Feature/MonitoringAkreditasiTest.php` | Baru, 7 test: status warning (≤12 bulan), status expired (tanggal lampau), status safe (>12 bulan), status unknown (tanggal kosong), jurnal tanpa akreditasi sama sekali dikecualikan dari daftar, urutan sort kedaluwarsa-sebelum-aman (walau alfabetis terbalik), form Master LOA berhasil menyimpan tanggal baru. |

### Verifikasi
- `tests/Feature/MonitoringAkreditasiTest.php` — 7/7 PASS, 20 assertions.
- Smoke test manual `app()->handle()` dengan data lokal riil: set tanggal kedaluwarsa 4 bulan lagi untuk 1 jurnal riil (JURRIPEN), halaman `/admin/monitoring-akreditasi` HTTP 200, jurnal tersebut tampil dengan badge "Perlu Bersiap".
- Full suite `tests/Feature` — 126 test, 339 assertions, **0 failure**.

### Catatan Deploy
Migration baru (`2026_08_01_000003_*`) perlu `php artisan migrate --force` di production. Semua jurnal existing otomatis `accreditation_expires_at = NULL` (kategori "Belum Diisi") — tidak ada yang salah tampil, admin tinggal isi tanggalnya satu-satu lewat Master LOA kapan pun siap.

---

## 4. Revisi #3: Ganti Tanggal Kalender ke Periode Volume/Nomor/Tahun

**Tujuan:** User mengoreksi section #3 di atas: "untuk akreditasi jurnal biasanya tidak menggunakan tanggal namun menggunakan periode, coba kamu cek dulu". Dicek langsung: ditarik SEMUA 125 data `loa_status` yang sudah terisi di database (bukan cuma sampel kecil) — ditemukan **100% konsisten** memakai format periode "Volume X Nomor Y Tahun Z" (26 kombinasi berbeda, mis. "sampai Volume 6 Nomor 1 Tahun 2027", "sampai Volume 7 Nomor 2 Tahun 2028", dst.), terikat ke penomoran volume/terbitan jurnal itu sendiri — BUKAN tanggal kalender pasti seperti yang saya asumsikan di section #3. Field `accreditation_expires_at` (tanggal) diganti total ke 3 kolom terpisah sesuai konfirmasi user.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_08_01_000004_replace_accreditation_expiry_date_with_periode.php` | Baru (BUKAN edit migration lama — migration `..._000003` sempat ter-commit duluan lewat proses lain sebelum revisi ini, jadi tidak diedit langsung, mengikuti prinsip "jangan ubah migration yang sudah ter-share"). `up()`: drop `accreditation_expires_at`, tambah `accreditation_end_volume`/`accreditation_end_nomor` (unsignedInteger) dan `accreditation_end_tahun` (unsignedSmallInteger), semua nullable. `down()`: kebalikannya. Ditest penuh: migrate → verifikasi → rollback → verifikasi kembali ke state sebelumnya → migrate lagi. |
| `app/Models/JournalMaster.php` | `$fillable`: ganti `accreditation_expires_at` → 3 field baru. Tambah accessor `getAccreditationPeriodeAttribute()` yang menggabungkan jadi teks "Volume X Nomor Y Tahun Z" (null kalau salah satu kosong). |
| `app/Http/Controllers/Admin/LoaMasterController.php` | `update()`: validasi & simpan 3 field baru (`integer\|min:1` utk volume/nomor, `integer\|digits:4` utk tahun) menggantikan validasi tanggal. |
| `resources/views/admin/loa-master/edit.blade.php` | Input tanggal tunggal diganti 3 input angka bersebelahan (Vol./No./Thn) dengan label sesuai istilah SK asli. |
| `app/Http/Controllers/Admin/MonitoringAkreditasiController.php` | Logika status ditulis ulang berbasis **Tahun** (bukan diff tanggal presisi hari): "Perlu Bersiap" = tahun sekarang sama dengan atau satu tahun sebelum `accreditation_end_tahun` (padanan ~12 bulan dengan presisi tahunan, karena sumber datanya memang cuma sampai tingkat Tahun — sengaja tidak berpura-pura presisi bulan/hari yang tidak ada). "Kedaluwarsa" = tahun akhir sudah lewat sepenuhnya. |
| `resources/views/admin/monitoring-akreditasi/index.blade.php` | Kolom "Berakhir Tanggal" → "Periode Berakhir" (tampilkan teks periode via accessor), kolom "Sisa Waktu" → satuan tahun (bukan bulan), badge kartu ringkasan disesuaikan teksnya. |
| `tests/Feature/MonitoringAkreditasiTest.php` | Ditulis ulang total (11 test): accessor periode gabung teks dengan benar, accessor null kalau data tidak lengkap, status warning utk tahun-ini & tahun-depan (2 skenario terpisah), status expired utk tahun lampau, status safe utk tahun jauh, status unknown tanpa data, jurnal tanpa akreditasi dikecualikan, urutan sort kedaluwarsa-sebelum-aman, halaman menampilkan teks periode yang benar, form Master LOA menyimpan ketiga field. |

### Verifikasi
- `tests/Feature/MonitoringAkreditasiTest.php` — 11/11 PASS, 29 assertions.
- Migration ditest reversibel penuh: `migrate` → verifikasi kolom baru ada → `migrate:rollback` → verifikasi kolom lama kembali → `migrate` lagi untuk state final.
- Smoke test manual `app()->handle()` dengan data lokal riil: set periode "Volume 6 Nomor 1 Tahun {tahun ini}" untuk 1 jurnal riil (JURRIPEN) → halaman monitoring menampilkan teks periode persis & badge "Perlu Bersiap"; halaman edit Master LOA menampilkan ketiga input baru.
- Full suite `tests/Feature` — 130 test, 348 assertions, **0 failure**.

### Catatan Deploy
Migration `2026_08_01_000004_*` perlu `php artisan migrate --force` di production **setelah** `..._000003` (urutan timestamp sudah benar, dijalankan otomatis berurutan oleh `migrate`). Efek bersihnya di production: kolom `accreditation_expires_at` tidak akan pernah sempat terisi data nyata (dibuat & dihapus lagi di hari yang sama sebelum deploy), langsung berakhir di 3 kolom periode yang baru, semua NULL by default ("Belum Diisi") — aman, tidak ada data yang hilang.
