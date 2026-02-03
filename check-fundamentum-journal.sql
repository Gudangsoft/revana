-- Query untuk cek apakah jurnal FUNDAMENTUM ada di database
-- Jalankan ini di database production

-- 1. Cek jurnal dengan nama mirip (case insensitive)
SELECT id, nama_jurnal, kode_jurnal, publisher, is_active 
FROM journal_masters 
WHERE LOWER(nama_jurnal) LIKE LOWER('%FUNDAMENTUM%')
   OR LOWER(nama_jurnal) LIKE LOWER('%Pengabdian Multidisiplin%');

-- 2. Jika tidak ada hasil, cek semua jurnal yang mengandung "Pengabdian"
SELECT id, nama_jurnal, kode_jurnal 
FROM journal_masters 
WHERE LOWER(nama_jurnal) LIKE LOWER('%Pengabdian%')
LIMIT 10;

-- 3. Cek slot yang ada untuk FUNDAMENTUM
SELECT js.id, js.kode_slot, jm.nama_jurnal, js.volume, js.nomor, js.tahun, js.bulan, 
       js.jumlah_slot, js.slot_terpakai, js.is_active, js.created_at
FROM journal_slots js
JOIN journal_masters jm ON js.journal_master_id = jm.id
WHERE LOWER(jm.nama_jurnal) LIKE LOWER('%FUNDAMENTUM%')
ORDER BY js.tahun DESC, js.volume DESC, js.nomor DESC;

-- 4. Jika jurnal tidak ada, tambahkan dulu:
-- INSERT INTO journal_masters (nama_jurnal, kode_jurnal, publisher, kategori, jenis_jurnal, is_active, created_at, updated_at)
-- VALUES ('FUNDAMENTUM : Jurnal Pengabdian Multidisiplin', 'FND', 'Publisher Name', 'Penelitian', 'Nasional', 1, NOW(), NOW());

-- 5. Setelah jurnal ada, cek lagi slot terakhir yang di-import
SELECT * FROM journal_slots 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY created_at DESC
LIMIT 20;
