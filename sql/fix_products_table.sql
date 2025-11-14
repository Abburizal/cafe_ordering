-- ============================================================================
-- FIX PRODUCTS TABLE - Menambahkan kolom is_active
-- ============================================================================
-- File: sql/fix_products_table.sql
-- Deskripsi: Script SQL untuk menambahkan kolom is_active ke tabel products
--            yang dibutuhkan oleh admin/product.php untuk fitur soft delete
-- Tanggal: 14 November 2025
-- ============================================================================

USE cafe_ordering;

-- 1. Cek apakah kolom is_active sudah ada (untuk mencegah error jika dijalankan 2x)
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'cafe_ordering'
    AND TABLE_NAME = 'products'
    AND COLUMN_NAME = 'is_active'
);

-- 2. Tambahkan kolom is_active jika belum ada
SET @query = IF(
    @column_exists = 0,
    'ALTER TABLE products ADD COLUMN is_active TINYINT(1) DEFAULT 1 COMMENT "Status aktif produk (1=aktif, 0=arsip)" AFTER image',
    'SELECT "Kolom is_active sudah ada, skip..." as message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Update semua produk yang sudah ada menjadi aktif (jika NULL)
UPDATE products 
SET is_active = 1 
WHERE is_active IS NULL OR is_active NOT IN (0, 1);

-- 4. Tambahkan index untuk performa query (optional tapi direkomendasikan)
SET @index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'cafe_ordering'
    AND TABLE_NAME = 'products'
    AND INDEX_NAME = 'idx_products_active'
);

SET @query = IF(
    @index_exists = 0,
    'CREATE INDEX idx_products_active ON products(is_active)',
    'SELECT "Index idx_products_active sudah ada, skip..." as message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Verifikasi perubahan
SELECT 
    'SUCCESS! Tabel products berhasil diupdate.' as status,
    COUNT(*) as total_products,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_products,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as archived_products
FROM products;

-- 6. Show struktur tabel untuk konfirmasi
SHOW COLUMNS FROM products;

-- ============================================================================
-- CARA MENJALANKAN SCRIPT INI:
-- ============================================================================
-- 
-- Method 1: Via phpMyAdmin
-- 1. Buka phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Pilih database 'cafe_ordering'
-- 3. Klik tab 'SQL'
-- 4. Copy-paste seluruh isi file ini
-- 5. Klik 'Go' / 'Kirim'
--
-- Method 2: Via MySQL Command Line
-- mysql -u root -p cafe_ordering < sql/fix_products_table.sql
--
-- Method 3: Via XAMPP Shell
-- cd /Applications/XAMPP/xamppfiles/htdocs/cafe_ordering
-- /Applications/XAMPP/xamppfiles/bin/mysql -u root cafe_ordering < sql/fix_products_table.sql
--
-- ============================================================================
