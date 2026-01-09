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

## Fitur Multiple Reviewers & Credentials

### Penambahan Fitur Dynamic Reviewer Assignment

**File yang dimodifikasi:**
- `database/migrations/2026_01_09_134032_add_reviewer_credentials_to_review_assignments.php` (NEW)
- `app/Models/ReviewAssignment.php`
- `app/Http/Controllers/Admin/ReviewAssignmentController.php`
- `resources/views/admin/assignments/create.blade.php`
- `resources/views/admin/assignments/show.blade.php`

**Deskripsi Perubahan:**

1. **Username & Password per Reviewer**
   - Setiap reviewer sekarang memiliki field username dan password sendiri
   - Username/password ditampilkan di bawah nama reviewer
   - Data tersimpan di database dan dapat dilihat di halaman detail assignment

2. **Dynamic Multiple Reviewers (hingga 5 reviewer)**
   - Tombol "+ Tambah Reviewer" untuk menambah reviewer secara dinamis
   - Maksimal 5 reviewer per assignment
   - Setiap reviewer dapat dihapus (kecuali reviewer pertama)
   - Validasi untuk mencegah reviewer duplikat

3. **Database Structure:**
   ```
   reviewer_id (existing - reviewer 1)
   reviewer_1_username (NEW)
   reviewer_1_password (NEW)
   
   reviewer_2_id (existing)
   reviewer_2_username (NEW)
   reviewer_2_password (NEW)
   
   reviewer_3_id (NEW)
   reviewer_3_username (NEW)
   reviewer_3_password (NEW)
   
   reviewer_4_id (NEW)
   reviewer_4_username (NEW)
   reviewer_4_password (NEW)
   
   reviewer_5_id (NEW)
   reviewer_5_username (NEW)
   reviewer_5_password (NEW)
   ```

4. **Model Enhancement:**
   - Added relationships: `reviewer3()`, `reviewer4()`, `reviewer5()`
   - Added method: `getAllReviewers()` untuk mendapatkan semua reviewer dengan credentials

5. **Controller Updates:**
   - Validation untuk semua reviewer fields (1-5)
   - Duplicate reviewer validation
   - Store semua reviewer data ke database
   - Load all reviewers di index dan show methods

6. **View Updates:**
   - Form create: Dynamic add/remove reviewer fields
   - Form create: Username/password input untuk setiap reviewer
   - Show page: Display semua reviewers dengan credentials mereka
   - Proper formatting dengan code tags untuk username/password

### Features:

✅ Tambah reviewer dinamis (tombol +)
✅ Maksimal 5 reviewer per assignment
✅ Username & password untuk setiap reviewer
✅ Hapus reviewer (kecuali reviewer 1)
✅ Validasi no duplicate reviewers
✅ Search functionality untuk setiap reviewer field
✅ Data tersimpan ke database
✅ Display credentials di halaman detail
✅ Migration berhasil dijalankan

### Testing:

- [x] Migration berhasil
- [x] Form create dengan dynamic reviewers
- [x] Username/password fields untuk setiap reviewer
- [x] Validasi duplikat reviewer
- [x] Data tersimpan ke database
- [x] Display di halaman show

---

**Developer:** GitHub Copilot  
**Date:** 9 Januari 2026
