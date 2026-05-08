# Log Update — 08 Mei 2026

## 1. Update Template Pesan WhatsApp ke Penulis

**Tujuan:** Menyesuaikan isi pesan WA yang dikirim ke penulis (saat submission masuk / kredensial diperbarui) dengan format komunikasi resmi yang lebih informatif dan jelas.

### Perubahan Utama
- Hapus format bullet point dan bold pada detail submission (lebih bersih di WA)
- Tambah section **🔎 Monitoring & Verifikasi** dengan link `https://verifyloa.apji.org/`
- Ubah peringatan password: dari "segera ubah password" → "password **tidak boleh** diubah" (sinkronisasi sistem)
- Tambah section **📞 Informasi Tambahan** (hubungi Tim Marketing)
- Hapus kalimat "Pesan ini dikirim secara otomatis oleh sistem SIPERA."

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SubmissionController.php` | Update `buildWhatsAppMessage()` — template baru + update (Diperbarui) |
| `app/Http/Controllers/Pic/JournalManagementController.php` | Update `buildWhatsAppMessage()` — template baru |
| `app/Http/Controllers/Marketing/DashboardController.php` | Update `buildWhatsAppMessage()` — template baru + update (Diperbarui) |

## 2. Fix Export Excel HTTP 500 (Memory Exhaustion)

**Tujuan:** Perbaiki error HTTP 500 saat export monitoring submission dengan 4971+ records yang menyebabkan memory exhaustion.

### Perubahan Utama
- Ganti `FromCollection` → `FromQuery` + `WithChunkReading` (chunk 500 record)
- Instance property `$rowNumber` menggantikan function-level static variable
- Tambah try/catch pada parsing `tanggal_submit` dengan fallback `Carbon::parse()`

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Exports/SubmissionsExport.php` | Refactor ke FromQuery + WithChunkReading, fix row counter, safe date parsing |

## 3. Template Pesan WA Kredensial Dapat Diedit dari Admin

**Tujuan:** Memungkinkan admin mengubah template pesan WA notifikasi kredensial penulis langsung dari halaman `/admin/sms-gateway` tanpa perlu edit kode.

### Perubahan Utama
- Tambah 2 setting key baru: `wa_template_credential_new` dan `wa_template_credential_update`
- Tambah 2 static helper method `defaultCredentialNewTemplate()` / `defaultCredentialUpdateTemplate()` di `SmsGatewayController`
- View SMS Gateway menampilkan 2 textarea editor dengan tombol Reset ke Default dan Hapus
- 3 method `buildWhatsAppMessage()` kini membaca template dari DB via `Setting::get()` + substitusi `str_replace()` dengan placeholder `{nama}`, `{kode}`, `{judul}`, `{namaJurnal}`, `{linkSubmit}`, `{username}`, `{password}`

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SmsGatewayController.php` | Tambah 2 key ke index/update, 2 static default template method |
| `resources/views/admin/sms-gateway/index.blade.php` | Tambah 2 textarea editor template kredensial |
| `app/Http/Controllers/Admin/SubmissionController.php` | `buildWhatsAppMessage()` baca dari DB, tambah `use Setting` |
| `app/Http/Controllers/Pic/JournalManagementController.php` | `buildWhatsAppMessage()` baca dari DB, tambah `use Setting` |
| `app/Http/Controllers/Marketing/DashboardController.php` | `buildWhatsAppMessage()` baca dari DB, tambah `use Setting` |

## 4. 🔄 Update: update

- **Commit:** `68dd06b` — 09:49 oleh Gudangsoft
- **File berubah:** 8 file
- `app/Exports/SubmissionsExport.php`
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Http/Controllers/Marketing/DashboardController.php`
- `app/Http/Controllers/Pic/JournalManagementController.php`
- `log-update-2026-05-07.md`
- `log-update-2026-05-08.md`
- `resources/views/admin/sms-gateway/index.blade.php`


## 5. 🔄 Update: update

- **Commit:** `8781f85` — 09:49 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-08.md`


## 6. 🔄 Update: update

- **Commit:** `5d12d69` — 10:00 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-05-08.md`


## 7. 🔄 Update: update

- **Commit:** `87f7d63` — 10:00 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-08.md`


## 8. 🔄 Update: sms ger=tway update

- **Commit:** `c776bc1` — 10:07 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-05-08.md`
- `resources/views/admin/sms-gateway/index.blade.php`


## 9. 🔄 Update: up token

- **Commit:** `394ceb5` — 10:13 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-05-08.md`
- `resources/views/admin/sms-gateway/index.blade.php`


## 10. 🔄 Update: update

- **Commit:** `83afb86` — 10:20 oleh Gudangsoft
- **File berubah:** 2 file
- `log-update-2026-05-08.md`
- `resources/views/admin/sms-gateway/index.blade.php`


## 11. 🔄 Update: smsget

- **Commit:** `c788d65` — 10:31 oleh Gudangsoft
- **File berubah:** 3 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-05-08.md`
- `resources/views/admin/sms-gateway/index.blade.php`


## 12. 🔄 Update: update

- **Commit:** `1334ab9` — 10:39 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-05-08.md`


## 13. 🔄 Update: update sms gertway

- **Commit:** `0fd6d10` — 10:42 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-05-08.md`


## 14. Fix: Form SMS Gateway Selalu Kosong Setelah Simpan

**Tujuan:** Memastikan form SMS Gateway selalu menampilkan nilai yang tersimpan, tidak kosong saat halaman dibuka kembali setelah simpan.

### Akar Masalah
`DB::table('settings')->whereIn()` secara konsisten mengembalikan hasil kosong di production environment, meskipun `Setting::updateOrCreate()` berhasil menyimpan (terbukti dari pesan sukses). Hal ini disebabkan oleh kemungkinan replica lag, query cache, atau konfigurasi koneksi di server production yang berbeda dari local.

### Solusi
Tambah lapisan persistensi berbasis file (`storage/app/sms_gateway_settings.json`) yang sepenuhnya independen dari DB dan Cache driver:
- `writeToFile()` menulis JSON ke `storage/app/sms_gateway_settings.json` setiap kali settings disimpan
- `readFromFile()` membaca file tersebut sebagai sumber data **utama** di `index()`
- DB tetap digunakan sebagai override jika berhasil dibaca (DB = source of truth)
- Protected field (`fonnte_api_token`) kini di-fallback ke file jika DB read gagal

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SmsGatewayController.php` | Tambah `readFromFile()`, `writeToFile()`, `$settingsFile`; update `index()` dan `update()` |


## 15. 🔄 Update: Fix form SMS Gateway selalu kosong setelah simpan

- **Commit:** `b60730e` — 10:51 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-05-08.md`


## 16. 🔄 Update: up

- **Commit:** `7af3f1d` — 10:54 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-08.md`


## 17. Fix: Form SMS Gateway Masih Kosong Setelah Simpan (Round 2)

**Tujuan:** Perbaiki form SMS Gateway yang tetap kosong setelah simpan meskipun sudah ada file-based fallback.

### Akar Masalah
Dua masalah bersamaan:
1. `DB::table('settings')->useWritePdo()->whereIn()` di `index()` mengembalikan hasil kosong — `useWritePdo()` berperilaku tidak konsisten di beberapa setup MySQL production
2. `file_put_contents()` gagal diam-diam (return `false` tanpa exception) — tidak terdeteksi karena tidak ada pengecekan return value
3. `ensureSettingsTableReady()` di `index()` tanpa try-catch — jika DB error, halaman langsung 500 dan data file tidak terbaca

### Solusi
- Ganti `DB::table()->useWritePdo()->whereIn()` → `Setting::whereIn()->pluck()` di `index()` (Eloquent lebih stabil)
- Ganti `DB::table()->useWritePdo()` → `Setting::get()` di semua tempat di `update()`
- Tambah cek return value `file_put_contents` + logging di `writeToFile()`
- Wrap `ensureSettingsTableReady()` di `index()` dengan try-catch agar tidak memblokir pembacaan data dari file

### File yang Diubah
| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/Admin/SmsGatewayController.php` | Ganti `DB::table()->useWritePdo()` → Eloquent, tambah session flash `sms_gw_just_saved`, tambah logging file write, wrap ensureSettingsTableReady |


## 18. 🔄 Update: update sms getway

- **Commit:** `b6a7923` — 17:04 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-05-08.md`


## 19. 🔄 Update: terisi

- **Commit:** `5d6c4a6` — 17:10 oleh Gudangsoft
- **File berubah:** 2 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`
- `log-update-2026-05-08.md`


## 20. 🔄 Update: Fix SMS Gateway form kosong — session flash + Eloquent read

- **Commit:** `d542f47` — 17:12 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-08.md`


## 21. 🔄 Update: ou

- **Commit:** `dfd3531` — 17:13 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-08.md`


## 22. 🔄 Update: dd

- **Commit:** `992d4d2` — 17:13 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-08.md`


## 23. 🔄 Update: aa

- **Commit:** `0ea66b6` — 17:14 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-08.md`


## 24. 🔄 Update: Fix SMS Gateway: session persisten + merge strategy tiga lapisan

- **Commit:** `0b8920a` — 17:21 oleh Gudangsoft
- **File berubah:** 1 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`


## 25. 🔄 Update: Fix merge order: session > DB > file, sync file on every load

- **Commit:** `75de9c1` — 17:30 oleh Gudangsoft
- **File berubah:** 1 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`


## 26. 🔄 Update: Fix SMS Gateway: render view langsung setelah save, tidak redirect

- **Commit:** `72d0e79` — 17:32 oleh Gudangsoft
- **File berubah:** 1 file
- `app/Http/Controllers/Admin/SmsGatewayController.php`


## 27. 🔄 Update: update

- **Commit:** `0a5bde3` — 17:37 oleh Gudangsoft
- **File berubah:** 1 file
- `log-update-2026-05-08.md`

