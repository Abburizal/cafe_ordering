# 📋 Panduan Setup dan Instalasi - Cafe Ordering System

## 🚀 Quick Start Guide

### 1. Persiapan Database

Pastikan MySQL/MariaDB sudah berjalan di XAMPP.

```bash
# Import database schema
mysql -u root cafe_ordering < cafe_ordering.sql
```

### 2. Setup Data Awal

#### A. Setup Meja
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/cafe_ordering
php admin/scripts/setup_tables.php
```

Atau via SQL:
```bash
mysql -u root cafe_ordering < admin/sql/insert_tables.sql
```

#### B. Setup Produk (Optional)
```bash
php admin/scripts/setup_products.php
```

#### C. Fix Password Security
```bash
php admin/scripts/fix_passwords.php
```

#### D. Setup Kategori (Optional)
```bash
mysql -u root cafe_ordering < admin/sql/add_categories_table.sql
```

#### E. Setup FCM Token Table (untuk notifikasi)
```bash
mysql -u root cafe_ordering < admin/sql/add_fcm_tokens_table.sql
```

### 3. Konfigurasi

Edit file `config/config.php`:
```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'cafe_ordering');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL
define('BASE_URL', 'http://localhost/cafe_ordering/');

// Midtrans (untuk QRIS Payment)
define('MIDTRANS_SERVER_KEY', 'YOUR_SERVER_KEY');
define('MIDTRANS_CLIENT_KEY', 'YOUR_CLIENT_KEY');
define('MIDTRANS_IS_PRODUCTION', false);
```

### 4. Akses Aplikasi

#### Admin Panel
```
URL: http://localhost/cafe_ordering/admin/login.php
Username: admin (sesuai yang di database)
Password: (password yang sudah di-hash)
```

#### Customer Interface
```
URL: http://localhost/cafe_ordering/public/
```

## 📁 Struktur Folder

```
cafe_ordering/
├── admin/                  # Admin Panel
│   ├── api/               # API Endpoints
│   │   ├── cek_pesanan_baru.php
│   │   ├── register_fcm_token.php
│   │   ├── upload_image.php
│   │   └── get_sales_chart.php
│   ├── assets/            # Assets admin
│   │   └── notification.js
│   ├── scripts/           # Setup scripts
│   │   ├── setup_tables.php
│   │   ├── setup_products.php
│   │   └── fix_passwords.php
│   ├── sql/               # SQL migrations
│   ├── categories.php     # Manajemen kategori
│   ├── dashboard.php      # Dashboard admin
│   ├── login.php          # Login admin
│   ├── orders.php         # Daftar pesanan
│   ├── product.php        # Manajemen produk
│   └── ...
├── app/                   # Core Application
│   ├── helpers.php        # Helper functions
│   ├── middleware.php     # Authentication middleware
│   ├── validator.php      # Input validation
│   └── image_handler.php  # Image upload handler
├── config/
│   └── config.php         # Configuration
├── public/                # Customer Interface
│   └── ...
└── vendor/                # Composer dependencies
```

## 🔧 Fitur yang Sudah Diimplementasikan

### ✅ Fase 1: Keamanan & Foundasi
- [x] Password hashing dengan `password_hash()`
- [x] Password verification dengan `password_verify()`
- [x] Input validation & sanitization
- [x] Session management
- [x] Middleware authentication

### ✅ Fase 2: Fitur Core
- [x] Setup data meja
- [x] Setup data produk
- [x] CRUD produk (dengan soft delete)
- [x] Manajemen kategori produk
- [x] Upload image handler dengan resize
- [x] API untuk notifikasi real-time
- [x] Auto-refresh order dashboard

### 📝 File Utility yang Tersedia

#### 1. Validator (`app/validator.php`)
```php
// Contoh penggunaan
require_once 'app/validator.php';

// Sanitize input
$name = Validator::sanitize_string($_POST['name']);

// Validasi email
if (!Validator::validate_email($email)) {
    echo "Email tidak valid";
}

// Validasi password
if (!Validator::validate_password($password, 8)) {
    echo "Password minimal 8 karakter";
}

// Validasi multiple fields
$errors = Validator::validate_inputs([
    'name' => ['required'],
    'email' => ['required', 'email'],
    'phone' => ['required', 'phone']
], $_POST);
```

#### 2. Image Handler (`app/image_handler.php`)
```php
// Contoh penggunaan
require_once 'app/image_handler.php';

$imageHandler = new ImageHandler();

// Upload image
$result = $imageHandler->upload($_FILES['image'], $old_image);
if ($result['success']) {
    $filename = $result['filename'];
}

// Get URL
$url = $imageHandler->get_image_url($filename);
$thumb_url = $imageHandler->get_image_url($filename, true);

// Delete image
$imageHandler->delete_image($filename);
```

## 🎯 Fitur yang Perlu Dikonfigurasi

### 1. Midtrans QRIS Payment
1. Daftar di https://midtrans.com
2. Dapatkan Server Key & Client Key (sandbox untuk testing)
3. Update `config/config.php`

### 2. Firebase Cloud Messaging (FCM)
1. Buat project di Firebase Console
2. Enable Cloud Messaging
3. Dapatkan Server Key
4. Update fungsi `send_admin_notification()` di `app/helpers.php`

## 🐛 Troubleshooting

### Database Connection Error
```bash
# Pastikan MySQL running
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server status

# Cek user & password di config.php
```

### Password Tidak Bisa Login
```bash
# Jalankan script fix password
php admin/scripts/fix_passwords.php
```

### Image Upload Gagal
```bash
# Pastikan folder writable
chmod -R 755 public/assets/images/products/
```

### Notifikasi Tidak Muncul
```bash
# Pastikan tabel admin_tokens ada
mysql -u root cafe_ordering < admin/sql/add_fcm_tokens_table.sql

# Enable notification di browser
```

## 📊 Next Steps

1. **Testing**: Test semua fitur yang sudah diimplementasikan
2. **Kategori**: Assign kategori ke produk yang ada
3. **Laporan**: Implementasi halaman laporan penjualan
4. **Search & Filter**: Tambahkan search di menu customer
5. **Deployment**: Setup production environment

## 📞 Support

Jika ada pertanyaan atau issue:
1. Cek file IMPLEMENTASI_PRIORITAS.md untuk roadmap lengkap
2. Lihat error log di browser console atau PHP error log
3. Pastikan semua script setup sudah dijalankan

---

**Version**: 1.0  
**Last Updated**: 2025-11-05  
**Status**: Development
