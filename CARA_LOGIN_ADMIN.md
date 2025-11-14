# CARA LOGIN ADMIN - PANDUAN LENGKAP

## 🔐 Masalah: Tidak Bisa Login

Jika Anda tidak bisa login ke admin panel, ikuti langkah-langkah berikut:

---

## ✅ SOLUSI 1: Reset Password Admin (TERMUDAH)

### Langkah-langkah:

1. **Buka browser** dan akses URL:
   ```
   http://localhost/cafe_ordering/admin/scripts/reset_admin_password.php
   ```

2. **Script akan otomatis**:
   - Membuat/update user admin
   - Generate password hash yang benar
   - Menampilkan kredensial login

3. **Kredensial Default** yang akan dibuat:
   ```
   Username: admin
   Password: admin123
   Email: admin@restoku.com
   ```

4. **Login**:
   - Buka: `http://localhost/cafe_ordering/admin/login.php`
   - Masukkan username: `admin`
   - Masukkan password: `admin123`
   - Klik "Masuk"

---

## ✅ SOLUSI 2: Buat Admin Baru via Register

1. Buka: `http://localhost/cafe_ordering/admin/register_admin.php`

2. Isi form registrasi:
   - Nama Lengkap: (bebas)
   - Username: (pilih username unik)
   - Email: (email valid)
   - Password: (minimal 6 karakter)
   - Konfirmasi Password: (sama dengan password)

3. Klik "Daftar"

4. Login menggunakan kredensial yang baru dibuat

---

## ✅ SOLUSI 3: Manual via Database (phpMyAdmin)

Jika kedua solusi di atas tidak work:

### Via phpMyAdmin:

1. **Buka phpMyAdmin**: `http://localhost/phpmyadmin`

2. **Pilih database**: `cafe_ordering`

3. **Buka tabel**: `users`

4. **Lihat data admin**:
   - Cari user dengan `role = 'admin'`
   - Lihat username di kolom `name`

5. **Generate password hash baru**:
   ```php
   <?php
   // Jalankan script ini di file terpisah atau online PHP sandbox
   echo password_hash('admin123', PASSWORD_DEFAULT);
   ?>
   ```
   Hasilnya seperti: `$2y$10$...`

6. **Update password di database**:
   ```sql
   UPDATE users 
   SET password = '$2y$10$abcdefghijk...' 
   WHERE name = 'admin' AND role = 'admin';
   ```

7. **Login** dengan password baru: `admin123`

---

## 🔍 TROUBLESHOOTING

### Problem: "Username atau password salah"

**Penyebab**:
- Password di database tidak ter-hash dengan benar
- Username salah (cek di database, field `name` bukan `username`)
- Role bukan 'admin'

**Solusi**:
1. Jalankan `reset_admin_password.php` (Solusi 1)
2. Atau gunakan register_admin.php untuk buat admin baru

### Problem: "Script tidak bisa diakses"

**Cek**:
1. XAMPP Apache sudah running?
2. Path benar? `/cafe_ordering/admin/scripts/reset_admin_password.php`
3. File ada di folder yang benar?

### Problem: "Database error"

**Cek**:
1. MySQL sudah running di XAMPP?
2. Database `cafe_ordering` sudah ada?
3. Tabel `users` sudah dibuat?
4. Config database benar di `config/config.php`?

---

## 📋 CREDENTIAL DEFAULT

Setelah menjalankan script reset:

```
🔑 LOGIN ADMIN
─────────────────────────
URL: http://localhost/cafe_ordering/admin/login.php

Username: admin
Password: admin123
Email: admin@restoku.com
─────────────────────────
```

---

## 🎯 QUICK TEST

Untuk test apakah password hash bekerja:

```php
<?php
// File: test_password.php (buat di root folder)
require_once 'config/config.php';

$test_password = 'admin123';

// Get admin dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE name = 'admin' AND role = 'admin'");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "Admin ditemukan: " . $admin['name'] . "<br>";
    echo "Password hash: " . $admin['password'] . "<br><br>";
    
    // Test verify
    if (password_verify($test_password, $admin['password'])) {
        echo "✅ Password BENAR! Anda bisa login dengan password: $test_password";
    } else {
        echo "❌ Password SALAH! Hash tidak cocok.";
    }
} else {
    echo "❌ Admin tidak ditemukan di database!";
}
?>
```

---

## ⚠️ CATATAN PENTING

1. **Password Default** (`admin123`) hanya untuk testing
2. **Setelah login**, segera ganti password via profile/settings
3. **Jangan commit** password ke Git
4. **Untuk production**, gunakan password yang kuat
5. **Script reset** hanya untuk development/testing

---

## 🚀 CARA TERCEPAT

```bash
# 1. Buka browser
# 2. Akses:
http://localhost/cafe_ordering/admin/scripts/reset_admin_password.php

# 3. Script akan create/reset admin
# 4. Login dengan:
#    Username: admin
#    Password: admin123
```

---

## 📞 BANTUAN LEBIH LANJUT

Jika masih bermasalah:

1. Cek error log PHP: `XAMPP/logs/php_error_log`
2. Cek error log Apache: `XAMPP/logs/error_log`
3. Enable error display di `config/config.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

---

**Terakhir diupdate**: 6 November 2025
