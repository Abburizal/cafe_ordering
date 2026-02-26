# 📋 SQL Code - Tabel Orders

## Tabel 4.5 Struktur Tabel Orders

| No | Nama Field | Tipe Data | Panjang | Keterangan | SQL Code |
|----|------------|-----------|---------|------------|----------|
| 1  | **id** | INT | 11 | Primary Key, Auto Increment | **CREATE TABLE:**<br>```sql<br>`id` int(11) NOT NULL<br>```<br><br>**ALTER TABLE (Primary Key):**<br>```sql<br>ALTER TABLE `orders`<br>  ADD PRIMARY KEY (`id`);<br>```<br><br>**ALTER TABLE (Auto Increment):**<br>```sql<br>ALTER TABLE `orders`<br>  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `orders` (`id`, `order_code`, `table_number`, `table_id`, `total`, `payment_method`, `status`) VALUES<br>(30, 'ORD-20251114-F3F798', 'MEJA 2', 2, 40000.00, 'cash', 'done');<br>``` |
| 2  | **order_code** | VARCHAR | 100 | Kode unik pemesanan | **CREATE TABLE:**<br>```sql<br>`order_code` varchar(100) NOT NULL<br>```<br><br>**ALTER TABLE (Unique Key):**<br>```sql<br>ALTER TABLE `orders`<br>  ADD UNIQUE KEY `order_code` (`order_code`);<br>```<br><br>**Generate Order Code (PHP):**<br>```php<br>// Format: ORD-YYYYMMDD-RANDOM<br>$order_code = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));<br>// Contoh: ORD-20251114-F3F798<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`) VALUES<br>('ORD-20251114-F3F798', 'MEJA 2', 40000.00, 'cash');<br>```<br><br>**SELECT by Order Code:**<br>```sql<br>-- Cari order berdasarkan kode<br>SELECT * FROM `orders` WHERE `order_code` = 'ORD-20251114-F3F798';<br><br>-- Cari order hari ini<br>SELECT * FROM `orders` WHERE `order_code` LIKE 'ORD-20251114-%';<br><br>-- Cari order bulan ini<br>SELECT * FROM `orders` WHERE `order_code` LIKE 'ORD-202511%';<br>```<br><br>**⚠️ PENTING:** order_code harus UNIQUE (tidak boleh duplikat) |
| 3  | **user_id** | INT | 11 | Foreign Key (Pelanggan) | **CREATE TABLE:**<br>```sql<br>`user_id` int(11) DEFAULT NULL<br>```<br><br>**ALTER TABLE (Index Foreign Key):**<br>```sql<br>ALTER TABLE `orders`<br>  ADD KEY `user_id` (`user_id`);<br>```<br><br>**Relasi dengan Tabel Users:**<br>```sql<br>-- Tabel users untuk pelanggan terdaftar<br>CREATE TABLE `users` (<br>  `id` int(11) NOT NULL,<br>  `username` varchar(50) NOT NULL,<br>  `email` varchar(100) NOT NULL,<br>  `phone` varchar(20) DEFAULT NULL<br>);<br>```<br><br>**INSERT dengan user_id:**<br>```sql<br>-- Order dari user terdaftar<br>INSERT INTO `orders` (`order_code`, `user_id`, `table_number`, `total`, `payment_method`) VALUES<br>('ORD-20251114-ABC123', 5, 'MEJA 3', 50000.00, 'qris');<br><br>-- Order tanpa user (guest/walk-in)<br>INSERT INTO `orders` (`order_code`, `user_id`, `table_number`, `total`, `payment_method`) VALUES<br>('ORD-20251114-DEF456', NULL, 'MEJA 4', 30000.00, 'cash');<br>```<br><br>**SELECT dengan JOIN:**<br>```sql<br>-- Tampilkan order dengan nama pelanggan<br>SELECT <br>  o.order_code,<br>  o.total,<br>  o.status,<br>  u.username,<br>  u.email<br>FROM orders o<br>LEFT JOIN users u ON o.user_id = u.id<br>WHERE o.status != 'cancelled'<br>ORDER BY o.created_at DESC;<br>```<br><br>**Statistik per User:**<br>```sql<br>-- Total pembelian per pelanggan<br>SELECT <br>  u.username,<br>  COUNT(o.id) as jumlah_order,<br>  SUM(o.total) as total_belanja<br>FROM users u<br>LEFT JOIN orders o ON u.id = o.user_id<br>GROUP BY u.id, u.username<br>ORDER BY total_belanja DESC;<br>```<br><br>**⚠️ Catatan:** Saat ini kebanyakan order memiliki user_id = NULL (guest order) |
| 4  | **table_number** | VARCHAR | 100 | Nomor meja pemesan | **CREATE TABLE:**<br>```sql<br>`table_number` varchar(100) NOT NULL<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`) VALUES<br>('ORD-20251114-F3F798', 'MEJA 2', 40000.00, 'cash');<br><br>-- Berbagai format<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`) VALUES<br>('ORD-20251114-ABC123', 'MEJA 1', 25000.00, 'qris'),<br>('ORD-20251114-DEF456', 'Meja VIP 5', 100000.00, 'qris'),<br>('ORD-20251114-GHI789', 'TABLE A1', 35000.00, 'cash');<br>```<br><br>**SELECT per Meja:**<br>```sql<br>-- Order dari meja tertentu<br>SELECT * FROM `orders` WHERE `table_number` = 'MEJA 2' ORDER BY `created_at` DESC;<br><br>-- Order meja hari ini<br>SELECT * FROM `orders` <br>WHERE `table_number` = 'MEJA 1' <br>  AND DATE(created_at) = CURDATE();<br>```<br><br>**Statistik per Meja:**<br>```sql<br>-- Meja paling laris<br>SELECT <br>  table_number,<br>  COUNT(*) as jumlah_order,<br>  SUM(total) as total_omzet<br>FROM orders<br>WHERE status = 'done'<br>GROUP BY table_number<br>ORDER BY jumlah_order DESC;<br>```<br><br>**⚠️ Perbedaan dengan table_id:**<br>- `table_number` = Nama/label meja (string bebas, "MEJA 1", "Meja VIP")<br>- `table_id` = ID referensi ke tabel `tables` (integer, foreign key) |
| 5  | **table_id** | INT | 11 | Foreign Key (Data Meja) | **CREATE TABLE:**<br>```sql<br>`table_id` int(11) DEFAULT NULL<br>```<br><br>**ALTER TABLE (Index Foreign Key):**<br>```sql<br>ALTER TABLE `orders`<br>  ADD KEY `table_id` (`table_id`);<br>```<br><br>**Relasi dengan Tabel Tables:**<br>```sql<br>-- Tabel tables untuk master data meja<br>CREATE TABLE `tables` (<br>  `id` int(11) NOT NULL,<br>  `name` varchar(50) NOT NULL,<br>  `code` varchar(50) NOT NULL,<br>  PRIMARY KEY (`id`),<br>  UNIQUE KEY `code` (`code`)<br>);<br><br>-- Contoh data tables<br>INSERT INTO `tables` (`id`, `name`, `code`) VALUES<br>(1, 'MEJA 1', 'TBL-001'),<br>(2, 'MEJA 2', 'TBL-002'),<br>(3, 'VIP ROOM 1', 'VIP-001');<br>```<br><br>**INSERT dengan table_id:**<br>```sql<br>-- Order dengan referensi ke tabel tables<br>INSERT INTO `orders` (`order_code`, `table_number`, `table_id`, `total`, `payment_method`) VALUES<br>('ORD-20251114-ABC123', 'MEJA 1', 1, 40000.00, 'qris');<br>```<br><br>**SELECT dengan JOIN:**<br>```sql<br>-- Tampilkan order dengan detail meja<br>SELECT <br>  o.order_code,<br>  o.table_number,<br>  t.name as nama_meja,<br>  t.code as kode_meja,<br>  o.total,<br>  o.status<br>FROM orders o<br>LEFT JOIN tables t ON o.table_id = t.id<br>WHERE o.created_at >= CURDATE()<br>ORDER BY o.created_at DESC;<br>```<br><br>**Query Meja Aktif:**<br>```sql<br>-- Meja yang sedang ada order aktif<br>SELECT DISTINCT<br>  t.id,<br>  t.name,<br>  COUNT(o.id) as jumlah_order_aktif<br>FROM tables t<br>INNER JOIN orders o ON t.id = o.table_id<br>WHERE o.status IN ('pending', 'processing')<br>GROUP BY t.id, t.name;<br>```<br><br>**⚠️ Catatan:** Field ini bisa NULL jika order tanpa scan QR meja |
| 6  | **total** | DECIMAL | 12,2 | Total tagihan pesanan | **CREATE TABLE:**<br>```sql<br>`total` decimal(12,2) NOT NULL DEFAULT 0.00<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`) VALUES<br>('ORD-20251114-ABC123', 'MEJA 1', 40000.00, 'qris');<br>```<br><br>**UPDATE Total:**<br>```sql<br>-- Update total order<br>UPDATE `orders` SET `total` = 45000.00 WHERE `order_code` = 'ORD-20251114-ABC123';<br><br>-- Hitung total dari order_items (auto calculate)<br>UPDATE orders o<br>SET total = (<br>  SELECT SUM(oi.price * oi.qty)<br>  FROM order_items oi<br>  WHERE oi.order_id = o.id<br>)<br>WHERE o.id = 30;<br>```<br><br>**SELECT dengan Filter Total:**<br>```sql<br>-- Order di atas 50 ribu<br>SELECT * FROM `orders` WHERE `total` >= 50000 AND status = 'done';<br><br>-- Order berdasarkan range harga<br>SELECT * FROM `orders` <br>WHERE `total` BETWEEN 20000 AND 100000<br>ORDER BY `total` DESC;<br>```<br><br>**Statistik Penjualan:**<br>```sql<br>-- Total omzet hari ini<br>SELECT SUM(total) as omzet_hari_ini<br>FROM orders<br>WHERE DATE(created_at) = CURDATE() AND status = 'done';<br><br>-- Total omzet per bulan<br>SELECT <br>  DATE_FORMAT(created_at, '%Y-%m') as bulan,<br>  COUNT(*) as jumlah_order,<br>  SUM(total) as total_omzet<br>FROM orders<br>WHERE status = 'done'<br>GROUP BY DATE_FORMAT(created_at, '%Y-%m')<br>ORDER BY bulan DESC;<br><br>-- Rata-rata nilai transaksi<br>SELECT <br>  AVG(total) as rata_transaksi,<br>  MIN(total) as transaksi_terkecil,<br>  MAX(total) as transaksi_terbesar<br>FROM orders<br>WHERE status = 'done';<br>```<br><br>**Query Order dengan Item:**<br>```sql<br>-- Detail order dengan total dan items<br>SELECT <br>  o.order_code,<br>  o.total,<br>  COUNT(oi.id) as jumlah_item,<br>  SUM(oi.qty) as total_qty<br>FROM orders o<br>LEFT JOIN order_items oi ON o.id = oi.order_id<br>GROUP BY o.id, o.order_code, o.total;<br>``` |
| 7  | **payment_method** | ENUM | - | Metode (Cash/QRIS) | **CREATE TABLE:**<br>```sql<br>`payment_method` enum('cash','qris','qris_mock') NOT NULL<br>```<br><br>**Nilai ENUM:**<br>- `cash` - Pembayaran tunai<br>- `qris` - Pembayaran QRIS (Midtrans)<br>- `qris_mock` - QRIS simulasi (untuk testing)<br><br>**INSERT Example:**<br>```sql<br>-- Pembayaran tunai<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`, `status`) VALUES<br>('ORD-20251114-ABC123', 'MEJA 1', 40000.00, 'cash', 'done');<br><br>-- Pembayaran QRIS<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`, `status`) VALUES<br>('ORD-20251114-DEF456', 'MEJA 2', 35000.00, 'qris', 'pending');<br><br>-- QRIS Mock (testing)<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`, `status`) VALUES<br>('ORD-20251114-GHI789', 'MEJA 3', 25000.00, 'qris_mock', 'done');<br>```<br><br>**UPDATE Payment Method:**<br>```sql<br>-- Ganti metode pembayaran<br>UPDATE `orders` SET `payment_method` = 'qris' WHERE `order_code` = 'ORD-20251114-ABC123';<br>```<br><br>**SELECT per Payment Method:**<br>```sql<br>-- Order dengan metode tunai<br>SELECT * FROM `orders` WHERE `payment_method` = 'cash';<br><br>-- Order dengan QRIS (real)<br>SELECT * FROM `orders` WHERE `payment_method` = 'qris';<br>```<br><br>**Statistik per Metode:**<br>```sql<br>-- Jumlah transaksi per metode pembayaran<br>SELECT <br>  payment_method,<br>  COUNT(*) as jumlah_transaksi,<br>  SUM(total) as total_nilai,<br>  AVG(total) as rata_transaksi<br>FROM orders<br>WHERE status = 'done'<br>GROUP BY payment_method;<br><br>-- Persentase penggunaan metode<br>SELECT <br>  payment_method,<br>  COUNT(*) as jumlah,<br>  ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM orders WHERE status = 'done'), 2) as persentase<br>FROM orders<br>WHERE status = 'done'<br>GROUP BY payment_method;<br>```<br><br>**⚠️ Validasi ENUM:**<br>```sql<br>-- Query ini akan ERROR (nilai tidak valid)<br>INSERT INTO `orders` (..., `payment_method`) VALUES (..., 'paypal'); -- ❌<br><br>-- Harus salah satu: 'cash', 'qris', 'qris_mock'<br>INSERT INTO `orders` (..., `payment_method`) VALUES (..., 'cash'); -- ✅<br>``` |
| 8  | **status** | ENUM | - | Status (Pending/Done/Cancelled) | **CREATE TABLE:**<br>```sql<br>`status` enum('pending','processing','done','cancelled') DEFAULT 'pending'<br>```<br><br>**Nilai ENUM:**<br>- `pending` - Menunggu pembayaran (default)<br>- `processing` - Sedang diproses dapur<br>- `done` - Selesai (pesanan diantar & dibayar)<br>- `cancelled` - Dibatalkan<br><br>**INSERT Example:**<br>```sql<br>-- Status pending (default)<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`) VALUES<br>('ORD-20251114-ABC123', 'MEJA 1', 40000.00, 'qris');<br>-- status otomatis = 'pending'<br><br>-- Langsung set status<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`, `status`) VALUES<br>('ORD-20251114-DEF456', 'MEJA 2', 35000.00, 'cash', 'done');<br>```<br><br>**UPDATE Status (Workflow):**<br>```sql<br>-- 1. Pending → Processing (setelah pembayaran dikonfirmasi)<br>UPDATE `orders` SET `status` = 'processing' WHERE `order_code` = 'ORD-20251114-ABC123';<br><br>-- 2. Processing → Done (pesanan selesai)<br>UPDATE `orders` SET `status` = 'done', `updated_at` = NOW() <br>WHERE `order_code` = 'ORD-20251114-ABC123';<br><br>-- 3. Pending → Cancelled (dibatalkan)<br>UPDATE `orders` SET `status` = 'cancelled', `updated_at` = NOW() <br>WHERE `order_code` = 'ORD-20251114-ABC123';<br>```<br><br>**SELECT per Status:**<br>```sql<br>-- Order yang menunggu pembayaran<br>SELECT * FROM `orders` WHERE `status` = 'pending' ORDER BY `created_at` ASC;<br><br>-- Order yang sedang diproses<br>SELECT * FROM `orders` WHERE `status` = 'processing' ORDER BY `created_at` ASC;<br><br>-- Order yang sudah selesai<br>SELECT * FROM `orders` WHERE `status` = 'done' ORDER BY `created_at` DESC;<br><br>-- Order aktif (belum selesai)<br>SELECT * FROM `orders` WHERE `status` IN ('pending', 'processing');<br>```<br><br>**Statistik per Status:**<br>```sql<br>-- Hitung order per status<br>SELECT <br>  status,<br>  COUNT(*) as jumlah,<br>  SUM(total) as total_nilai<br>FROM orders<br>GROUP BY status;<br><br>-- Success rate (persentase order selesai)<br>SELECT <br>  ROUND(SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as success_rate,<br>  ROUND(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as cancel_rate<br>FROM orders;<br>```<br><br>**Dashboard Admin:**<br>```sql<br>-- Order yang butuh action<br>SELECT <br>  order_code,<br>  table_number,<br>  total,<br>  status,<br>  TIMESTAMPDIFF(MINUTE, created_at, NOW()) as menit_tunggu<br>FROM orders<br>WHERE status IN ('pending', 'processing')<br>ORDER BY created_at ASC;<br>```<br><br>**⚠️ Status Flow:**<br>```<br>pending → processing → done<br>   ↓<br>cancelled<br>``` |
| 9  | **midtrans_id** | VARCHAR | 255 | ID Transaksi Midtrans | **CREATE TABLE:**<br>```sql<br>`midtrans_id` varchar(255) DEFAULT NULL<br>```<br><br>**INSERT dengan Midtrans:**<br>```sql<br>-- Order dengan payment gateway Midtrans<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`, `midtrans_id`) VALUES<br>('ORD-20251114-ABC123', 'MEJA 1', 40000.00, 'qris', 'MT-12345-67890-ABCDEF');<br><br>-- Order tanpa Midtrans (cash)<br>INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`, `midtrans_id`) VALUES<br>('ORD-20251114-DEF456', 'MEJA 2', 35000.00, 'cash', NULL);<br>```<br><br>**UPDATE Midtrans ID:**<br>```sql<br>-- Set midtrans_id setelah create payment<br>UPDATE `orders` <br>SET `midtrans_id` = 'MT-12345-67890-ABCDEF' <br>WHERE `order_code` = 'ORD-20251114-ABC123';<br>```<br><br>**SELECT by Midtrans ID:**<br>```sql<br>-- Cari order berdasarkan transaksi Midtrans<br>SELECT * FROM `orders` WHERE `midtrans_id` = 'MT-12345-67890-ABCDEF';<br><br>-- Order yang menggunakan Midtrans<br>SELECT * FROM `orders` WHERE `midtrans_id` IS NOT NULL;<br><br>-- Order tanpa Midtrans<br>SELECT * FROM `orders` WHERE `midtrans_id` IS NULL;<br>```<br><br>**Callback Handler Query:**<br>```sql<br>-- Update status dari callback Midtrans<br>UPDATE `orders` <br>SET `status` = 'processing', `updated_at` = NOW()<br>WHERE `midtrans_id` = 'MT-12345-67890-ABCDEF' AND `status` = 'pending';<br>```<br><br>**Statistik Midtrans:**<br>```sql<br>-- Transaksi sukses via Midtrans<br>SELECT <br>  COUNT(*) as total_transaksi,<br>  SUM(total) as total_nilai<br>FROM orders<br>WHERE midtrans_id IS NOT NULL AND status = 'done';<br><br>-- Transaksi pending Midtrans (perlu dicek)<br>SELECT <br>  order_code,<br>  midtrans_id,<br>  total,<br>  created_at<br>FROM orders<br>WHERE midtrans_id IS NOT NULL <br>  AND status = 'pending'<br>  AND created_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE);<br>```<br><br>**⚠️ Catatan:**<br>- Field ini hanya terisi jika menggunakan payment gateway Midtrans<br>- Untuk pembayaran cash, nilai NULL<br>- Digunakan untuk tracking dan callback dari Midtrans |

---

## 📌 SQL Lengkap Tabel Orders

### **CREATE TABLE**
```sql
CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `table_number` varchar(100) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','qris','qris_mock') NOT NULL,
  `status` enum('pending','processing','done','cancelled') DEFAULT 'pending',
  `midtrans_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### **ALTER TABLE - Keys & Indexes**
```sql
-- Primary Key
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

-- Unique Key untuk order_code (tidak boleh duplikat)
ALTER TABLE `orders`
  ADD UNIQUE KEY `order_code` (`order_code`);

-- Index untuk Foreign Keys (performance)
ALTER TABLE `orders`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `table_id` (`table_id`);

-- Auto Increment
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
```

---

## 📊 Contoh Data Sample

```sql
INSERT INTO `orders` (`id`, `order_code`, `user_id`, `table_number`, `table_id`, `total`, `payment_method`, `status`, `midtrans_id`, `created_at`, `updated_at`) VALUES
(30, 'ORD-20251114-F3F798', NULL, 'MEJA 2', 2, 40000.00, 'cash', 'done', NULL, '2025-11-14 19:24:58', '2025-11-14 19:37:13'),
(31, 'ORD-20251114-D25B5A', NULL, 'MEJA 2', 2, 40000.00, 'qris', 'done', NULL, '2025-11-14 19:25:33', '2025-11-14 19:37:06'),
(25, 'ORD-20251108-B51178', NULL, 'MEJA 1', 1, 20000.00, 'qris', 'processing', NULL, '2025-11-08 10:14:19', NULL),
(21, 'ORD-20251105-FD4E9B', NULL, 'MEJA 1', NULL, 18000.00, 'qris', 'pending', NULL, '2025-11-05 17:19:59', NULL),
(2, 'ORD-20251017-720B37', NULL, '', NULL, 540000.00, 'qris_mock', 'cancelled', NULL, '2025-10-17 11:15:51', NULL);
```

---

## 🔍 Query SQL Berguna

### **CRUD Operations**

```sql
-- CREATE (INSERT) - Order baru
INSERT INTO `orders` (`order_code`, `table_number`, `table_id`, `total`, `payment_method`, `status`) 
VALUES ('ORD-20251114-ABC123', 'MEJA 3', 3, 50000.00, 'qris', 'pending');

-- READ (SELECT) - Semua order
SELECT * FROM `orders` ORDER BY `created_at` DESC;

-- READ - Order aktif
SELECT * FROM `orders` WHERE `status` IN ('pending', 'processing') ORDER BY `created_at` ASC;

-- UPDATE - Ubah status
UPDATE `orders` 
SET `status` = 'done', `updated_at` = NOW() 
WHERE `order_code` = 'ORD-20251114-ABC123';

-- DELETE - Hard delete (jarang digunakan)
DELETE FROM `orders` WHERE `id` = 30 AND `status` = 'cancelled';

-- CANCEL - Soft delete (batalkan order)
UPDATE `orders` 
SET `status` = 'cancelled', `updated_at` = NOW() 
WHERE `order_code` = 'ORD-20251114-ABC123';
```

### **Status Workflow**

```sql
-- 1. Customer scan QR & checkout → status: pending
INSERT INTO `orders` (`order_code`, `table_number`, `total`, `payment_method`, `status`) 
VALUES ('ORD-20251114-ABC123', 'MEJA 1', 40000.00, 'qris', 'pending');

-- 2. Payment confirmed → status: processing
UPDATE `orders` 
SET `status` = 'processing', `updated_at` = NOW() 
WHERE `order_code` = 'ORD-20251114-ABC123';

-- 3. Order completed → status: done
UPDATE `orders` 
SET `status` = 'done', `updated_at` = NOW() 
WHERE `order_code` = 'ORD-20251114-ABC123';

-- Alternative: Cancel order
UPDATE `orders` 
SET `status` = 'cancelled', `updated_at` = NOW() 
WHERE `order_code` = 'ORD-20251114-ABC123';
```

### **Reports & Analytics**

```sql
-- Dashboard: Omzet hari ini
SELECT 
  COUNT(*) as total_order,
  SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as order_selesai,
  SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as order_pending,
  SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as order_batal,
  SUM(CASE WHEN status = 'done' THEN total ELSE 0 END) as omzet
FROM orders
WHERE DATE(created_at) = CURDATE();

-- Laporan per bulan
SELECT 
  DATE_FORMAT(created_at, '%Y-%m') as bulan,
  COUNT(*) as jumlah_order,
  SUM(total) as omzet,
  AVG(total) as rata_transaksi
FROM orders
WHERE status = 'done'
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY bulan DESC;

-- Top 5 meja terlaris
SELECT 
  table_number,
  COUNT(*) as jumlah_order,
  SUM(total) as total_omzet
FROM orders
WHERE status = 'done'
GROUP BY table_number
ORDER BY jumlah_order DESC
LIMIT 5;

-- Performa payment method
SELECT 
  payment_method,
  COUNT(*) as total_transaksi,
  SUM(total) as total_nilai,
  ROUND(AVG(total), 0) as rata_transaksi
FROM orders
WHERE status = 'done'
GROUP BY payment_method;
```

### **Advanced Queries dengan JOIN**

```sql
-- Order detail dengan items
SELECT 
  o.order_code,
  o.table_number,
  o.total,
  o.status,
  o.payment_method,
  GROUP_CONCAT(CONCAT(p.name, ' x', oi.qty) SEPARATOR ', ') as items
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.id
WHERE o.status = 'processing'
GROUP BY o.id, o.order_code, o.table_number, o.total, o.status, o.payment_method
ORDER BY o.created_at ASC;

-- Order dengan customer info
SELECT 
  o.order_code,
  o.total,
  o.status,
  u.username,
  u.email,
  t.name as nama_meja
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
LEFT JOIN tables t ON o.table_id = t.id
WHERE o.created_at >= CURDATE()
ORDER BY o.created_at DESC;

-- Produk terlaris dari orders
SELECT 
  p.name as nama_produk,
  COUNT(oi.id) as jumlah_terjual,
  SUM(oi.qty) as total_qty,
  SUM(oi.price * oi.qty) as total_nilai
FROM order_items oi
JOIN products p ON oi.product_id = p.id
JOIN orders o ON oi.order_id = o.id
WHERE o.status = 'done'
GROUP BY p.id, p.name
ORDER BY jumlah_terjual DESC
LIMIT 10;
```

### **Monitoring & Maintenance**

```sql
-- Order pending terlalu lama (> 30 menit)
SELECT 
  order_code,
  table_number,
  total,
  payment_method,
  TIMESTAMPDIFF(MINUTE, created_at, NOW()) as menit_tunggu,
  created_at
FROM orders
WHERE status = 'pending' 
  AND created_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
ORDER BY created_at ASC;

-- Cleanup: Auto cancel order pending > 1 jam
UPDATE orders
SET status = 'cancelled', updated_at = NOW()
WHERE status = 'pending' 
  AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR);

-- Check duplikat order_code (seharusnya tidak ada)
SELECT order_code, COUNT(*) as jumlah
FROM orders
GROUP BY order_code
HAVING COUNT(*) > 1;
```

---

## 🔗 Relasi Tabel

### **orders → order_items (One to Many)**
```sql
-- Satu order bisa punya banyak items
SELECT 
  o.order_code,
  COUNT(oi.id) as jumlah_item
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id, o.order_code;
```

### **orders → users (Many to One)**
```sql
-- Banyak order bisa dari satu user
SELECT 
  u.username,
  COUNT(o.id) as total_order
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id, u.username;
```

### **orders → tables (Many to One)**
```sql
-- Banyak order bisa dari satu meja
SELECT 
  t.name,
  COUNT(o.id) as total_order
FROM tables t
LEFT JOIN orders o ON t.id = o.table_id
GROUP BY t.id, t.name;
```

---

## 📁 File Sumber

**Database:** `/cafe_ordering.sql`  
**Baris:** 77-89

**File PHP yang menggunakan:**
- `/app/helpers.php` - Insert & update orders
- `/public/pay_qris.php` - Create order QRIS
- `/public/tunai.php` - Create order tunai
- `/public/confirm_payment.php` - Update status
- `/admin/orders.php` - Admin management
- `/admin/orders_detail.php` - Detail order

---

**Dokumentasi dibuat:** 2026-02-03  
**Versi Aplikasi:** cafe_ordering v1.0  
**Database:** MySQL/MariaDB  
**Engine:** InnoDB  
**Charset:** utf8mb4
