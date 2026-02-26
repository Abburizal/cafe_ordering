# 🚀 CARA MENJALANKAN FITUR BARU - STEP BY STEP

## ⚡ QUICK START (5 Menit)

### STEP 1: Install Composer Dependencies
```bash
# Buka Terminal/CMD di folder cafe_ordering
cd /Applications/XAMPP/xamppfiles/htdocs/cafe_ordering

# Install library QR Code
composer install
```

**ATAU jika error:**
```bash
composer require endroid/qr-code
```

---

### STEP 2: Pastikan Database Sudah Setup

#### Cek Tabel Users:
```sql
-- Buka phpMyAdmin: http://localhost/phpmyadmin
-- Pilih database: cafe_ordering
-- Jalankan query ini:

SHOW TABLES LIKE 'users';
```

**Jika tabel `users` BELUM ada, buat dengan:**
```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Insert Test User:
```sql
-- Password: pass123 (sudah di-hash)
INSERT INTO users (username, email, password, role) VALUES 
('Test User', 'user@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');
```

---

### STEP 3: Jalankan XAMPP
```
✅ Start Apache
✅ Start MySQL
```

---

## 🧪 TESTING SETIAP FITUR

### 🔐 TEST 1: LOGIN & CHECKOUT (2 Menit)

#### A. Test Login Redirect:
```
1. Buka browser: http://localhost/cafe_ordering/public/menu.php
2. Tambahkan produk ke cart (klik "Tambah ke Keranjang")
3. Klik icon cart di kanan atas
4. Klik tombol "Checkout"

✅ HASIL: Harus redirect ke halaman LOGIN
```

#### B. Test Login Berhasil:
```
5. Di halaman login, input:
   Email: user@gmail.com
   Password: pass123
   
6. Klik "Masuk"

✅ HASIL: Login berhasil, kembali ke halaman CHECKOUT
```

#### C. Test Login Gagal:
```
1. Logout (jika sudah login)
2. Coba login dengan password salah: wrongpassword
3. Klik "Masuk"

✅ HASIL: Muncul pesan error "Email atau Password Salah"
```

**📹 Video Demo:** Lihat file `TEST_CASE_FITUR_BARU.md` baris 8-22

---

### 🔔 TEST 2: NOTIFIKASI REAL-TIME (5 Menit)

#### Setup Notification Script:

**PENTING! Tambahkan script ini ke halaman admin dulu:**

1. **Buka file:** `/admin/orders.php`
2. **Cari baris:** `</body>` (paling bawah)
3. **Tambahkan SEBELUM `</body>`:**
```html
<!-- Real-time Notification -->
<script src="assets/js/notification.js"></script>
```

#### Test Notifikasi:

```
STEP 1: Buka 2 Tab Browser

Tab 1 (ADMIN):
- URL: http://localhost/cafe_ordering/admin/orders.php
- Login sebagai admin
- BIARKAN TAB INI TERBUKA

Tab 2 (CUSTOMER):
- URL: http://localhost/cafe_ordering/public/menu.php
- Tambah produk ke cart
- Checkout & bayar

TUNGGU 5-10 DETIK...

✅ HASIL di Tab 1: 
   - Muncul TOAST NOTIFICATION (kotak hijau di kanan atas)
   - Terdengar suara "TING"
   - Tabel order otomatis ter-update
```

**🔊 PASTIKAN:**
- Volume komputer TIDAK mute
- Speaker/headphone sudah ON

**📸 Screenshot Notifikasi:**
```
┌──────────────────────────────────┐
│ 🔔 Order Baru!                   │
│ MEJA 1                            │
│ 2 item - Rp 50,000               │
└──────────────────────────────────┘
```

---

### 📝 TEST 3: VALIDASI PRODUK (3 Menit)

```
1. Buka: http://localhost/cafe_ordering/admin/product.php
2. Login sebagai admin
3. Klik "Tambah Produk"
```

#### A. Test Field Kosong:
```
4. ISI HANYA Nama Produk: "Nasi Goreng"
5. KOSONGKAN Harga
6. Klik "Simpan"

✅ HASIL: Muncul pesan "⚠️ Bidang Harga wajib diisi!"
```

#### B. Test Format File Salah:
```
7. Isi semua field
8. Upload file PDF atau DOCX
9. Klik "Simpan"

✅ HASIL: Muncul pesan "⚠️ Format file harus JPG, PNG, GIF, atau WEBP!"
```

#### C. Test Berhasil Tambah:
```
10. Isi semua field:
    - Nama: "Nasi Goreng Special"
    - Harga: 25000
    - Kategori: (pilih salah satu)
    - Gambar: Upload file JPG/PNG
11. Klik "Simpan"

✅ HASIL: Muncul "✅ Berhasil Menambahkan Menu"
✅ Produk muncul di daftar
```

#### D. Test Hapus Produk:
```
12. Klik tombol "Hapus" pada produk
13. Konfirmasi alert

✅ HASIL: Produk terhapus dari daftar
```

---

### 🖼️ TEST 4: QR CODE (5 Menit)

```
1. Buka: http://localhost/cafe_ordering/admin/tables.php
2. Login sebagai admin
```

#### A. Test Duplikat Nomor Meja:
```
3. Input Nomor Meja: "Meja 1" (sudah ada)
4. Input Code: "TBL-NEW"
5. Klik "Tambah Meja"

✅ HASIL: Muncul "⚠️ Nomor Meja sudah terdaftar"
```

#### B. Test Duplikat Code:
```
6. Input Nomor Meja: "Meja 99"
7. Input Code: "TBL-001" (sudah ada)
8. Klik "Tambah Meja"

✅ HASIL: Muncul "⚠️ Kode Meja sudah terdaftar"
```

#### C. Test Generate QR:
```
9. Input Nomor Meja: "Meja 10"
10. Input Code: "TBL-010"
11. Klik "Tambah Meja"

✅ HASIL: Meja tersimpan, QR Code ter-generate
```

#### D. Test Download QR:
```
12. Pada daftar meja, cari "Meja 10"
13. Klik tombol "Download QR"

✅ HASIL: File "QR_TBL-010.png" terunduh
```

#### E. Test Scan QR (dengan HP):
```
14. Buka file QR_TBL-010.png
15. Scan dengan HP (kamera atau app scanner)

✅ HASIL: HP redirect ke menu.php?table=TBL-010
✅ Session table tersimpan
```

---

## 🐛 TROUBLESHOOTING

### Problem 1: "composer: command not found"
```bash
# Install Composer dulu:
# Download: https://getcomposer.org/download/

# Atau lewat XAMPP shell:
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

### Problem 2: Notifikasi Tidak Muncul
```
✅ Cek: File notification.js sudah di-include di orders.php?
✅ Cek: Browser console (F12) ada error?
✅ Cek: URL API benar? Lihat di browser: 
   http://localhost/cafe_ordering/admin/api/check_new_orders.php
```

### Problem 3: Login Selalu "Password Salah"
```sql
-- Re-insert user dengan password yang benar:
DELETE FROM users WHERE email = 'user@gmail.com';

INSERT INTO users (username, email, password, role) VALUES 
('Test User', 'user@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Password: pass123
```

### Problem 4: QR Code Error
```bash
# Pastikan library ter-install:
cd /Applications/XAMPP/xamppfiles/htdocs/cafe_ordering
composer require endroid/qr-code

# Cek folder vendor/ ada?
ls -la vendor/
```

### Problem 5: Upload Gambar Error
```
✅ Cek: Folder public/assets/images/ ada?
✅ Cek: Permission folder 755
✅ Cek: php.ini upload_max_filesize = 10M
✅ Cek: php.ini post_max_size = 10M
```

---

## 📋 CHECKLIST LENGKAP

Sebelum testing, pastikan:

- [ ] XAMPP Apache & MySQL sudah running
- [ ] Database `cafe_ordering` sudah ada
- [ ] Tabel `users` sudah ada & ada data test user
- [ ] Composer sudah ter-install
- [ ] Library `endroid/qr-code` sudah ter-install
- [ ] Folder `public/assets/images/` ada & writable
- [ ] File `admin/assets/js/notification.js` ada
- [ ] Browser sudah clear cache (Ctrl+Shift+Delete)

---

## 🎯 QUICK TEST (1 Menit)

Jika mau test cepat semua fitur bekerja:

```bash
# 1. Test Login
URL: http://localhost/cafe_ordering/public/login.php
Email: user@gmail.com | Password: pass123
Expected: Login berhasil ✅

# 2. Test Notification API
URL: http://localhost/cafe_ordering/admin/api/check_new_orders.php
Expected: JSON response ✅

# 3. Test Product Page
URL: http://localhost/cafe_ordering/admin/product.php
Expected: Form validasi bekerja ✅

# 4. Test QR Download
URL: http://localhost/cafe_ordering/admin/download_qr.php?id=1
Expected: File PNG terunduh ✅
```

---

## 📞 BANTUAN

Jika masih error, berikan info:

1. **Error message** (screenshot atau copy paste)
2. **Browser console** (F12 → Console tab)
3. **PHP error log** (XAMPP → logs/error_log)
4. **URL yang di-akses**
5. **Step yang sudah dilakukan**

---

**Version:** 1.0  
**Last Updated:** 2026-02-03  
**Total Testing Time:** 15-20 menit  
**Difficulty:** ⭐⭐ (Easy)
