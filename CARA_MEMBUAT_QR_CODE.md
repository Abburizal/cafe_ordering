# 📱 Cara Membuat dan Menggunakan QR Code untuk Meja

## 🎯 Langkah-langkah Generate QR Code

### **Metode 1: Generate Semua QR Code Sekaligus** (Recommended)

#### 1️⃣ Login ke Admin Panel
```
URL: http://localhost/cafe_ordering/admin/login.php
```
- Masukkan username dan password admin
- Klik Login

#### 2️⃣ Buka Halaman Generate QR Code
Ada 2 cara:

**Cara A: Dari Menu Tables**
```
1. Klik menu "Meja" di navigation bar
2. Klik tombol "Lihat Semua QR Code"
```

**Cara B: Direct URL**
```
http://localhost/cafe_ordering/admin/generate_qr/
```

#### 3️⃣ Download/Print QR Code

**Opsi 1: Print Langsung**
- Klik tombol **"🖨️ Print Semua"**
- Akan muncul preview print
- Pilih printer atau "Save as PDF"
- Print atau simpan PDF

**Opsi 2: Download Individual**
- Klik kanan pada gambar QR Code meja yang diinginkan
- Pilih **"Save Image As..."**
- Simpan dengan nama: `QR_MEJA_1.png`, `QR_MEJA_2.png`, dst
- Format: PNG, ukuran: 300x300 pixels

---

### **Metode 2: Generate QR Code Per Meja**

#### 1️⃣ Buka Management Meja
```
URL: http://localhost/cafe_ordering/admin/tables.php
```

#### 2️⃣ Lihat QR Individual
- Cari meja yang diinginkan di tabel
- Klik tombol **"Lihat QR"** (warna ungu)
- QR Code akan muncul di modal popup

#### 3️⃣ Download QR Code
- Klik kanan pada gambar QR
- Pilih **"Save Image As..."**
- Simpan dengan nama yang jelas

---

## 🖨️ Cara Print QR Code

### **Print via Browser (PDF)**
1. Buka halaman generate QR: `admin/generate_qr/`
2. Klik tombol **"Print Semua"**
3. Di dialog print, pilih **"Save as PDF"**
4. Akan tersimpan sebagai PDF dengan semua QR Code
5. Buka PDF dan print sesuai kebutuhan

### **Print Individual**
1. Download QR Code untuk setiap meja (format PNG)
2. Buka di aplikasi editor (Photoshop, Canva, Word, PowerPoint)
3. Buat desain kartu dengan:
   - QR Code di tengah
   - Nama meja (MEJA 1, MEJA 2, dll)
   - Instruksi: "Scan untuk Order"
   - Logo/branding cafe (opsional)
4. Print di kertas:
   - Kertas glossy (recommended)
   - Kertas sticker
   - Kertas karton

### **Template Kartu (Saran)**
```
┌─────────────────────────────┐
│                             │
│       [Logo Cafe]           │
│                             │
│     ┌───────────┐           │
│     │           │           │
│     │ QR CODE   │           │
│     │           │           │
│     └───────────┘           │
│                             │
│       MEJA 1                │
│                             │
│  "Scan untuk Pesan Menu"    │
│                             │
└─────────────────────────────┘
```

---

## 📐 Ukuran Rekomendasi

### **Ukuran QR Code:**
- Minimal: 5cm x 5cm
- Optimal: 8cm x 8cm  
- Maksimal: 15cm x 15cm

### **Ukuran Kartu:**
- A6 (10.5cm x 14.8cm) - Standard postcard
- A5 (14.8cm x 21cm) - Tabel tent
- Custom sesuai kebutuhan

---

## 📍 Cara Menempatkan QR Code di Meja

### **Opsi 1: Table Tent (Standing Card)**
1. Print QR Code di kertas karton A5
2. Lipat menjadi bentuk segitiga/tenda
3. Taruh di tengah meja
4. **Keuntungan**: Mudah dilihat, tidak rusak

### **Opsi 2: Sticker**
1. Print QR Code di kertas sticker
2. Tempel langsung di permukaan meja
3. **Keuntungan**: Tidak bisa hilang
4. **Kekurangan**: Susah diganti

### **Opsi 3: Akrilik Holder**
1. Print QR Code ukuran standar
2. Masukkan ke holder akrilik bening
3. Taruh di meja atau tempel di dinding
4. **Keuntungan**: Professional, mudah ganti

### **Opsi 4: Laminating**
1. Print QR Code
2. Laminating dengan plastik glossy
3. Tempel dengan double tape
4. **Keuntungan**: Tahan air, awet

---

## ✅ Checklist Sebelum Deploy

### **Testing QR Code:**
- [ ] Print QR Code ukuran test (5x5cm)
- [ ] Scan dengan 3 smartphone berbeda
- [ ] Pastikan redirect ke halaman menu
- [ ] Check jarak scan optimal (10-30cm)
- [ ] Test di pencahayaan berbeda

### **Quality Check:**
- [ ] QR Code tidak blur
- [ ] Tidak ada lipatan di QR Code
- [ ] Kontras baik (hitam-putih jelas)
- [ ] Tidak tertutup plastik reflektif

### **Placement:**
- [ ] QR Code mudah terlihat customer
- [ ] Tidak tertutup menu atau hiasan lain
- [ ] Tinggi sesuai (mudah di-scan sambil duduk)
- [ ] Aman dari tumpahan minuman

---

## 🧪 Cara Test QR Code

### **Test 1: Scan dengan Smartphone**
1. Buka kamera smartphone
2. Arahkan ke QR Code
3. Tap notifikasi yang muncul
4. Harus redirect ke: `http://localhost/cafe_ordering/public/index.php?code=TBL-001`
5. Harus langsung masuk menu dengan meja terpilih

### **Test 2: Scan dengan App QR Scanner**
1. Download app: "QR Code Reader" atau buka `scan.php`
2. Scan QR Code
3. Check URL yang terdeteksi
4. Pastikan formatnya benar

### **Test 3: Jarak Scan**
- **Terlalu dekat**: < 5cm - Sulit focus
- **Optimal**: 10-20cm - Perfect
- **Terlalu jauh**: > 50cm - Tidak terdeteksi

---

## 🛠️ Troubleshooting

### **QR Code tidak terbaca**
✅ **Solusi:**
- Pastikan pencahayaan cukup
- Bersihkan kamera smartphone
- Pegang smartphone stabil
- Coba jarak 10-20cm
- Pastikan QR Code tidak blur saat print

### **Redirect ke halaman error**
✅ **Solusi:**
- Check URL di QR Code benar
- Pastikan meja dengan kode tersebut ada di database
- Check koneksi internet (jika production)

### **QR Code rusak/hilang**
✅ **Solusi:**
- Generate ulang dari admin panel
- Selalu simpan backup file PNG semua QR Code
- Consider print cadangan

---

## 💡 Tips Pro

### **Untuk Cafe/Restaurant:**
1. **Buat backup** - Simpan semua file PNG QR Code di folder khusus
2. **Laminating** - Protect QR Code dari air dan kotoran
3. **Multiple placement** - QR Code di meja + di dinding dekat meja
4. **Clear instruction** - Tambahkan text "Scan QR Code untuk Menu"
5. **Train staff** - Ajarkan staff cara bantu customer yang kesulitan scan

### **Design Tips:**
- Gunakan warna kontras (hitam QR Code, background putih)
- Jangan tambah logo di tengah QR Code (bisa ganggu scan)
- Border putih minimal 1cm di sekitar QR Code
- Font jelas dan besar untuk nama meja

### **Maintenance:**
- Check QR Code setiap minggu (kondisi fisik)
- Replace yang rusak/pudar
- Update jika ganti domain/URL
- Test scan berkala

---

## 📊 Monitoring

### **Yang Perlu Dimonitor:**
- Berapa customer yang scan vs pilih manual
- Meja mana yang paling sering di-scan
- Waktu check-in customer
- Error rate scan QR

### **Future Enhancement:**
- [ ] Add analytics tracking per QR scan
- [ ] Dynamic QR Code (bisa update tanpa print ulang)
- [ ] QR Code dengan promo khusus
- [ ] Multi-language QR landing page

---

## 📞 Support

Jika ada masalah atau pertanyaan:
1. Cek dokumentasi: `FITUR_BARCODE_CHECKIN.md`
2. Testing guide: `TESTING_BARCODE.md`
3. Check console browser untuk error (F12)

---

## 🎯 Quick Reference

| Aksi | URL |
|------|-----|
| Generate All QR | `/admin/generate_qr/` |
| Management Meja | `/admin/tables.php` |
| Scan QR Customer | `/public/scan.php` |
| API Generate QR | `/admin/api/generate_qr.php?code=TBL-001` |

---

**Happy Scanning! 📱✨**

*Last Updated: 2025-01-17*
