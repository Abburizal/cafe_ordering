# ⚡ QUICK START - 3 LANGKAH MUDAH!

## 📦 STEP 1: Install Library (30 detik)

**Buka Terminal/CMD:**
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/cafe_ordering
composer install
```

**ATAU:**
```bash
composer require endroid/qr-code
```

✅ **Selesai? Lanjut ke Step 2**

---

## 🗄️ STEP 2: Setup Database (1 menit)

**Buka phpMyAdmin:** http://localhost/phpmyadmin

**Pilih database:** `cafe_ordering`

**Jalankan SQL ini:**
```sql
-- Buat tabel users (jika belum ada)
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

✅ **Selesai? Lanjut ke Step 3**

---

## 🚀 STEP 3: Jalankan & Test (2 menit)

### Pastikan XAMPP Running:
- ✅ Apache: **ON**
- ✅ MySQL: **ON**

### Test Login:
```
1. Buka: http://localhost/cafe_ordering/public/login.php
2. Email: user@gmail.com
3. Password: pass123
4. Klik "Masuk"
```

**✅ BERHASIL = Login masuk!**

---

## 🎉 SELESAI!

Semua fitur sudah jalan. Untuk test lebih detail, buka:
- `CARA_MENJALANKAN_FITUR_BARU.md` - Panduan lengkap
- `TEST_CASE_FITUR_BARU.md` - 19 test case

---

## ⚠️ JIKA ERROR:

### Error: "composer not found"
```bash
# Download Composer:
https://getcomposer.org/download/
```

### Error: "Table users doesn't exist"
```
→ Jalankan lagi SQL di Step 2
```

### Error: "Password salah terus"
```sql
-- Re-insert user:
DELETE FROM users WHERE email = 'user@gmail.com';
INSERT INTO users (username, email, password, role) VALUES 
('Test User', 'user@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');
```

### Error: "Notifikasi tidak muncul"
```
1. Buka: admin/orders.php
2. Cari baris: </body>
3. Tambahkan SEBELUM </body>:
   <script src="assets/js/notification.js"></script>
```

---

**Total Waktu Setup: 3-5 Menit** ⚡
