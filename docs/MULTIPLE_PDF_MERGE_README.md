# 📄 Multiple PDF Upload & Auto-Merge Feature

## 🎯 Ringkasan Fitur

Fitur ini memungkinkan **reviewer** untuk:
- ✅ Upload **1-10 file PDF** sekaligus untuk revisi jurnal
- ✅ System **otomatis menggabungkan** semua PDF menjadi 1 file
- ✅ Admin cukup **download 1 PDF gabungan** saja

## 🚀 Cara Penggunaan

### Untuk Reviewer:

1. **Buka halaman detail task** yang sudah status `SUBMITTED`
2. **Klik tombol** "Upload File Revisi Jurnal"
3. **Pilih multiple PDF files** (Ctrl+Click atau Shift+Click untuk pilih banyak file)
4. **Preview** akan muncul menampilkan list file yang dipilih
5. **Klik "Upload & Gabungkan PDF"**
6. **Tunggu proses** upload dan merge
7. **Selesai!** File gabungan siap di-review admin

**Screenshot Modal:**
```
┌─────────────────────────────────────────────┐
│ 📤 Upload File Revisi Jurnal (Multiple PDFs)│
├─────────────────────────────────────────────┤
│ ℹ️  Upload Multiple PDFs:                    │
│ • Anda dapat upload 1-10 file PDF sekaligus │
│ • Semua file akan otomatis digabung         │
│ • Format: PDF only                          │
│ • Maksimal ukuran: 10MB per file            │
│                                             │
│ Pilih File PDF * (Dapat pilih >1 file)     │
│ [Choose Files] 5 files selected             │
│                                             │
│ File yang akan diupload:                    │
│ 📄 1. introduction.pdf (2.5 MB) ✓ PDF       │
│ 📄 2. methodology.pdf (3.1 MB) ✓ PDF        │
│ 📄 3. results.pdf (4.2 MB) ✓ PDF            │
│ 📄 4. discussion.pdf (2.8 MB) ✓ PDF         │
│ 📄 5. conclusion.pdf (1.5 MB) ✓ PDF         │
│                                             │
│ ↕️ File akan digabung sesuai urutan di atas  │
│                                             │
│ [Batal]  [Upload & Gabungkan 5 PDF] 🚀     │
└─────────────────────────────────────────────┘
```

### Untuk Admin:

1. **Buka detail assignment** review
2. **Scroll ke section** "File Revisi Jurnal"
3. **Klik "Download PDF Gabungan (Merged)"** → Dapat 1 PDF lengkap
4. **Opsional:** Klik file individual jika perlu

**Screenshot Admin View:**
```
┌─────────────────────────────────────────────┐
│ ✓ File Revisi Jurnal                        │
├─────────────────────────────────────────────┤
│ ✓ File revisi jurnal telah diupload         │
│                                             │
│ [📥 Download PDF Gabungan (Merged)] 🔍      │
│   ← RECOMMENDED (All 5 files merged)       │
│                                             │
│ ─────────────────────────────────────      │
│ 📁 File individual (5 files):               │
│ [File 1] [File 2] [File 3] [File 4] [File 5]│
└─────────────────────────────────────────────┘
```

## ⚙️ Technical Specs

### Validation Rules:
- **Format:** PDF only (`.pdf`)
- **Size:** Max 10MB per file
- **Count:** 1-10 files per upload
- **Total:** Max ~100MB untuk 10 files

### File Storage:
```
storage/app/public/review-revisions/{assignment_id}/
├── revision_timestamp_1_file1.pdf
├── revision_timestamp_2_file2.pdf
├── revision_timestamp_3_file3.pdf
└── merged_revision_{assignment_id}_{reviewer_id}_{timestamp}.pdf ← Main
```

### Database Columns:
```sql
review_results table:
- revision_files (JSON)           → Array of individual file paths
- merged_revision_file (VARCHAR)  → Path to merged PDF
- revision_file (VARCHAR)         → Legacy single file (kept for backward compat)
```

## 🛠️ Implementation Details

### 1. PDF Merger Service
**Class:** `App\Services\PdfMergerService`

```php
$pdfMerger = app(\App\Services\PdfMergerService::class);

// Merge multiple PDFs
$result = $pdfMerger->mergePdfs(
    ['path/file1.pdf', 'path/file2.pdf', 'path/file3.pdf'],
    'output/merged.pdf'
);

// Validate PDF
$isValid = $pdfMerger->isValidPdf('path/file.pdf');

// Get page count
$pageCount = $pdfMerger->getPageCount('path/file.pdf');
```

### 2. Controller Method
**Method:** `ReviewResultController@uploadRevision`

```php
// Validate multiple files
$request->validate([
    'revision_files' => 'required|array|min:1|max:10',
    'revision_files.*' => 'required|file|mimes:pdf|max:10240',
]);

// Store files
foreach ($request->file('revision_files') as $file) {
    $path = $file->storeAs('review-revisions/' . $assignment->id, $filename, 'public');
    $uploadedFiles[] = $path;
}

// Merge PDFs
$merged = $pdfMerger->mergePdfs($uploadedFiles, $mergedPath);

// Save to database
$result->update([
    'revision_files' => $uploadedFiles,
    'merged_revision_file' => $merged
]);
```

### 3. View Component (Blade)

**Multiple File Input:**
```html
<input type="file" 
       name="revision_files[]" 
       multiple 
       accept=".pdf"
       required>
```

**JavaScript Preview:**
```javascript
fileInput.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    
    // Validate count
    if (files.length > 10) {
        alert('Maksimal 10 file!');
        return;
    }
    
    // Show preview
    files.forEach((file, index) => {
        // Display file info, size, validation status
    });
});
```

## 📋 Migration Command

```bash
# Run migration
php artisan migrate

# Rollback if needed
php artisan migrate:rollback --step=1
```

## 🧪 Testing Guide

### Manual Testing:

1. **Test Upload 1 PDF:**
   - Upload 1 file → Check merged file created
   - Download → Verify content correct

2. **Test Upload Multiple PDFs:**
   - Upload 3-5 files → Check all merged
   - Download → Verify all pages present, correct order

3. **Test Max Limit:**
   - Upload 10 files → Should succeed
   - Upload 11 files → Should fail with validation error

4. **Test Invalid Files:**
   - Upload non-PDF → Should reject
   - Upload >10MB file → Should reject
   - Upload corrupt PDF → Should fail merge, rollback

5. **Test Admin View:**
   - Check merged PDF download works
   - Check individual files download
   - Verify file count badge

### Automated Testing (TODO):

```php
/** @test */
public function can_upload_multiple_pdfs_and_merge()
{
    // Create test PDFs
    $files = [
        UploadedFile::fake()->create('file1.pdf', 5000, 'application/pdf'),
        UploadedFile::fake()->create('file2.pdf', 3000, 'application/pdf'),
        UploadedFile::fake()->create('file3.pdf', 4000, 'application/pdf'),
    ];
    
    // Upload
    $response = $this->post(route('reviewer.results.uploadRevision', $assignment), [
        'revision_files' => $files
    ]);
    
    // Assert
    $response->assertRedirect();
    $this->assertDatabaseHas('review_results', [
        'review_assignment_id' => $assignment->id,
    ]);
    
    $result = $assignment->reviewResults()->first();
    $this->assertNotNull($result->revision_files);
    $this->assertNotNull($result->merged_revision_file);
    $this->assertCount(3, $result->revision_files);
    Storage::assertExists($result->merged_revision_file);
}
```

## ❗ Troubleshooting

### Error: "Gagal menggabungkan file PDF"

**Penyebab:**
- File bukan PDF valid
- PDF corrupt/rusak
- PDF ter-encrypt
- Memory limit PHP tidak cukup

**Solusi:**
1. Pastikan semua file adalah PDF valid
2. Cek file tidak corrupt (bisa dibuka di PDF reader)
3. Tingkatkan `memory_limit` di `php.ini`:
   ```ini
   memory_limit = 256M
   ```

### Error: "Maximum upload file size exceeded"

**Penyebab:**
- File >10MB
- Total files terlalu besar
- PHP upload limit terlalu kecil

**Solusi:**
1. Compress PDF sebelum upload
2. Tingkatkan limit di `php.ini`:
   ```ini
   upload_max_filesize = 20M
   post_max_size = 100M
   ```

### Error: "Class 'setasign\Fpdi\Fpdi' not found"

**Penyebab:**
- Library belum terinstall

**Solusi:**
```bash
composer require setasign/fpdf
composer require setasign/fpdi
```

## 📚 Library Documentation

- **FPDI:** https://www.setasign.com/products/fpdi/documentation/
- **FPDF:** http://www.fpdf.org/

## ✅ Checklist Deployment

- [ ] Run migration: `php artisan migrate`
- [ ] Verify composer packages installed
- [ ] Check PHP memory_limit (256M recommended)
- [ ] Check upload_max_filesize & post_max_size
- [ ] Create storage directory: `storage/app/public/review-revisions/`
- [ ] Set permissions: `chmod -R 775 storage/`
- [ ] Test upload 1 file
- [ ] Test upload multiple files
- [ ] Test merge functionality
- [ ] Test admin download
- [ ] Verify backward compatibility (old single files)

## 🎉 Benefits

### For Reviewers:
✅ Upload banyak file sekaligus (lebih cepat)  
✅ Tidak perlu manual merge PDF sendiri  
✅ Tetap bisa akses file individual jika perlu  

### For Admin:
✅ Download 1 file saja (praktis)  
✅ File sudah terorganisir & lengkap  
✅ Hemat waktu review  

### For System:
✅ Storage terorganisir per assignment  
✅ Backward compatible dengan data lama  
✅ Audit trail lengkap (individual + merged)  

---

**Status:** ✅ Ready for Production  
**Version:** 1.0.0  
**Last Updated:** January 13, 2026
