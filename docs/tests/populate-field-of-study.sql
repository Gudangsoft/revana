-- =====================================================
-- Populate Field of Studies (Bidang Ilmu)
-- =====================================================

-- Cek apakah tabel sudah ada data
SELECT * FROM field_of_studies;

-- Jika kosong atau sedikit, insert data bidang ilmu:

INSERT INTO field_of_studies (name, description, is_active, `order`, created_at, updated_at) VALUES
('Pertanian (Agriculture)', 'Ilmu pertanian, perkebunan, kehutanan', 1, 1, NOW(), NOW()),
('Seni (Art)', 'Seni rupa, musik, desain', 1, 2, NOW(), NOW()),
('Ekonomi (Economics)', 'Ekonomi, bisnis, manajemen, akuntansi', 1, 3, NOW(), NOW()),
('Pendidikan (Education)', 'Ilmu pendidikan, pembelajaran', 1, 4, NOW(), NOW()),
('Teknik (Engineering)', 'Teknik sipil, elektro, mesin, informatika', 1, 5, NOW(), NOW()),
('Kesehatan (Health)', 'Kedokteran, keperawatan, farmasi, kesehatan masyarakat', 1, 6, NOW(), NOW()),
('Humaniora (Humanities)', 'Sastra, bahasa, sejarah, filsafat', 1, 7, NOW(), NOW()),
('Agama (Religion)', 'Studi agama, teologi', 1, 8, NOW(), NOW()),
('Sains (Science)', 'Matematika, fisika, kimia, biologi', 1, 9, NOW(), NOW()),
('Sosial (Social)', 'Sosiologi, politik, hukum, psikologi', 1, 10, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    description = VALUES(description),
    is_active = VALUES(is_active),
    `order` = VALUES(`order`),
    updated_at = NOW();

-- Verifikasi data sudah masuk
SELECT id, name, description, is_active FROM field_of_studies ORDER BY `order`;

-- =====================================================
-- Setelah data masuk:
-- 1. Refresh halaman admin create assignment
-- 2. Dropdown "Bidang/Section/Topik" akan muncul dengan pilihan
-- 3. Buat assignment baru dengan pilih bidang ilmu
-- 4. Reviewer akan otomatis dapat field tersebut
-- =====================================================
