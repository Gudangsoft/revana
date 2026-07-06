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

