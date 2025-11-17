# 📱 Fitur Barcode/QR Code Check-in Meja

## 🎯 Deskripsi
Sistem QR Code Check-in memungkinkan customer untuk scan QR Code yang ada di meja menggunakan smartphone mereka dan langsung diarahkan ke menu dengan nomor meja yang sudah terotomatis.

## ✨ Fitur Utama

### 1. **Customer Side**
- ✅ Halaman scan QR Code dengan kamera (`/public/scan.php`)
- ✅ Tombol "Scan QR Code Meja" di halaman utama
- ✅ Auto-detect kamera belakang di mobile
- ✅ Fallback ke pilihan meja manual jika gagal scan
- ✅ Real-time QR code detection

### 2. **Admin Side**
- ✅ Halaman kelola meja (`/admin/tables.php`)
- ✅ CRUD (Create, Read, Update, Delete) meja
- ✅ Generate QR Code individual per meja
- ✅ Generate QR Code semua meja sekaligus
- ✅ Print/Download QR Code
- ✅ Preview QR Code sebelum print

## 📁 File-file yang Dibuat

```
cafe_ordering/
├── public/
│   └── scan.php                    # Halaman scan QR Code customer
├── admin/
│   ├── tables.php                  # Management meja + QR Code
│   ├── api/
│   │   └── generate_qr.php         # API generate QR Code
│   └── generate_qr/                # Generate semua QR Code (updated)
└── FITUR_BARCODE_CHECKIN.md        # Dokumentasi ini
```

## 🚀 Cara Menggunakan

### Untuk Admin:

1. **Kelola Meja**
   - Login ke admin panel
   - Buka menu **"Meja"** di navigation bar
   - Atau akses: `http://localhost/cafe_ordering/admin/tables.php`

2. **Tambah Meja Baru**
   - Klik tombol **"+ Tambah Meja"**
   - Isi nama meja (contoh: MEJA 1)
   - Isi kode meja (contoh: TBL-001) - harus unik
   - Klik **"Tambah"**

3. **Edit/Hapus Meja**
   - Klik tombol **"Edit"** untuk mengubah data meja
   - Klik tombol **"Hapus"** untuk menghapus meja

4. **Generate QR Code**
   - **Per Meja**: Klik tombol **"Lihat QR"** pada meja yang diinginkan
   - **Semua Meja**: Klik **"Lihat Semua QR Code"** di bagian atas
   - Klik kanan pada gambar QR → "Save Image As..." untuk download
   - Atau klik tombol **"Print Semua"** untuk print

### Untuk Customer:

1. **Scan QR Code**
   - Buka: `http://localhost/cafe_ordering/public/` atau `scan.php`
   - Klik tombol **"Scan QR Code Meja"**
   - Izinkan akses kamera
   - Arahkan kamera ke QR Code di meja
   - Otomatis redirect ke menu dengan meja sudah terpilih

2. **Pilih Meja Manual** (jika scan gagal)
   - Klik **"Pilih Meja Manual"**
   - Pilih kartu meja yang diinginkan

## 🔧 Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **QR Code Generator**: `endroid/qr-code` (Composer package)
- **QR Code Scanner**: `html5-qrcode` library (JavaScript)
- **Styling**: TailwindCSS

## 📋 Database

Tabel `tables` sudah ada dengan struktur:
```sql
CREATE TABLE `tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` varchar(50) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
);
```

## 🔗 URL Pattern

QR Code menghasilkan URL dengan format:
```
http://localhost/cafe_ordering/public/index.php?code=TBL-001
```

Sistem akan:
1. Validasi kode meja di database
2. Set session `table_id` dan `table_number`
3. Redirect ke halaman menu

## 🎨 UI/UX Features

### Halaman Scan
- Gradient background modern
- Camera preview dengan border radius
- Status indicator (loading, success, error)
- Instructions panel
- Fallback button untuk manual selection

### Halaman Admin Tables
- Modal-based forms
- Responsive grid layout
- Hover effects on cards
- Real-time QR code preview
- Print-friendly styling

## 🔐 Security

- ✅ Admin authentication required
- ✅ PDO prepared statements (SQL injection protection)
- ✅ Input validation dan sanitization
- ✅ Session-based table assignment

## 📱 Mobile Support

- ✅ Responsive design
- ✅ Auto-detect back camera on mobile devices
- ✅ Touch-friendly buttons
- ✅ Mobile-optimized QR scanner

## 🐛 Troubleshooting

### QR Code tidak terbaca
- Pastikan pencahayaan cukup
- Jaga jarak 10-30cm dari kamera
- Pastikan QR Code tidak blur atau rusak

### Kamera tidak berfungsi
- Izinkan akses kamera di browser
- Pastikan tidak ada aplikasi lain yang menggunakan kamera
- Gunakan HTTPS atau localhost (HTTP tidak support camera API)

### QR Code tidak generate
- Pastikan composer package `endroid/qr-code` ter-install
- Jalankan: `composer require endroid/qr-code`

## 📝 Notes

- QR Code size: 300x300 pixels
- Margin: 10 pixels
- Format: PNG
- Error correction: Default (Medium)

## 🎯 Future Improvements

- [ ] Bulk import meja dari CSV
- [ ] QR Code dengan logo restaurant
- [ ] Statistik scan per meja
- [ ] Expired QR Code dengan timestamp
- [ ] Multi-language QR Code landing page

## 👨‍💻 Developer

Created by: Satriyo Nugroho
Date: 2025

---

**Happy Scanning! 📱✨**
