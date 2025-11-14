-- Tambah tabel untuk menyimpan FCM tokens admin
CREATE TABLE IF NOT EXISTS admin_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    device_type VARCHAR(50),
    device_name VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_token_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index untuk performa (cek dulu apakah sudah ada)
SET @exist1 := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'cafe_ordering' AND TABLE_NAME = 'admin_tokens' AND INDEX_NAME = 'idx_token_active');
SET @sqlstmt1 := IF(@exist1 = 0, 'CREATE INDEX idx_token_active ON admin_tokens(is_active)', 'SELECT "Index already exists"');
PREPARE stmt1 FROM @sqlstmt1;
EXECUTE stmt1;

SET @exist2 := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'cafe_ordering' AND TABLE_NAME = 'admin_tokens' AND INDEX_NAME = 'idx_user_active');
SET @sqlstmt2 := IF(@exist2 = 0, 'CREATE INDEX idx_user_active ON admin_tokens(user_id, is_active)', 'SELECT "Index already exists"');
PREPARE stmt2 FROM @sqlstmt2;
EXECUTE stmt2;
