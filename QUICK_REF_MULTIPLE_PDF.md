# 📋 Quick Reference: Multiple PDF Upload & Merge

## 🎯 TL;DR
Upload multiple PDFs → Auto-merge → Download 1 PDF

---

## 👤 For Reviewers

### How to Upload:
1. Task page → "Upload File Revisi Jurnal"
2. Select 1-10 PDF files (Ctrl+Click)
3. Click "Upload & Gabungkan PDF"
4. Done! ✅

### Rules:
- ✅ Format: PDF only
- ✅ Size: Max 10MB per file
- ✅ Count: 1-10 files
- ❌ DOC/DOCX not supported (convert to PDF first)

---

## 👔 For Admin

### How to Download:
1. Assignment detail → "File Revisi Jurnal" section
2. Click "**Download PDF Gabungan (Merged)**" 🎯
3. Get 1 complete PDF ✅

### Options:
- **Merged PDF** ← Use this (recommended)
- Individual files ← If needed

---

## 💻 For Developers

### Key Files:
```
app/Services/PdfMergerService.php          ← Merge logic
app/Http/Controllers/Reviewer/
  ReviewResultController.php               ← Upload handler
resources/views/reviewer/tasks/show.blade.php ← UI
```

### Database:
```sql
review_results:
  - revision_files (JSON)           → ['file1.pdf', 'file2.pdf']
  - merged_revision_file (VARCHAR)  → 'merged.pdf'
```

### Usage:
```php
$pdfMerger = app(\App\Services\PdfMergerService::class);
$merged = $pdfMerger->mergePdfs($files, $outputPath);
```

---

## 🔧 Troubleshooting

| Problem | Solution |
|---------|----------|
| "Gagal merge" | Check file PDF valid |
| "File too large" | Compress PDF or increase upload_max_filesize |
| "Memory limit" | Set memory_limit=256M |
| "Class not found" | Run: composer require setasign/fpdi |

---

## ✅ Checklist Deployment

```bash
□ php artisan migrate
□ composer install
□ chmod -R 775 storage/
□ php artisan storage:link
□ Test upload
□ Test merge
□ Test download
```

---

## 📊 Validation Rules

```php
'revision_files' => 'required|array|min:1|max:10',
'revision_files.*' => 'required|file|mimes:pdf|max:10240',
```

---

## 🎨 UI Components

### Modal: `#uploadRevisionModal`
- Input: `revision_files[]` (multiple, accept=.pdf)
- Preview: `#selectedFilesList`
- Submit: Auto-merge on upload

### Admin View:
- Primary: Download merged PDF (green button)
- Secondary: Individual files (small buttons)

---

## 📱 API Endpoint

```
POST /reviewer/tasks/{assignment}/upload-revision
Body: revision_files[] (multipart/form-data)
Response: Redirect with success/error message
```

---

## 🔐 Authorization

Only assigned reviewers can upload:
- reviewer_id
- reviewer_2_id
- reviewer_3_id
- reviewer_4_id
- reviewer_5_id

---

## 📦 Dependencies

```json
"setasign/fpdf": "^1.8",
"setasign/fpdi": "^2.6"
```

---

## 🎯 Key Methods

### PdfMergerService:
- `mergePdfs(array $files, string $output)` → Merge PDFs
- `isValidPdf(string $path)` → Validate PDF
- `getPageCount(string $path)` → Count pages

### Controller:
- `uploadRevision(Request $request, ReviewAssignment $assignment)` → Handle upload

---

## 💾 Storage Structure

```
storage/app/public/review-revisions/
  ├── {assignment_id}/
  │   ├── revision_{timestamp}_1_file.pdf
  │   ├── revision_{timestamp}_2_file.pdf
  │   └── merged_revision_{id}_{timestamp}.pdf ← Main
```

---

## ⚡ Performance Tips

- ⚡ Use small PDFs for testing
- ⚡ Set memory_limit to 256M
- ⚡ Show loading indicator for large files
- ⚡ Consider queue for >5 files

---

## 🔄 Rollback

If merge fails → All uploaded files deleted (rollback)

---

## 📖 Documentation

- Full docs: `CHANGELOG_2026-01-13_MULTIPLE_PDF_MERGE.md`
- Usage guide: `MULTIPLE_PDF_MERGE_README.md`
- Summary: `FEATURE_SUMMARY_MULTIPLE_PDF.md`

---

**Quick Help:** Check logs at `storage/logs/laravel.log`

---

✅ **Status:** Production Ready  
📅 **Date:** January 13, 2026
