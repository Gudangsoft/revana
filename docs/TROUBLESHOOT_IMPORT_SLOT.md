# Troubleshooting Import Journal Slot - Data Tidak Muncul

## Masalah
Data slot journal yang di-import mendapat notifikasi berhasil, tetapi data tidak muncul di list.

## Kemungkinan Penyebab

### 1. Nama Jurnal Tidak Ditemukan di Database
Import akan **melewati (skip)** data jika nama jurnal tidak ditemukan di `journal_masters`.

**Cek manual di database:**
```sql
-- Cek apakah jurnal ada
SELECT id, nama_jurnal, kode_jurnal, is_active 
FROM journal_masters 
WHERE nama_jurnal LIKE '%FUNDAMENTUM%';

-- Jika tidak ada, tambahkan dulu jurnal ke database
-- Atau sesuaikan nama di Excel dengan nama yang ada di database
```

### 2. Data Sudah Ada (Update, bukan Insert baru)
Jika slot dengan kombinasi `journal_master_id + volume + nomor + tahun` sudah ada, sistem hanya **update** bukan insert baru.

**Cek slot yang sudah ada:**
```sql
SELECT js.*, jm.nama_jurnal
FROM journal_slots js
JOIN journal_masters jm ON js.journal_master_id = jm.id
WHERE jm.nama_jurnal LIKE '%FUNDAMENTUM%'
ORDER BY js.volume DESC, js.nomor DESC;
```

### 3. Nama di Excel Tidak Exact Match
Import menggunakan `LIKE '%nama%'` untuk mencari jurnal. Pastikan nama di Excel ada substring yang cocok dengan nama di database.

**Contoh yang TIDAK akan match:**
- Excel: `FUNDAMENTUM : Jurnal Pengabdian Multidisiplin`
- Database: `Fundamentum Jurnal Pengabdian` ← Tidak ada tanda `:`

## Solusi

### A. Cek Data di Database (Rekomendasi)
Jalankan query berikut untuk melihat apa yang sebenarnya terjadi:

```sql
-- 1. Lihat journal yang ada dengan nama mirip
SELECT * FROM journal_masters 
WHERE nama_jurnal LIKE '%FUNDAMENTUM%' 
   OR nama_jurnal LIKE '%Pengabdian Multidisiplin%';

-- 2. Lihat slot yang baru saja di-import (5 terakhir)
SELECT js.*, jm.nama_jurnal, js.created_at
FROM journal_slots js
JOIN journal_masters jm ON js.journal_master_id = jm.id
ORDER BY js.created_at DESC
LIMIT 5;

-- 3. Lihat total slot per jurnal
SELECT jm.nama_jurnal, COUNT(*) as total_slots
FROM journal_slots js
JOIN journal_masters jm ON js.journal_master_id = jm.id
GROUP BY jm.id, jm.nama_jurnal
HAVING jm.nama_jurnal LIKE '%FUNDAMENTUM%';
```

### B. Perbaikan di Excel
Pastikan format Excel sudah benar:

| nama_jurnal | volume | nomor | bulan | tahun | jumlah_slot | status |
|-------------|--------|-------|-------|-------|-------------|--------|
| FUNDAMENTUM : Jurnal Pengabdian Multidisiplin | 1 | 1 | Januari | 2026 | 10 | Aktif |

**Atau gunakan kode_jurnal (lebih akurat):**

| kode_jurnal | volume | nomor | bulan | tahun | jumlah_slot | status |
|-------------|--------|-------|-------|-------|-------------|--------|
| JRN2024001 | 1 | 1 | Januari | 2026 | 10 | Aktif |

### C. Tambah Jurnal Jika Belum Ada
Jika jurnal belum ada di database, tambahkan dulu:

```sql
INSERT INTO journal_masters (nama_jurnal, kode_jurnal, publisher, is_active, created_at, updated_at)
VALUES ('FUNDAMENTUM : Jurnal Pengabdian Multidisiplin', 'FND2024', 'Publisher Name', 1, NOW(), NOW());
```

### D. Periksa Log Import
Setelah update code, notifikasi import akan memberikan info lebih detail tentang jurnal yang tidak ditemukan.

## File yang Sudah Diupdate

1. **JournalSlotsImport.php** - Menambahkan tracking error dan skipped data
2. **JournalSlotController.php** - Menampilkan detail error saat import

Sekarang saat import, jika ada jurnal yang tidak ditemukan, sistem akan menampilkan:
```
"Tidak ada data yang diimport atau diperbarui.

Jurnal tidak ditemukan:
- Jurnal tidak ditemukan: FUNDAMENTUM : Jurnal Pengabdian Multidisiplin (kode: )
- Jurnal tidak ditemukan: Nama Jurnal Lain (kode: )
... dan 3 error lainnya."
```

## Langkah Debugging

1. **Export dulu data slot yang ada** (untuk backup)
2. **Import ulang file Excel** yang sama
3. **Perhatikan pesan notifikasi** - sekarang akan menampilkan jurnal mana yang tidak ditemukan
4. **Cek nama jurnal di database** menggunakan query SQL di atas
5. **Sesuaikan nama di Excel atau tambah jurnal baru** di database

## Catatan Penting

- Import menggunakan `LIKE '%nama%'` untuk fleksibilitas
- Jika slot sudah ada (volume+nomor+tahun sama), data hanya di-update
- Kolom `is_active` harus `1`, `aktif`, `yes`, atau `ya` (case insensitive)
- Pagination default 20 data per halaman - cek halaman lain jika perlu
