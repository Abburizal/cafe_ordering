# 🚨 FIX: MySQL Not Running - SOLUSI CEPAT

## ❌ MASALAH: phpMyAdmin "No such file or directory"

Error ini artinya: **MySQL belum jalan / belum start!**

---

## ✅ SOLUSI 1: Start MySQL via XAMPP Control Panel (RECOMMENDED)

### Mac:
```
1. Buka Finder
2. Go to Applications → XAMPP
3. Double-click "XAMPP Control.app" atau "manager-osx"
4. Di tab "Manage Servers":
   - Pilih "MySQL Database"
   - Klik tombol "Start" (atau "Restart")
5. Tunggu sampai status jadi "Running" (hijau)
```

### Atau via Terminal:
```bash
# Start MySQL
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start

# Check status
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server status
```

**Expected output:**
```
SUCCESS! MySQL running (12345)
```

---

## ✅ SOLUSI 2: Test MySQL Tanpa phpMyAdmin

### Cara 1: Via Terminal/Command Line
```bash
# Masuk ke MySQL
/Applications/XAMPP/xamppfiles/bin/mysql -u root

# Jalankan SQL commands:
CREATE DATABASE IF NOT EXISTS cafe_ordering;
USE cafe_ordering;

-- Copy paste SQL dari bawah ini:
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, email, password, role) VALUES 
('Test User', 'user@gmail.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'customer');

-- Check hasilnya:
SELECT * FROM users;

-- Keluar:
EXIT;
```

### Cara 2: Import SQL File
```bash
# Buat file SQL
cat > /tmp/setup_users.sql << 'EOF'
CREATE DATABASE IF NOT EXISTS cafe_ordering;
USE cafe_ordering;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, email, password, role) VALUES 
('Test User', 'user@gmail.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'customer');
EOF

# Import
/Applications/XAMPP/xamppfiles/bin/mysql -u root < /tmp/setup_users.sql

# Verify
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "SELECT * FROM cafe_ordering.users"
```

---

## 🔍 CHECK: Apakah MySQL Sudah Running?

### Quick Test:
```bash
# Method 1: Check process
ps aux | grep mysql

# Method 2: Check port
lsof -i :3306

# Method 3: Try connect
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "SELECT 1"
```

**✅ Jika berhasil, hasilnya:**
```
+---+
| 1 |
+---+
| 1 |
+---+
```

**❌ Jika error:**
```
ERROR 2002 (HY000): Can't connect to local MySQL server
```
→ Berarti MySQL belum running, ulangi Solusi 1

---

## 🚀 ALL-IN-ONE SETUP SCRIPT

Copy paste script ini di Terminal:

```bash
#!/bin/bash
echo "🔧 XAMPP MySQL Setup Script"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 1. Start MySQL
echo "1️⃣  Starting MySQL..."
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
sleep 3

# 2. Check if running
if ps aux | grep -v grep | grep mysql > /dev/null; then
    echo "✅ MySQL is running!"
else
    echo "❌ MySQL failed to start. Please check XAMPP Control Panel."
    exit 1
fi

# 3. Setup database
echo ""
echo "2️⃣  Setting up database..."
/Applications/XAMPP/xamppfiles/bin/mysql -u root << 'EOSQL'
CREATE DATABASE IF NOT EXISTS cafe_ordering;
USE cafe_ordering;

CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  email varchar(100) NOT NULL UNIQUE,
  password varchar(255) NOT NULL,
  role enum('customer','admin') DEFAULT 'customer',
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO users (username, email, password, role) VALUES 
('Test User', 'user@gmail.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'customer');

SELECT 'Database setup complete!' as Status;
EOSQL

# 4. Verify
echo ""
echo "3️⃣  Verifying setup..."
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "SELECT id, username, email, role FROM cafe_ordering.users"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Setup complete!"
echo ""
echo "Test login:"
echo "  URL: http://localhost/cafe_ordering/public/login.php"
echo "  Email: user@gmail.com"
echo "  Password: pass123"
```

**Cara pakai:**
```bash
# Save script
curl -o setup.sh https://pastebin.com/raw/YOUR_PASTE_ID

# Or create manually:
nano setup.sh
# Paste script di atas, Ctrl+X, Y, Enter

# Make executable
chmod +x setup.sh

# Run
./setup.sh
```

---

## 🔄 RESTART XAMPP COMPLETELY

Jika masih error, restart semua:

```bash
# Stop everything
sudo /Applications/XAMPP/xamppfiles/xampp stop

# Wait 5 seconds
sleep 5

# Start everything
sudo /Applications/XAMPP/xamppfiles/xampp start

# Check status
sudo /Applications/XAMPP/xamppfiles/xampp status
```

---

## 📱 ALTERNATIF: Pakai MySQL Workbench

Jika phpMyAdmin tetap error, pakai MySQL Workbench:

1. **Download:** https://dev.mysql.com/downloads/workbench/
2. **Install** MySQL Workbench
3. **Connect:**
   - Host: `localhost` atau `127.0.0.1`
   - Port: `3306`
   - Username: `root`
   - Password: (kosongkan)
4. **Run SQL** dari file `QUICK_START_FITUR.md`

---

## ⚠️ TROUBLESHOOTING LANJUTAN

### Problem: Port 3306 already used
```bash
# Check what's using port 3306
sudo lsof -i :3306

# Kill process if needed (replace PID)
sudo kill -9 <PID>

# Then start MySQL again
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
```

### Problem: Permission denied
```bash
# Fix permissions
sudo chown -R mysql:mysql /Applications/XAMPP/xamppfiles/var/mysql/
sudo chmod -R 755 /Applications/XAMPP/xamppfiles/var/mysql/
```

### Problem: MySQL won't start (check logs)
```bash
# Check error log
tail -50 /Applications/XAMPP/xamppfiles/logs/mysql_error.log

# Or
cat /Applications/XAMPP/xamppfiles/var/mysql/*.err
```

---

## ✅ VERIFICATION CHECKLIST

Setelah fix, cek ini semua:

```bash
# 1. MySQL running?
ps aux | grep mysql
# ✅ Harus ada proses "mysqld"

# 2. Port 3306 listening?
lsof -i :3306
# ✅ Harus ada "mysqld"

# 3. Can connect?
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "SELECT 1"
# ✅ Harus return "1"

# 4. Database exists?
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "SHOW DATABASES LIKE 'cafe_ordering'"
# ✅ Harus return "cafe_ordering"

# 5. Table users exists?
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "SHOW TABLES FROM cafe_ordering LIKE 'users'"
# ✅ Harus return "users"

# 6. Test user exists?
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "SELECT email FROM cafe_ordering.users WHERE email='user@gmail.com'"
# ✅ Harus return "user@gmail.com"
```

**Jika SEMUA ✅ di atas PASS, lanjut test login!**

---

## 🎯 NEXT STEP

Setelah MySQL jalan:

```
1. Test MySQL: ✅ (sudah fixed)
2. Test phpMyAdmin: http://localhost/phpmyadmin
3. Test Login: http://localhost/cafe_ordering/public/login.php
   Email: user@gmail.com
   Password: pass123
```

---

## 📞 MASIH ERROR?

Berikan output dari command ini:

```bash
# 1. MySQL status
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server status

# 2. Process list
ps aux | grep mysql

# 3. Port check
lsof -i :3306

# 4. Error log (last 20 lines)
tail -20 /Applications/XAMPP/xamppfiles/logs/mysql_error.log

# 5. XAMPP status
sudo /Applications/XAMPP/xamppfiles/xampp status
```

Copy paste output semua command di atas!

---

**Priority:** Start MySQL dulu sebelum lanjut ke step lain!
