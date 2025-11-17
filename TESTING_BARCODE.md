# 🧪 Testing QR Code Barcode Check-in

## 📝 Checklist Testing

### 1. Setup Awal ✅
- [ ] XAMPP sudah running (Apache + MySQL)
- [ ] Database `cafe_ordering` sudah ada
- [ ] Tabel `tables` sudah ada dengan data meja
- [ ] Composer package `endroid/qr-code` sudah ter-install

### 2. Test Admin Side

#### A. Login Admin
```
URL: http://localhost/cafe_ordering/admin/login.php
```
- [ ] Login dengan akun admin
- [ ] Dashboard tampil dengan menu "Meja"

#### B. Management Meja (`/admin/tables.php`)
```
URL: http://localhost/cafe_ordering/admin/tables.php
```

**Test Tambah Meja:**
- [ ] Klik tombol "+ Tambah Meja"
- [ ] Isi nama: `MEJA 6`
- [ ] Isi kode: `TBL-006`
- [ ] Submit → Berhasil ditambahkan
- [ ] Muncul di list tabel

**Test Edit Meja:**
- [ ] Klik tombol "Edit" pada salah satu meja
- [ ] Ubah nama meja
- [ ] Submit → Berhasil diupdate
- [ ] Perubahan tersimpan

**Test Hapus Meja:**
- [ ] Klik tombol "Hapus" pada meja test
- [ ] Konfirmasi hapus
- [ ] Meja terhapus dari list

**Test Lihat QR Individual:**
- [ ] Klik tombol "Lihat QR" pada salah satu meja
- [ ] QR Code tampil di modal
- [ ] Klik kanan → "Save Image As..." berfungsi
- [ ] Tutup modal

#### C. Generate All QR Codes (`/admin/generate_qr/`)
```
URL: http://localhost/cafe_ordering/admin/generate_qr/
```
- [ ] Tampil grid semua QR Code
- [ ] Setiap meja punya QR Code sendiri
- [ ] Klik "Print Semua" → Preview print tampil
- [ ] Klik kanan pada QR → Download berfungsi

### 3. Test Customer Side

#### A. Halaman Utama
```
URL: http://localhost/cafe_ordering/public/
```
- [ ] Tampil tombol "Scan QR Code Meja" (gradien ungu-biru)
- [ ] Tombol responsive dan hover effect bekerja
- [ ] Kartu pilihan meja manual tetap berfungsi

#### B. Halaman Scan QR (`/public/scan.php`)
```
URL: http://localhost/cafe_ordering/public/scan.php
```

**Test Camera Access:**
- [ ] Klik tombol "Scan QR Code Meja"
- [ ] Browser minta izin akses kamera
- [ ] Izinkan akses kamera
- [ ] Preview kamera tampil
- [ ] Status berubah: "Kamera aktif"

**Test Scan QR Code:**

*Cara 1: Scan dari Print/Monitor Lain*
- [ ] Print atau tampilkan QR Code di device lain
- [ ] Arahkan kamera ke QR Code
- [ ] QR Code terdeteksi otomatis
- [ ] Status: "Berhasil! Mengarahkan ke menu..."
- [ ] Auto-redirect ke `menu.php`
- [ ] Session meja sudah tersimpan (cek di menu)

*Cara 2: Test dengan URL Manual*
- [ ] Buka console browser (F12)
- [ ] Ketik: `window.location.href = 'http://localhost/cafe_ordering/public/index.php?code=TBL-001'`
- [ ] Redirect ke menu dengan meja TBL-001 terpilih

**Test Fallback:**
- [ ] Klik "Pilih Meja Manual"
- [ ] Redirect ke halaman index
- [ ] Bisa pilih meja manual

#### C. Test Integration dengan Menu
```
URL: http://localhost/cafe_ordering/public/menu.php
```
Setelah scan QR:
- [ ] Nomor meja tampil di navbar atau header
- [ ] Session `table_id` dan `table_number` tersimpan
- [ ] Bisa add to cart
- [ ] Checkout mencatat nomor meja

### 4. Test Responsive

#### Desktop (1920x1080)
- [ ] Scan page: Camera preview centered
- [ ] Tables page: Grid 3 kolom
- [ ] QR Modal: Ukuran pas

#### Tablet (768x1024)
- [ ] Navigation responsive
- [ ] Grid 2 kolom
- [ ] Touch friendly buttons

#### Mobile (375x667)
- [ ] Camera full width
- [ ] Grid 1 kolom
- [ ] Auto-detect back camera
- [ ] Scan area optimal

### 5. Test Error Handling

**Invalid QR Code:**
- [ ] Scan QR code random (bukan dari sistem)
- [ ] Error message muncul
- [ ] Tidak crash

**QR Code Meja Tidak Ditemukan:**
- [ ] URL manual: `?code=TBL-999` (tidak exist)
- [ ] Error message: "Kode meja tidak valid"
- [ ] Redirect ke index

**No Camera Access:**
- [ ] Block camera permission
- [ ] Error message: "Gagal mengakses kamera"
- [ ] Fallback button tetap muncul

**Duplicate Table Code:**
- [ ] Coba tambah meja dengan kode yang sudah ada
- [ ] Error: "Gagal menambahkan meja"
- [ ] Data tidak tersimpan

### 6. Test Security

**Admin Authentication:**
- [ ] Logout dari admin
- [ ] Akses `/admin/tables.php` tanpa login
- [ ] Redirect ke login page

**SQL Injection Prevention:**
- [ ] Input `TBL-001' OR '1'='1` di kode meja
- [ ] Tidak ada error SQL
- [ ] Input di-sanitize

**XSS Prevention:**
- [ ] Input `<script>alert('XSS')</script>` di nama meja
- [ ] Script tidak dijalankan
- [ ] Di-escape sebagai text

### 7. Test Performance

**QR Generation Speed:**
- [ ] Generate 10 QR codes sekaligus
- [ ] Load time < 3 detik
- [ ] Tidak freeze browser

**Scan Speed:**
- [ ] Scan QR Code
- [ ] Detection time < 2 detik
- [ ] Redirect smooth

## 🎯 Expected Results

✅ **Success Criteria:**
- Admin bisa CRUD meja
- QR Code ter-generate dengan benar
- Customer bisa scan dan auto check-in
- Session meja tersimpan persistent
- Error handling proper
- Mobile responsive
- No security vulnerabilities

## 📱 Testing Devices

Tested on:
- [ ] Chrome Desktop (Windows/Mac)
- [ ] Safari Mobile (iOS)
- [ ] Chrome Mobile (Android)
- [ ] Firefox Desktop
- [ ] Edge Desktop

## 🐛 Known Issues

*Catat bug yang ditemukan di sini:*

1. Issue: 
   - Description:
   - Steps to reproduce:
   - Expected vs Actual:

## 📊 Test Summary

- Total Tests: 50+
- Passed: ___
- Failed: ___
- Skipped: ___
- Coverage: ___%

---

**Testing Date:** __________  
**Tester:** __________  
**Version:** 1.0.0  

Happy Testing! 🧪✨
