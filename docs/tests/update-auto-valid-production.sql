-- SQL Script untuk auto-validasi production yang sudah ada link publish
-- Update semua submission yang sudah ada link_publish tapi production_valid masih false

UPDATE submissions 
SET production_valid = 1 
WHERE link_publish IS NOT NULL 
AND link_publish != '' 
AND production_valid = 0;

-- Lihat hasil update
SELECT 
    id,
    kode_submit,
    link_publish,
    petugas_production_id,
    production_valid,
    updated_at
FROM submissions 
WHERE link_publish IS NOT NULL 
AND link_publish != '';
