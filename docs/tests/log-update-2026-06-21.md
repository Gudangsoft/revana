# Log Update — 21 Juni 2026

## 31. Fix Bug Route Nama Birthday Wish Admin

**Tujuan:** Perbaiki bug yang menyebabkan admin dashboard error (RouteNotFoundException) setiap kali dirender karena nama route tidak cocok.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `routes/web.php` | Ubah nama route admin birthday wish dari `birthday.wish` menjadi `admin.birthday.wish` (view sudah benar memanggil `admin.birthday.wish`) |

### Akar Masalah
View `resources/views/admin/dashboard.blade.php` memanggil `route('admin.birthday.wish')` tapi route terdaftar sebagai `birthday.wish` tanpa prefix `admin.`. Laravel melempar `InvalidArgumentException` saat Blade mengevaluasi nilai parameter `@include` — bukan hanya saat tombol ditekan, tapi setiap kali dashboard dirender.

---

## 30. Master LOA Marketing — Samakan dengan Halaman Admin

**Tujuan:** Marketing bisa setting LOA lengkap (semua field sama dengan admin) untuk jurnal yang mereka kelola, bukan hanya tanggal.

### File yang Diubah / Dibuat
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah `import Storage`; ganti `loaMasterUpdate()` menjadi full (semua field + file upload); tambah `loaMasterEdit()`, `loaMasterPreview()`, `authorizeLoaJournal()` helper |
| `routes/web.php` | Tambah route `GET /loa-master/{journalMaster}/edit` dan `GET /loa-master/{journalMaster}/preview`; ubah update ke `PUT` |
| `resources/views/marketing/loa-master/index.blade.php` | Rewrite: stat cards + tabel bergaya admin (logo, warna, LOA otomatis, kelengkapan) + search + filter chips |
| `resources/views/marketing/loa-master/edit.blade.php` | View baru mirip admin edit: bahasa, identitas, warna, logo, logo akreditasi, header/footer image, LOA otomatis — extend marketing layout |

### Cara Kerja
- Index: stat cards (Total/Lengkap/Otomatis) + tabel dengan filter kelengkapan & LOA otomatis + search
- Setting per jurnal: semua field sama dengan admin (kode, e-ISSN, kota, tanggal, jabatan, warna, logo, akreditasi, header, footer, auto-send)
- Preview: buka LOA submission marketing terbaru di jurnal tersebut
- Otorisasi: marketing hanya bisa akses jurnal di mana mereka punya submission

---

## 29. Menu Master LOA di Portal Marketing

**Tujuan:** Marketing bisa melihat dan mengatur tanggal LOA default per jurnal, namun hanya untuk jurnal yang memiliki submission dari marketing tersebut.

### File yang Diubah / Dibuat
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Marketing/DashboardController.php` | Tambah `loaMasterIndex()` (filter jurnal via submissions marketing) dan `loaMasterUpdate()` (update `loa_tanggal`, cek otorisasi) |
| `routes/web.php` | Tambah route `GET /loa-master` dan `POST /loa-master/{journalMaster}` di grup middleware marketing |
| `resources/views/marketing/loa-master/index.blade.php` | View baru — tabel jurnal + form date picker inline per baris |
| `resources/views/marketing/layouts/app.blade.php` | Tambah menu "Master LOA" di sidebar (setelah Laporan Jurnal) |

### Cara Kerja
- Menu hanya tampil jurnal di mana marketing punya minimal 1 submission
- Relasi: `submissions.marketing_id` → `journal_slots.journal_master_id`
- Update `loa_tanggal` di `journal_masters` — berlaku untuk semua artikel di jurnal itu
- Kosongkan tanggal → LOA pakai hari ini saat dibuka
- Per-artikel masih bisa di-override via URL `?tanggal=` di halaman detail submission

---

## 18. Marketing Bisa Ubah Tanggal LOA per Artikel

**Tujuan:** Marketing punya halaman LOA khusus dengan date picker, tanpa akses admin.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/LoaController.php` | Tambah method `showMarketing()` — auth guard marketing, pass `canEditDate=true`, `backUrl` ke detail submission marketing |
| `routes/web.php` | Tambah route `GET /marketing/submissions/{submission}/loa` → `LoaController::showMarketing` (middleware auth:marketing) |
| `resources/views/admin/loa/receipt.blade.php` | Date picker muncul jika `$isAdminView` ATAU `$canEditDate`; tombol "Edit Afiliasi" hanya untuk admin; `backUrl` override tombol Kembali |
| `resources/views/marketing/show-submission.blade.php` | Tombol "Lihat LOA" pakai route `marketing.submissions.loa` (bukan loa.public) |

### Cara Kerja
- Marketing buka detail submission → klik "Lihat LOA" → halaman LOA dengan date picker
- Ubah tanggal → reload dengan `?tanggal=` → tanggal dokumen berubah
- Kembali → kembali ke halaman detail submission marketing
- "Edit Afiliasi & Data" tidak tampil (admin only)

---

## 17. Akses LOA dari Halaman Marketing

**Tujuan:** Marketing bisa langsung lihat LOA per artikel tanpa perlu masuk ke halaman admin.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/marketing/show-submission.blade.php` | Tambah card hijau "LOA Tersedia" + tombol "Lihat LOA" (hanya muncul jika `kode_loa` terisi) |
| `resources/views/marketing/submissions.blade.php` | Tambah ikon tombol LOA di kolom aksi tabel daftar submission |

### Cara Kerja
- Jika submission belum ada LOA (`kode_loa` null) → tombol tidak tampil
- Jika sudah ada LOA → tombol "Lihat LOA" muncul, buka di tab baru ke halaman publik LOA
- Tidak perlu route/controller baru — reuse `loa.public` yang sudah ada

---

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

## 16. Tata Ulang Layout LOA Master Edit

**Tujuan:** Sederhanakan form LOA Master — hapus Nama Editor & Tanda Tangan Editor, gabungkan Jabatan Editor ke card Identitas Jurnal, Logo Jurnal jadi layout horizontal.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa-master/edit.blade.php` | Hapus card Editor-in-Chief terpisah; pindah Jabatan Editor ke card Identitas (5 kolom); hapus Tanda Tangan Editor; card Logo Jurnal jadi layout horizontal |

---

## 14. Tanggal LOA Diatur di Setting Jurnal (LOA Master)

**Tujuan:** Admin set tanggal LOA default per jurnal di LOA Master edit. Jika kosong → pakai hari ini. Date picker inline di dokumen dihapus.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/loa-master/edit.blade.php` | Tambah field `Tanggal LOA` (date input) di sebelah Kota TTD |
| `app/Http/Controllers/Admin/LoaController.php` | `loaDate()`: prioritas — URL param → `loa_tanggal` jurnal → hari ini |
| `resources/views/admin/loa/receipt.blade.php` | Hapus date picker inline dari sig-block |

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


## 18. 🔄 Update: g

- **Commit:** `9b05714` — 22:41 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-21.md`
- `resources/views/admin/loa/receipt.blade.php`


## 19. 🔄 Update: a

- **Commit:** `9a8adf4` — 22:45 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-06-21.md`


## 20. 🔄 Update: qr

- **Commit:** `e0011f1` — 22:51 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-21.md`
- `resources/views/admin/loa/receipt.blade.php`


## 21. 🔄 Update: up

- **Commit:** `c1505a2` — 23:16 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/LoaController.php`
- `log-update-2026-06-21.md`
- `resources/views/admin/loa-master/edit.blade.php`
- `resources/views/admin/loa/receipt.blade.php`


## 23. 🔄 Update: ok

- **Commit:** `c4da605` — 23:28 oleh Gudangsoft
- **File berubah:** 4 file
- `app/Http/Controllers/Admin/LoaController.php`
- `log-update-2026-06-21.md`
- `resources/views/admin/loa-master/edit.blade.php`
- `resources/views/admin/loa/receipt.blade.php`


## 25. 🔄 Update: loa mar

- **Commit:** `94a105e` — 23:44 oleh Gudangsoft
- **File berubah:** 3 file
- `log-update-2026-06-21.md`
- `resources/views/marketing/show-submission.blade.php`
- `resources/views/marketing/submissions.blade.php`


## 27. 🔄 Update: up loa mar

- **Commit:** `250fc8d` — 23:49 oleh Gudangsoft
- **File berubah:** 6 file
- `app/Http/Controllers/Admin/LoaController.php`
- `log-update-2026-06-21.md`
- `resources/views/admin/loa/receipt.blade.php`
- `resources/views/marketing/show-submission.blade.php`
- `resources/views/marketing/submissions.blade.php`
- `routes/web.php`


## 28. 🔄 Update: mar

- **Commit:** `ee7580e` — 23:53 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-06-21.md`
- `resources/views/marketing/show-submission.blade.php`


## 32. Fix Admin Dashboard — Resilient Route::has() untuk Birthday Notification

**Tujuan:** Admin dashboard masih 500 karena PHP OPcache menyimpan bytecode lama `routes/web.php` (route masih bernama `birthday.wish` di OPcache meski file sudah diubah). Solusi: ubah view agar tidak crash meski nama route berubah.

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `resources/views/admin/dashboard.blade.php` | Ganti `route('admin.birthday.wish')` dengan fallback `Route::has()` — cek nama baru dulu, fallback ke nama lama, fallback ke `#` |

### Penjelasan
Sebelumnya:
```blade
'wishRoute' => route('admin.birthday.wish'),
```
Sekarang:
```blade
'wishRoute' => Route::has('admin.birthday.wish') ? route('admin.birthday.wish') : (Route::has('birthday.wish') ? route('birthday.wish') : '#'),
```
Dashboard tidak akan crash apapun kondisi OPcache atau nama route yang aktif.
