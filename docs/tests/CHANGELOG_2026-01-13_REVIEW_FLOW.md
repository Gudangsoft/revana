# Changelog - Review Form Flow Update
**Date:** 13 Januari 2026

## Summary
Mengubah alur formulir review agar reviewer dapat langsung download PDF setelah submit formulir tanpa menunggu validasi admin. Upload file revisi jurnal dilakukan setelah download PDF, dan validasi admin dilakukan setelah file revisi diupload.

## Alur Baru
1. **Submit Formulir Review** → Reviewer mengisi dan submit formulir review
2. **Download PDF Otomatis** → Setelah submit, langsung redirect ke download PDF (tanpa validasi admin)
3. **Upload File Revisi Jurnal** → Reviewer upload file revisi jurnal (PDF/DOC/DOCX/ZIP/RAR, max 10MB)
4. **Validasi Admin** → Admin melakukan validasi setelah file revisi diupload

## Alur Lama (Sebelum Update)
1. Submit Formulir Review
2. Menunggu validasi admin
3. Setelah approved, baru bisa download PDF

## Changes Made

### 1. Database Changes
**File:** `database/migrations/2026_01_13_160217_add_revision_file_to_review_results_table.php`
- Menambah kolom `revision_file` di tabel `review_results` untuk menyimpan path file revisi jurnal

### 2. Model Updates
**File:** `app/Models/ReviewResult.php`
- Menambahkan `revision_file` ke dalam `$fillable` array

### 3. Controller Updates
**File:** `app/Http/Controllers/Reviewer/ReviewResultController.php`

#### a. Method `downloadPdf()`
- **REMOVED:** Validasi `if ($assignment->status !== 'APPROVED')`
- **CHANGED:** Reviewer dapat download PDF langsung setelah submit tanpa menunggu approval admin
- Error message diperbaiki jika belum ada review result

#### b. Method `store()`
- **REMOVED:** Validasi field `references_manipulation` dan `irrelevant_references` (sudah dihapus dari form)
- **CHANGED:** Redirect dari `reviewer.tasks.show` ke `reviewer.results.downloadPdf` 
- Otomatis download PDF setelah submit formulir

#### c. Method `uploadRevision()` (NEW)
- Method baru untuk upload file revisi jurnal
- Validasi file: PDF, DOC, DOCX, max 10MB
- Menghapus file lama jika ada
- Menyimpan file ke storage `review-revisions`
- Update field `revision_file` di review_results

### 4. Routes Updates
**File:** `routes/web.php`
- **ADDED:** Route baru untuk upload revision
  ```php
  Route::post('/tasks/{assignment}/upload-revision', [ReviewResultController::class, 'uploadRevision'])
       ->name('results.uploadRevision');
  ```
- **REMOVED:** Route lama di TaskController `tasks.uploadRevision`

### 5. View Updates

#### a. Reviewer Task Show Page
**File:** `resources/views/reviewer/tasks/show.blade.php`

**Status ON_PROGRESS/REVISION:**
- Tampilkan tombol "ISI FORMULIR REVIEW ARTIKEL"

**Status SUBMITTED:**
- Tampilkan tombol "Download PDF Review"
- Tampilkan tombol "Upload File Revisi Jurnal"
- Tampilkan status file revisi (sudah/belum upload)
- Tampilkan alert menunggu validasi admin

**Status APPROVED:**
- Tampilkan tombol "Download PDF Review"
- Tampilkan alert review sudah disetujui

#### b. Upload Revision Modal
**File:** `resources/views/reviewer/tasks/show.blade.php`
- Update form action ke `reviewer.results.uploadRevision`
- Label diperjelas menjadi "File Revisi Jurnal"
- Accept file types: .pdf, .doc, .docx, .zip, .rar
- Max size: 10MB
- Cek file revisi dari review_results, bukan dari assignment

#### c. Admin Assignment Show Page
**File:** `resources/views/admin/assignments/show.blade.php`
- **REMOVED:** Section "Ada Manipulasi Referensi"
- **ADDED:** Section "File Revisi Jurnal" untuk menampilkan dan download file revisi yang diupload reviewer

#### d. Review Form
**File:** `resources/views/reviewer/results/create.blade.php`
- **REMOVED:** Pertanyaan #2 tentang referensi tidak relevan/manipulasi sitasi
- **CHANGED:** Pertanyaan #3 (Saran referensi tambahan) menjadi pertanyaan #2

#### e. PDF View
**File:** `resources/views/reviewer/results/pdf.blade.php`
- **REMOVED:** Pertanyaan #2 tentang referensi tidak relevan/manipulasi sitasi
- **CHANGED:** Pertanyaan #3 menjadi pertanyaan #2

## Migration Command
```bash
php artisan migrate
```

## Testing Checklist
- [ ] Submit formulir review → otomatis download PDF
- [ ] Download PDF setelah submit (status SUBMITTED)
- [ ] Upload file revisi jurnal (PDF, DOC, DOCX, ZIP, RAR)
- [ ] Tampilan file revisi di reviewer task show
- [ ] Tampilan file revisi di admin assignment show
- [ ] Validasi file size max 10MB
- [ ] Validasi file types
- [ ] Hapus file lama saat upload file baru
- [ ] Admin dapat download file revisi

## Notes
- File revisi disimpan di storage `public/review-revisions/`
- PDF review tetap generate sesuai formulir yang sudah diisi
- Admin masih tetap perlu approve review setelah file revisi diupload
- Backward compatibility: Form lama tetap bisa berfungsi (field references_manipulation dan irrelevant_references sudah nullable di database)

## Affected Files
1. `database/migrations/2026_01_13_160217_add_revision_file_to_review_results_table.php` (NEW)
2. `app/Models/ReviewResult.php` (MODIFIED)
3. `app/Http/Controllers/Reviewer/ReviewResultController.php` (MODIFIED)
4. `routes/web.php` (MODIFIED)
5. `resources/views/reviewer/tasks/show.blade.php` (MODIFIED)
6. `resources/views/reviewer/results/create.blade.php` (MODIFIED)
7. `resources/views/reviewer/results/pdf.blade.php` (MODIFIED)
8. `resources/views/admin/assignments/show.blade.php` (MODIFIED)
