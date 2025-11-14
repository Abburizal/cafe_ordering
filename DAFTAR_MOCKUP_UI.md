# DAFTAR HALAMAN UNTUK RANCANGAN UI (BALSAMIQ/MOCKUP)

## 📱 A. HALAMAN CUSTOMER (Public - Mobile First)

### 1. **index.php** - Halaman Awal / Landing Page
   - **Fungsi**: Entry point untuk customer scan QR meja
   - **Komponen**:
     - Logo restoran
     - Judul "Selamat Datang"
     - Input nomor meja (alternatif scan QR)
     - Tombol "Mulai Pesan"
     - Background gradient modern
   - **User Flow**: Customer masuk → Input/Scan meja → Ke menu

---

### 2. **menu.php** - Daftar Menu
   - **Fungsi**: Menampilkan semua menu makanan/minuman
   - **Komponen**:
     - Header dengan nomor meja & icon keranjang
     - Tab kategori (All, Makanan, Minuman, Dessert)
     - Grid/Card produk dengan:
       - Foto produk
       - Nama produk
       - Harga
       - Tombol "Tambah ke Keranjang"
     - Floating cart button (badge jumlah item)
     - Search bar (optional)
   - **User Flow**: Browse menu → Pilih item → Tambah ke keranjang

---

### 3. **cart.php** - Keranjang Belanja
   - **Fungsi**: Review item yang sudah dipilih
   - **Komponen**:
     - Header "Keranjang Belanja"
     - Tabel/List item pesanan:
       - Nama produk
       - Harga satuan
       - Qty (dengan + dan -)
       - Subtotal
       - Tombol hapus item
     - Summary box:
       - Total item
       - Total harga
     - Tombol "Lanjut ke Pembayaran"
     - Tombol "Tambah Menu Lagi"
   - **User Flow**: Review cart → Update qty → Checkout

---

### 4. **checkout.php** - Konfirmasi Checkout
   - **Fungsi**: Pilih metode pembayaran
   - **Komponen**:
     - Header "Konfirmasi Checkout"
     - Info box:
       - Nomor meja
       - Total pembayaran (highlight)
     - Section "Pilih Metode Pembayaran":
       - Card button QRIS (dengan icon)
       - Card button Tunai (dengan icon)
     - Link "Kembali ke Keranjang"
   - **User Flow**: Lihat total → Pilih metode → Proses pembayaran

---

### 5. **pay_qris.php** - Pembayaran QRIS
   - **Fungsi**: Tampilkan QR Code untuk pembayaran
   - **Komponen**:
     - Icon success "Order Diterima!"
     - Info box:
       - Nomor meja
       - Total bayar (besar & highlight)
     - QR Code (besar, centered)
     - Instruksi "Scan QR Code dengan aplikasi pembayaran"
     - Tombol "Konfirmasi Pembayaran Sudah Dilakukan" (hijau)
     - Link "Cek Status Pesanan"
   - **User Flow**: Lihat QR → Scan → Bayar → Konfirmasi

---

### 6. **tunai.php** - Konfirmasi Pembayaran Tunai
   - **Fungsi**: Notifikasi pesanan berhasil, tunggu waiter
   - **Komponen**:
     - Modal/Card sukses:
       - Icon check circle (animasi)
       - Judul "Pesanan Berhasil Dibuat!"
       - Info order:
         - Kode order
         - Nomor meja
         - Total bayar
       - Box instruksi kuning:
         - "Pesanan sedang diproses"
         - "Waiter akan datang ke meja"
         - "Siapkan uang tunai"
       - Tombol "Cek Status Pesanan"
       - Tombol "Kembali ke Menu"
   - **User Flow**: Lihat konfirmasi → Tunggu waiter → Bayar tunai

---

### 7. **confirm_payment.php** - Konfirmasi Pembayaran Berhasil
   - **Fungsi**: Notifikasi pembayaran sukses
   - **Komponen**:
     - Animasi confetti
     - Icon check dalam circle (animasi pulse)
     - Judul "Pembayaran Dikonfirmasi!"
     - Detail order:
       - Kode order
       - Nomor meja
       - Metode pembayaran
       - Total dibayar
     - Status box "Pesanan sedang diproses dapur"
     - Tombol "Cek Status"
     - Tombol "Menu"
   - **User Flow**: Lihat konfirmasi → Cek status / Order lagi

---

### 8. **order_status.php** - Status Pesanan Real-time
   - **Fungsi**: Tracking status pesanan
   - **Komponen**:
     - Header "Status Pesanan"
     - Progress bar / Timeline:
       - Pending (menunggu)
       - Processing (dimasak)
       - Done (selesai)
       - Cancelled (dibatalkan)
     - Detail pesanan:
       - Kode order
       - Waktu order
       - List item
       - Total
       - Status pembayaran
     - Auto refresh status
     - Tombol "Kembali ke Menu"
   - **User Flow**: Monitor status → Tunggu selesai

---

### 9. **riwayat.php** - Riwayat Pesanan
   - **Fungsi**: Lihat history pesanan customer
   - **Komponen**:
     - Header "Riwayat Pesanan"
     - Filter status (All, Selesai, Dibatalkan)
     - List card pesanan:
       - Tanggal & waktu
       - Kode order
       - Total
       - Status (badge warna)
       - Button "Lihat Detail"
     - Empty state jika tidak ada riwayat
   - **User Flow**: Browse history → Lihat detail order lama

---

### 10. **success.php** - Halaman Sukses
   - **Fungsi**: Konfirmasi umum sukses
   - **Komponen**:
     - Icon success besar
     - Pesan sukses
     - Detail transaksi (jika ada)
     - Tombol "Kembali ke Menu"
   - **User Flow**: Lihat konfirmasi → Action selanjutnya

---

## 🖥️ B. HALAMAN ADMIN (Dashboard - Desktop/Tablet)

### 1. **login.php** - Login Admin
   - **Fungsi**: Autentikasi staff/admin
   - **Komponen**:
     - Logo/Brand
     - Judul "Login Admin"
     - Form:
       - Input username
       - Input password
       - Checkbox "Ingat saya"
       - Tombol "Login"
     - Link "Register Admin Baru" (jika diperlukan)
     - Error message area
   - **User Flow**: Input credentials → Login → Dashboard

---

### 2. **register_admin.php** - Registrasi Admin
   - **Fungsi**: Daftar admin baru
   - **Komponen**:
     - Judul "Registrasi Admin"
     - Form:
       - Input nama lengkap
       - Input username
       - Input email
       - Input password
       - Input konfirmasi password
       - Select role (admin/staff)
       - Tombol "Daftar"
     - Link "Sudah punya akun? Login"
   - **User Flow**: Isi form → Submit → Login

---

### 3. **dashboard.php** - Dashboard Utama
   - **Fungsi**: Overview statistik restoran
   - **Komponen**:
     - Sidebar navigasi:
       - Dashboard
       - Orders
       - Products
       - Categories
       - Logout
     - Top bar:
       - Nama admin
       - Notifikasi pesanan baru
       - Tanggal/waktu
     - Content area:
       - Stats cards (4 kolom):
         - Total pesanan hari ini
         - Total pendapatan
         - Pesanan pending
         - Menu terlaris
       - Chart penjualan (line/bar chart)
       - Tabel pesanan terbaru
       - Quick actions
   - **User Flow**: Lihat overview → Navigate ke menu lain

---

### 4. **orders.php** - Manajemen Pesanan
   - **Fungsi**: Kelola semua pesanan
   - **Komponen**:
     - Header "Daftar Pesanan"
     - Filter & search:
       - Filter status (All, Pending, Processing, Done)
       - Filter tanggal
       - Search by kode order
     - Tabel pesanan:
       - No
       - Kode Order
       - Meja
       - Total
       - Metode Bayar
       - Status (badge)
       - Waktu
       - Actions:
         - Lihat detail
         - Update status
         - Print nota
     - Pagination
     - Auto refresh (real-time)
   - **User Flow**: Filter → Lihat pesanan → Update status

---

### 5. **orders_detail.php** - Detail Pesanan
   - **Fungsi**: Lihat detail lengkap 1 pesanan
   - **Komponen**:
     - Header "Detail Pesanan #[CODE]"
     - Info order:
       - Kode order
       - Nomor meja
       - Waktu order
       - Status (dropdown untuk update)
     - Tabel item pesanan:
       - Nama produk
       - Qty
       - Harga
       - Subtotal
     - Summary:
       - Total item
       - Total bayar
       - Metode pembayaran
     - Timeline status
     - Tombol actions:
       - Update status
       - Print nota
       - Cancel order
     - Tombol "Kembali"
   - **User Flow**: Lihat detail → Update status → Save

---

### 6. **product.php** - Manajemen Produk
   - **Fungsi**: CRUD menu makanan/minuman
   - **Komponen**:
     - Header "Manajemen Produk"
     - Tombol "Tambah Produk Baru"
     - Filter kategori
     - Search produk
     - Tabel produk:
       - Foto thumbnail
       - Nama
       - Kategori
       - Harga
       - Stock/Status
       - Actions:
         - Edit
         - Hapus
         - Toggle active/inactive
     - Modal form add/edit:
       - Input nama
       - Select kategori
       - Input harga
       - Upload foto
       - Textarea deskripsi
       - Checkbox active
       - Tombol Save/Cancel
   - **User Flow**: Browse → Add/Edit → Save → List update

---

### 7. **categories.php** - Manajemen Kategori
   - **Fungsi**: CRUD kategori produk
   - **Komponen**:
     - Header "Manajemen Kategori"
     - Tombol "Tambah Kategori"
     - Tabel kategori:
       - ID
       - Nama kategori
       - Jumlah produk
       - Actions:
         - Edit
         - Hapus
     - Modal form add/edit:
       - Input nama kategori
       - Textarea deskripsi
       - Tombol Save/Cancel
   - **User Flow**: Browse → Add/Edit kategori → Save

---

## 📋 RINGKASAN UNTUK BALSAMIQ

### Total Halaman: 17 Mockup

**Customer (Mobile)**: 10 halaman
1. Landing Page
2. Menu
3. Cart
4. Checkout
5. Pay QRIS
6. Pay Tunai
7. Confirm Payment
8. Order Status
9. Riwayat
10. Success

**Admin (Desktop)**: 7 halaman
1. Login
2. Register
3. Dashboard
4. Orders List
5. Order Detail
6. Products
7. Categories

---

## 🎨 TIPS MOCKUP BALSAMIQ

### Prioritas Mockup:
**HIGH (Wajib):**
- Landing Page
- Menu
- Cart
- Checkout
- Pay QRIS / Tunai
- Dashboard Admin
- Orders Management

**MEDIUM:**
- Order Status
- Confirm Payment
- Order Detail Admin
- Product Management

**LOW (Optional):**
- Riwayat
- Success Page
- Categories
- Register Admin

### Design Guidelines:
- **Customer**: Mobile-first (320-375px width)
- **Admin**: Desktop/Tablet (1024px+ width)
- **Colors**: Indigo & Orange (sesuai tema existing)
- **Icons**: Feather Icons
- **Font**: Inter
- **Style**: Modern, Clean, Minimal

---

## 📝 CATATAN

Mockup ini akan digunakan untuk:
1. Presentasi bimbingan
2. Dokumentasi skripsi
3. Panduan development future features
4. Portfolio project

Fokus pada **user experience** dan **information architecture** 
yang jelas di setiap halaman.
