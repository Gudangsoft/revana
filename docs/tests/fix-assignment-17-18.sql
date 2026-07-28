-- =====================================================
-- QUICK FIX: Update Assignment ID 17 & 18
-- =====================================================

-- 1. Lihat daftar bidang ilmu yang tersedia
SELECT id, name, description FROM field_of_studies WHERE is_active = 1 ORDER BY `order`;

-- 2. Update Assignment ID 17 & 18
-- GANTI ANGKA 1 dengan ID bidang ilmu yang sesuai!

-- Contoh jika artikel tentang TEKNIK (ID = 1)
UPDATE review_assignments SET field_of_study_id = 1 WHERE id IN (17, 18);

-- Atau jika berbeda per assignment:
-- UPDATE review_assignments SET field_of_study_id = 1 WHERE id = 17;
-- UPDATE review_assignments SET field_of_study_id = 2 WHERE id = 18;

-- 3. Verifikasi hasilnya
SELECT 
    ra.id,
    ra.article_title,
    fos.name as bidang_ilmu
FROM review_assignments ra
LEFT JOIN field_of_studies fos ON ra.field_of_study_id = fos.id
WHERE ra.id IN (17, 18);

-- =====================================================
-- Setelah SQL dijalankan:
-- 1. Refresh halaman reviewer
-- 2. Field "Bidang/Section/Topik" akan OTOMATIS muncul (readonly)
-- =====================================================
