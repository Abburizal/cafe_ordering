# 📋 Dokumentasi Source Code Field Tabel Products

## Tabel 4.4 Struktur Tabel Products

| No | Nama Field | Tipe Data | Panjang | Keterangan | Source Code |
|----|------------|-----------|---------|------------|-------------|
| 1  | **id** | INT | 11 | Primary Key, Auto Increment | **Database Schema** (`cafe_ordering.sql` line 163):<br>```sql<br>`id` int(11) NOT NULL<br>```<br><br>**Insert Product** (`admin/product.php` line 46):<br>```php<br>$stmt = $pdo->prepare("INSERT INTO products (name, price, image, stock, description, is_active) VALUES (?, ?, ?, ?, ?, 1)");<br>// id akan otomatis di-generate<br>```<br><br>**Get Product** (`public/menu.php` line 129):<br>```php<br><input type="hidden" name="product_id" value="<?= e($p['id']) ?>"><br>``` |
| 2  | **name** | VARCHAR | 150 | Nama menu makanan/minuman | **Database Schema** (`cafe_ordering.sql` line 164):<br>```sql<br>`name` varchar(150) NOT NULL<br>```<br><br>**Insert/Update** (`admin/product.php` line 28, 121):<br>```php<br>// Tambah produk<br>$name = trim($_POST['name']);<br>$stmt->execute([$name, $price, $uniqueImageName, $stock, $description]);<br><br>// Edit produk<br>$name = trim($_POST['name']);<br>$stmt->execute([$name, $price, $uniqueImageName, $stock, $description, $id]);<br>```<br><br>**Display** (`public/menu.php` line 123):<br>```php<br><h3 class="font-bold text-xl text-gray-900 mb-1 truncate"><?= e($p['name']) ?></h3><br>``` |
| 3  | **price** | DECIMAL | 12,2 | Harga satuan produk | **Database Schema** (`cafe_ordering.sql` line 165):<br>```sql<br>`price` decimal(12,2) NOT NULL DEFAULT 0.00<br>```<br><br>**Insert/Update** (`admin/product.php` line 29, 122):<br>```php<br>$price = trim($_POST['price']);<br>$stmt->execute([$name, $price, $uniqueImageName, $stock, $description]);<br>```<br><br>**Display** (`public/menu.php` line 126):<br>```php<br><div class="mt-4 text-indigo-600 font-extrabold text-2xl"><?= currency($p['price']) ?></div><br>```<br><br>**Helper Function** (`app/helpers.php`):<br>```php<br>function currency($amount) {<br>    return 'Rp ' . number_format($amount, 0, ',', '.');<br>}<br>``` |
| 4  | **description** | TEXT | - | Deskripsi detail menu | **Database Schema** (`cafe_ordering.sql` line 166):<br>```sql<br>`description` text DEFAULT NULL<br>```<br><br>**Insert/Update** (`admin/product.php` line 33, 124):<br>```php<br>// Tambah produk<br>$description = trim($_POST['description'] ?? NULL);<br>$stmt->execute([$name, $price, $uniqueImageName, $stock, $description]);<br><br>// Edit produk<br>$description = trim($_POST['description'] ?? NULL);<br>$stmt->execute([$name, $price, $uniqueImageName, $stock, $description, $id]);<br>```<br><br>**Display** (`public/menu.php` line 124):<br>```php<br><p class="text-sm text-gray-500 min-h-[3rem] line-clamp-2"><br>  <?= e($p['description'] ?? 'Deskripsi produk belum tersedia.') ?><br></p><br>``` |
| 5  | **category_id** | INT | 11 | Foreign Key (Kategori Menu) | **Database Schema** (`cafe_ordering.sql` line 167):<br>```sql<br>`category_id` int(11) DEFAULT NULL<br>```<br><br>**⚠️ STATUS: Field ini sudah ada di database tetapi BELUM DIIMPLEMENTASIKAN di aplikasi!**<br><br>**Relasi dengan Tabel Categories** (`cafe_ordering.sql` line 48-57):<br>```sql<br>CREATE TABLE `categories` (<br>  `id` int(11) NOT NULL,<br>  `name` varchar(100) NOT NULL,<br>  `description` text DEFAULT NULL,<br>  `icon` varchar(50) DEFAULT NULL,<br>  `display_order` int(11) DEFAULT 0,<br>  `is_active` tinyint(1) DEFAULT 1<br>)<br>```<br><br>**File Management Kategori**: `/admin/categories.php`<br><br>**Contoh Data Categories**:<br>- 1: Makanan 🍽️<br>- 2: Minuman 🥤<br>- 3: Snack 🍟<br>- 4: Dessert 🍰<br>- 5: Coffee ☕<br>- 6: Special ⭐<br><br>**TODO: Implementasi category_id di form product.php** |
| 6  | **stock** | INT | 11 | Jumlah stok tersedia | **Database Schema** (`cafe_ordering.sql` line 168):<br>```sql<br>`stock` int(11) DEFAULT 0<br>```<br><br>**Insert/Update** (`admin/product.php` line 32, 123):<br>```php<br>// Tambah produk<br>$stock = (int)($_POST['stock'] ?? 100);<br>$stmt->execute([$name, $price, $uniqueImageName, $stock, $description]);<br><br>// Edit produk<br>$stock = (int)($_POST['stock'] ?? 0);<br>$stmt->execute([$name, $price, $uniqueImageName, $stock, $description, $id]);<br>```<br><br>**⚠️ CATATAN**: Field stock belum divalidasi saat customer order. Sistem belum mengurangi stok otomatis saat ada pembelian. |
| 7  | **image** | VARCHAR | 255 | Nama file gambar produk | **Database Schema** (`cafe_ordering.sql` line 169):<br>```sql<br>`image` varchar(255) DEFAULT NULL<br>```<br><br>**Upload Image** (`admin/product.php` line 30-44):<br>```php<br>$image = $_FILES['image']['name'] ?? '';<br>$targetDir = "../public/assets/images/";<br><br>// Sanitize filename dan tambah timestamp<br>$sanitizedName = sanitizeFilename($image);<br>$uniqueImageName = time() . '_' . $sanitizedName;<br>$targetFile = $targetDir . $uniqueImageName;<br><br>// Upload file<br>if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {<br>    $stmt = $pdo->prepare("INSERT INTO products (..., image) VALUES (..., ?)");<br>    $stmt->execute([..., $uniqueImageName]);<br>}<br>```<br><br>**Sanitize Function** (`admin/product.php` line 12-24):<br>```php<br>function sanitizeFilename($filename) {<br>    $ext = pathinfo($filename, PATHINFO_EXTENSION);<br>    $name = pathinfo($filename, PATHINFO_FILENAME);<br>    $name = strtolower($name);<br>    $name = preg_replace('/[^a-z0-9_-]/', '_', $name);<br>    return $name . '.' . strtolower($ext);<br>}<br>```<br><br>**Display Image** (`public/menu.php` line 112-119):<br>```php<br>$image_path = "assets/images/" . ($p['image'] ?? '');<br>$image_url = file_exists(__DIR__ . "/" . $image_path) <br>    ? $image_path <br>    : 'https://placehold.co/600x320/eeeeee/333333?text=NO+IMAGE';<br><br><img src="<?= e($image_url) ?>" <br>     alt="<?= e($p['name']) ?>" <br>     class="w-full h-36 sm:h-40 object-cover"><br>```<br><br>**Path Image**: `/public/assets/images/` |
| 8  | **is_active** | TINYINT | 1 | Status aktif menu (0/1) | **Database Schema** (`cafe_ordering.sql` line 170):<br>```sql<br>`is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif produk (1=aktif, 0=arsip)'<br>```<br><br>**Arsip/Nonaktifkan Produk** (`admin/product.php` line 57-75):<br>```php<br>// Soft Delete - Set is_active = 0<br>if (isset($_GET['archive'])) {<br>    $id = (int)$_GET['archive'];<br>    $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");<br>    $stmt->execute([$id]);<br>    $message = "📦 Produk berhasil diarsip";<br>}<br>```<br><br>**Aktifkan Kembali** (`admin/product.php` line 77-83):<br>```php<br>if (isset($_GET['activate'])) {<br>    $id = (int)$_GET['activate'];<br>    $stmt = $pdo->prepare("UPDATE products SET is_active = 1 WHERE id = ?");<br>    $stmt->execute([$id]);<br>    $message = "✅ Produk berhasil diaktifkan kembali!";<br>}<br>```<br><br>**Query Admin** (`admin/product.php` line 165):<br>```php<br>// Ambil semua produk (aktif dan arsip)<br>$products = $pdo->query(<br>    "SELECT * FROM products ORDER BY is_active DESC, id DESC"<br>)->fetchAll(PDO::FETCH_ASSOC);<br>```<br><br>**⚠️ BUG**: Customer masih bisa lihat produk is_active=0!<br>**FIX NEEDED** (`public/menu.php` line 23):<br>```php<br>// Seharusnya:<br>$stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY name");<br>``` |

---

## 📂 Struktur File Terkait

```
cafe_ordering/
├── admin/
│   ├── product.php          ← CRUD produk (INSERT, UPDATE, DELETE)
│   ├── categories.php       ← Manajemen kategori
│   └── sql/
│       └── add_categories_table.sql
├── public/
│   ├── menu.php             ← Tampilan menu customer
│   ├── add_cart.php         ← Tambah ke keranjang
│   ├── cart.php             ← Keranjang belanja
│   └── assets/
│       └── images/          ← Folder upload gambar produk
├── app/
│   └── helpers.php          ← Function currency(), e()
└── cafe_ordering.sql        ← Database schema lengkap
```

---

## ⚠️ Issues & TODO

### 1. **category_id belum digunakan**
- Field ada di database tapi tidak ada di form product.php
- Perlu tambahkan dropdown select category di form

### 2. **is_active tidak terfilter di customer**
- Customer bisa lihat produk yang sudah diarsip (is_active=0)
- **Fix**: Tambahkan `WHERE is_active = 1` di query menu.php

### 3. **Stock tidak berkurang otomatis**
- Saat customer order, stock tidak dikurangi
- Perlu implementasi di checkout.php atau orders.php

### 4. **Validasi stock belum ada**
- Customer bisa order meski stock = 0
- Perlu validasi di add_cart.php

---

## 🔧 Rekomendasi Perbaikan

### Fix 1: Filter is_active di menu customer
```php
// File: public/menu.php line 23
// Dari:
$stmt = $pdo->query("SELECT * FROM products ORDER BY name");

// Menjadi:
$stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY name");
```

### Fix 2: Implementasi category_id di form
```php
// File: admin/product.php - Tambahkan di form
// Ambil semua kategori
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order")->fetchAll();

// Di form HTML:
<select name="category_id" class="input-style">
    <option value="">-- Pilih Kategori --</option>
    <?php foreach($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= $cat['icon'] ?> <?= $cat['name'] ?></option>
    <?php endforeach; ?>
</select>
```

### Fix 3: Validasi dan kurangi stock
```php
// File: public/add_cart.php - Tambahkan validasi
$stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product || $product['stock'] < $qty) {
    header('Location: menu.php?error=out_of_stock');
    exit;
}
```

---

**Dokumentasi dibuat:** 2026-02-03  
**Versi Aplikasi:** cafe_ordering v1.0  
**Database:** MySQL/MariaDB
