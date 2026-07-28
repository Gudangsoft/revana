# Update Assignment Field of Study

## Masalah
Assignment lama (sebelum update ini) belum memiliki `field_of_study_id`, sehingga field "Bidang/Section/Topik" tidak terisi otomatis di form reviewer.

## Solusi

### Opsi 1: Update Manual via Command (Recommended)

Gunakan Artisan command untuk update assignment lama secara batch:

```bash
# 1. Lihat daftar field of study yang tersedia
php artisan tinker
>>> App\Models\FieldOfStudy::all(['id', 'name']);

# 2. Update semua assignment yang belum punya field_of_study_id
php artisan assignments:update-field-of-study {field_of_study_id}

# Contoh: Set semua assignment lama ke field "Teknik" (ID=1)
php artisan assignments:update-field-of-study 1

# Atau langsung update tanpa konfirmasi
php artisan assignments:update-field-of-study 1 --all
```

**Command akan:**
- Mencari semua assignment dengan `field_of_study_id = NULL`
- Menampilkan list assignment yang akan diupdate
- Meminta konfirmasi
- Update semua assignment tersebut

### Opsi 2: Update Manual via Database

```sql
-- Lihat assignment yang belum punya field_of_study_id
SELECT id, article_title, status FROM review_assignments WHERE field_of_study_id IS NULL;

-- Update assignment tertentu (ganti ID sesuai kebutuhan)
UPDATE review_assignments SET field_of_study_id = 1 WHERE id = 17;

-- Atau update semua sekaligus
UPDATE review_assignments SET field_of_study_id = 1 WHERE field_of_study_id IS NULL;
```

### Opsi 3: Biarkan Manual Input (Fallback)

Jika assignment tidak punya `field_of_study_id`, form reviewer akan otomatis menampilkan input text biasa yang bisa diisi manual.

```php
// View logic sudah handle ini:
@if($assignment->fieldOfStudy)
    // Tampilkan readonly field
@else
    // Tampilkan input text manual
@endif
```

## Untuk Assignment Baru

Sejak update ini, semua assignment baru yang dibuat admin **WAJIB** memilih bidang ilmu dari dropdown. Field ini akan otomatis terisi di form reviewer dan PDF.

## Testing

1. **Test Assignment Lama (tanpa field_of_study_id):**
   - Buka `/reviewer/tasks/{id}/submit-result`
   - Field "Bidang/Section/Topik" akan muncul sebagai input text manual
   - Isi manual, submit form ✅

2. **Test Assignment Baru (dengan field_of_study_id):**
   - Admin buat assignment baru di `/admin/assignments/create`
   - Pilih bidang ilmu dari dropdown
   - Reviewer buka task
   - Field "Bidang/Section/Topik" terisi otomatis (readonly) ✅
   - Submit form ✅
   - PDF menampilkan bidang ilmu ✅

## Migration Status

```bash
# Jalankan migration (jika belum)
php artisan migrate

# Migration file:
# 2026_01_13_184510_add_field_of_study_id_to_review_assignments_table.php
```

## Files Modified

1. ✅ `database/migrations/2026_01_13_184510_add_field_of_study_id_to_review_assignments_table.php`
2. ✅ `app/Models/ReviewAssignment.php` - Added fieldOfStudy() relation
3. ✅ `app/Http/Controllers/Admin/ReviewAssignmentController.php` - Added field dropdown & validation
4. ✅ `app/Http/Controllers/Reviewer/ReviewResultController.php` - Eager load fieldOfStudy
5. ✅ `resources/views/admin/assignments/create.blade.php` - Added field dropdown
6. ✅ `resources/views/reviewer/results/create.blade.php` - Auto-fill or manual input
7. ✅ `resources/views/reviewer/results/pdf.blade.php` - Show field from assignment
8. ✅ `app/Console/Commands/UpdateAssignmentFieldOfStudy.php` - Batch update command
