# ⚡ COMMAND CHEAT SHEET

## 🚀 INSTALASI (Copy-Paste Saja!)

### 1. Install Composer Dependencies
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/cafe_ordering
composer install
```

### 2. Setup Database (Copy paste ke phpMyAdmin)
```sql
-- Buat tabel users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert test user (password: pass123)
INSERT INTO users (username, email, password, role) VALUES 
('Test User', 'user@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');
```

---

## 🔗 QUICK ACCESS URLS

### Customer Side:
```
Menu Page:     http://localhost/cafe_ordering/public/menu.php
Login:         http://localhost/cafe_ordering/public/login.php
Register:      http://localhost/cafe_ordering/public/register.php
Cart:          http://localhost/cafe_ordering/public/cart.php
Checkout:      http://localhost/cafe_ordering/public/checkout.php
```

### Admin Side:
```
Login:         http://localhost/cafe_ordering/admin/login.php
Dashboard:     http://localhost/cafe_ordering/admin/dashboard.php
Orders:        http://localhost/cafe_ordering/admin/orders.php
Products:      http://localhost/cafe_ordering/admin/product.php
Tables:        http://localhost/cafe_ordering/admin/tables.php
```

### API Endpoints:
```
Last Order ID: http://localhost/cafe_ordering/admin/api/get_last_order_id.php
Check Orders:  http://localhost/cafe_ordering/admin/api/check_new_orders.php
```

---

## 🧪 ONE-LINER TESTS

### Test 1: Login
```
Open: http://localhost/cafe_ordering/public/login.php
Email: user@gmail.com | Pass: pass123
```

### Test 2: Notification API
```
Open: http://localhost/cafe_ordering/admin/api/check_new_orders.php
Expect: {"success":true,"new_orders":0,...}
```

### Test 3: QR Download
```
Open: http://localhost/cafe_ordering/admin/download_qr.php?id=1
Expect: PNG file downloaded
```

---

## 🐛 TROUBLESHOOTING COMMANDS

### Reset User Password
```sql
-- Password menjadi: pass123
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'user@gmail.com';
```

### Check Tables Exist
```sql
SHOW TABLES LIKE 'users';
SHOW TABLES LIKE 'tables';
SHOW TABLES LIKE 'orders';
```

### Check Composer Install
```bash
ls -la vendor/endroid/
```

### Clear Browser Cache (Chrome)
```
Ctrl + Shift + Delete
→ Pilih "Cached images and files"
→ Clear data
```

---

## 📁 FILES CHECKLIST

### Copy paste di Terminal untuk cek files:
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/cafe_ordering

# Check new files
ls -lh public/login.php
ls -lh public/register.php
ls -lh admin/assets/js/notification.js
ls -lh admin/api/get_last_order_id.php
ls -lh admin/api/check_new_orders.php
ls -lh admin/download_qr.php

# Check vendor
ls -lh vendor/endroid/
```

---

## 🔧 INTEGRATION SNIPPET

### Add to admin/orders.php (before </body>):
```html
<!-- Add this line before </body> tag -->
<script src="assets/js/notification.js"></script>
```

### Add to admin/dashboard.php (before </body>):
```html
<!-- Add this line before </body> tag -->
<script src="assets/js/notification.js"></script>
```

---

## 💾 BACKUP COMMANDS

### Backup Database:
```bash
# Via Terminal
mysqldump -u root cafe_ordering > backup_$(date +%Y%m%d).sql

# Via phpMyAdmin
# 1. Select cafe_ordering
# 2. Click "Export"
# 3. Click "Go"
```

### Backup Files:
```bash
cd /Applications/XAMPP/xamppfiles/htdocs
tar -czf cafe_ordering_backup_$(date +%Y%m%d).tar.gz cafe_ordering/
```

---

## 📊 QUICK STATUS CHECK

### Check XAMPP Status:
```bash
# Check Apache
curl -I http://localhost/

# Check MySQL
mysql -u root -e "SELECT 1"
```

### Check PHP Version:
```bash
php -v
```

### Check Composer:
```bash
composer --version
```

---

## 🎯 ALL-IN-ONE TEST SCRIPT

```bash
#!/bin/bash
echo "🧪 Testing Cafe Ordering System..."

echo "1. Checking files..."
[ -f "public/login.php" ] && echo "✅ login.php" || echo "❌ login.php MISSING"
[ -f "admin/assets/js/notification.js" ] && echo "✅ notification.js" || echo "❌ notification.js MISSING"

echo "2. Checking vendor..."
[ -d "vendor/endroid" ] && echo "✅ endroid/qr-code installed" || echo "❌ Run: composer install"

echo "3. Testing URLs..."
curl -s -o /dev/null -w "%{http_code}" http://localhost/cafe_ordering/public/login.php | grep -q "200" && echo "✅ Login page OK" || echo "❌ Login page ERROR"

echo "✅ Test complete!"
```

**Save as:** `test.sh` dan jalankan dengan: `bash test.sh`

---

## 📞 HELP COMMANDS

### Show PHP Errors:
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/error_log
```

### Show MySQL Errors:
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/mysql_error.log
```

### Restart XAMPP:
```bash
sudo /Applications/XAMPP/xamppfiles/xampp restart
```

---

**Quick Reference:** Keep this file open while testing!
