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
