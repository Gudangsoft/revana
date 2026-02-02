# Changelog - Fix: Data Jurnal Tidak Muncul

**Date:** February 2, 2026  
**Issue:** Journal data not displaying on submission detail page  
**URL:** https://portal.apji.org/pic/submissions/{id}

## Problem

On the submission detail page (e.g., /pic/submissions/566), the "Jurnal:" field was empty when it should display the journal name. This was occurring because the code was trying to access a non-existent field name.

## Root Cause

The view files were attempting to access `$submission->journalSlot->journalMaster->name`, but the `journal_masters` table uses the field name `nama_jurnal`, not `name`.

## Solution

Updated all occurrences of `journalMaster->name` to `journalMaster->nama_jurnal` in the following files:

1. **resources/views/pic/submissions/show.blade.php** (Line 53)
   - Fixed journal name display on PIC submission detail page

2. **resources/views/admin/dashboard.blade.php** (Lines 238, 304)
   - Fixed journal name display in admin dashboard tables

3. **resources/views/pic/submissions/monitoring-card.blade.php** (Line 162)
   - Fixed journal name display in monitoring card

## Files Changed

- `resources/views/pic/submissions/show.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/pic/submissions/monitoring-card.blade.php`

## Testing

After deploying this fix:
1. Navigate to any submission detail page (e.g., /pic/submissions/566)
2. Verify that the "Jurnal:" field now displays the journal name correctly
3. Check admin dashboard to ensure journal names appear in the tables
4. Verify monitoring cards show journal names properly

## Impact

- **Affected Users:** PIC users and Admin users
- **Severity:** Medium (data not displayed but not causing system errors)
- **Risk:** Low (simple field name correction)
