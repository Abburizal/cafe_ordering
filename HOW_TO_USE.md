# 🎯 Cara Menggunakan Cafe Ordering System

## 📋 Daftar Isi
1. [Login Admin](#login-admin)
2. [Manajemen Kategori](#manajemen-kategori)
3. [Manajemen Produk](#manajemen-produk)
4. [Melihat Pesanan](#melihat-pesanan)
5. [Customer Flow](#customer-flow)

---

## 1. 🔐 Login Admin

**URL:** http://localhost/cafe_ordering/admin/login.php

**Akun Admin yang Tersedia:**
```
Username: admin, satriyo, yana, bahar, atau kasir
Password: (gunakan password yang sudah terdaftar)
```

**Catatan:** Semua password sudah di-hash untuk keamanan.

---

## 2. 🏷️ Manajemen Kategori

**URL:** http://localhost/cafe_ordering/admin/categories.php

### Tambah Kategori Baru:
1. Isi form:
   - Nama Kategori (wajib)
   - Deskripsi
   - Icon (emoji, contoh: 🍔)
   - Urutan tampilan (angka)
2. Klik tombol "Tambah"

### Edit Kategori:
1. Klik tombol "Edit" pada kategori yang ingin diubah
2. Modal akan muncul
3. Ubah data yang diperlukan
4. Klik "Update"

### Nonaktifkan Kategori:
- Klik tombol "Nonaktifkan" pada kategori
- Kategori tidak akan dihapus, hanya dinonaktifkan

**Kategori Default yang Sudah Ada:**
- 🍽️ Makanan
- 🥤 Minuman
- 🍟 Snack
- 🍰 Dessert
- ☕ Coffee
- ⭐ Special

---

## 3. 📦 Manajemen Produk

**URL:** http://localhost/cafe_ordering/admin/product.php

### Tambah Produk:
1. Klik tombol "Tambah Produk"
2. Isi form:
   - Nama Produk
   - Harga
   - Kategori (pilih dari dropdown)
   - Deskripsi
   - Stock
   - Upload Gambar
3. Submit form

### Upload Gambar:
**Cara 1: Via Form Product**
- Upload langsung saat tambah/edit produk
- Gambar otomatis diresize ke 800x800px
- Thumbnail 300x300px dibuat otomatis

**Cara 2: Via API**
```bash
curl -X POST http://localhost/cafe_ordering/admin/api/upload_image.php \
  -F "image=@/path/to/image.jpg"
```

**Format yang Didukung:**
- JPG/JPEG
- PNG
- GIF
- WebP

**Ukuran Maksimal:** 2MB

### Edit Produk:
1. Klik tombol "Edit" pada produk
2. Ubah data yang diperlukan
3. Upload gambar baru (optional)
4. Save changes

### Nonaktifkan Produk:
- Klik "Arsipkan" untuk soft delete
- Produk tidak akan muncul di customer menu
- Data riwayat pesanan tetap ada

---

## 4. 📋 Melihat Pesanan

**URL:** http://localhost/cafe_ordering/admin/orders.php

### Status Pesanan:
- 🟡 **Pending**: Pesanan baru masuk
- �� **Confirmed**: Pesanan dikonfirmasi
- 🟣 **Preparing**: Sedang diproses
- 🟢 **Ready**: Siap diantar
- ✅ **Completed**: Selesai
- ❌ **Cancelled**: Dibatalkan

### Update Status:
1. Klik pesanan untuk lihat detail
2. Pilih status baru dari dropdown
3. Save perubahan

### Notifikasi Real-Time:
**⚠️ Perlu Integrasi**

Untuk mengaktifkan notifikasi otomatis:
1. Tambahkan di `admin/dashboard.php`:
```html
<script src="assets/notification.js"></script>
```

2. Allow notification permission di browser

---

## 5. 🛒 Customer Flow

**URL:** http://localhost/cafe_ordering/public/

### Langkah Customer:
1. **Pilih Meja**
   - Scan QR Code atau pilih meja manual
   - 14 meja tersedia

2. **Browse Menu**
   - Lihat produk berdasarkan kategori
   - Lihat harga dan deskripsi

3. **Tambah ke Keranjang**
   - Klik produk untuk tambah
   - Atur quantity
   - Tambah catatan (optional)

4. **Checkout**
   - Review pesanan
   - Pilih metode pembayaran:
     - Cash
     - QRIS (perlu konfigurasi Midtrans)
     - Transfer

5. **Konfirmasi Pesanan**
   - Order akan masuk ke admin dashboard
   - Track status pesanan

---

## 🔧 Utility Classes

### Validator Class
```php
require_once 'app/validator.php';

// Sanitize input
$name = Validator::sanitize_string($_POST['name']);

// Validate email
if (!Validator::validate_email($email)) {
    echo "Email tidak valid";
}

// Validate multiple fields
$errors = Validator::validate_inputs([
    'name' => ['required'],
    'email' => ['required', 'email'],
    'phone' => ['phone']
], $_POST);
```

### ImageHandler Class
```php
require_once 'app/image_handler.php';

$handler = new ImageHandler();

// Upload image
$result = $handler->upload($_FILES['image']);
if ($result['success']) {
    $filename = $result['filename'];
}

// Get URL
$url = $handler->get_image_url($filename);
$thumb = $handler->get_image_url($filename, true);
```

---

## 🎨 Kustomisasi

### Ubah Warna Tema:
Edit di setiap file PHP bagian:
```html
<div class="bg-orange-500">  <!-- Ganti orange-500 -->
```

### Tambah Kategori Custom:
1. Via admin/categories.php
2. Atau via SQL:
```sql
INSERT INTO categories (name, description, icon, display_order) 
VALUES ('Pizza', 'Menu pizza spesial', '🍕', 7);
```

---

## 🐛 Troubleshooting

### Problem: Tidak bisa login
```bash
php admin/scripts/fix_passwords.php
```

### Problem: Gambar tidak muncul
```bash
chmod -R 755 public/assets/images/products/
```

### Problem: Kategori kosong
```bash
mysql -u root cafe_ordering < admin/sql/add_categories_table.sql
```

### Problem: Meja tidak ada
```bash
php admin/scripts/setup_tables.php
```

---

## 📞 Support

Dokumentasi lengkap:
- `SETUP_GUIDE.md` - Setup detail
- `PERBAIKAN_SUMMARY.md` - Summary fitur baru
- `QUICK_START.txt` - Quick reference
- `IMPLEMENTASI_PRIORITAS.md` - Roadmap

---

**Version:** 1.0  
**Last Updated:** 2025-11-05  
**Status:** Production Ready
