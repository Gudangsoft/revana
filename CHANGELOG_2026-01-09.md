# Update Log - 9 Januari 2026

## Perbaikan UI/UX - Assign Reviewer

### Perubahan pada halaman Assign Reviewer (`admin/assignments/create`)

**File yang dimodifikasi:**
- `resources/views/admin/assignments/create.blade.php`

**Deskripsi Perubahan:**

1. **List Reviewer Hidden by Default**
   - List reviewer tidak lagi ditampilkan secara default
   - List hanya muncul ketika user mulai mengetik di search box
   - Mengurangi visual clutter dan meningkatkan user experience

2. **Search-First Approach**
   - User harus mengetik untuk mencari reviewer (search-first)
   - List reviewer muncul dengan hasil filter sesuai keyword
   - Lebih efisien untuk memilih dari banyak reviewer

3. **Auto-populate Selected Reviewer**
   - Ketika reviewer dipilih, nama reviewer langsung muncul di search box
   - List reviewer otomatis hidden setelah memilih
   - Tidak ada alert box terpisah untuk menampilkan reviewer terpilih

4. **Easy Re-selection**
   - User dapat mengganti reviewer dengan mengetik ulang di search box
   - Ketika user mulai mengetik, selection sebelumnya otomatis di-reset
   - List muncul kembali dengan hasil filter baru

### Behavior Details:

**Before:**
- List panjang semua reviewer ditampilkan (mengganggu)
- User harus scroll untuk mencari reviewer
- Setelah memilih, ada alert box terpisah

**After:**
- List hidden secara default
- User ketik nama/email → list muncul (filtered)
- Pilih reviewer → nama masuk ke box, list hidden
- Ketik lagi untuk ganti reviewer

### Technical Changes:

**HTML/Blade:**
- Menambahkan `style="display: none;"` pada select element
- Menghapus alert box untuk menampilkan reviewer terpilih
- Menambahkan atribut `data-name` dan `data-email` pada setiap option

**JavaScript:**
- Event listener untuk show/hide list berdasarkan input
- Auto-populate search box dengan nama reviewer yang dipilih
- Reset selection ketika user mulai mengetik ulang
- Validasi untuk mencegah reviewer 1 dan 2 yang sama

### Testing:

✅ List reviewer hidden saat pertama kali load
✅ List muncul ketika user mengetik
✅ Filter search bekerja dengan baik
✅ Nama reviewer masuk ke box setelah dipilih
✅ List hidden setelah memilih
✅ Bisa mengganti reviewer dengan mengetik ulang
✅ Validasi reviewer 1 != reviewer 2 tetap berfungsi

---

**Developer:** GitHub Copilot  
**Date:** 9 Januari 2026
