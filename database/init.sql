CREATE TABLE IF NOT EXISTS `health_metrics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `metric_date` DATE NOT NULL,
  `weight_kg` DECIMAL(5,2) NOT NULL,
  `systolic_bp` INT NOT NULL COMMENT 'Huyết áp tâm thu',
  `diastolic_bp` INT NOT NULL COMMENT 'Huyết áp tâm trương',
  `heart_rate` INT NOT NULL COMMENT 'Nhịp tim',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Chèn dữ liệu mẫu nếu bảng trống
INSERT INTO `health_metrics` (metric_date, weight_kg, systolic_bp, diastolic_bp, heart_rate)
SELECT * FROM (
    SELECT '2025-07-20' AS metric_date, 68.5 AS weight_kg, 120 AS systolic_bp, 80 AS diastolic_bp, 72 AS heart_rate UNION ALL
    SELECT '2025-07-21', 68.2, 118, 78, 70 UNION ALL
    SELECT '2025-07-22', 68.3, 122, 81, 75 UNION ALL
    SELECT '2025-07-23', 68.0, 119, 79, 68 UNION ALL
    SELECT '2025-07-24', 68.1, 121, 80, 71
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `health_metrics`);

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) UNIQUE NOT NULL,
  `setting_value` VARCHAR(255) NOT NULL
);

-- Chèn chiều cao mặc định (170cm) nếu chưa có
INSERT INTO settings (setting_key, setting_value)
SELECT * FROM (SELECT 'user_height_cm', '170') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'user_height_cm');
-- Thêm cột user_height_cm vào bảng health_metrics
ALTER TABLE health_metrics ADD COLUMN user_height_cm FLOAT DEFAULT NULL;
