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

