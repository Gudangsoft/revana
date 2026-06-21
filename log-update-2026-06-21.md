# Log Update — 21 Juni 2026

## 1. Upload Gambar Footer di LOA Master

**Tujuan:** Admin bisa upload gambar footer (JPG/PNG lebar penuh A4) di halaman Setting LOA per jurnal. Jika diisi, gambar tersebut menggantikan bar akreditasi SINTA dan baris verifikasi QR di bagian bawah dokumen LOA (halaman 1 & 2).

### File yang Diubah / Dibuat
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_21_000001_add_footer_image_to_journal_masters.php` | Tambah kolom `footer_image_path` string nullable di tabel `journal_masters` |
| `app/Models/JournalMaster.php` | Tambah `footer_image_path` ke `$fillable` |
| `app/Http/Controllers/Admin/LoaMasterController.php` | Validasi + upload/remove `footer_image` ke `journals/footers` |
| `app/Http/Controllers/Admin/LoaController.php` | Pass `footerImageUrl` ke view receipt (method `publicView` & `show`) |
| `resources/views/admin/loa-master/edit.blade.php` | Tambah card kuning "Gambar Footer LOA" dengan upload, preview real-time, dan remove checkbox |
| `resources/views/admin/loa/receipt.blade.php` | Page 1 & Page 2: jika `footerImageUrl` ada, tampil sebagai `<img>` full-width menggantikan bar SINTA + verified-bar |

### Catatan
Jalankan `php artisan migrate` di production untuk membuat kolom `footer_image_path`.

### Cara Kerja
- Footer **belum** diupload → LOA menampilkan bar SINTA (jika akreditasi SINTA) + baris verifikasi QR
- Footer **sudah** diupload → LOA menampilkan gambar footer penuh di bagian bawah, bar SINTA & verified disembunyikan
- Footer bisa dihapus kapan saja via checkbox "Hapus gambar footer"

## 2. Footer Image Mepet Tepi Bawah + QR Tetap Tampil

**Tujuan:** Gambar footer selalu berada di bagian paling bawah halaman A4 (mepet tepi bawah). QR code verifikasi tetap tampil di atas footer image.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Tambah `display:flex; flex-direction:column` ke `.a4-page` dan `flex:1` ke `.page-inner` agar konten mengisi halaman dan footer selalu ke bawah. QR/verified-bar selalu tampil; footer image ditempatkan sebagai elemen terakhir (paling bawah). |

---

## 3. 🔄 Update: footer

- **Commit:** `4e9544c` — 00:11 oleh Gudangsoft
- **File berubah:** 8 file
- `app/Http/Controllers/Admin/LoaController.php`
- `app/Http/Controllers/Admin/LoaMasterController.php`
- `app/Models/JournalMaster.php`
- `database/migrations/2026_06_21_000001_add_footer_image_to_journal_masters.php`
- `log-update-2026-06-20.md`
- `log-update-2026-06-21.md`
- `resources/views/admin/loa-master/edit.blade.php`
- `resources/views/admin/loa/receipt.blade.php`


## 5. Upload Logo Akreditasi di LOA Master

**Tujuan:** Admin bisa upload gambar logo akreditasi (SINTA, Scopus, WoS, dll) per jurnal. Logo tampil di bar bawah dokumen LOA menggantikan badge teks SINTA otomatis.

### File yang Diubah / Dibuat
| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_21_000002_add_accreditation_logo_to_journal_masters.php` | Kolom `accreditation_logo_path` nullable di `journal_masters` |
| `app/Models/JournalMaster.php` | Tambah `accreditation_logo_path` ke `$fillable` |
| `app/Http/Controllers/Admin/LoaMasterController.php` | Validasi + upload/remove `accreditation_logo` ke `journals/accreditation` |
| `app/Http/Controllers/Admin/LoaController.php` | Pass `accreditationLogoUrl` ke view receipt |
| `resources/views/admin/loa-master/edit.blade.php` | Card baru "Logo Akreditasi" — upload, preview dengan background warna header jurnal, remove checkbox |
| `resources/views/admin/loa/receipt.blade.php` | SINTA bar: jika `accreditationLogoUrl` ada → tampil gambar logo; jika tidak → fallback badge teks SINTA CSS |

### Prioritas tampilan bar bawah LOA
1. Footer image (custom) → menggantikan seluruh bar
2. Tanpa footer image: Logo akreditasi (gambar) jika ada → tampil di SINTA bar
3. Tanpa logo akreditasi: Badge teks SINTA (CSS) jika jurnal ber-akreditasi SINTA
4. Tanpa keduanya: bar tidak muncul

### Catatan
Jalankan `php artisan migrate` di production untuk kolom `accreditation_logo_path`.

---

## 4. 🔄 Update: s

- **Commit:** `3657e9a` — 00:20 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-21.md`
- `resources/views/admin/loa/receipt.blade.php`


## 6. 🔄 Update: logo sinta

- **Commit:** `a98dd52` — 00:28 oleh Gudangsoft
- **File berubah:** 7 file
- `app/Http/Controllers/Admin/LoaController.php`
- `app/Http/Controllers/Admin/LoaMasterController.php`
- `app/Models/JournalMaster.php`
- `database/migrations/2026_06_21_000002_add_accreditation_logo_to_journal_masters.php`
- `log-update-2026-06-21.md`
- `resources/views/admin/loa-master/edit.blade.php`
- `resources/views/admin/loa/receipt.blade.php`


## 7. Fix Posisi Logo Akreditasi di Dokumen LOA

**Tujuan:** Logo akreditasi tampil di area putih antara QR/verified-bar dan footer image, bukan di bar terpisah yang tersembunyi saat ada footer image.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Logo akreditasi dipindah ke bawah verified-bar (di atas footer image), tampil selalu ketika ada logo. SINTA CSS badge hanya muncul jika tidak ada logo akreditasi dan tidak ada footer image. |

---

## 8. 🔄 Update: a

- **Commit:** `5686dc5` — 00:31 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-06-21.md`


## 10. Perbaikan Tampilan Logo Akreditasi di LOA

**Tujuan:** Logo akreditasi tampil di dalam bar berwarna (warna sekunder jurnal) di atas QR verified-bar — terlihat seperti sertifikasi resmi, bukan kotak putih mengambang.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Logo akreditasi masuk ke dalam `.sinta-bar` (bar warna sekunder); kotak putih mengambang dihapus. Logo diberi filter `brightness(0) invert(1)` agar putih di atas background berwarna. |

## 14. Edit Tanggal LOA Manual dari Top Bar

**Tujuan:** Admin bisa ubah tanggal LOA langsung dari halaman preview tanpa edit URL secara manual.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LoaController.php` | Pass `loaDateRaw` (format YYYY-MM-DD) ke view di kedua method `publicView` & `show` |
| `resources/views/admin/loa/receipt.blade.php` | Tambah date input `📅 Tanggal LOA` di top bar (hanya tampil jika admin view); onChange reload URL dengan `?tanggal=` |

### Cara Kerja
- Buka LOA dari admin → date picker tampil di top bar
- Ubah tanggal → halaman reload otomatis → tanggal di dokumen berubah
- Tidak tampil saat LOA dibuka oleh penulis (public view)
- Tidak ikut terprint

---

## 13. Hapus Judul Halaman 1 + Format Nomor LOA Baru

**Tujuan:** Hapus baris judul "RECEIPT FOR PAPER" / "SURAT PENERIMAAN ARTIKEL" (hanya tampil subtitle). Format nomor LOA diubah menjadi: `KodeArtikel/KodeSipera/InisialJurnal/RomawiBulan/Tahun`.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Hapus `<div class="doc-title">` (judul yang dicoret) |
| `app/Http/Controllers/Admin/LoaController.php` | `loaNumber()`: format baru = `id_artikel/kode_submit/kode_singkat/romawi/tahun` |

### Contoh Nomor LOA Baru
`1839/FT202602120028SIPERA/SIPERA/VII/2026`

---

## 12. Pindah Logo Akreditasi ke Bawah QR (Kotak Putih)

**Tujuan:** Logo akreditasi tampil di area putih di bawah QR verified-bar, bukan di dalam bar berwarna SINTA. Lebih rapi dan terlihat jelas.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa/receipt.blade.php` | Tambah CSS `.acred-logo-bar`; hapus logo dari `.sinta-bar`; logo akreditasi ditampilkan di div putih setelah verified-bar, sebelum footer image. |

---

## 9. 🔄 Update: u

- **Commit:** `71604fe` — 00:39 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-21.md`
- `resources/views/admin/loa/receipt.blade.php`


## 11. 🔄 Update: s

- **Commit:** `1443561` — 00:41 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-21.md`
- `resources/views/admin/loa/receipt.blade.php`


## 13. 🔄 Update: z

- **Commit:** `87f3c2f` — 00:47 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-21.md`
- `resources/views/admin/loa/receipt.blade.php`


## 14. 🔄 Update: sa

- **Commit:** `7a41776` — 00:51 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-21.md`
- `resources/views/admin/loa/receipt.blade.php`


## 17. 🔄 Update: up

- **Commit:** `59f93ea` — 22:34 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/LoaController.php`
- `log-update-2026-06-21.md`
- `resources/views/admin/loa/receipt.blade.php`

