-- Tambah tabel categories untuk kategori produk
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambah kolom category_id ke tabel products jika belum ada
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'cafe_ordering' AND TABLE_NAME = 'products' AND COLUMN_NAME = 'category_id');
SET @sqlstmt := IF(@exist = 0, 'ALTER TABLE products ADD COLUMN category_id INT DEFAULT NULL AFTER description', 'SELECT "Column already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- Insert kategori default
INSERT IGNORE INTO categories (name, description, icon, display_order) VALUES
('Makanan', 'Menu makanan utama', '🍽️', 1),
('Minuman', 'Minuman segar dan hangat', '🥤', 2),
('Snack', 'Cemilan ringan', '🍟', 3),
('Dessert', 'Pencuci mulut manis', '🍰', 4),
('Coffee', 'Kopi dan turunannya', '☕', 5),
('Special', 'Menu spesial / promo', '⭐', 6);
