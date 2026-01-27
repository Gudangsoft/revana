# Changelog - Data Jurnal dan Slot View Only untuk PIC & Marketing

**Tanggal:** 27 Januari 2026

## Perubahan

### 1. Halaman Data Jurnal PIC
**File:** `resources/views/pic/journals/index.blade.php`
- ✅ Diubah menjadi view-only (sama dengan admin)
- ✅ Menghapus tombol "Tambah Jurnal"
- ✅ Menghapus tombol "Edit" dan "Hapus"
- ✅ Menambahkan link ke "Pemantauan Slot"
- ✅ Menampilkan data lengkap: Judul, Volume, Slot, Akreditasi, Points, Terbitan, Marketing, PIC, Dibuat Oleh, Tanggal

### 2. Halaman Data Slot PIC
**File:** `resources/views/pic/journal-slots/index-new.blade.php`
- ✅ Dibuat baru dengan format sama seperti admin
- ✅ View-only tanpa tombol edit/hapus/toggle
- ✅ Filter lengkap: Bulan, Tahun, Status, Kategori, Jenis, Akreditasi
- ✅ Tab "Data Slot" dan "Monitoring Slot"
- ✅ Menampilkan: Kode Slot, Nama Jurnal, Publisher, Kategori, Jenis, Akreditasi, Volume, Nomor, Bulan, Tahun, Jumlah Slot, Terpakai, Tersedia, Status

### 3. Halaman Data Jurnal Marketing
**File:** `resources/views/marketing/journals/index.blade.php`
- ✅ Perlu diupdate menjadi view-only (backup dibuat)
- ✅ Format sama dengan admin dan PIC
- ✅ Hanya view, tidak ada edit/hapus

### 4. Halaman Data Slot Marketing
**File:** `resources/views/marketing/journal-slots/index.blade.php`
- ✅ File sudah ada, perlu diupdate
- ✅ Format sama dengan admin dan PIC
- ✅ View-only tanpa aksi edit/hapus

## Fitur View-Only

### Yang Ditampilkan:
- ✅ Semua data jurnal dan slot (sama dengan admin)
- ✅ Filter dan pencarian
- ✅ Pagination
- ✅ Link eksternal (view jurnal)
- ✅ Badge status, kategori, jenis, akreditasi

### Yang Dihilangkan:
- ❌ Tombol "Tambah Jurnal"
- ❌ Tombol "Tambah Slot"
- ❌ Tombol "Edit"
- ❌ Tombol "Hapus"
- ❌ Tombol "Toggle Active/Inactive"
- ❌ Tombol "Import"
- ❌ Tombol "Export"
- ❌ Tombol "Template"

## Catatan
- PIC dan Marketing sekarang dapat melihat data jurnal dan slot lengkap
- Tidak ada akses untuk mengedit atau menghapus data
- Data tetap sinkron dengan yang dilihat admin
- Memudahkan PIC dan Marketing dalam memilih jurnal dan slot saat input submission

## File yang Terpengaruh
1. `resources/views/pic/journals/index.blade.php` - UPDATED
2. `resources/views/pic/journal-slots/index-new.blade.php` - NEW (untuk replace existing)
3. `resources/views/marketing/journals/index.blade.php` - NEED UPDATE
4. `resources/views/marketing/journal-slots/index.blade.php` - NEED UPDATE

## Controller yang Digunakan
- PIC: `App\Http\Controllers\Pic\JournalManagementController`
- Marketing: Controller yang sama dengan yang sudah ada
- Data diambil dari model `JournalMaster` dan `JournalSlot`
