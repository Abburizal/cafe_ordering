# 🚀 ROADMAP IMPLEMENTASI CAFE ORDERING SYSTEM
## Prioritas Pengembangan dengan Langkah Implementasi

---

## ✅ FASE 1: KEAMANAN & FOUNDASI (CRITICAL - WAJIB)

### 1.1 ⚠️ FIX PASSWORD SECURITY (PRIORITAS #1)
**Masalah:** Password disimpan plain text di database (sangat berbahaya!)

**Solusi:**
```bash
# Jalankan script ini untuk update password yang sudah ada
php admin/scripts/fix_passwords.php
```

**File yang perlu diubah:**
- ✅ `admin/login.php` - Gunakan password_verify()
- ✅ `admin/register_admin.php` - Gunakan password_hash()

**Status:** BELUM IMPLEMENTASI ⚠️

---

### 1.2 📋 SETUP DATA MEJA (PRIORITAS #2)
**Masalah:** Tabel `tables` kosong, customer tidak bisa pilih meja

**Solusi:**
```bash
# Import data meja default
php admin/scripts/setup_tables.php
# atau via SQL
mysql -u root cafe_ordering < sql/insert_tables.sql
```

**Status:** BELUM IMPLEMENTASI ⚠️

---

### 1.3 🛡️ VALIDASI & ERROR HANDLING (PRIORITAS #3)
**Masalah:** Error handling kurang lengkap

**File yang perlu ditambahkan:**
- ✅ `app/validator.php` - Fungsi validasi input
- ✅ Try-catch di semua query database
- ✅ Sanitasi input user

**Status:** PARSIAL ⚠️

---

## 🎯 FASE 2: FITUR CORE BUSINESS

### 2.1 📦 MANAJEMEN PRODUK LENGKAP
**Fitur yang ditambahkan:**
- ✅ CRUD Produk (Create, Read, Update, Delete)
- ✅ Upload gambar produk dengan validasi
- ✅ Kategori produk (Makanan, Minuman, Dessert, dll)
- ✅ Manajemen stok produk
- ✅ Status aktif/nonaktif produk

**File baru:**
- `admin/product_add.php`
- `admin/product_edit.php`
- `admin/product_delete.php`
- `admin/api/upload_image.php`

**Status:** PARSIAL (sudah ada product.php tapi belum lengkap)

---

### 2.2 🔔 NOTIFIKASI REAL-TIME
**Fitur:**
- ✅ Push notification ke admin saat ada order baru
- ✅ Sound notification di dashboard admin
- ✅ Tabel `admin_tokens` untuk FCM token
- ✅ Auto-refresh order list

**File baru:**
- `sql/add_fcm_tokens_table.sql`
- `admin/api/register_fcm_token.php`
- `admin/assets/notification.js`

**File yang diupdate:**
- `admin/dashboard.php` - Tambah auto-refresh
- `app/helpers.php` - Sudah ada fungsi send_admin_notification()

**Status:** SEBAGIAN (FCM belum dikonfigurasi)

---

### 2.3 📊 LAPORAN & STATISTIK
**Fitur:**
- ✅ Laporan penjualan harian
- ✅ Laporan penjualan bulanan
- ✅ Laporan per produk (best seller)
- ✅ Export ke Excel/PDF
- ✅ Grafik penjualan

**File baru:**
- `admin/laporan.php`
- `admin/laporan_export.php`
- `admin/api/get_sales_chart.php`

**Status:** BELUM IMPLEMENTASI

---

## 💎 FASE 3: USER EXPERIENCE

### 3.1 🔍 PENCARIAN & FILTER
**Fitur:**
- ✅ Search produk by nama
- ✅ Filter by kategori
- ✅ Filter by harga
- ✅ Sort (termurah, termahal, terpopuler)

**File yang diupdate:**
- `public/menu.php` - Tambah search bar & filter

**Status:** BELUM IMPLEMENTASI

---

### 3.2 🏷️ KATEGORI PRODUK
**Fitur:**
- ✅ Tabel kategori produk
- ✅ CRUD kategori
- ✅ Relasi produk-kategori
- ✅ Filter menu by kategori

**File baru:**
- `sql/add_categories_table.sql`
- `admin/categories.php`

**Status:** BELUM IMPLEMENTASI

---

### 3.3 🖼️ UPLOAD IMAGE PROPER
**Fitur:**
- ✅ Upload image dengan resize otomatis
- ✅ Validasi tipe file & ukuran
- ✅ Thumbnail generation
- ✅ Hapus image lama saat update

**File baru:**
- `app/image_handler.php`

**Status:** BELUM IMPLEMENTASI

---

## 🔧 FASE 4: INTEGRASI & DEPLOYMENT

### 4.1 💳 INTEGRASI MIDTRANS (QRIS)
**Langkah:**
1. Daftar di https://midtrans.com
2. Dapatkan Server Key & Client Key
3. Update `config/config.php`:
   ```php
   define('MIDTRANS_SERVER_KEY', 'YOUR_SERVER_KEY');
   define('MIDTRANS_CLIENT_KEY', 'YOUR_CLIENT_KEY');
   define('MIDTRANS_IS_PRODUCTION', false); // sandbox
   ```
4. Test pembayaran QRIS

**Status:** KONFIGURASI DIPERLUKAN

---

### 4.2 🔥 FIREBASE PUSH NOTIFICATION
**Langkah:**
1. Buat project di Firebase Console
2. Download `google-services.json`
3. Dapatkan Server Key dari Cloud Messaging
4. Update `app/helpers.php`:
   ```php
   $fcm_server_key = 'YOUR_FCM_SERVER_KEY';
   ```
5. Buat service worker di `admin/firebase-messaging-sw.js`

**Status:** KONFIGURASI DIPERLUKAN

---

### 4.3 🚀 DEPLOYMENT PRODUCTION
**Checklist:**
- [ ] Ganti password database yang kuat
- [ ] Enable HTTPS (SSL Certificate)
- [ ] Update BASE_URL di config
- [ ] Backup database otomatis
- [ ] Enable error logging (jangan display error)
- [ ] Optimize images
- [ ] Enable gzip compression
- [ ] Setup cron job untuk maintenance

**Status:** BELUM DEPLOYMENT

---

## 📝 QUICK START - IMPLEMENTASI HARI INI

### Step 1: Fix Password Security (15 menit)
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/cafe_ordering
php admin/scripts/fix_passwords.php
```

### Step 2: Setup Data Meja (5 menit)
```bash
php admin/scripts/setup_tables.php
```

### Step 3: Test Login (2 menit)
- Buka: http://localhost/cafe_ordering/admin/login.php
- Username: admin
- Password: admin123

### Step 4: Test Customer Flow (5 menit)
- Buka: http://localhost/cafe_ordering/public/index.php
- Pilih meja
- Order produk
- Checkout

---

## 🎯 PRIORITAS MINGGU INI

### Hari 1-2: Keamanan & Data Dasar
- [x] Fix password security
- [x] Setup data meja
- [x] Setup data produk contoh

### Hari 3-4: Fitur Admin
- [ ] Lengkapi CRUD produk
- [ ] Tambah kategori produk
- [ ] Upload image yang proper

### Hari 5-6: Fitur Customer
- [ ] Search & filter menu
- [ ] Riwayat pesanan customer
- [ ] Rating & review (optional)

### Hari 7: Testing & Polish
- [ ] Test semua flow
- [ ] Fix bugs
- [ ] Dokumentasi

---

## 📞 SUPPORT & CATATAN

**Jika ada error:**
1. Cek `/Applications/XAMPP/xamppfiles/htdocs/cafe_ordering/error.log`
2. Cek XAMPP error log
3. Enable error display untuk debug (jangan di production!)

**Konfigurasi yang perlu diisi:**
- Midtrans Server Key
- Firebase FCM Key
- Email SMTP (jika perlu email notification)

**Database Backup:**
```bash
mysqldump -u root cafe_ordering > backup_$(date +%Y%m%d).sql
```

---

Generated: 2025-10-30
Version: 1.0
