# 📱 Panduan Membuat QR Code Manual dengan IP Address

## 🎯 Kenapa Pakai IP Address?

Ketika menggunakan QR code di cafe/restaurant, customer akan scan QR dari **smartphone mereka sendiri** yang terhubung ke **WiFi yang sama**. Smartphone tidak bisa mengakses `localhost`, jadi kita harus pakai **IP Address** komputer server.

---

## 🔍 IP Address Anda

**IP Address Komputer Server:** `192.168.1.27`

> **Catatan:** IP ini valid selama komputer dan smartphone terhubung ke WiFi yang sama.

---

## 📋 Daftar Link untuk QR Code

### **Format Link yang Benar:**

Gunakan format ini untuk membuat QR Code di https://myqrcode.com/generator

#### **Opsi 1: Direct ke Menu (Recommended)**
```
Meja 1:  http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-001
Meja 2:  http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-002
Meja 3:  http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-003
Meja 4:  http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-004
Meja 5:  http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-005
Meja 6:  http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-006
Meja 7:  http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-007
Meja 8:  http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-008
Meja 9:  http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-009
Meja 10: http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-010
```

#### **Opsi 2: Via Table ID (Alternative)**
```
Meja 1:  http://192.168.1.27/cafe_ordering/public/index.php?table=1
Meja 2:  http://192.168.1.27/cafe_ordering/public/index.php?table=2
Meja 3:  http://192.168.1.27/cafe_ordering/public/index.php?table=3
Meja 4:  http://192.168.1.27/cafe_ordering/public/index.php?table=4
Meja 5:  http://192.168.1.27/cafe_ordering/public/index.php?table=5
```

---

## 🎨 Cara Membuat QR Code di MyQRCode.com

### **Langkah 1: Buka Website**
1. Buka browser: https://myqrcode.com/generator
2. Atau gunakan alternatif: https://www.qr-code-generator.com/

### **Langkah 2: Pilih Type "URL"**
- Pilih tipe: **URL** (bukan Text atau vCard)

### **Langkah 3: Masukkan Link**
**Untuk MEJA 1:**
```
http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-001
```

**Untuk MEJA 2:**
```
http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-002
```

Dan seterusnya... (ganti kode meja sesuai kebutuhan)

### **Langkah 4: Customize QR Code (Opsional)**
- **Size:** 300x300px atau lebih besar
- **Error Correction:** Medium (M) atau High (H)
- **Color:** Hitam (default) - untuk scan terbaik
- **Add Logo:** Boleh tambah logo cafe (jangan terlalu besar)

### **Langkah 5: Download**
- Klik **Download** atau **Generate**
- Format: **PNG** (recommended) atau JPG
- Simpan dengan nama: `QR_MEJA_1.png`, `QR_MEJA_2.png`, dst

---

## 📐 Template Excel untuk Tracking

Buat file Excel untuk tracking semua link:

| No | Nama Meja | Kode Meja | Link QR Code | Status Print |
|----|-----------|-----------|--------------|--------------|
| 1  | MEJA 1    | TBL-001   | http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-001 | ☐ |
| 2  | MEJA 2    | TBL-002   | http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-002 | ☐ |
| 3  | MEJA 3    | TBL-003   | http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-003 | ☐ |
| 4  | MEJA 4    | TBL-004   | http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-004 | ☐ |
| 5  | MEJA 5    | TBL-005   | http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-005 | ☐ |

---

## 🖨️ Cara Print QR Code

### **Opsi A: Print Langsung**
1. Download semua QR Code PNG
2. Masukkan ke Word/PowerPoint
3. Tambahkan:
   - Nama Meja (MEJA 1, MEJA 2, dll)
   - Instruksi: "Scan untuk Menu"
   - Logo Cafe (opsional)
4. Print di kertas glossy atau karton

### **Opsi B: Desain di Canva**
1. Buka Canva.com
2. Buat design custom (10cm x 14cm)
3. Upload QR Code PNG
4. Tambah text dan desain
5. Download dan print

### **Template Desain Kartu:**
```
┌─────────────────────────────────┐
│                                 │
│        [LOGO CAFE]              │
│                                 │
│      ┌─────────────┐            │
│      │             │            │
│      │  QR CODE    │            │
│      │             │            │
│      └─────────────┘            │
│                                 │
│         MEJA 1                  │
│                                 │
│  "Scan QR Code untuk Menu"     │
│  Connect WiFi: [NAMA_WIFI]     │
│                                 │
└─────────────────────────────────┘
```

---

## ⚙️ Setup XAMPP untuk Akses dari Smartphone

### **1. Start XAMPP Services**
```
✅ Apache (port 80)
✅ MySQL (port 3306)
```

### **2. Allow Firewall (Penting!)**

**macOS:**
```bash
# Check if port 80 is accessible
sudo lsof -i :80

# Allow incoming connections
# System Preferences → Security & Privacy → Firewall → Firewall Options
# Allow XAMPP/Apache
```

**Windows:**
1. Windows Defender Firewall
2. Allow an app: XAMPP Apache
3. Allow on Private networks

### **3. Test dari Komputer**
Buka browser di komputer yang sama:
```
http://192.168.1.27/cafe_ordering/public/
```

Harus bisa akses halaman utama ✅

### **4. Test dari Smartphone**
**Penting:** Smartphone harus terhubung ke **WiFi yang sama** dengan komputer!

1. Connect smartphone ke WiFi yang sama
2. Buka browser di smartphone
3. Ketik: `http://192.168.1.27/cafe_ordering/public/`
4. Jika berhasil → Lanjut ke step berikutnya
5. Jika tidak bisa → Cek firewall atau WiFi

---

## 🧪 Testing QR Code

### **Test 1: Generate dan Test Link**
1. Generate QR Code untuk MEJA 1
2. Scan dengan smartphone
3. Harus redirect ke: menu dengan MEJA 1 terpilih

### **Test 2: Copy-Paste Link**
Sebelum buat QR, test link manual:
1. Copy link: `http://192.168.1.27/cafe_ordering/public/index.php?code=TBL-001`
2. Send ke smartphone via WhatsApp/Telegram
3. Klik link di smartphone
4. Harus masuk ke menu dengan MEJA 1 ✅

### **Test 3: Multiple Devices**
Test dengan berbagai smartphone:
- iPhone (Safari)
- Android (Chrome)
- Tablet
- Pastikan semua bisa akses

---

## 🔄 Jika IP Address Berubah

IP Address bisa berubah jika:
- Router restart
- Komputer reconnect ke WiFi
- DHCP expired

### **Solusi 1: Set Static IP**

**macOS:**
```
System Preferences → Network → Wi-Fi → Advanced → TCP/IP
Configure IPv4: Using DHCP with manual address
IPv4 Address: 192.168.1.27
```

**Windows:**
```
Control Panel → Network Connections → Properties
Internet Protocol Version 4 → Use the following IP address
IP: 192.168.1.27
Subnet: 255.255.255.0
Gateway: 192.168.1.1
```

### **Solusi 2: Cek IP Berkala**
```bash
# macOS/Linux
ifconfig | grep "inet "

# Windows
ipconfig | findstr IPv4
```

Jika IP berubah, generate ulang QR Code dengan IP baru.

---

## 📱 Info WiFi untuk Customer

Pastikan ada info WiFi di tempat:

```
═══════════════════════════════
    WIFI GRATIS UNTUK CUSTOMER
═══════════════════════════════
  
  📶 Nama WiFi: [NAMA_WIFI_ANDA]
  🔐 Password:  [PASSWORD_WIFI]
  
  Scan QR Code di meja untuk order!
  
═══════════════════════════════
```

---

## 🎯 Checklist Lengkap

### **Persiapan:**
- [ ] XAMPP Apache & MySQL running
- [ ] Cek IP address: `192.168.1.27`
- [ ] Test akses dari browser komputer
- [ ] Test akses dari smartphone (WiFi sama)
- [ ] Firewall diizinkan untuk Apache

### **Generate QR:**
- [ ] Buka https://myqrcode.com/generator
- [ ] Generate QR untuk MEJA 1 (TBL-001)
- [ ] Generate QR untuk MEJA 2 (TBL-002)
- [ ] Generate QR untuk MEJA 3 (TBL-003)
- [ ] (... sesuai jumlah meja)
- [ ] Download semua dalam folder terorganisir

### **Design & Print:**
- [ ] Buat desain kartu di Canva/Word
- [ ] Tambah nama meja dan instruksi
- [ ] Print di kertas glossy/karton
- [ ] Laminating (recommended)

### **Deploy:**
- [ ] Tempel QR Code di setiap meja
- [ ] Test scan dari berbagai smartphone
- [ ] Info WiFi dipasang di tempat terlihat
- [ ] Backup file PNG QR Code

### **Dokumentasi:**
- [ ] Simpan list semua link di Excel
- [ ] Backup QR Code PNG
- [ ] Catat IP address yang digunakan
- [ ] Training staff tentang cara bantu customer

---

## 🚨 Troubleshooting

### **Problem: QR Code Scan tapi Error**
✅ **Solusi:**
- Cek smartphone terhubung WiFi yang sama
- Test link manual di browser smartphone dulu
- Pastikan Apache XAMPP running
- Cek firewall tidak block port 80

### **Problem: Tidak Bisa Akses dari Smartphone**
✅ **Solusi:**
```
1. Ping test dari smartphone:
   - Install app "Network Analyzer"
   - Ping ke 192.168.1.27
   
2. Cek WiFi sama network:
   - Komputer: 192.168.1.x
   - Smartphone: 192.168.1.y
   - Harus subnet sama (192.168.1.x)

3. Restart Apache di XAMPP

4. Disable firewall sementara untuk test
```

### **Problem: Link Redirect ke Localhost**
✅ **Solusi:**
Jangan copy link dari browser komputer (yang pakai localhost).
Selalu gunakan IP: `192.168.1.27`

---

## 💡 Tips Pro

1. **Cetak Cadangan:** Print 2x lebih banyak QR Code untuk backup
2. **Waterproof:** Gunakan laminating glossy untuk tahan air
3. **QR Size:** Minimal 5x5cm agar mudah di-scan
4. **Testing:** Test semua QR sebelum deploy
5. **WiFi Info:** Tempel info WiFi di dekat kasir dan pintu masuk
6. **Backup Digital:** Simpan semua PNG di cloud (Google Drive/Dropbox)

---

## 📊 Quick Reference

| Item | Value |
|------|-------|
| **IP Address** | 192.168.1.27 |
| **Base URL** | http://192.168.1.27/cafe_ordering/public/ |
| **Format Link** | index.php?code=TBL-XXX |
| **QR Generator** | https://myqrcode.com/generator |
| **QR Size** | 300x300px minimum |
| **Print Size** | 5cm x 5cm minimum |

---

## 📞 Support

Jika ada masalah:
1. Cek dokumentasi: `FITUR_BARCODE_CHECKIN.md`
2. Cek IP address berubah atau tidak
3. Test akses manual dari smartphone browser
4. Cek Apache XAMPP status

---

**Selamat Membuat QR Code! 📱✨**

*Last Updated: 2025-11-17*
*IP Address: 192.168.1.27*
