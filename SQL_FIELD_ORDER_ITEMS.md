# 📋 SQL Code - Tabel Order Items

## Tabel 4.6 Struktur Tabel Order Items

| No | Nama Field | Tipe Data | Panjang | Keterangan | SQL Code |
|----|------------|-----------|---------|------------|----------|
| 1  | **id** | INT | 11 | Primary Key, Auto Increment | **CREATE TABLE:**<br>```sql<br>`id` int(11) NOT NULL<br>```<br><br>**ALTER TABLE (Primary Key):**<br>```sql<br>ALTER TABLE `order_items`<br>  ADD PRIMARY KEY (`id`);<br>```<br><br>**ALTER TABLE (Auto Increment):**<br>```sql<br>ALTER TABLE `order_items`<br>  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `qty`, `price`) VALUES<br>(1, 2, 4, 10, 50000.00);<br>``` |
| 2  | **order_id** | INT | 11 | Foreign Key (Tabel Orders) | **CREATE TABLE:**<br>```sql<br>`order_id` int(11) NOT NULL<br>```<br><br>**ALTER TABLE (Index Foreign Key):**<br>```sql<br>ALTER TABLE `order_items`<br>  ADD KEY `order_id` (`order_id`);<br>```<br><br>**Relasi dengan Tabel Orders:**<br>```sql<br>-- Tabel orders (parent table)<br>CREATE TABLE `orders` (<br>  `id` int(11) NOT NULL,<br>  `order_code` varchar(100) NOT NULL,<br>  `total` decimal(12,2) NOT NULL,<br>  `status` enum('pending','processing','done','cancelled'),<br>  PRIMARY KEY (`id`)<br>);<br>```<br><br>**INSERT dengan order_id:**<br>```sql<br>-- Tambah item ke order yang sudah ada<br>INSERT INTO `order_items` (`order_id`, `product_id`, `qty`, `price`) VALUES<br>(2, 4, 3, 50000.00);<br>```<br><br>**SELECT dengan JOIN:**<br>```sql<br>-- Tampilkan items dengan info order<br>SELECT <br>  o.order_code,<br>  o.status,<br>  oi.qty,<br>  oi.price,<br>  (oi.qty * oi.price) as subtotal<br>FROM order_items oi<br>JOIN orders o ON oi.order_id = o.id<br>WHERE o.order_code = 'ORD-20251114-F3F798';<br>```<br><br>**Query Items per Order:**<br>```sql<br>-- Total items dalam satu order<br>SELECT <br>  order_id,<br>  COUNT(*) as jumlah_jenis_item,<br>  SUM(qty) as total_quantity,<br>  SUM(qty * price) as total_nilai<br>FROM order_items<br>WHERE order_id = 2<br>GROUP BY order_id;<br><br>-- Detail lengkap order dengan items<br>SELECT <br>  o.order_code,<br>  o.table_number,<br>  o.total,<br>  COUNT(oi.id) as jumlah_item,<br>  SUM(oi.qty) as total_qty<br>FROM orders o<br>LEFT JOIN order_items oi ON o.id = oi.order_id<br>WHERE o.id = 2<br>GROUP BY o.id, o.order_code, o.table_number, o.total;<br>```<br><br>**DELETE Items by Order:**<br>```sql<br>-- Hapus semua items dari order tertentu<br>DELETE FROM `order_items` WHERE `order_id` = 2;<br><br>-- Hapus items dari order yang dibatalkan<br>DELETE oi FROM order_items oi<br>JOIN orders o ON oi.order_id = o.id<br>WHERE o.status = 'cancelled' AND o.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);<br>```<br><br>**⚠️ PENTING:** order_id harus valid (ada di tabel orders) |
| 3  | **product_id** | INT | 11 | Foreign Key (Tabel Products) | **CREATE TABLE:**<br>```sql<br>`product_id` int(11) NOT NULL<br>```<br><br>**ALTER TABLE (Index Foreign Key):**<br>```sql<br>ALTER TABLE `order_items`<br>  ADD KEY `product_id` (`product_id`);<br>```<br><br>**Relasi dengan Tabel Products:**<br>```sql<br>-- Tabel products (parent table)<br>CREATE TABLE `products` (<br>  `id` int(11) NOT NULL,<br>  `name` varchar(150) NOT NULL,<br>  `price` decimal(12,2) NOT NULL,<br>  `stock` int(11) DEFAULT 0,<br>  `is_active` tinyint(1) DEFAULT 1,<br>  PRIMARY KEY (`id`)<br>);<br>```<br><br>**INSERT dengan product_id:**<br>```sql<br>-- Tambah item dengan produk tertentu<br>INSERT INTO `order_items` (`order_id`, `product_id`, `qty`, `price`) VALUES<br>(2, 11, 2, 22000.00);  -- product_id 11 = Ayam Geprek<br>```<br><br>**SELECT dengan JOIN Products:**<br>```sql<br>-- Tampilkan items dengan nama produk<br>SELECT <br>  oi.order_id,<br>  p.name as nama_produk,<br>  oi.qty,<br>  oi.price,<br>  (oi.qty * oi.price) as subtotal<br>FROM order_items oi<br>JOIN products p ON oi.product_id = p.id<br>WHERE oi.order_id = 2;<br><br>-- Detail order lengkap dengan produk<br>SELECT <br>  o.order_code,<br>  p.name as produk,<br>  oi.qty,<br>  oi.price,<br>  (oi.qty * oi.price) as subtotal<br>FROM orders o<br>JOIN order_items oi ON o.id = oi.order_id<br>JOIN products p ON oi.product_id = p.id<br>WHERE o.order_code = 'ORD-20251114-F3F798';<br>```<br><br>**Laporan Produk Terlaris:**<br>```sql<br>-- Produk paling banyak dipesan<br>SELECT <br>  p.id,<br>  p.name as nama_produk,<br>  COUNT(oi.id) as jumlah_transaksi,<br>  SUM(oi.qty) as total_terjual,<br>  SUM(oi.qty * oi.price) as total_omzet<br>FROM order_items oi<br>JOIN products p ON oi.product_id = p.id<br>JOIN orders o ON oi.order_id = o.id<br>WHERE o.status = 'done'<br>GROUP BY p.id, p.name<br>ORDER BY total_terjual DESC<br>LIMIT 10;<br>```<br><br>**Validasi Product:**<br>```sql<br>-- Cek apakah produk masih aktif sebelum order<br>SELECT id, name, price, stock, is_active<br>FROM products<br>WHERE id = 11 AND is_active = 1;<br><br>-- Produk yang tidak pernah dipesan<br>SELECT p.id, p.name<br>FROM products p<br>LEFT JOIN order_items oi ON p.id = oi.product_id<br>WHERE oi.id IS NULL AND p.is_active = 1;<br>```<br><br>**⚠️ PENTING:**<br>- product_id harus valid (ada di tabel products)<br>- Sebaiknya produk masih aktif (is_active = 1) saat order dibuat |
| 4  | **qty** | INT | 11 | Jumlah item dipesan | **CREATE TABLE:**<br>```sql<br>`qty` int(11) NOT NULL DEFAULT 1<br>```<br><br>**INSERT Example:**<br>```sql<br>-- Pesan 3 item<br>INSERT INTO `order_items` (`order_id`, `product_id`, `qty`, `price`) VALUES<br>(2, 11, 3, 22000.00);<br><br>-- Default qty = 1 (jika tidak diisi)<br>INSERT INTO `order_items` (`order_id`, `product_id`, `price`) VALUES<br>(2, 11, 22000.00);  -- qty otomatis = 1<br>```<br><br>**UPDATE Quantity:**<br>```sql<br>-- Ubah jumlah item<br>UPDATE `order_items` SET `qty` = 5 WHERE `id` = 1;<br><br>-- Tambah quantity<br>UPDATE `order_items` SET `qty` = `qty` + 2 WHERE `id` = 1;<br><br>-- Kurangi quantity<br>UPDATE `order_items` SET `qty` = `qty` - 1 WHERE `id` = 1 AND `qty` > 1;<br>```<br><br>**SELECT dengan Quantity:**<br>```sql<br>-- Items dengan qty besar (bulk order)<br>SELECT <br>  oi.id,<br>  p.name,<br>  oi.qty,<br>  oi.price,<br>  (oi.qty * oi.price) as subtotal<br>FROM order_items oi<br>JOIN products p ON oi.product_id = p.id<br>WHERE oi.qty >= 5;<br><br>-- Total quantity per order<br>SELECT <br>  order_id,<br>  SUM(qty) as total_items<br>FROM order_items<br>GROUP BY order_id;<br>```<br><br>**Validasi Stock:**<br>```sql<br>-- Cek stock sebelum insert<br>SELECT id, name, stock<br>FROM products<br>WHERE id = 11 AND stock >= 3;  -- Cek apakah stock cukup<br><br>-- Kurangi stock setelah order confirmed<br>UPDATE products p<br>JOIN order_items oi ON p.id = oi.product_id<br>SET p.stock = p.stock - oi.qty<br>WHERE oi.order_id = 2 AND p.stock >= oi.qty;<br>```<br><br>**Statistik Quantity:**<br>```sql<br>-- Rata-rata qty per item<br>SELECT <br>  AVG(qty) as rata_qty,<br>  MIN(qty) as qty_minimum,<br>  MAX(qty) as qty_maksimum<br>FROM order_items;<br><br>-- Distribusi quantity<br>SELECT <br>  CASE <br>    WHEN qty = 1 THEN '1 item'<br>    WHEN qty BETWEEN 2 AND 5 THEN '2-5 items'<br>    WHEN qty BETWEEN 6 AND 10 THEN '6-10 items'<br>    ELSE '10+ items'<br>  END as range_qty,<br>  COUNT(*) as jumlah<br>FROM order_items<br>GROUP BY <br>  CASE <br>    WHEN qty = 1 THEN '1 item'<br>    WHEN qty BETWEEN 2 AND 5 THEN '2-5 items'<br>    WHEN qty BETWEEN 6 AND 10 THEN '6-10 items'<br>    ELSE '10+ items'<br>  END;<br>```<br><br>**⚠️ Validasi:**<br>- qty harus > 0 (minimal 1)<br>- qty tidak boleh melebihi stock yang tersedia<br>- Default value = 1 jika tidak diisi |
| 5  | **price** | DECIMAL | 12,2 | Harga satuan saat transaksi | **CREATE TABLE:**<br>```sql<br>`price` decimal(12,2) NOT NULL<br>```<br><br>**⚠️ PENTING: Kenapa Simpan Harga di order_items?**<br>Harga disimpan saat transaksi untuk **menjaga konsistensi data historis**. Jika harga produk berubah di tabel products, order lama tetap menggunakan harga saat transaksi.<br><br>**INSERT Example:**<br>```sql<br>-- Simpan harga produk saat order dibuat<br>INSERT INTO `order_items` (`order_id`, `product_id`, `qty`, `price`) <br>SELECT 2, 11, 3, price  -- Ambil harga dari tabel products<br>FROM products WHERE id = 11;<br><br>-- Atau insert langsung<br>INSERT INTO `order_items` (`order_id`, `product_id`, `qty`, `price`) VALUES<br>(2, 11, 3, 22000.00);<br>```<br><br>**Copy Price from Products:**<br>```sql<br>-- Ambil harga terbaru dari products saat insert<br>INSERT INTO order_items (order_id, product_id, qty, price)<br>VALUES (2, 11, 3, (SELECT price FROM products WHERE id = 11));<br><br>-- Batch insert dari cart<br>INSERT INTO order_items (order_id, product_id, qty, price)<br>SELECT <br>  2 as order_id,<br>  p.id as product_id,<br>  3 as qty,<br>  p.price<br>FROM products p<br>WHERE p.id IN (11, 12, 13) AND p.is_active = 1;<br>```<br><br>**Calculate Subtotal:**<br>```sql<br>-- Hitung subtotal per item<br>SELECT <br>  id,<br>  order_id,<br>  product_id,<br>  qty,<br>  price,<br>  (qty * price) as subtotal<br>FROM order_items;<br><br>-- Hitung total order<br>SELECT <br>  order_id,<br>  SUM(qty * price) as total_order<br>FROM order_items<br>GROUP BY order_id;<br>```<br><br>**Update Order Total:**<br>```sql<br>-- Update total di tabel orders berdasarkan order_items<br>UPDATE orders o<br>SET o.total = (<br>  SELECT SUM(oi.qty * oi.price)<br>  FROM order_items oi<br>  WHERE oi.order_id = o.id<br>)<br>WHERE o.id = 2;<br><br>-- Atau dengan JOIN<br>UPDATE orders o<br>JOIN (<br>  SELECT order_id, SUM(qty * price) as total<br>  FROM order_items<br>  GROUP BY order_id<br>) oi ON o.id = oi.order_id<br>SET o.total = oi.total;<br>```<br><br>**Compare Price Changes:**<br>```sql<br>-- Bandingkan harga saat transaksi vs harga sekarang<br>SELECT <br>  oi.id,<br>  p.name as produk,<br>  oi.price as harga_saat_transaksi,<br>  p.price as harga_sekarang,<br>  (p.price - oi.price) as selisih,<br>  ROUND(((p.price - oi.price) / oi.price * 100), 2) as persen_perubahan<br>FROM order_items oi<br>JOIN products p ON oi.product_id = p.id<br>WHERE oi.price != p.price;<br>```<br><br>**Price Analytics:**<br>```sql<br>-- Item dengan harga tertinggi yang pernah dijual<br>SELECT <br>  p.name,<br>  MAX(oi.price) as harga_tertinggi,<br>  MIN(oi.price) as harga_terendah,<br>  AVG(oi.price) as harga_rata_rata<br>FROM order_items oi<br>JOIN products p ON oi.product_id = p.id<br>GROUP BY p.id, p.name<br>ORDER BY harga_tertinggi DESC;<br><br>-- Revenue per produk<br>SELECT <br>  p.name as produk,<br>  COUNT(oi.id) as jumlah_transaksi,<br>  SUM(oi.qty) as total_terjual,<br>  SUM(oi.qty * oi.price) as total_revenue<br>FROM order_items oi<br>JOIN products p ON oi.product_id = p.id<br>JOIN orders o ON oi.order_id = o.id<br>WHERE o.status = 'done'<br>GROUP BY p.id, p.name<br>ORDER BY total_revenue DESC;<br>```<br><br>**Discount/Promo Check:**<br>```sql<br>-- Items yang dijual lebih murah dari harga normal<br>SELECT <br>  oi.id,<br>  o.order_code,<br>  p.name,<br>  p.price as harga_normal,<br>  oi.price as harga_jual,<br>  (p.price - oi.price) as diskon<br>FROM order_items oi<br>JOIN orders o ON oi.order_id = o.id<br>JOIN products p ON oi.product_id = p.id<br>WHERE oi.price < p.price;<br>```<br><br>**⚠️ Best Practices:**<br>1. **Selalu copy harga dari products saat membuat order**<br>2. **Jangan update price di order_items** (untuk menjaga data historis)<br>3. **Price di order_items adalah snapshot** harga saat transaksi<br>4. **Jika ada diskon**, simpan harga setelah diskon di field ini |

---

## 📌 SQL Lengkap Tabel Order Items

### **CREATE TABLE**
```sql
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### **ALTER TABLE - Keys & Indexes**
```sql
-- Primary Key
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

-- Index untuk Foreign Keys (performance)
ALTER TABLE `order_items`
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

-- Auto Increment
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
```

### **Foreign Key Constraints (Optional - untuk data integrity)**
```sql
-- Tambah constraint foreign key (opsional)
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_orderitems_order` 
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orderitems_product` 
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- ON DELETE CASCADE: Hapus items jika order dihapus
-- ON DELETE RESTRICT: Tidak bisa hapus produk jika masih ada di order_items
```

---

## 📊 Contoh Data Sample

```sql
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `qty`, `price`) VALUES
(1, 2, 4, 10, 50000.00),   -- Order #2: 10x Roti Bakar @ 50.000
(2, 2, 5, 2, 20000.00),    -- Order #2: 2x Oreo Cookies @ 20.000
(3, 3, 5, 1, 20000.00),    -- Order #3: 1x Oreo Cookies @ 20.000
(12, 23, 11, 1, 22000.00), -- Order #23: 1x Ayam Geprek @ 22.000
(13, 23, 17, 1, 18000.00), -- Order #23: 1x Cappuccino @ 18.000
(16, 25, 5, 1, 20000.00);  -- Order #25: 1x Oreo Cookies @ 20.000
```

---

## 🔍 Query SQL Berguna

### **CRUD Operations**

```sql
-- CREATE (INSERT) - Tambah item ke order
INSERT INTO `order_items` (`order_id`, `product_id`, `qty`, `price`) 
VALUES (2, 11, 3, 22000.00);

-- CREATE - Copy price from products
INSERT INTO order_items (order_id, product_id, qty, price)
SELECT 2, 11, 3, price FROM products WHERE id = 11;

-- READ (SELECT) - Semua items
SELECT * FROM `order_items` ORDER BY `order_id`, `id`;

-- READ - Items dari order tertentu
SELECT * FROM `order_items` WHERE `order_id` = 2;

-- UPDATE - Ubah quantity
UPDATE `order_items` SET `qty` = 5 WHERE `id` = 1;

-- DELETE - Hapus item
DELETE FROM `order_items` WHERE `id` = 1;

-- DELETE - Hapus semua items dari order
DELETE FROM `order_items` WHERE `order_id` = 2;
```

### **Transaction: Create Order with Items**

```sql
-- Transaction lengkap: buat order + items
START TRANSACTION;

-- 1. Insert order
INSERT INTO orders (order_code, table_number, table_id, total, payment_method, status)
VALUES ('ORD-20251114-ABC123', 'MEJA 3', 3, 0, 'qris', 'pending');

SET @order_id = LAST_INSERT_ID();

-- 2. Insert items (copy harga dari products)
INSERT INTO order_items (order_id, product_id, qty, price)
SELECT @order_id, 11, 2, price FROM products WHERE id = 11;

INSERT INTO order_items (order_id, product_id, qty, price)
SELECT @order_id, 14, 3, price FROM products WHERE id = 14;

-- 3. Update total order
UPDATE orders 
SET total = (
  SELECT SUM(qty * price) FROM order_items WHERE order_id = @order_id
)
WHERE id = @order_id;

COMMIT;
```

### **Detail Order dengan Items**

```sql
-- Order lengkap dengan detail items
SELECT 
  o.order_code,
  o.table_number,
  o.status,
  o.payment_method,
  p.name as produk,
  oi.qty,
  oi.price,
  (oi.qty * oi.price) as subtotal,
  o.total as total_order
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN products p ON oi.product_id = p.id
WHERE o.order_code = 'ORD-20251114-F3F798'
ORDER BY oi.id;

-- Ringkasan order
SELECT 
  o.order_code,
  o.table_number,
  COUNT(oi.id) as jumlah_jenis_item,
  SUM(oi.qty) as total_quantity,
  SUM(oi.qty * oi.price) as calculated_total,
  o.total as order_total
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
WHERE o.id = 2
GROUP BY o.id, o.order_code, o.table_number, o.total;
```

### **Product Performance Reports**

```sql
-- Top 10 produk terlaris
SELECT 
  p.id,
  p.name as produk,
  COUNT(DISTINCT oi.order_id) as jumlah_order,
  SUM(oi.qty) as total_terjual,
  SUM(oi.qty * oi.price) as total_omzet,
  AVG(oi.price) as harga_rata_rata
FROM order_items oi
JOIN products p ON oi.product_id = p.id
JOIN orders o ON oi.order_id = o.id
WHERE o.status = 'done'
GROUP BY p.id, p.name
ORDER BY total_terjual DESC
LIMIT 10;

-- Produk yang tidak pernah dipesan
SELECT 
  p.id,
  p.name,
  p.price,
  p.is_active
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
WHERE oi.id IS NULL
ORDER BY p.name;

-- Analisis penjualan per kategori (jika category_id sudah digunakan)
SELECT 
  c.name as kategori,
  COUNT(DISTINCT oi.id) as jumlah_item_terjual,
  SUM(oi.qty) as total_quantity,
  SUM(oi.qty * oi.price) as total_revenue
FROM order_items oi
JOIN products p ON oi.product_id = p.id
LEFT JOIN categories c ON p.category_id = c.id
JOIN orders o ON oi.order_id = o.id
WHERE o.status = 'done'
GROUP BY c.id, c.name
ORDER BY total_revenue DESC;
```

### **Sales Analytics**

```sql
-- Omzet per hari
SELECT 
  DATE(o.created_at) as tanggal,
  COUNT(DISTINCT o.id) as jumlah_order,
  SUM(oi.qty) as total_items_terjual,
  SUM(oi.qty * oi.price) as omzet
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
WHERE o.status = 'done'
GROUP BY DATE(o.created_at)
ORDER BY tanggal DESC;

-- Average items per order
SELECT 
  AVG(items_per_order) as rata_items_per_order,
  AVG(qty_per_order) as rata_qty_per_order,
  AVG(total_per_order) as rata_nilai_per_order
FROM (
  SELECT 
    order_id,
    COUNT(*) as items_per_order,
    SUM(qty) as qty_per_order,
    SUM(qty * price) as total_per_order
  FROM order_items
  GROUP BY order_id
) as order_stats;

-- Best selling combinations (items yang sering dipesan bersamaan)
SELECT 
  oi1.product_id as produk_1,
  p1.name as nama_produk_1,
  oi2.product_id as produk_2,
  p2.name as nama_produk_2,
  COUNT(*) as frekuensi
FROM order_items oi1
JOIN order_items oi2 ON oi1.order_id = oi2.order_id AND oi1.product_id < oi2.product_id
JOIN products p1 ON oi1.product_id = p1.id
JOIN products p2 ON oi2.product_id = p2.id
GROUP BY oi1.product_id, p1.name, oi2.product_id, p2.name
HAVING COUNT(*) >= 2
ORDER BY frekuensi DESC
LIMIT 10;
```

### **Stock Management**

```sql
-- Kurangi stock setelah order confirmed
UPDATE products p
JOIN order_items oi ON p.id = oi.product_id
SET p.stock = p.stock - oi.qty
WHERE oi.order_id = 2 AND p.stock >= oi.qty;

-- Cek produk dengan stock tidak cukup untuk pending orders
SELECT 
  p.id,
  p.name,
  p.stock as stock_available,
  SUM(oi.qty) as stock_needed,
  (p.stock - SUM(oi.qty)) as stock_difference
FROM products p
JOIN order_items oi ON p.id = oi.product_id
JOIN orders o ON oi.order_id = o.id
WHERE o.status IN ('pending', 'processing')
GROUP BY p.id, p.name, p.stock
HAVING stock_difference < 0;

-- Restore stock jika order dibatalkan
UPDATE products p
JOIN order_items oi ON p.id = oi.product_id
JOIN orders o ON oi.order_id = o.id
SET p.stock = p.stock + oi.qty
WHERE o.id = 2 AND o.status = 'cancelled';
```

### **Data Validation & Cleanup**

```sql
-- Cek order items tanpa parent order (data orphan)
SELECT oi.*
FROM order_items oi
LEFT JOIN orders o ON oi.order_id = o.id
WHERE o.id IS NULL;

-- Cek order items dengan produk yang sudah dihapus
SELECT oi.*
FROM order_items oi
LEFT JOIN products p ON oi.product_id = p.id
WHERE p.id IS NULL;

-- Hapus items dari order yang sudah lama cancelled (cleanup)
DELETE oi FROM order_items oi
JOIN orders o ON oi.order_id = o.id
WHERE o.status = 'cancelled' 
  AND o.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Validasi total order vs sum items
SELECT 
  o.id,
  o.order_code,
  o.total as order_total,
  COALESCE(SUM(oi.qty * oi.price), 0) as calculated_total,
  (o.total - COALESCE(SUM(oi.qty * oi.price), 0)) as difference
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id, o.order_code, o.total
HAVING ABS(difference) > 0.01;  -- Toleransi 1 cent untuk rounding
```

---

## 🔗 Relasi Tabel

### **Diagram Relasi**
```
orders (1) ----< (*) order_items (*) >---- (1) products
   |                    |
   id                order_id
                     product_id
```

### **Join Query Example**
```sql
-- Full detail: Order → Items → Products
SELECT 
  o.id as order_id,
  o.order_code,
  o.table_number,
  o.status,
  oi.id as item_id,
  p.id as product_id,
  p.name as product_name,
  oi.qty,
  oi.price,
  (oi.qty * oi.price) as subtotal,
  o.total
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.id
WHERE o.created_at >= CURDATE()
ORDER BY o.created_at DESC, oi.id;
```

---

## 💡 Best Practices

### **1. Insert Items dengan Harga dari Products**
```sql
-- ✅ BENAR: Copy harga saat transaksi
INSERT INTO order_items (order_id, product_id, qty, price)
SELECT 2, 11, 3, price FROM products WHERE id = 11;

-- ❌ SALAH: Hard-code harga
INSERT INTO order_items (order_id, product_id, qty, price)
VALUES (2, 11, 3, 22000.00);  -- Harga bisa berubah!
```

### **2. Validasi Stock Sebelum Insert**
```sql
-- Cek stock dulu
SELECT id, stock FROM products WHERE id = 11 AND stock >= 3;

-- Jika cukup, baru insert
INSERT INTO order_items (order_id, product_id, qty, price)
SELECT 2, 11, 3, price 
FROM products 
WHERE id = 11 AND stock >= 3 AND is_active = 1;
```

### **3. Update Order Total Setelah Modify Items**
```sql
-- Setiap kali tambah/edit/hapus items, update total order
UPDATE orders o
SET o.total = (
  SELECT COALESCE(SUM(oi.qty * oi.price), 0)
  FROM order_items oi
  WHERE oi.order_id = o.id
)
WHERE o.id = 2;
```

### **4. Use Transaction untuk Consistency**
```sql
START TRANSACTION;

-- Insert order
INSERT INTO orders (...) VALUES (...);
SET @order_id = LAST_INSERT_ID();

-- Insert items
INSERT INTO order_items (...) VALUES (...);

-- Update total
UPDATE orders SET total = (...) WHERE id = @order_id;

-- Kurangi stock
UPDATE products ... ;

COMMIT;
```

---

## 📁 File Sumber

**Database:** `/cafe_ordering.sql`  
**Baris:** CREATE TABLE order_items

**File PHP yang menggunakan:**
- `/app/helpers.php` - Insert order items
- `/public/checkout.php` - Create order with items
- `/admin/orders_detail.php` - View order items

---

**Dokumentasi dibuat:** 2026-02-03  
**Versi Aplikasi:** cafe_ordering v1.0  
**Database:** MySQL/MariaDB  
**Engine:** InnoDB  
**Charset:** utf8mb4
