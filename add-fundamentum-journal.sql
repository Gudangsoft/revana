-- Script untuk menambahkan jurnal FUNDAMENTUM jika belum ada
-- Jalankan di database production

-- 1. Cek dulu apakah sudah ada
SELECT COUNT(*) as jumlah 
FROM journal_masters 
WHERE LOWER(nama_jurnal) LIKE '%fundamentum%';

-- 2. Jika hasil query di atas = 0, jalankan INSERT ini:
INSERT INTO journal_masters (
    nama_jurnal, 
    kode_jurnal, 
    publisher, 
    kategori, 
    jenis_jurnal, 
    accreditation,
    is_active, 
    created_at, 
    updated_at
)
VALUES (
    'FUNDAMENTUM : Jurnal Pengabdian Multidisiplin',
    'FND2024',
    'LPKD APJI',
    'Penelitian',
    'Nasional',
    1, -- accreditation ID (sesuaikan dengan ID akreditasi yang ada)
    1,
    NOW(),
    NOW()
);

-- 3. Verifikasi data sudah masuk
SELECT id, nama_jurnal, kode_jurnal FROM journal_masters 
WHERE LOWER(nama_jurnal) LIKE '%fundamentum%';

-- 4. Setelah jurnal ada, import ulang Excel file
-- Atau jalankan script import manual di bawah

-- 5. Import manual slot (jika perlu):
-- Ganti <journal_id> dengan ID dari query nomor 3
/*
INSERT INTO journal_slots (
    kode_slot, journal_master_id, volume, nomor, bulan, tahun, 
    jumlah_slot, slot_terpakai, is_active, created_by, created_at, updated_at
) VALUES
('SLT2023001', <journal_id>, 1, 1, 'Februari', 2023, 30, 0, 1, 1, NOW(), NOW()),
('SLT2023002', <journal_id>, 1, 2, 'Mei', 2023, 30, 0, 1, 1, NOW(), NOW()),
('SLT2023003', <journal_id>, 1, 3, 'Agustus', 2023, 30, 0, 1, 1, NOW(), NOW()),
('SLT2023004', <journal_id>, 1, 4, 'November', 2023, 30, 0, 1, 1, NOW(), NOW()),
('SLT2024001', <journal_id>, 2, 1, 'Februari', 2024, 27, 0, 1, 1, NOW(), NOW()),
('SLT2024002', <journal_id>, 2, 2, 'Mei', 2024, 23, 0, 1, 1, NOW(), NOW()),
('SLT2024003', <journal_id>, 2, 3, 'Agustus', 2024, 16, 0, 1, 1, NOW(), NOW()),
('SLT2024004', <journal_id>, 2, 4, 'November', 2024, 12, 0, 1, 1, NOW(), NOW());
*/

-- 6. Verifikasi slot berhasil ditambahkan
SELECT js.kode_slot, jm.nama_jurnal, js.volume, js.nomor, js.tahun, js.jumlah_slot
FROM journal_slots js
JOIN journal_masters jm ON js.journal_master_id = jm.id
WHERE LOWER(jm.nama_jurnal) LIKE '%fundamentum%'
ORDER BY js.tahun DESC, js.volume DESC, js.nomor DESC;
