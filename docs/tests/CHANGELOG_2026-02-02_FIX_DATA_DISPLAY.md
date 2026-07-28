# Changelog - Fix: Data Display Issues (Jurnal & PIC Marketing)

**Date:** February 2, 2026  
**Issues:** 
1. Journal data not displaying on submission detail page
2. PIC Marketing data not displaying on monitoring pages

**URLs:** 
- https://portal.apji.org/pic/submissions/{id}
- https://portal.apji.org/pic/submissions/monitoring

## Problem 1: Data Jurnal Tidak Muncul

On the submission detail page (e.g., /pic/submissions/566), the "Jurnal:" field was empty when it should display the journal name. This was occurring because the code was trying to access a non-existent field name.

### Root Cause

The view files were attempting to access `$submission->journalSlot->journalMaster->name`, but the `journal_masters` table uses the field name `nama_jurnal`, not `name`.

### Solution

Updated all occurrences of `journalMaster->name` to `journalMaster->nama_jurnal` in the following files:

1. **resources/views/pic/submissions/show.blade.php** (Line 53)
   - Fixed journal name display on PIC submission detail page

2. **resources/views/admin/dashboard.blade.php** (Lines 238, 304)
   - Fixed journal name display in admin dashboard tables

3. **resources/views/pic/submissions/monitoring-card.blade.php** (Line 162)
   - Fixed journal name display in monitoring card

## Problem 2: Data PIC Marketing Tidak Muncul

On the monitoring pages (/pic/submissions/monitoring and /pic/fasttrack/monitoring), the "PIC Marketing" column was not displaying the marketing person's name correctly.

### Root Cause

The view files were attempting to access `$s->picMarketing?->name`, but the correct relationship name in the Submission model is `marketing()`, not `picMarketing()`.

### Solution

Updated all occurrences of `picMarketing` to `marketing` in the following files:

1. **resources/views/pic/submissions/monitoring.blade.php** (Line 709)
   - Fixed PIC Marketing name display in submissions monitoring table

2. **resources/views/pic/fasttrack/monitoring.blade.php** (Line 720)
   - Fixed PIC Marketing name display in fasttrack monitoring table

3. **resources/views/pic/fasttrack/monitoring-backup.blade.php** (Line 709)
   - Fixed PIC Marketing name display in fasttrack monitoring backup table

## Files Changed

- `resources/views/pic/submissions/show.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/pic/submissions/monitoring-card.blade.php`
- `resources/views/pic/submissions/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring.blade.php`
- `resources/views/pic/fasttrack/monitoring-backup.blade.php`

## Testing

After deploying this fix:
1. Navigate to any submission detail page (e.g., /pic/submissions/566)
   - Verify that the "Jurnal:" field now displays the journal name correctly
2. Navigate to submissions monitoring page (/pic/submissions/monitoring)
   - Verify that the "PIC Marketing" column shows the correct marketing person's name
3. Navigate to fasttrack monitoring page (/pic/fasttrack/monitoring)
   - Verify that the "PIC Marketing" column shows the correct marketing person's name
4. Check admin dashboard to ensure journal names appear in the tables
5. Verify monitoring cards show journal names properly

## Impact

- **Affected Users:** PIC users and Admin users
- **Severity:** Medium (data not displayed but not causing system errors)
- **Risk:** Low (simple field/relationship name corrections)
