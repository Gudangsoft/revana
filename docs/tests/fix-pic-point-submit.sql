-- Tambahkan konfigurasi point untuk task 'submit' PIC
-- File ini perlu dijalankan di database production

INSERT IGNORE INTO task_point_settings (user_type, task_key, task_label, points, is_active, created_at, updated_at)
VALUES ('pic', 'submit', 'Submit Artikel', 1, 1, NOW(), NOW());

-- Verifikasi data sudah masuk
SELECT * FROM task_point_settings WHERE user_type = 'pic' ORDER BY id;
