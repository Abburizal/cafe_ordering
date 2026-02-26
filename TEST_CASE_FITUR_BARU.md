# 🧪 TEST CASE - Implementasi Fitur Baru

## Test Scenario 1: Login & Checkout Flow

| No | Test Case | Steps | Expected Result | Status |
|----|-----------|-------|-----------------|--------|
| 1.1 | Klik "Bayar" saat Guest (Belum Login) | 1. Buka `/public/cart.php`<br>2. Tambah produk<br>3. Klik "Checkout" | Sistem mengarahkan ke halaman Login | ⬜ |
| 1.2 | Login dengan Akun Valid | 1. Di halaman login<br>2. Input: `user@gmail.com`<br>3. Input password: `pass123`<br>4. Klik "Masuk" | Login berhasil, sistem mengembalikan user ke halaman Checkout (bukan Dashboard) | ⬜ |
| 1.3 | Login dengan Password Salah | 1. Di halaman login<br>2. Input: `user@gmail.com`<br>3. Input password salah: `wrongpass`<br>4. Klik "Masuk" | Menampilkan pesan "Email atau Password Salah". User tetap di halaman login | ⬜ |
| 1.4 | Register Akun Baru | 1. Klik "Daftar Sekarang"<br>2. Isi semua field<br>3. Klik "Daftar Sekarang" | Auto-login, redirect ke checkout | ⬜ |

---

## Test Scenario 2: Notifikasi Real-time Admin

| No | Test Case | Steps | Expected Result | Status |
|----|-----------|-------|-----------------|--------|
| 2.1 | Notifikasi Order Baru | 1. Buka tab 1: `/admin/orders.php`<br>2. Buka tab 2: `/public/menu.php`<br>3. Di tab 2: Pelanggan buat order baru (Status: Pending)<br>4. Tunggu max 10 detik | Di tab 1 (Admin) muncul Pop-up Toast dan suara notifikasi "Ting" dalam <10 detik | ⬜ |
| 2.2 | Multiple Notifications | 1. Admin page tetap buka<br>2. Buat 3 order baru berturut-turut<br>3. Amati notifikasi | Setiap order memicu notifikasi terpisah | ⬜ |
| 2.3 | Sound Notification | 1. Pastikan volume device ON<br>2. Customer buat order<br>3. Dengarkan | Suara "ting" terdengar jelas di Admin | ⬜ |

---

## Test Scenario 3: Validasi CRUD Produk

| No | Test Case | Steps | Expected Result | Status |
|----|-----------|-------|-----------------|--------|
| 3.1 | Tambah Produk Valid | 1. Buka `/admin/product.php`<br>2. Isi Nama: "Nasi Goreng"<br>3. Isi Harga: "25000"<br>4. Pilih Kategori (jika ada)<br>5. Upload gambar JPG<br>6. Klik "Simpan" | Data tersimpan di database, muncul notifikasi "Berhasil Menambahkan Menu", dan produk tampil di daftar menu | ⬜ |
| 3.2 | Hapus Produk dengan Konfirmasi | 1. Pilih salah satu produk<br>2. Klik tombol "Hapus"<br>3. Konfirmasi alert | Data produk terhapus dari database dan hilang dari tampilan daftar menu | ⬜ |
| 3.3 | Validasi Field Kosong - Nama | 1. Klik "Tambah Produk"<br>2. Kosongkan "Nama Produk"<br>3. Isi field lain<br>4. Klik "Simpan" | Sistem menolak penyimpanan dan menampilkan pesan validasi "Bidang Nama Produk wajib diisi" | ⬜ |
| 3.4 | Validasi Field Kosong - Harga | 1. Klik "Tambah Produk"<br>2. Kosongkan "Harga"<br>3. Isi field lain<br>4. Klik "Simpan" | Sistem menolak penyimpanan dan menampilkan pesan validasi "Bidang Harga wajib diisi" | ⬜ |
| 3.5 | Validasi Format File - PDF | 1. Klik "Tambah Produk"<br>2. Isi semua field<br>3. Upload file `.PDF`<br>4. Klik "Simpan" | Sistem menolak file dan menampilkan pesan "Format file harus JPG/PNG/GIF/WEBP" | ⬜ |
| 3.6 | Validasi Format File - DOCX | 1. Klik "Tambah Produk"<br>2. Isi semua field<br>3. Upload file `.DOCX`<br>4. Klik "Simpan" | Sistem menolak file dan menampilkan pesan "Format file harus JPG/PNG/GIF/WEBP" | ⬜ |
| 3.7 | Validasi Ukuran File | 1. Klik "Tambah Produk"<br>2. Isi semua field<br>3. Upload file gambar >5MB<br>4. Klik "Simpan" | Sistem menolak dan menampilkan pesan "Ukuran file maksimal 5MB!" | ⬜ |

---

## Test Scenario 4: QR Code Management

| No | Test Case | Steps | Expected Result | Status |
|----|-----------|-------|-----------------|--------|
| 4.1 | Generate QR Code | 1. Buka `/admin/tables.php`<br>2. Input Nomor Meja: "Meja 10"<br>3. Input Code: "TBL-010"<br>4. Klik "Tambah Meja"<br>5. Klik "Generate QR" | Sistem menyimpan nomor meja dan secara otomatis membuat file gambar QR Code unik | ⬜ |
| 4.2 | Download QR Code | 1. Pilih meja yang sudah ada<br>2. Klik tombol "Download QR" | Sistem mengunduh file gambar QR Code (.png) ke perangkat lokal. File dapat dipindai dengan HP | ⬜ |
| 4.3 | Scan QR Code | 1. Download QR code<br>2. Print atau tampilkan di layar<br>3. Scan dengan HP | HP redirect ke `/public/menu.php?table=TBL-010` dan session table tersimpan | ⬜ |
| 4.4 | Validasi Duplikat Nomor Meja | 1. Input Nomor Meja: "Meja 1" (sudah ada)<br>2. Input Code: "TBL-NEW"<br>3. Klik "Tambah Meja" | Sistem menolak input dan menampilkan pesan "Nomor Meja sudah terdaftar" | ⬜ |
| 4.5 | Validasi Duplikat Code | 1. Input Nomor Meja: "Meja 99"<br>2. Input Code: "TBL-001" (sudah ada)<br>3. Klik "Tambah Meja" | Sistem menolak input dan menampilkan pesan "Kode Meja sudah terdaftar" | ⬜ |

---

## 📝 Testing Notes

### Pre-requisites:
- [ ] Database `cafe_ordering` sudah ter-setup
- [ ] Tabel `users` sudah ada
- [ ] Tabel `tables` sudah ada dengan kolom UNIQUE pada `code`
- [ ] Composer package `endroid/qr-code` sudah ter-install
- [ ] Admin account tersedia (username: admin, password: admin123)

### Test Accounts:
**Customer:**
- Email: `user@gmail.com`
- Password: `pass123`

**Admin:**
- Username: `admin`
- Password: `admin123`

### Testing Environment:
- URL Base: `http://localhost/cafe_ordering/`
- Browser: Chrome/Firefox (latest version)
- PHP Version: 7.4 atau lebih tinggi
- MySQL Version: 5.7 atau lebih tinggi

---

## 📊 Test Result Summary

| Category | Total Tests | Passed | Failed | Pending |
|----------|-------------|--------|--------|---------|
| Login & Checkout | 4 | 0 | 0 | 4 |
| Notifikasi Real-time | 3 | 0 | 0 | 3 |
| Validasi CRUD Produk | 7 | 0 | 0 | 7 |
| QR Code Management | 5 | 0 | 0 | 5 |
| **TOTAL** | **19** | **0** | **0** | **19** |

---

## ✅ Checklist Before Testing

- [ ] XAMPP/WAMP sudah running
- [ ] Apache dan MySQL aktif
- [ ] Database sudah di-import
- [ ] File sudah di-upload ke htdocs
- [ ] Composer dependencies sudah ter-install
- [ ] Browser cache sudah di-clear
- [ ] Volume device sudah ON (untuk test sound)

---

## 🐛 Bug Report Template

Jika menemukan bug saat testing, gunakan format ini:

```markdown
**Bug Title:** [Judul singkat bug]

**Test Case:** [Nomor test case, misal: 3.5]

**Steps to Reproduce:**
1. [Step 1]
2. [Step 2]
3. [Step 3]

**Expected Result:**
[Apa yang seharusnya terjadi]

**Actual Result:**
[Apa yang benar-benar terjadi]

**Screenshot:**
[Attach screenshot jika ada]

**Browser/Device:**
[Chrome 120, Windows 10]

**Error Message (if any):**
[Copy paste error message]
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-02-03  
**Total Test Cases:** 19  
**Estimated Testing Time:** 30-45 minutes
