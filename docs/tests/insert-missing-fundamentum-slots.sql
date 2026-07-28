-- Import Manual Slot FUNDAMENTUM yang Hilang
-- Volume 1-2, Tahun 2023-2024
-- Jalankan di database production

-- 1. Cek ID jurnal FUNDAMENTUM
SELECT @journal_id := id FROM journal_masters 
WHERE LOWER(nama_jurnal) LIKE '%fundamentum%' 
LIMIT 1;

-- 2. Tampilkan ID yang akan digunakan
SELECT @journal_id as journal_id;

-- 3. Insert 8 slot yang hilang (ganti @journal_id dengan ID yang muncul di step 2 jika perlu)
INSERT INTO journal_slots 
(kode_slot, journal_master_id, volume, nomor, bulan, tahun, jumlah_slot, slot_terpakai, is_active, created_by, created_at, updated_at)
VALUES
-- Volume 1, Tahun 2023
('SLT20230101', @journal_id, 1, 1, 'Februari', 2023, 30, 0, 1, 1, NOW(), NOW()),
('SLT20230102', @journal_id, 1, 2, 'Mei', 2023, 30, 0, 1, 1, NOW(), NOW()),
('SLT20230103', @journal_id, 1, 3, 'Agustus', 2023, 30, 0, 1, 1, NOW(), NOW()),
('SLT20230104', @journal_id, 1, 4, 'November', 2023, 30, 0, 1, 1, NOW(), NOW()),
-- Volume 2, Tahun 2024
('SLT20240201', @journal_id, 2, 1, 'Februari', 2024, 27, 0, 1, 1, NOW(), NOW()),
('SLT20240202', @journal_id, 2, 2, 'Mei', 2024, 23, 0, 1, 1, NOW(), NOW()),
('SLT20240203', @journal_id, 2, 3, 'Agustus', 2024, 16, 0, 1, 1, NOW(), NOW()),
('SLT20240204', @journal_id, 2, 4, 'November', 2024, 12, 0, 1, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  jumlah_slot = VALUES(jumlah_slot),
  updated_at = NOW();

-- 4. Verifikasi data berhasil ditambahkan
SELECT js.kode_slot, jm.nama_jurnal, js.volume, js.nomor, js.bulan, js.tahun, js.jumlah_slot, js.created_at
FROM journal_slots js
JOIN journal_masters jm ON js.journal_master_id = jm.id
WHERE jm.id = @journal_id
ORDER BY js.tahun ASC, js.volume ASC, js.nomor ASC;

-- 5. Lihat total slot per volume
SELECT volume, COUNT(*) as total_slots, SUM(jumlah_slot) as total_kapasitas
FROM journal_slots
WHERE journal_master_id = @journal_id
GROUP BY volume
ORDER BY volume;
