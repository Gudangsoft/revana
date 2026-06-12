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

## 2. Form Screening Awal Penerimaan Artikel

**Tujuan:** Form screening sesuai SOP APJI — editor mengisi checklist 7 seksi (29 item), memilih keputusan, lalu mengirim email hasil ke penulis.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_12_020000_create_screening_forms_table.php` | Baru — tabel `screening_forms` |
| `app/Models/ScreeningForm.php` | Baru — model dengan relasi ke Submission + helper hitung score |
| `app/Http/Controllers/Admin/ScreeningFormController.php` | Baru — CRUD + kirim email screening |
| `app/Models/Submission.php` | Tambah relasi `screeningForm()` |
| `app/Models/EmailTemplate.php` | Tambah 3 trigger key: `screening_diterima`, `screening_revisi`, `screening_ditolak` |
| `resources/views/admin/screenings/form.blade.php` | Baru — form 7 seksi checklist tri-state, 100 preset catatan, keputusan, kirim email |
| `resources/views/admin/screenings/show.blade.php` | Baru — halaman hasil screening dengan tombol kirim email |
| `resources/views/admin/submissions/monitoring.blade.php` | Badge screening di kolom kode_submit |
| `resources/views/admin/partials/sidebar.blade.php` | Fix active state |
| `routes/web.php` | 6 route screening |

### Variabel email screening
`{nama_artikel}`, `{kode_submit}`, `{id_artikel}`, `{nama_penulis}`, `{email_penulis}`, `{keputusan}`, `{similarity}`, `{catatan_editor}`, `{tanggal}`, `{app_name}`

## 3. Tambah Field Email Penulis di Semua Tabel Monitoring

**Tujuan:** Melengkapi info kontak penulis selain WA — email bisa diisi inline di monitoring dan digunakan untuk kirim email screening/notifikasi.

### File yang Diubah

| File | Perubahan |
|------|-----------|
| `database/migrations/2026_06_12_030000_add_email_penulis_to_submissions.php` | Baru — kolom `email_penulis` nullable di tabel submissions |
| `app/Models/Submission.php` | Tambah `email_penulis` ke `$fillable` |
| `app/Http/Controllers/Admin/SubmissionController.php` | Tambah `email_penulis` dan `no_hp_penulis` ke `$allowedFields` quickUpdateCredential |
| `resources/views/admin/submissions/monitoring.blade.php` | Kolom "No HP / Email": input inline edit email, icon mailto |
| `resources/views/admin/fasttrack-management/monitoring/index.blade.php` | Sama seperti admin submissions |
| `resources/views/pic/submissions/monitoring.blade.php` | Tampil email sebagai mailto link (read-only) |
| `resources/views/pic/fasttrack/monitoring.blade.php` | Sama seperti PIC submissions |
| `app/Http/Controllers/Admin/ScreeningFormController.php` | Pre-fill email penulis dari `$submission->email_penulis` |

## 4. Lampiran Email (Attachment) pada Template Email

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


## 4. 🔄 Update: lampiran file email

- **Commit:** `cb686e5` — 14:02 oleh Gudangsoft
- **File berubah:** 9 file
- `app/Http/Controllers/Admin/EmailTemplateController.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Models/EmailTemplate.php`
- `app/Models/EmailTemplateAttachment.php`
- `database/migrations/2026_06_12_010000_create_email_template_attachments_table.php`
- `log-update-2026-06-12.md`
- `resources/views/admin/email-templates/form.blade.php`
- `resources/views/admin/email-templates/index.blade.php`
- `routes/web.php`


## 7. 🔄 Update: mail view

- **Commit:** `eb470f3` — 14:15 oleh Gudangsoft
- **File berubah:** 16 file
- `app/Http/Controllers/Admin/ScreeningFormController.php`
- `app/Http/Controllers/Admin/SubmissionController.php`
- `app/Models/EmailTemplate.php`
- `app/Models/ScreeningForm.php`
- `app/Models/Submission.php`
- `database/migrations/2026_06_12_020000_create_screening_forms_table.php`
- `database/migrations/2026_06_12_030000_add_email_penulis_to_submissions.php`
- `log-update-2026-06-12.md`
- `resources/views/admin/fasttrack-management/monitoring/index.blade.php`
- `resources/views/admin/partials/sidebar.blade.php`

