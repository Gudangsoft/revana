# CHANGELOG - SIPERA Review Form Update
**Date:** January 11, 2026
**Version:** 2.0

## Summary
Updated the review form from the old format to the new **FORMULIR REVIEW ARTIKEL ILMIAH SIPERA** format for reviewers/mitra bestari in Bahasa Indonesia.

## Changes Made

### 1. Database Migration
**File:** `database/migrations/2026_01_11_222111_update_review_results_for_sipera_form.php`
- Added new fields to `review_results` table:
  - **Section A - Informasi Naskah:** manuscript_id, manuscript_title, article_type, field_section_topic
  - **Section B - Konflik Kepentingan & Etika:** conflict_of_interest, plagiarism_detected, excessive_self_citation, other_ethical_issues, ai_usage_statement (with explanation fields)
  - **Section C - Penilaian Cepat:** 10 rating aspects (scope, novelty, significance, soundness, methodology, analysis, presentation, figures, references, language) each with score 1-5 and notes
  - **Section D - Checklist Evaluasi Detail:** 10 checklist items (abstract, intro, novelty, literature, method, design, results, discussion, conclusion, data_availability) with Ya/Tidak/Perlu Perbaikan options
  - **Section E - Evaluasi Referensi:** references_adequate, references_manipulation, irrelevant_references, suggested_references
  - **Section F - Rekomendasi Akhir:** recommendation_reason

### 2. Model Update
**File:** `app/Models/ReviewResult.php`
- Added all new SIPERA fields to $fillable array
- Added new boolean cast fields for ethics and reference evaluation
- Kept old fields for backward compatibility

### 3. Controller Update
**File:** `app/Http/Controllers/Reviewer/ReviewResultController.php`
- Updated validation rules in `store()` method to include all SIPERA fields
- Added validation for new recommendation option: REJECT_RESUBMIT
- Removed old technical checkbox handling
- Changed file_path value to 'formulir-review-sipera'

### 4. Form View (Create/Edit)
**File:** `resources/views/reviewer/results/create.blade.php`
- **Complete redesign** with new SIPERA format structure:
  - Section A: Informasi Naskah (manuscript info with article type radio buttons)
  - Section B: Pernyataan Konflik Kepentingan & Etika (5 ethical questions with Yes/No radios and explanation fields)
  - Section C: Penilaian Cepat (10 aspects with 1-5 rating scale and short notes)
  - Section D: Checklist Evaluasi Detail (10 questions with 3 options: Ya/Tidak/Perlu Perbaikan)
  - Section E: Evaluasi Referensi (reference evaluation with suggested references textarea)
  - Section F: Rekomendasi Akhir Reviewer (5 recommendation options + reason textarea)
  - Pernyataan Reviewer (reviewer statement with name, date, and signature)
- Added JavaScript for auto-focusing explanation fields
- Added checkbox symbols (☐) in labels to match official form design
- Responsive design with Bootstrap 5

### 5. PDF Template
**File:** `resources/views/reviewer/results/pdf.blade.php`
- **Complete redesign** to match SIPERA format
- Professional PDF layout with:
  - Proper title and subtitle
  - All sections A-F clearly formatted
  - Checkbox symbols for selections
  - Signature section at bottom
  - Page break after Section C for better pagination
- Font: Times New Roman 11pt with proper spacing
- Better table layouts and borders

## New Features

### Ethics and Integrity Section
- Conflict of interest declaration
- Plagiarism detection reporting
- Self-citation monitoring
- Other ethical issues reporting
- **AI usage statement** (new requirement for transparency)

### Enhanced Rating System
- Changed from 8 to **10 rating aspects**
- Added: scope, soundness, figures, references, language
- Each aspect includes score (1-5) AND short notes

### Detailed Checklist
- **10 specific evaluation questions**
- 3-option assessment (Ya/Tidak/Perlu Perbaikan)
- Covers all article sections from abstract to data availability

### Reference Evaluation
- Specific questions about reference adequacy
- Citation manipulation detection
- **Mandatory suggested references** with DOI requirements

### Expanded Recommendations
- Added 5th option: "REJECT_RESUBMIT" (Reject but resubmission possible)
- **Mandatory reason field** for all recommendations

## Backward Compatibility

The old fields are retained in the database and model:
- `journal_name`, `article_code`, `article_title` (now replaced by manuscript_id, manuscript_title)
- `score_1` through `score_8` with comments
- `technical_1`, `technical_2`, `technical_3`
- `improvement_suggestions`
- `reviewer_signature`, `statement_date`

These can still be accessed for old records but **new submissions will use the SIPERA fields**.

## Backup Files Created

The following backup files were created during migration:
- `resources/views/reviewer/results/create.blade.php.backup`
- `resources/views/reviewer/results/pdf.blade.php.backup`

These contain the old form format and can be restored if needed.

## Migration Instructions

To apply these changes to the database:
```bash
php artisan migrate
```

The migration is **non-destructive** and only adds new columns. Existing data is preserved.

## Notes for Developers

1. **Display Views:** The admin and reviewer "show" views (`admin/assignments/show.blade.php`, `reviewer/tasks/show.blade.php`) still display the OLD format. These should be updated in a future iteration to show SIPERA fields.

2. **Validation:** All new fields have appropriate validation in the controller. Required fields are marked with `<span class="text-danger">*</span>` in the form.

3. **Form Auto-Fill:** The form automatically populates:
   - Manuscript ID from assignment article_number
   - Manuscript title from assignment article_title
   - Review date with current date
   - Reviewer name from authenticated user

4. **PDF Generation:** The PDF uses DomPDF library with Times New Roman font. Signature images are included if available.

## Recommendation Values

The new recommendation field accepts:
- `ACCEPT` - Terima tanpa revisi
- `MINOR_REVISION` - Terima dengan revisi minor
- `MAJOR_REVISION` - Revisi mayor – tinjau ulang
- `REJECT_RESUBMIT` - Tolak – dapat submit ulang jika diperbaiki (NEW)
- `REJECT` - Tolak – tidak disarankan submit ulang

## Testing Checklist

- [x] Migration runs successfully
- [x] Form displays correctly
- [x] All fields are validated
- [x] Form submission works
- [x] PDF generation works
- [ ] Admin view displays SIPERA format (future work)
- [ ] Reviewer show view displays SIPERA format (future work)

## Future Improvements

1. Update admin/reviewer show views to display SIPERA format
2. Add data migration script to populate SIPERA fields from old fields for existing records
3. Add field mapping for backward compatibility display
4. Consider adding summary statistics for rating scores
5. Add export functionality for SIPERA data analysis
