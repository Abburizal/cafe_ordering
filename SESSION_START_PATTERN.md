# Pattern Session Start yang Benar untuk PHP

## ❌ **JANGAN LAKUKAN INI:**
```php
<?php
session_start(); // Langsung memanggil tanpa pengecekan
```

**Error yang terjadi:**
```
Notice: session_start(): Ignoring session_start() because a session is already active
```

---

## ✅ **CARA YANG BENAR:**

### **Method 1: Menggunakan `session_status()`** (Recommended)
```php
<?php
// Start session hanya jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

### **Method 2: Menggunakan `session_id()`**
```php
<?php
// Cek apakah session ID sudah ada
if (session_id() === '') {
    session_start();
}
```

### **Method 3: Menggunakan `@` (Tidak Recommended - Hanya suppress error)**
```php
<?php
@session_start(); // Menyembunyikan error, tapi session tetap tidak double
```
**⚠️ Warning:** Method ini hanya menyembunyikan error, tidak menyelesaikan masalah.

---

## 📋 **Penjelasan `session_status()` Return Values:**

| Konstanta | Value | Keterangan |
|-----------|-------|------------|
| `PHP_SESSION_DISABLED` | 0 | Session disabled |
| `PHP_SESSION_NONE` | 1 | Session belum dimulai |
| `PHP_SESSION_ACTIVE` | 2 | Session sudah aktif |

---

## 🎯 **Best Practice dalam Aplikasi:**

### **1. Di File Config Utama** (`config/config.php`)
```php
<?php
// Start session di awal aplikasi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database config
define('DB_HOST', '127.0.0.1');
// ... dst
```

### **2. Di File-file yang Include Config**
```php
<?php
require_once __DIR__ . '/../config/config.php'; // Session sudah aktif

// Tidak perlu session_start() lagi karena config.php sudah handle
// Tapi jika ingin defensive, bisa tambahkan:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

### **3. Di File Standalone** (tidak include config.php)
```php
<?php
// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kode aplikasi
$_SESSION['user_id'] = 123;
```

---

## 🔧 **Perbaikan yang Telah Dilakukan:**

### File yang diperbaiki:
1. ✅ `config/config.php` - Ditambah pengecekan session_status()
2. ✅ `admin/logout.php` - Ditambah pengecekan session_status()
3. ✅ `public/order_status.php` - Ditambah pengecekan session_status()

### File yang sudah benar (tidak perlu perbaikan):
- ✅ `public/cart.php`
- ✅ `public/menu.php`
- ✅ `public/index.php`
- ✅ `public/confirm_payment.php`
- ✅ `public/checkout.php`
- ✅ `public/success.php`
- ✅ `public/scan.php`
- ✅ `public/pay_qris.php`
- ✅ `public/update_cart.php`
- ✅ `app/middleware.php`
- ✅ `app/validator.php`

---

## 🐛 **Debugging Session Issues:**

### Cek status session saat ini:
```php
<?php
echo "Session Status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Data: ";
print_r($_SESSION);
```

### Clear session dan mulai ulang:
```php
<?php
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();
    session_destroy();
}

// Mulai session baru
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

---

## 📝 **Common Patterns di Aplikasi:**

### Pattern 1: File yang require config.php
```php
<?php
require_once __DIR__ . '/../config/config.php'; // Session sudah aktif dari sini
require_once __DIR__ . '/../app/helpers.php';

// Langsung gunakan $_SESSION tanpa session_start() lagi
$cart = $_SESSION['cart'] ?? [];
```

### Pattern 2: File API/AJAX
```php
<?php
// Pastikan session aktif untuk API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
echo json_encode(['user' => $_SESSION['user'] ?? null]);
```

### Pattern 3: Middleware/Guard
```php
<?php
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}
```

---

## ⚡ **Performance Tips:**

1. **Start session sekali** di config.php atau index.php
2. **Gunakan session_write_close()** jika tidak perlu menulis session lagi
3. **Hindari session di API endpoint** yang tidak memerlukan state

```php
<?php
// Baca session
$user = $_SESSION['user'] ?? null;

// Tutup session agar tidak lock
session_write_close();

// Proses panjang tanpa lock session
heavy_database_operation();
```

---

## ✅ **Kesimpulan:**

**Selalu gunakan pattern ini di setiap file PHP:**
```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

Ini adalah cara yang **aman**, **jelas**, dan **tidak akan menimbulkan error** meskipun dipanggil berkali-kali.

---

**Last Updated:** 2025-11-18  
**Status:** ✅ All session issues resolved
