-- ============================================================
-- Migration: Add Validator to Submissions Table
-- File: 2026_04_22_000001_add_validator_to_submissions_table.php
-- Jalankan di phpMyAdmin atau MySQL CLI di server
-- ============================================================

-- Step 1: Add validator columns
ALTER TABLE `submissions`
    ADD COLUMN `petugas_validator_id` bigint(20) UNSIGNED NULL AFTER `production_validated_at`,
    ADD COLUMN `validator_valid` tinyint(1) NOT NULL DEFAULT 0 AFTER `petugas_validator_id`,
    ADD COLUMN `validator_validated_at` timestamp NULL AFTER `validator_valid`,
    ADD COLUMN `catatan_validator` text NULL AFTER `validator_validated_at`;

-- Step 2: Add foreign key for validator
ALTER TABLE `submissions`
    ADD CONSTRAINT `submissions_petugas_validator_id_foreign`
    FOREIGN KEY (`petugas_validator_id`) REFERENCES `pics` (`id`) ON DELETE SET NULL;

-- Step 3: Modify status ENUM to include VALIDATOR_PROCESS
ALTER TABLE `submissions`
    MODIFY COLUMN `status` ENUM(
        'SUBMITTED',
        'EDITOR1_PROCESS',
        'AUTHOR1_PROCESS',
        'EDITOR2_PROCESS',
        'REVIEWER1_PROCESS',
        'REVIEWER2_PROCESS',
        'EDITOR3_PROCESS',
        'AUTHOR2_PROCESS',
        'PRODUCTION_PROCESS',
        'VALIDATOR_PROCESS',
        'PUBLISHED',
        'REJECTED'
    ) NOT NULL DEFAULT 'SUBMITTED';

-- Step 4: Record migration in Laravel migrations table
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_04_22_000001_add_validator_to_submissions_table', (SELECT IFNULL(MAX(batch), 0) + 1 FROM `migrations`)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_04_22_000001_add_validator_to_submissions_table'
);

-- Verify columns were added
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'submissions'
  AND COLUMN_NAME IN ('petugas_validator_id', 'validator_valid', 'validator_validated_at', 'catatan_validator');
