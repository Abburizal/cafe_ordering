# 📋 SQL Code - Tabel Products

## Tabel 4.4 Struktur Tabel Products

| No | Nama Field | Tipe Data | Panjang | Keterangan | SQL Code |
|----|------------|-----------|---------|------------|----------|
| 1  | **id** | INT | 11 | Primary Key, Auto Increment | **CREATE TABLE:**<br>```sql<br>`id` int(11) NOT NULL<br>```<br><br>**ALTER TABLE (Primary Key):**<br>```sql<br>ALTER TABLE `products`<br>  ADD PRIMARY KEY (`id`);<br>```<br><br>**ALTER TABLE (Auto Increment):**<br>```sql<br>ALTER TABLE `products`<br>  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `products` (`id`, `name`, `price`, `description`, `category_id`, `stock`, `image`, `is_active`, `created_at`) VALUES<br>(9, 'Nasi Goreng Spesial', 25000.00, 'Nasi goreng dengan telur, ayam, dan sayuran', NULL, 50, 'nasi-goreng.jpg', 1, '2025-11-05 14:00:46');<br>``` |
| 2  | **name** | VARCHAR | 150 | Nama menu makanan/minuman | **CREATE TABLE:**<br>```sql<br>`name` varchar(150) NOT NULL<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `products` (`name`, `price`, `description`, `category_id`, `stock`, `image`, `is_active`) VALUES<br>('Nasi Goreng Spesial', 25000.00, 'Nasi goreng dengan telur, ayam, dan sayuran', NULL, 50, 'nasi-goreng.jpg', 1);<br>```<br><br>**UPDATE Example:**<br>```sql<br>UPDATE `products` SET `name` = 'Nasi Goreng Special' WHERE `id` = 9;<br>```<br><br>**SELECT Example:**<br>```sql<br>SELECT `id`, `name`, `price` FROM `products` WHERE `name` LIKE '%Goreng%';<br>``` |
| 3  | **price** | DECIMAL | 12,2 | Harga satuan produk | **CREATE TABLE:**<br>```sql<br>`price` decimal(12,2) NOT NULL DEFAULT 0.00<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `products` (`name`, `price`) VALUES ('Es Teh Manis', 5000.00);<br>```<br><br>**UPDATE Example:**<br>```sql<br>UPDATE `products` SET `price` = 6000.00 WHERE `id` = 14;<br>```<br><br>**SELECT dengan Filter Harga:**<br>```sql<br>-- Produk di bawah 20 ribu<br>SELECT `name`, `price` FROM `products` WHERE `price` < 20000;<br><br>-- Produk termahal<br>SELECT `name`, `price` FROM `products` ORDER BY `price` DESC LIMIT 1;<br><br>-- Rata-rata harga<br>SELECT AVG(`price`) as rata_harga FROM `products` WHERE `is_active` = 1;<br>``` |
| 4  | **description** | TEXT | - | Deskripsi detail menu | **CREATE TABLE:**<br>```sql<br>`description` text DEFAULT NULL<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `products` (`name`, `price`, `description`) VALUES<br>('Ayam Geprek', 22000.00, 'Ayam crispy dengan sambal geprek level 1-5');<br>```<br><br>**UPDATE Example:**<br>```sql<br>UPDATE `products` SET `description` = 'Ayam crispy geprek pedas level 1-10' WHERE `id` = 11;<br>```<br><br>**SELECT dengan Search:**<br>```sql<br>-- Cari produk berdasarkan deskripsi<br>SELECT `name`, `description` FROM `products` WHERE `description` LIKE '%pedas%';<br><br>-- Produk tanpa deskripsi<br>SELECT `name` FROM `products` WHERE `description` IS NULL OR `description` = '';<br>``` |
| 5  | **category_id** | INT | 11 | Foreign Key (Kategori Menu) | **CREATE TABLE:**<br>```sql<br>`category_id` int(11) DEFAULT NULL<br>```<br><br>**Tabel Categories (Relasi):**<br>```sql<br>CREATE TABLE `categories` (<br>  `id` int(11) NOT NULL,<br>  `name` varchar(100) NOT NULL,<br>  `description` text DEFAULT NULL,<br>  `icon` varchar(50) DEFAULT NULL,<br>  `display_order` int(11) DEFAULT 0,<br>  `is_active` tinyint(1) DEFAULT 1<br>);<br>```<br><br>**INSERT dengan category_id:**<br>```sql<br>-- Tambah produk dengan kategori<br>INSERT INTO `products` (`name`, `price`, `category_id`) VALUES<br>('Nasi Goreng', 25000.00, 1);  -- 1 = Makanan<br>```<br><br>**UPDATE category_id:**<br>```sql<br>-- Set kategori untuk produk yang sudah ada<br>UPDATE `products` SET `category_id` = 2 WHERE `name` LIKE '%Kopi%';  -- 2 = Minuman<br>UPDATE `products` SET `category_id` = 5 WHERE `name` IN ('Kopi Hitam', 'Cappuccino');  -- 5 = Coffee<br>```<br><br>**SELECT dengan JOIN:**<br>```sql<br>-- Tampilkan produk dengan nama kategori<br>SELECT <br>  p.id,<br>  p.name as nama_produk,<br>  p.price,<br>  c.name as kategori,<br>  c.icon<br>FROM products p<br>LEFT JOIN categories c ON p.category_id = c.id<br>WHERE p.is_active = 1<br>ORDER BY c.display_order, p.name;<br>```<br><br>**SELECT per Kategori:**<br>```sql<br>-- Hitung jumlah produk per kategori<br>SELECT <br>  c.name as kategori,<br>  COUNT(p.id) as jumlah_produk<br>FROM categories c<br>LEFT JOIN products p ON c.id = p.category_id<br>GROUP BY c.id, c.name;<br>```<br><br>**⚠️ Catatan:** Saat ini semua data `category_id` = NULL (belum digunakan) |
| 6  | **stock** | INT | 11 | Jumlah stok tersedia | **CREATE TABLE:**<br>```sql<br>`stock` int(11) DEFAULT 0<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `products` (`name`, `price`, `stock`) VALUES<br>('Es Teh Manis', 5000.00, 100);<br>```<br><br>**UPDATE Stock:**<br>```sql<br>-- Tambah stok<br>UPDATE `products` SET `stock` = `stock` + 50 WHERE `id` = 14;<br><br>-- Kurangi stok (saat ada pembelian)<br>UPDATE `products` SET `stock` = `stock` - 1 WHERE `id` = 14 AND `stock` > 0;<br><br>-- Set stok langsung<br>UPDATE `products` SET `stock` = 0 WHERE `id` = 14;<br>```<br><br>**SELECT dengan Filter Stock:**<br>```sql<br>-- Produk yang stoknya habis<br>SELECT `name`, `stock` FROM `products` WHERE `stock` = 0;<br><br>-- Produk dengan stok rendah (< 10)<br>SELECT `name`, `stock` FROM `products` WHERE `stock` < 10 AND `is_active` = 1;<br><br>-- Produk dengan stok terbanyak<br>SELECT `name`, `stock` FROM `products` ORDER BY `stock` DESC LIMIT 10;<br>```<br><br>**Transaction untuk Kurangi Stock (Safe):**<br>```sql<br>START TRANSACTION;<br><br>-- Cek stok sebelum kurangi<br>SELECT `stock` FROM `products` WHERE `id` = 14 FOR UPDATE;<br><br>-- Kurangi stok jika cukup<br>UPDATE `products` SET `stock` = `stock` - 3 WHERE `id` = 14 AND `stock` >= 3;<br><br>COMMIT;<br>``` |
| 7  | **image** | VARCHAR | 255 | Nama file gambar produk | **CREATE TABLE:**<br>```sql<br>`image` varchar(255) DEFAULT NULL<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `products` (`name`, `price`, `image`) VALUES<br>('Nasi Goreng', 25000.00, 'nasi-goreng.jpg');<br><br>-- Dengan timestamp (dari upload PHP)<br>INSERT INTO `products` (`name`, `price`, `image`) VALUES<br>('Cappuccino', 18000.00, '1763149169_cappuccino.png');<br>```<br><br>**UPDATE Image:**<br>```sql<br>-- Ganti gambar<br>UPDATE `products` SET `image` = '1763149954_new-image.jpg' WHERE `id` = 24;<br><br>-- Hapus gambar (set NULL)<br>UPDATE `products` SET `image` = NULL WHERE `id` = 24;<br>```<br><br>**SELECT dengan Filter Image:**<br>```sql<br>-- Produk yang belum ada gambar<br>SELECT `id`, `name` FROM `products` WHERE `image` IS NULL;<br><br>-- Produk dengan gambar tertentu<br>SELECT `id`, `name`, `image` FROM `products` WHERE `image` LIKE '%.jpg';<br>```<br><br>**Path Image:** `/public/assets/images/` |
| 8  | **is_active** | TINYINT | 1 | Status aktif menu (0/1) | **CREATE TABLE:**<br>```sql<br>`is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif produk (1=aktif, 0=arsip)'<br>```<br><br>**ALTER TABLE (Add Index):**<br>```sql<br>ALTER TABLE `products`<br>  ADD KEY `idx_products_active` (`is_active`);<br>```<br><br>**INSERT Example:**<br>```sql<br>-- Produk aktif (default)<br>INSERT INTO `products` (`name`, `price`, `is_active`) VALUES<br>('Es Teh Manis', 5000.00, 1);<br><br>-- Produk non-aktif (arsip)<br>INSERT INTO `products` (`name`, `price`, `is_active`) VALUES<br>('Menu Lama', 10000.00, 0);<br>```<br><br>**UPDATE Status (Soft Delete):**<br>```sql<br>-- Arsipkan produk (soft delete)<br>UPDATE `products` SET `is_active` = 0 WHERE `id` = 14;<br><br>-- Aktifkan kembali<br>UPDATE `products` SET `is_active` = 1 WHERE `id` = 14;<br><br>-- Arsipkan banyak produk sekaligus<br>UPDATE `products` SET `is_active` = 0 WHERE `price` > 1000000;<br>```<br><br>**SELECT dengan Filter is_active:**<br>```sql<br>-- Hanya produk aktif (untuk customer)<br>SELECT * FROM `products` WHERE `is_active` = 1 ORDER BY `name`;<br><br>-- Hanya produk yang diarsip<br>SELECT * FROM `products` WHERE `is_active` = 0 ORDER BY `name`;<br><br>-- Semua produk (untuk admin)<br>SELECT * FROM `products` ORDER BY `is_active` DESC, `id` DESC;<br>```<br><br>**Count Status:**<br>```sql<br>-- Hitung jumlah produk aktif dan arsip<br>SELECT <br>  SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as aktif,<br>  SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as arsip,<br>  COUNT(*) as total<br>FROM `products`;<br>``` |

---

## 📌 SQL Lengkap Tabel Products

### **CREATE TABLE**
```sql
CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif produk (1=aktif, 0=arsip)',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### **ALTER TABLE - Primary Key & Index**
```sql
-- Primary Key
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

-- Index untuk is_active (performance)
ALTER TABLE `products`
  ADD KEY `idx_products_active` (`is_active`);

-- Auto Increment
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
```

---

## 📊 Contoh Data Sample

```sql
INSERT INTO `products` (`id`, `name`, `price`, `description`, `category_id`, `stock`, `image`, `is_active`, `created_at`) VALUES
(9, 'Nasi Goreng Spesial', 25000.00, 'Nasi goreng dengan telur, ayam, dan sayuran', NULL, 50, 'nasi-goreng.jpg', 1, '2025-11-05 14:00:46'),
(10, 'Mie Goreng', 20000.00, 'Mie goreng pedas dengan topping ayam', NULL, 40, 'mie-goreng.jpg', 1, '2025-11-05 14:00:46'),
(11, 'Ayam Geprek', 22000.00, 'Ayam crispy dengan sambal geprek level 1-5', NULL, 30, 'ayam-geprek.jpg', 1, '2025-11-05 14:00:46'),
(14, 'Es Teh Manis', 5000.00, 'Teh manis dingin segar', NULL, 100, 'es-teh.jpg', 1, '2025-11-05 14:00:46'),
(16, 'Kopi Hitam', 10000.00, 'Kopi hitam premium', NULL, 60, 'kopi-hitam.jpg', 1, '2025-11-05 14:00:46'),
(17, 'Cappuccino', 18000.00, 'Kopi cappuccino dengan foam lembut', NULL, 50, 'cappuccino.jpg', 1, '2025-11-05 14:00:46');
```

---

## 🔍 Query SQL Berguna

### **CRUD Operations**

```sql
-- CREATE (INSERT)
INSERT INTO `products` (`name`, `price`, `description`, `stock`, `image`, `is_active`) 
VALUES ('Nasi Goreng', 25000.00, 'Nasi goreng spesial', 50, 'nasi-goreng.jpg', 1);

-- READ (SELECT)
SELECT * FROM `products` WHERE `is_active` = 1;

-- UPDATE
UPDATE `products` 
SET `name` = 'Nasi Goreng Special', `price` = 27000.00 
WHERE `id` = 9;

-- DELETE (Hard Delete)
DELETE FROM `products` WHERE `id` = 9;

-- SOFT DELETE (Arsip)
UPDATE `products` SET `is_active` = 0 WHERE `id` = 9;
```

### **Advanced Queries**

```sql
-- Produk terlaris (perlu join dengan order_items)
SELECT 
  p.name,
  COUNT(oi.id) as jumlah_terjual
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id, p.name
ORDER BY jumlah_terjual DESC
LIMIT 10;

-- Total nilai inventory
SELECT 
  SUM(price * stock) as total_nilai_stock
FROM products 
WHERE is_active = 1;

-- Produk per kategori dengan total
SELECT 
  c.name as kategori,
  COUNT(p.id) as jumlah_produk,
  AVG(p.price) as harga_rata2
FROM categories c
LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
GROUP BY c.id, c.name
ORDER BY c.display_order;
```

---

## 📁 File Sumber

**Database:** `/cafe_ordering.sql`  
**Baris:** 162-197

**Dibuat:** 2026-02-03  
**Database:** cafe_ordering  
**Engine:** InnoDB  
**Charset:** utf8mb4
