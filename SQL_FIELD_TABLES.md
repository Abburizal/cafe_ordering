# 📋 SQL Code - Tabel Tables

## Tabel 4.7 Struktur Tabel Tables

| No | Nama Field | Tipe Data | Panjang | Keterangan | SQL Code |
|----|------------|-----------|---------|------------|----------|
| 1  | **id** | INT | 11 | Primary Key, Auto Increment | **CREATE TABLE:**<br>```sql<br>`id` int(11) NOT NULL<br>```<br><br>**ALTER TABLE (Primary Key):**<br>```sql<br>ALTER TABLE `tables`<br>  ADD PRIMARY KEY (`id`);<br>```<br><br>**ALTER TABLE (Auto Increment):**<br>```sql<br>ALTER TABLE `tables`<br>  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;<br>```<br><br>**INSERT Example:**<br>```sql<br>INSERT INTO `tables` (`id`, `name`, `code`) VALUES<br>(1, 'MEJA 1', 'TBL-001');<br>``` |
| 2  | **name** | VARCHAR | 50 | Nama label meja (Contoh: "Meja 01") | **CREATE TABLE:**<br>```sql<br>`name` varchar(50) NOT NULL<br>```<br><br>**INSERT Example:**<br>```sql<br>-- Meja regular<br>INSERT INTO `tables` (`name`, `code`) VALUES<br>('MEJA 1', 'TBL-001');<br><br>-- Meja VIP<br>INSERT INTO `tables` (`name`, `code`) VALUES<br>('VIP 1', 'TBL-VIP1');<br><br>-- Area khusus<br>INSERT INTO `tables` (`name`, `code`) VALUES<br>('TAKE AWAY', 'TBL-TAKEAWAY'),<br>('OUTDOOR 1', 'TBL-OUT1'),<br>('PRIVATE ROOM', 'TBL-PRIVATE1');<br>```<br><br>**UPDATE Name:**<br>```sql<br>-- Ubah nama meja<br>UPDATE `tables` SET `name` = 'MEJA VIP 1' WHERE `id` = 1;<br><br>-- Ubah format nama (uppercase)<br>UPDATE `tables` SET `name` = UPPER(`name`);<br>```<br><br>**SELECT dengan Filter:**<br>```sql<br>-- Cari meja berdasarkan nama<br>SELECT * FROM `tables` WHERE `name` = 'MEJA 1';<br><br>-- Cari meja dengan LIKE<br>SELECT * FROM `tables` WHERE `name` LIKE '%VIP%';<br><br>-- Semua meja regular (MEJA 1-10)<br>SELECT * FROM `tables` WHERE `name` LIKE 'MEJA %' ORDER BY `id`;<br><br>-- Meja special (VIP, TAKE AWAY, dll)<br>SELECT * FROM `tables` WHERE `name` NOT LIKE 'MEJA %' ORDER BY `name`;<br>```<br><br>**Grouping & Counting:**<br>```sql<br>-- Hitung jumlah meja per kategori<br>SELECT <br>  CASE<br>    WHEN name LIKE 'MEJA %' THEN 'Regular'<br>    WHEN name LIKE 'VIP %' THEN 'VIP'<br>    ELSE 'Special'<br>  END as kategori,<br>  COUNT(*) as jumlah_meja<br>FROM tables<br>GROUP BY <br>  CASE<br>    WHEN name LIKE 'MEJA %' THEN 'Regular'<br>    WHEN name LIKE 'VIP %' THEN 'VIP'<br>    ELSE 'Special'<br>  END;<br>```<br><br>**⚠️ Catatan:**<br>- Field `name` adalah label yang ditampilkan ke user<br>- Format bebas, bisa: "MEJA 1", "Meja 01", "Table A1", dll<br>- Disarankan konsisten dalam penamaan |
| 3  | **code** | VARCHAR | 50 | Kode unik QR meja | **CREATE TABLE:**<br>```sql<br>`code` varchar(50) NOT NULL<br>```<br><br>**ALTER TABLE (Unique Key):**<br>```sql<br>ALTER TABLE `tables`<br>  ADD UNIQUE KEY `code` (`code`);<br>```<br><br>**⚠️ PENTING: Code harus UNIQUE (tidak boleh duplikat)**<br><br>**INSERT Example:**<br>```sql<br>-- Format standar: TBL-XXX<br>INSERT INTO `tables` (`name`, `code`) VALUES<br>('MEJA 1', 'TBL-001'),<br>('MEJA 2', 'TBL-002'),<br>('MEJA 3', 'TBL-003');<br><br>-- Format custom<br>INSERT INTO `tables` (`name`, `code`) VALUES<br>('VIP 1', 'TBL-VIP1'),<br>('VIP 2', 'TBL-VIP2'),<br>('TAKE AWAY', 'TBL-TAKEAWAY');<br><br>-- Format dengan timestamp (untuk generate unik)<br>INSERT INTO `tables` (`name`, `code`) VALUES<br>('MEJA 11', CONCAT('TBL-', UNIX_TIMESTAMP()));<br><br>-- Format dengan UUID<br>INSERT INTO `tables` (`name`, `code`) VALUES<br>('MEJA 12', CONCAT('TBL-', SUBSTRING(UUID(), 1, 8)));<br>```<br><br>**Generate QR Code (Untuk Scan Meja):**<br>```sql<br>-- Get code untuk generate QR<br>SELECT id, name, code FROM `tables` WHERE id = 1;<br>-- QR akan berisi URL: https://cafe.com/menu.php?table=TBL-001<br>```<br><br>**SELECT by Code (Saat Customer Scan QR):**<br>```sql<br>-- Customer scan QR code<br>SELECT * FROM `tables` WHERE `code` = 'TBL-001';<br><br>-- Validasi code<br>SELECT COUNT(*) as exists FROM `tables` WHERE `code` = 'TBL-001';<br>```<br><br>**UPDATE Code:**<br>```sql<br>-- Ganti code meja (regenerate QR)<br>UPDATE `tables` SET `code` = 'TBL-NEW-001' WHERE `id` = 1;<br><br>-- Standardisasi format code<br>UPDATE `tables` <br>SET `code` = CONCAT('TBL-', LPAD(id, 3, '0')) <br>WHERE `code` NOT LIKE 'TBL-%';<br>```<br><br>**Validation Queries:**<br>```sql<br>-- Cek duplikat code (seharusnya tidak ada)<br>SELECT code, COUNT(*) as jumlah<br>FROM tables<br>GROUP BY code<br>HAVING COUNT(*) > 1;<br><br>-- Cek code yang tidak valid (empty/null)<br>SELECT * FROM `tables` WHERE `code` IS NULL OR `code` = '';<br><br>-- Cek code dengan format tidak standar<br>SELECT * FROM `tables` WHERE `code` NOT LIKE 'TBL-%';<br>```<br><br>**Generate Batch Codes:**<br>```sql<br>-- Generate code untuk meja yang belum punya<br>UPDATE tables<br>SET code = CONCAT('TBL-', LPAD(id, 3, '0'))<br>WHERE code IS NULL OR code = '';<br>```<br><br>**Security Note:**<br>```sql<br>-- Code ini sebaiknya random/unpredictable untuk keamanan<br>-- Contoh generate code dengan hash:<br>INSERT INTO `tables` (`name`, `code`) VALUES<br>('MEJA 15', CONCAT('TBL-', SUBSTRING(MD5(RAND()), 1, 10)));<br>```<br><br>**⚠️ Best Practices:**<br>1. **Code harus UNIQUE** - sistem akan error jika ada duplikat<br>2. **Format konsisten** - gunakan format standar (e.g., TBL-001)<br>3. **Jangan mudah ditebak** - untuk keamanan<br>4. **Generate ulang** jika ada yang bocor/disalahgunakan<br>5. **Simpan backup** QR code (PDF/image) |

---

## 📌 SQL Lengkap Tabel Tables

### **CREATE TABLE**
```sql
CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### **ALTER TABLE - Keys & Indexes**
```sql
-- Primary Key
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`);

-- Unique Key untuk code (tidak boleh duplikat)
ALTER TABLE `tables`
  ADD UNIQUE KEY `code` (`code`);

-- Auto Increment
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
```

---

## 📊 Contoh Data Sample

```sql
INSERT INTO `tables` (`id`, `name`, `code`) VALUES
(1, 'MEJA 1', 'TBL-001'),
(2, 'MEJA 2', 'TBL-002'),
(3, 'MEJA 3', 'TBL-003'),
(4, 'MEJA 4', 'TBL-004'),
(5, 'MEJA 5', 'TBL-005'),
(6, 'MEJA 6', 'TBL-006'),
(7, 'MEJA 7', 'TBL-007'),
(8, 'MEJA 8', 'TBL-008'),
(9, 'MEJA 9', 'TBL-009'),
(10, 'MEJA 10', 'TBL-010'),
(11, 'MEJA 11', 'TBL-011'),
(22, 'VIP 1', 'TBL-VIP1'),
(23, 'VIP 2', 'TBL-VIP2'),
(24, 'TAKE AWAY', 'TBL-TAKEAWAY');
```

---

## 🔍 Query SQL Berguna

### **CRUD Operations**

```sql
-- CREATE (INSERT) - Tambah meja baru
INSERT INTO `tables` (`name`, `code`) 
VALUES ('MEJA 12', 'TBL-012');

-- CREATE - Batch insert banyak meja
INSERT INTO `tables` (`name`, `code`) VALUES
('MEJA 13', 'TBL-013'),
('MEJA 14', 'TBL-014'),
('MEJA 15', 'TBL-015');

-- READ (SELECT) - Semua meja
SELECT * FROM `tables` ORDER BY `id`;

-- READ - Meja by ID
SELECT * FROM `tables` WHERE `id` = 1;

-- READ - Meja by Code (untuk scan QR)
SELECT * FROM `tables` WHERE `code` = 'TBL-001';

-- UPDATE - Ubah nama meja
UPDATE `tables` SET `name` = 'MEJA PREMIUM 1' WHERE `id` = 1;

-- UPDATE - Ubah code (regenerate QR)
UPDATE `tables` SET `code` = 'TBL-NEW-001' WHERE `id` = 1;

-- DELETE - Hapus meja
DELETE FROM `tables` WHERE `id` = 12;
```

### **QR Code Management**

```sql
-- Generate QR Code URL untuk semua meja
SELECT 
  id,
  name,
  code,
  CONCAT('https://cafe.example.com/menu.php?table=', code) as qr_url
FROM tables
ORDER BY id;

-- Export untuk print QR (PDF generator)
SELECT 
  id,
  name,
  code,
  CONCAT('Scan untuk pesan di ', name) as qr_description
FROM tables
WHERE id <= 20  -- Hanya meja regular
ORDER BY id;

-- Check QR yang perlu di-regenerate
SELECT * FROM tables
WHERE code IS NULL OR code = '' OR LENGTH(code) < 5;
```

### **Table Status & Analytics**

```sql
-- Meja yang sedang ada order aktif
SELECT 
  t.id,
  t.name,
  t.code,
  COUNT(o.id) as jumlah_order_aktif,
  SUM(o.total) as total_pending
FROM tables t
INNER JOIN orders o ON t.id = o.table_id
WHERE o.status IN ('pending', 'processing')
GROUP BY t.id, t.name, t.code
ORDER BY jumlah_order_aktif DESC;

-- Meja yang tersedia (tidak ada order aktif)
SELECT 
  t.id,
  t.name,
  t.code
FROM tables t
LEFT JOIN orders o ON t.id = o.table_id AND o.status IN ('pending', 'processing')
WHERE o.id IS NULL
ORDER BY t.id;

-- Statistik per meja (all time)
SELECT 
  t.name as meja,
  COUNT(o.id) as total_order,
  SUM(CASE WHEN o.status = 'done' THEN 1 ELSE 0 END) as order_selesai,
  SUM(CASE WHEN o.status = 'done' THEN o.total ELSE 0 END) as total_omzet,
  AVG(CASE WHEN o.status = 'done' THEN o.total ELSE NULL END) as rata_transaksi
FROM tables t
LEFT JOIN orders o ON t.id = o.table_id
GROUP BY t.id, t.name
ORDER BY total_omzet DESC;

-- Top 5 meja terlaris
SELECT 
  t.name as meja,
  COUNT(o.id) as jumlah_order,
  SUM(o.total) as total_omzet
FROM tables t
JOIN orders o ON t.id = o.table_id
WHERE o.status = 'done'
GROUP BY t.id, t.name
ORDER BY total_omzet DESC
LIMIT 5;

-- Meja yang jarang digunakan
SELECT 
  t.id,
  t.name,
  COUNT(o.id) as jumlah_order
FROM tables t
LEFT JOIN orders o ON t.id = o.table_id AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY t.id, t.name
HAVING jumlah_order < 5
ORDER BY jumlah_order ASC;
```

### **Capacity & Layout Management**

```sql
-- Hitung total meja per area
SELECT 
  CASE
    WHEN name LIKE 'MEJA %' THEN 'Area Regular'
    WHEN name LIKE 'VIP %' THEN 'Area VIP'
    WHEN name LIKE 'OUTDOOR %' THEN 'Area Outdoor'
    ELSE 'Area Khusus'
  END as area,
  COUNT(*) as jumlah_meja
FROM tables
GROUP BY 
  CASE
    WHEN name LIKE 'MEJA %' THEN 'Area Regular'
    WHEN name LIKE 'VIP %' THEN 'Area VIP'
    WHEN name LIKE 'OUTDOOR %' THEN 'Area Outdoor'
    ELSE 'Area Khusus'
  END;

-- Meja dengan nomor urut
SELECT 
  id,
  name,
  code,
  ROW_NUMBER() OVER (ORDER BY id) as nomor_urut
FROM tables;
```

### **Maintenance & Validation**

```sql
-- Validasi: Cek duplikat code (TIDAK BOLEH ADA)
SELECT code, COUNT(*) as jumlah
FROM tables
GROUP BY code
HAVING COUNT(*) > 1;

-- Validasi: Cek code empty/null
SELECT * FROM tables 
WHERE code IS NULL OR code = '' OR LENGTH(code) < 3;

-- Cleanup: Hapus meja yang tidak pernah digunakan
DELETE t FROM tables t
LEFT JOIN orders o ON t.id = o.table_id
WHERE o.id IS NULL AND t.created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Standardisasi: Format nama meja
UPDATE tables 
SET name = UPPER(TRIM(name))
WHERE name != UPPER(TRIM(name));

-- Generate code untuk meja yang belum punya
UPDATE tables
SET code = CONCAT('TBL-', LPAD(id, 3, '0'))
WHERE code IS NULL OR code = '';
```

### **Relasi dengan Orders**

```sql
-- Orders terakhir per meja
SELECT 
  t.name as meja,
  o.order_code,
  o.total,
  o.status,
  o.created_at
FROM tables t
LEFT JOIN orders o ON t.id = o.table_id
WHERE o.id IN (
  SELECT MAX(id) FROM orders GROUP BY table_id
)
ORDER BY t.id;

-- Meja dengan order pending terlama
SELECT 
  t.name as meja,
  o.order_code,
  o.status,
  TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as menit_tunggu
FROM tables t
JOIN orders o ON t.id = o.table_id
WHERE o.status = 'pending'
ORDER BY o.created_at ASC;

-- Timeline penggunaan meja hari ini
SELECT 
  t.name as meja,
  o.order_code,
  o.status,
  TIME(o.created_at) as jam_order,
  TIME(o.updated_at) as jam_selesai
FROM tables t
JOIN orders o ON t.id = o.table_id
WHERE DATE(o.created_at) = CURDATE()
ORDER BY o.created_at;
```

---

## 🔗 Relasi Tabel

### **tables → orders (One to Many)**
```sql
-- Satu meja bisa punya banyak orders
SELECT 
  t.id,
  t.name,
  COUNT(o.id) as total_orders
FROM tables t
LEFT JOIN orders o ON t.id = o.table_id
GROUP BY t.id, t.name
ORDER BY total_orders DESC;
```

### **Full Join: Tables → Orders → Order Items → Products**
```sql
-- Detail lengkap: Meja → Orders → Items → Products
SELECT 
  t.name as meja,
  o.order_code,
  o.status,
  p.name as produk,
  oi.qty,
  oi.price,
  (oi.qty * oi.price) as subtotal
FROM tables t
JOIN orders o ON t.id = o.table_id
JOIN order_items oi ON o.id = oi.order_id
JOIN products p ON oi.product_id = p.id
WHERE DATE(o.created_at) = CURDATE()
ORDER BY t.id, o.created_at;
```

---

## 💡 Use Cases & Scenarios

### **1. Setup Awal Cafe**
```sql
-- Generate 20 meja regular
INSERT INTO tables (name, code)
SELECT 
  CONCAT('MEJA ', n) as name,
  CONCAT('TBL-', LPAD(n, 3, '0')) as code
FROM (
  SELECT 1 as n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
  UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
  UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15
  UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20
) numbers;

-- Tambah meja VIP
INSERT INTO tables (name, code) VALUES
('VIP 1', 'TBL-VIP1'),
('VIP 2', 'TBL-VIP2'),
('VIP 3', 'TBL-VIP3');

-- Tambah area khusus
INSERT INTO tables (name, code) VALUES
('TAKE AWAY', 'TBL-TAKEAWAY'),
('DELIVERY', 'TBL-DELIVERY'),
('OUTDOOR 1', 'TBL-OUT1');
```

### **2. Customer Scan QR Code**
```sql
-- 1. Customer scan QR → GET code dari URL
-- 2. Query untuk validasi & redirect
SELECT id, name, code
FROM tables
WHERE code = 'TBL-001';

-- 3. Simpan table info ke session
-- 4. Redirect ke menu.php
```

### **3. Dashboard Admin - Monitor Meja**
```sql
-- Real-time table status
SELECT 
  t.id,
  t.name,
  t.code,
  CASE 
    WHEN COUNT(o.id) > 0 THEN 'BUSY'
    ELSE 'AVAILABLE'
  END as status,
  COUNT(o.id) as active_orders,
  SUM(o.total) as pending_amount
FROM tables t
LEFT JOIN orders o ON t.id = o.table_id AND o.status IN ('pending', 'processing')
GROUP BY t.id, t.name, t.code
ORDER BY t.id;
```

### **4. Generate QR Codes untuk Print**
```sql
-- Export data untuk QR generator
SELECT 
  id,
  name,
  code,
  CONCAT('https://cafe-order.example.com/scan.php?code=', code) as qr_data,
  CONCAT('QR_', code, '.png') as filename
FROM tables
WHERE id BETWEEN 1 AND 20
ORDER BY id;

-- Output ini bisa diproses oleh script PHP/Python untuk generate QR image
```

### **5. Laporan Penggunaan Meja**
```sql
-- Laporan bulanan per meja
SELECT 
  t.name as meja,
  DATE_FORMAT(o.created_at, '%Y-%m') as bulan,
  COUNT(o.id) as jumlah_order,
  SUM(CASE WHEN o.status = 'done' THEN o.total ELSE 0 END) as omzet,
  AVG(CASE WHEN o.status = 'done' THEN o.total ELSE NULL END) as rata_transaksi
FROM tables t
LEFT JOIN orders o ON t.id = o.table_id
WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY t.id, t.name, DATE_FORMAT(o.created_at, '%Y-%m')
ORDER BY bulan DESC, omzet DESC;
```

---

## 🎨 QR Code Integration

### **Generate QR URL**
```php
// PHP Code untuk generate QR
<?php
$stmt = $pdo->query("SELECT id, name, code FROM tables ORDER BY id");
$tables = $stmt->fetchAll();

foreach($tables as $table) {
    $qr_url = "https://cafe-order.example.com/menu.php?table=" . $table['code'];
    
    // Generate QR dengan library (contoh: phpqrcode, endroid/qr-code)
    QRcode::png($qr_url, "qrcodes/{$table['code']}.png", QR_ECLEVEL_L, 10);
    
    echo "QR Code generated: {$table['name']} - {$table['code']}\n";
}
?>
```

### **Scan & Validate QR**
```sql
-- Di file scan.php atau menu.php
-- 1. Ambil code dari URL parameter
-- 2. Validasi dengan query:

SELECT id, name FROM tables WHERE code = ? LIMIT 1;

-- 3. Jika ketemu:
--    - Simpan table_id & table_number ke session
--    - Redirect ke menu.php
-- 4. Jika tidak ketemu:
--    - Redirect ke error page (QR invalid)
```

---

## 📁 File Sumber

**Database:** `/cafe_ordering.sql`  
**Baris:** 205-209 (CREATE TABLE)

**File PHP yang menggunakan:**
- `/admin/tables.php` - CRUD management meja
- `/public/menu.php` - Validasi QR scan
- `/public/index.php` - Redirect scan QR

---

## ⚠️ Important Notes

### **1. Security: Code harus UNIQUE & Unpredictable**
```sql
-- ❌ JANGAN gunakan code yang mudah ditebak
INSERT INTO tables (name, code) VALUES ('MEJA 1', 'TBL-001');  -- Mudah ditebak!

-- ✅ GUNAKAN code random (lebih aman)
INSERT INTO tables (name, code) VALUES 
('MEJA 1', CONCAT('TBL-', SUBSTRING(MD5(RAND()), 1, 12)));
-- Hasil: TBL-a4f7c8d9e2b1
```

### **2. Unique Constraint: Code tidak boleh duplikat**
```sql
-- Query ini akan ERROR karena code sudah ada
INSERT INTO tables (name, code) VALUES ('MEJA 12', 'TBL-001');
-- Error: Duplicate entry 'TBL-001' for key 'code'

-- Solusi: Cek dulu sebelum insert
INSERT INTO tables (name, code)
SELECT 'MEJA 12', 'TBL-012'
WHERE NOT EXISTS (SELECT 1 FROM tables WHERE code = 'TBL-012');
```

### **3. QR Code Best Practices**
- Print QR dalam format yang tahan lama (laminate, acrylic)
- Simpan backup digital semua QR codes
- Regenerate code jika ada yang rusak/hilang
- Test scan QR sebelum deploy ke customer
- Monitor QR yang tidak pernah di-scan (meja tidak terpakai)

---

**Dokumentasi dibuat:** 2026-02-03  
**Versi Aplikasi:** cafe_ordering v1.0  
**Database:** MySQL/MariaDB  
**Engine:** InnoDB  
**Charset:** utf8mb4
