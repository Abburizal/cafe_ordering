# 📋 Tabel 4.8 Implementasi Antarmuka (Interface)

## Daftar Halaman Aplikasi Cafe Ordering System

### **A. Halaman Customer (Public)**

| No | Halaman | Nama File | Deskripsi |
|----|---------|-----------|-----------|
| 1 | Halaman Utama / Landing Page | `public/index.php` | Halaman awal untuk redirect scan QR atau akses langsung |
| 2 | Scan QR Code | `public/scan.php` | Halaman untuk scan QR code meja |
| 3 | Daftar Menu | `public/menu.php` | Halaman katalog produk/menu makanan & minuman |
| 4 | Keranjang Belanja | `public/cart.php` | Halaman keranjang pesanan sebelum checkout |
| 5 | Checkout / Konfirmasi Pesanan | `public/checkout.php` | Halaman konfirmasi pesanan & pilih metode pembayaran |
| 6 | Pembayaran QRIS | `public/pay_qris.php` | Halaman pembayaran dengan QRIS (Midtrans) |
| 7 | Pembayaran Tunai | `public/tunai.php` | Halaman pembayaran cash/tunai |
| 8 | Konfirmasi Pembayaran | `public/confirm_payment.php` | Halaman konfirmasi setelah pembayaran |
| 9 | Status Pesanan | `public/order_status.php` | Halaman tracking status pesanan real-time |
| 10 | Pembayaran Berhasil | `public/success.php` | Halaman sukses setelah pembayaran berhasil |
| 11 | Pembayaran Dibatalkan | `public/cancel.php` | Halaman ketika pembayaran dibatalkan |
| 12 | Riwayat Pesanan | `public/riwayat.php` | Halaman histori pesanan customer |
| 13 | Tambah ke Keranjang (Process) | `public/add_cart.php` | Proses menambah produk ke keranjang (no view) |
| 14 | Update Keranjang (Process) | `public/update_cart.php` | Proses update quantity di keranjang (no view) |
| 15 | Notifikasi Midtrans (Webhook) | `public/midtrans_notify.php` | Endpoint callback Midtrans (no view) |

---

### **B. Halaman Admin (Admin Panel)**

| No | Halaman | Nama File | Deskripsi |
|----|---------|-----------|-----------|
| 16 | Login Admin | `admin/login.php` | Halaman login untuk admin/kasir |
| 17 | Registrasi Admin | `admin/register_admin.php` | Halaman registrasi akun admin baru |
| 18 | Dashboard Admin | `admin/dashboard.php` | Halaman utama admin dengan statistik & overview |
| 19 | Manajemen Produk | `admin/product.php` | Halaman CRUD produk (tambah, edit, hapus, arsip) |
| 20 | Manajemen Kategori | `admin/categories.php` | Halaman CRUD kategori produk |
| 21 | Manajemen Pesanan | `admin/orders.php` | Halaman daftar semua pesanan & update status |
| 22 | Detail Pesanan | `admin/orders_detail.php` | Halaman detail pesanan dengan items & info lengkap |
| 23 | Manajemen Meja | `admin/tables.php` | Halaman CRUD meja & generate QR code |
| 24 | Logout Admin | `admin/logout.php` | Proses logout admin (no view) |

---

## 📊 Statistik Halaman

**Total Halaman:** 24 halaman
- **Customer Pages:** 15 halaman (12 views + 3 process)
- **Admin Pages:** 9 halaman (8 views + 1 process)

---

## 🗺️ Flow Diagram Customer Journey

```
┌─────────────────────────────────────────────────────────────┐
│                    CUSTOMER FLOW                             │
└─────────────────────────────────────────────────────────────┘

1. index.php (Landing)
   │
   ├──> scan.php (Scan QR Meja)
   │      │
   │      └──> menu.php (Pilih Menu)
   │             │
   │             ├──> add_cart.php (Tambah ke Keranjang)
   │             │      │
   │             │      └──> cart.php (Lihat Keranjang)
   │             │             │
   │             │             ├──> update_cart.php (Update Qty)
   │             │             │      │
   │             │             │      └──> (Kembali ke cart.php)
   │             │             │
   │             │             └──> checkout.php (Checkout)
   │             │                    │
   │             │                    ├──> pay_qris.php (QRIS)
   │             │                    │      │
   │             │                    │      ├──> success.php ✅
   │             │                    │      └──> cancel.php ❌
   │             │                    │
   │             │                    └──> tunai.php (Cash)
   │             │                           │
   │             │                           └──> confirm_payment.php
   │             │                                  │
   │             │                                  └──> order_status.php
   │             │                                         │
   │             │                                         └──> success.php ✅
   │             │
   │             └──> riwayat.php (Histori Pesanan)
   │
   └──> menu.php (Direct Access - Tanpa QR)

┌─────────────────────────────────────────────────────────────┐
│ Webhook: midtrans_notify.php (Background Process)           │
└─────────────────────────────────────────────────────────────┘
```

---

## 🗺️ Flow Diagram Admin Journey

```
┌─────────────────────────────────────────────────────────────┐
│                     ADMIN FLOW                               │
└─────────────────────────────────────────────────────────────┘

1. login.php (Login Admin)
   │
   ├──> register_admin.php (Register - First Time)
   │
   └──> dashboard.php (Dashboard Utama)
          │
          ├──> product.php (Kelola Produk)
          │      ├── Tambah Produk
          │      ├── Edit Produk
          │      ├── Arsip Produk
          │      └── Hapus Produk
          │
          ├──> categories.php (Kelola Kategori)
          │      ├── Tambah Kategori
          │      ├── Edit Kategori
          │      └── Hapus Kategori
          │
          ├──> orders.php (Kelola Pesanan)
          │      ├── Update Status (Pending → Processing → Done)
          │      ├── Cancel Order
          │      └──> orders_detail.php (Detail Order)
          │
          ├──> tables.php (Kelola Meja)
          │      ├── Tambah Meja
          │      ├── Edit Meja
          │      ├── Hapus Meja
          │      └── Generate QR Code
          │
          └──> logout.php (Logout)
```

---

## 📂 Struktur Folder

```
cafe_ordering/
├── public/                    # Customer-facing pages (15 files)
│   ├── index.php             # Landing page
│   ├── scan.php              # QR scanner
│   ├── menu.php              # Menu katalog
│   ├── cart.php              # Shopping cart
│   ├── checkout.php          # Checkout
│   ├── pay_qris.php          # QRIS payment
│   ├── tunai.php             # Cash payment
│   ├── confirm_payment.php   # Payment confirmation
│   ├── order_status.php      # Order tracking
│   ├── success.php           # Success page
│   ├── cancel.php            # Cancel page
│   ├── riwayat.php           # Order history
│   ├── add_cart.php          # Add to cart (process)
│   ├── update_cart.php       # Update cart (process)
│   └── midtrans_notify.php   # Midtrans webhook (process)
│
├── admin/                     # Admin panel pages (9 files)
│   ├── login.php             # Admin login
│   ├── register_admin.php    # Admin registration
│   ├── dashboard.php         # Dashboard
│   ├── product.php           # Product management
│   ├── categories.php        # Category management
│   ├── orders.php            # Order management
│   ├── orders_detail.php     # Order detail
│   ├── tables.php            # Table management
│   └── logout.php            # Logout (process)
│
├── config/                    # Configuration files
│   └── config.php            # Database config
│
├── app/                       # Application logic
│   ├── helpers.php           # Helper functions
│   ├── middleware.php        # Authentication middleware
│   └── validator.php         # Validation functions
│
└── assets/                    # Static files (CSS, JS, Images)
    ├── css/
    ├── js/
    └── images/
```

---

## 🎨 Kategori Halaman Berdasarkan Fungsi

### **1. Authentication (2 halaman)**
- `admin/login.php` - Login
- `admin/register_admin.php` - Register

### **2. Menu & Product Display (2 halaman)**
- `public/index.php` - Landing
- `public/menu.php` - Product catalog

### **3. Shopping Cart & Checkout (5 halaman)**
- `public/cart.php` - View cart
- `public/checkout.php` - Checkout
- `public/add_cart.php` - Add to cart
- `public/update_cart.php` - Update cart

### **4. Payment (5 halaman)**
- `public/pay_qris.php` - QRIS payment
- `public/tunai.php` - Cash payment
- `public/confirm_payment.php` - Confirm payment
- `public/success.php` - Success page
- `public/cancel.php` - Cancel page

### **5. Order Tracking (2 halaman)**
- `public/order_status.php` - Track order
- `public/riwayat.php` - Order history

### **6. Admin Management (5 halaman)**
- `admin/dashboard.php` - Dashboard
- `admin/product.php` - Product CRUD
- `admin/categories.php` - Category CRUD
- `admin/orders.php` - Order management
- `admin/orders_detail.php` - Order detail
- `admin/tables.php` - Table CRUD

### **7. Utility & Process (3 halaman)**
- `public/scan.php` - QR scanner
- `public/midtrans_notify.php` - Webhook
- `admin/logout.php` - Logout

---

## 🔐 Access Control

### **Public Pages (No Auth Required)**
- ✅ `public/index.php`
- ✅ `public/scan.php`
- ✅ `public/menu.php`
- ✅ `public/cart.php`
- ✅ `public/checkout.php`
- ✅ `public/pay_qris.php`
- ✅ `public/tunai.php`
- ✅ `public/success.php`
- ✅ `public/cancel.php`
- ✅ `public/riwayat.php`
- ✅ `public/order_status.php`
- ✅ `public/confirm_payment.php`

### **Process Pages (No View - Logic Only)**
- 🔄 `public/add_cart.php`
- 🔄 `public/update_cart.php`
- 🔄 `public/midtrans_notify.php`
- 🔄 `admin/logout.php`

### **Admin Pages (Auth Required)**
- 🔒 `admin/dashboard.php` - require_admin()
- 🔒 `admin/product.php` - require_admin()
- 🔒 `admin/categories.php` - require_admin()
- 🔒 `admin/orders.php` - require_admin()
- 🔒 `admin/orders_detail.php` - require_admin()
- 🔒 `admin/tables.php` - require_admin()

### **Login Pages (Auth Not Required)**
- 🔓 `admin/login.php`
- 🔓 `admin/register_admin.php`

---

## 📱 Responsive Design

Semua halaman didesain responsive dengan:
- **Framework CSS:** Tailwind CSS 3.4.0
- **Icons:** Feather Icons
- **Font:** Inter (Google Fonts)
- **Mobile-First Approach:** Optimized untuk HP, Tablet, Desktop

---

## 🔗 API Endpoints (Untuk Realtime Features)

Selain halaman UI, ada juga API endpoints:

| Endpoint | File | Fungsi |
|----------|------|--------|
| `/public/api/get_orders.php` | API untuk realtime order updates | WebSocket / Polling |
| `/admin/api/register_fcm_token.php` | API untuk push notification (FCM) | Notifikasi admin |

---

## 📄 Export ke Format Lain

### **Excel/CSV Format:**
```csv
No,Halaman,Nama File,Kategori,Auth Required
1,Halaman Utama,public/index.php,Landing,No
2,Scan QR Code,public/scan.php,Utility,No
3,Daftar Menu,public/menu.php,Product,No
...
```

### **PDF Format:**
Tabel di atas bisa di-export ke PDF untuk dokumentasi teknis.

---

## 📝 Catatan Penting

### **1. Halaman dengan Session Dependency**
Beberapa halaman memerlukan session data:
- `cart.php` - Butuh `$_SESSION['cart']`
- `menu.php` - Butuh `$_SESSION['table_number']` (dari scan QR)
- `checkout.php` - Butuh `$_SESSION['cart']` dan `$_SESSION['table_id']`

### **2. Halaman dengan Database Query Intensif**
- `admin/dashboard.php` - Banyak query agregat (statistik)
- `admin/orders.php` - Query dengan JOIN (orders + order_items + products)
- `public/order_status.php` - Realtime polling query

### **3. Halaman dengan External Service**
- `pay_qris.php` - Integrasi Midtrans API
- `midtrans_notify.php` - Webhook dari Midtrans

### **4. Security Considerations**
- Admin pages: Protected dengan `require_admin()` middleware
- SQL Injection: Semua query menggunakan prepared statements
- XSS Protection: Output di-escape dengan `e()` helper function
- CSRF: Implementasi token untuk form admin

---

## 🚀 Future Enhancement (Belum Diimplementasi)

Halaman yang bisa ditambahkan:
1. `public/profile.php` - Customer profile & order history
2. `public/forgot_password.php` - Reset password
3. `admin/reports.php` - Laporan penjualan & analytics
4. `admin/settings.php` - System settings
5. `admin/users.php` - User management
6. `admin/promos.php` - Promo & discount management
7. `public/rating.php` - Rating & review produk

---

**Dokumentasi dibuat:** 2026-02-03  
**Versi Aplikasi:** cafe_ordering v1.0  
**Total Halaman:** 24 pages  
**Framework:** PHP + Tailwind CSS  
**Database:** MySQL/MariaDB
