# Log Update — 12 Juni 2026

## 1. Template Email Monitoring — Sistem Email Otomatis

**Tujuan:** Menambahkan fitur template email untuk notifikasi otomatis pada semua proses pengerjaan di monitoring (penugasan PIC dan validasi tahap).

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_12_000000_create_email_templates_table.php` | Baru — tabel `email_templates` (id, name, trigger_key unique, subject, body, is_active) |
| `app/Models/EmailTemplate.php` | Baru — model dengan 18 trigger keys, method `render()` substitusi variabel, `findActive()` |
| `app/Http/Controllers/Admin/EmailTemplateController.php` | Baru — CRUD + preview + toggle-active |
| `resources/views/admin/email-templates/index.blade.php` | Baru — halaman daftar template, badge grid 18 trigger, toggle aktif, preview modal |
| `resources/views/admin/email-templates/form.blade.php` | Baru — form buat/edit template, klik variabel sisipkan, live preview tanpa save |
| `routes/web.php` | Tambah 8 route admin/email-templates |
| `resources/views/admin/partials/sidebar.blade.php` | Tambah menu "Template Email" setelah "Pengaturan Email" |
| `app/Http/Controllers/Admin/SubmissionController.php` | Integrasi kirim email di `quickAssign()` dan `toggleValidField()` dengan try/catch |

### Detail Fitur

**18 Trigger Keys:**
- `assign_editor1` s/d `assign_validator` — email dikirim saat PIC ditugaskan via quick assign
- `validate_editor1` s/d `validate_validator` — email dikirim saat checkbox validasi dicentang

**Variabel tersedia di template:**
`{nama_artikel}`, `{kode_submit}`, `{id_artikel}`, `{nama_pic}`, `{email_pic}`, `{nama_tahap}`, `{tanggal}`, `{username_editor}`, `{password_editor}`, `{username_reviewer1}`, `{password_reviewer1}`, `{username_reviewer2}`, `{password_reviewer2}`, `{app_name}`

**Catatan:** Email tidak akan terkirim sampai `MAIL_USERNAME` dan `MAIL_PASSWORD` diisi di `.env` production. Kegagalan kirim email di-log di Laravel log, tidak mempengaruhi respons AJAX.

## 2. Lampiran Email (Attachment) pada Template Email

**Tujuan:** Memungkinkan admin melampirkan file (PDF, Word, gambar, dll.) ke template email agar ikut dikirim bersama notifikasi.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_12_010000_create_email_template_attachments_table.php` | Baru — tabel `email_template_attachments` |
| `app/Models/EmailTemplateAttachment.php` | Baru — model dengan relasi ke `EmailTemplate` dan `getFullPath()` |
| `app/Models/EmailTemplate.php` | Tambah relasi `attachments()` |
| `app/Http/Controllers/Admin/EmailTemplateController.php` | Handle upload/hapus lampiran, `deleteAttachment()`, lampiran di `preview()` |
| `resources/views/admin/email-templates/form.blade.php` | Drop zone upload, list file existing (bisa hapus/batal), preview file baru |
| `resources/views/admin/email-templates/index.blade.php` | Badge jumlah lampiran di tabel, daftar lampiran di modal preview |
| `routes/web.php` | Tambah route DELETE `email-template-attachments/{attachment}` |
| `app/Http/Controllers/Admin/SubmissionController.php` | Attach file ke `Mail::html()` saat kirim email di `quickAssign()` dan `toggleValidField()` |

## 2. 🔄 Update: template admin

- **Commit:** `8c541e6` — 13:52 oleh Gudangsoft
- **File berubah:** 9 file
- `app/Http/Controllers/Admin/EmailTemplateController.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Models/EmailTemplate.php`
- `database/migrations/2026_06_12_000000_create_email_templates_table.php`
- `log-update-2026-06-12.md`
- `resources/views/admin/email-templates/form.blade.php`
- `resources/views/admin/email-templates/index.blade.php`
- `resources/views/admin/partials/sidebar.blade.php`
- `routes/web.php`

