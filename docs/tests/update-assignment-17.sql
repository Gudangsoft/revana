-- ============================================
-- Update Assignment ID 17 dengan Field of Study
-- ============================================

-- 1. Lihat daftar Field of Study yang tersedia
SELECT id, name, description FROM field_of_studies WHERE is_active = 1 ORDER BY `order`;

-- 2. Update Assignment ID 17 (ganti field_of_study_id sesuai bidang yang sesuai)
-- Contoh: Jika artikel tentang Teknik, set ke ID yang sesuai

-- PILIH SALAH SATU:

-- Jika bidang "Teknik" (asumsi ID = 1)
UPDATE review_assignments SET field_of_study_id = 1 WHERE id = 17;

-- Jika bidang "Kedokteran" (asumsi ID = 2)
UPDATE review_assignments SET field_of_study_id = 2 WHERE id = 17;

-- Jika bidang "Ekonomi" (asumsi ID = 3)
UPDATE review_assignments SET field_of_study_id = 3 WHERE id = 17;

-- 3. Verifikasi hasil update
SELECT 
    ra.id,
    ra.article_title,
    ra.status,
    fos.name as field_name
FROM review_assignments ra
LEFT JOIN field_of_studies fos ON ra.field_of_study_id = fos.id
WHERE ra.id = 17;

-- ============================================
-- Atau Update SEMUA assignment yang belum punya field_of_study_id
-- ============================================

-- Lihat assignment mana saja yang belum punya field
SELECT id, article_title, article_number, status 
FROM review_assignments 
WHERE field_of_study_id IS NULL;

-- Set default untuk semua (misalnya ke bidang "Umum" ID=1)
UPDATE review_assignments 
SET field_of_study_id = 1 
WHERE field_of_study_id IS NULL;
