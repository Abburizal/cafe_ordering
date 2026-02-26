# 📊 DIAGRAM FLOW - Analisis Implementasi QR Code System
## Cafe Ordering System - Untuk Skripsi

---

## 🎯 DIAGRAM 1: ARSITEKTUR SISTEM QR CODE (OVERVIEW)
**Judul untuk Skripsi:** "Gambar 3.1 Arsitektur Sistem QR Code pada Cafe Ordering System"

```
╔═══════════════════════════════════════════════════════════════════╗
║                  SISTEM CAFE ORDERING DENGAN QR CODE              ║
╚═══════════════════════════════════════════════════════════════════╝

┌─────────────────────────┐              ┌─────────────────────────┐
│      ADMIN SIDE         │              │     CUSTOMER SIDE       │
│                         │              │                         │
│  ┌─────────────────┐   │              │  ┌─────────────────┐   │
│  │  Login Admin    │   │              │  │  Scan QR Code   │   │
│  └────────┬────────┘   │              │  │  (scan.php)     │   │
│           │             │              │  └────────┬────────┘   │
│           ▼             │              │           │             │
│  ┌─────────────────┐   │              │           ▼             │
│  │ Management Meja │   │              │  ┌─────────────────┐   │
│  │  (tables.php)   │   │              │  │  View Menu      │   │
│  └────────┬────────┘   │              │  │ (index.php)     │   │
│           │             │              │  └────────┬────────┘   │
│           ▼             │              │           │             │
│  ┌─────────────────┐   │              │           ▼             │
│  │ Generate QR     │   │              │  ┌─────────────────┐   │
│  │ (generate_qr/)  │   │              │  │  Order Process  │   │
│  └────────┬────────┘   │              │  └─────────────────┘   │
│           │             │              │                         │
└───────────┼─────────────┘              └───────────┼─────────────┘
            │                                        │
            │                                        │
            └────────────────┬───────────────────────┘
                             ▼
            ╔════════════════════════════════════════╗
            ║      APPLICATION LAYER (PHP)           ║
            ╠════════════════════════════════════════╣
            ║  • QR Generator: endroid/qr-code       ║
            ║  • QR Scanner: html5-qrcode (JS)       ║
            ║  • Session Management                  ║
            ║  • Authentication & Authorization      ║
            ║  • Real-time Updates (WebSocket)       ║
            ╚════════════════╤═══════════════════════╝
                             │
                             ▼
            ╔════════════════════════════════════════╗
            ║         DATABASE LAYER (MySQL)         ║
            ╠════════════════════════════════════════╣
            ║  Tables:                               ║
            ║  • tables (id, name, code)             ║
            ║  • orders (id, table_id, status)       ║
            ║  • order_items (order_id, product_id)  ║
            ║  • products (id, name, price)          ║
            ║  • users (id, username, role)          ║
            ╚════════════════════════════════════════╝
```

**Penjelasan untuk Laporan:**
```
Gambar 3.1 menunjukkan arsitektur keseluruhan sistem QR Code pada 
Cafe Ordering System. Sistem terdiri dari dua sisi utama yaitu Admin 
Side untuk pengelolaan meja dan generate QR Code, serta Customer Side 
untuk scanning QR Code dan melakukan pemesanan. 

Kedua sisi tersebut terhubung melalui Application Layer yang 
menggunakan library endroid/qr-code untuk generate QR dan 
html5-qrcode untuk scanning. Data disimpan dalam Database Layer 
menggunakan MySQL dengan beberapa tabel utama yaitu tables untuk 
menyimpan data meja, orders untuk data pesanan, dan tabel lainnya.
```

---

## 🔄 DIAGRAM 2: FLOWCHART - ADMIN GENERATE QR CODE
**Judul untuk Skripsi:** "Gambar 3.2 Flowchart Proses Generate QR Code oleh Admin"

```
                    ┌─────────────┐
                    │    START    │
                    └──────┬──────┘
                           │
                           ▼
                    ┌─────────────┐
                    │ Admin Login │
                    └──────┬──────┘
                           │
                           ▼
                ┌──────────────────────┐
                │ Buka Halaman Meja    │
                │   (tables.php)       │
                └──────────┬───────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │ Ada data    │      NO
                    │ meja?       │─────────┐
                    └──────┬──────┘         │
                          │ YES              │
                          ▼                  ▼
              ┌───────────────────┐   ┌─────────────┐
              │ Pilih Meja untuk  │   │ Buat Meja   │
              │ Generate QR       │   │ Baru        │
              └──────┬────────────┘   └──────┬──────┘
                     │                       │
                     └───────────┬───────────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │ Klik "Lihat QR" atau    │
                    │ "Lihat Semua QR Code"   │
                    └──────────┬──────────────┘
                               │
                               ▼
                    ┌─────────────────────────┐
                    │ System kirim request ke │
                    │ generate_qr.php dengan  │
                    │ parameter code          │
                    └──────────┬──────────────┘
                               │
                               ▼
                    ┌─────────────────────────┐
                    │ Ambil data meja dari    │
                    │ database berdasarkan    │
                    │ code (TBL-001)          │
                    └──────────┬──────────────┘
                               │
                               ▼
                        ┌─────────────┐
                        │ Data meja   │      NO
                        │ ditemukan?  │──────────┐
                        └──────┬──────┘          │
                              │ YES               │
                              ▼                   ▼
                ┌──────────────────────────┐  ┌──────────────┐
                │ Build URL:               │  │ Tampilkan    │
                │ BASE_URL/index.php?      │  │ Error        │
                │ code=TBL-001             │  └──────┬───────┘
                └──────────┬───────────────┘         │
                           │                         │
                           ▼                         │
                ┌──────────────────────────┐        │
                │ Panggil library          │        │
                │ endroid/qr-code:         │        │
                │ new QrCode(              │        │
                │   data: $url,            │        │
                │   size: 300,             │        │
                │   margin: 10             │        │
                │ )                        │        │
                └──────────┬───────────────┘        │
                           │                        │
                           ▼                        │
                ┌──────────────────────────┐       │
                │ Generate PNG Image       │       │
                │ $writer->write($qrCode)  │       │
                └──────────┬───────────────┘       │
                           │                       │
                           ▼                       │
                ┌──────────────────────────┐      │
                │ Tampilkan QR Code Image  │      │
                │ di Browser/Modal         │      │
                └──────────┬───────────────┘      │
                           │                      │
                           ▼                      │
                    ┌─────────────┐              │
                    │ Admin bisa: │              │
                    │ • Download  │              │
                    │ • Print     │              │
                    │ • Save      │              │
                    └──────┬──────┘              │
                           │                     │
                           └──────────┬──────────┘
                                      │
                                      ▼
                                ┌──────────┐
                                │   END    │
                                └──────────┘
```

**Penjelasan untuk Laporan:**
```
Gambar 3.2 menunjukkan alur proses generate QR Code oleh admin. 
Proses dimulai dari admin login dan membuka halaman management meja. 
Admin dapat memilih meja yang sudah ada atau membuat meja baru. 
Setelah memilih meja, admin mengklik tombol "Lihat QR" yang akan 
mengirim request ke API generate_qr.php dengan parameter code meja.

System kemudian mengambil data meja dari database dan membangun URL 
yang akan di-encode ke dalam QR Code. URL tersebut berformat 
BASE_URL/index.php?code=TBL-001 dimana TBL-001 adalah kode unik meja.

Library endroid/qr-code kemudian dipanggil untuk generate QR Code 
dengan ukuran 300x300 pixel dan margin 10 pixel. Hasil generate 
berupa PNG image yang ditampilkan di browser. Admin dapat melakukan 
download, print, atau save QR Code tersebut untuk ditempel di meja.
```

---

## 🔄 DIAGRAM 3: FLOWCHART - CUSTOMER SCAN QR CODE
**Judul untuk Skripsi:** "Gambar 3.3 Flowchart Proses Scan QR Code oleh Customer"

```
                    ┌─────────────┐
                    │    START    │
                    └──────┬──────┘
                           │
                           ▼
                ┌──────────────────────┐
                │ Customer Duduk       │
                │ di Meja              │
                └──────────┬───────────┘
                           │
                           ▼
                ┌──────────────────────┐
                │ Buka scan.php di     │
                │ Smartphone           │
                └──────────┬───────────┘
                           │
                           ▼
                ┌──────────────────────┐
                │ Browser minta        │
                │ izin akses kamera    │
                └──────────┬───────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │ Customer    │      NO
                    │ izinkan?    │──────────┐
                    └──────┬──────┘          │
                          │ YES               │
                          ▼                   ▼
              ┌───────────────────┐    ┌──────────────┐
              │ Kamera aktif      │    │ Tampilkan    │
              │ html5-qrcode      │    │ Error &      │
              │ mulai scanning    │    │ Fallback     │
              │ (fps: 10)         │    │ Option       │
              └──────┬────────────┘    └──────┬───────┘
                     │                        │
                     ▼                        ▼
              ┌───────────────────┐    ┌──────────────┐
              │ Customer arahkan  │    │ Customer     │
              │ kamera ke QR Code │    │ pilih meja   │
              │ di meja           │    │ manual       │
              └──────┬────────────┘    └──────┬───────┘
                     │                        │
                     ▼                        │
              ┌───────────────────┐          │
              │ QR Code terdeteksi│          │
              │ & di-decode       │          │
              └──────┬────────────┘          │
                     │                       │
                     ▼                       │
              ┌───────────────────┐         │
              │ Hasil decode:     │         │
              │ BASE_URL/         │         │
              │ index.php?        │         │
              │ code=TBL-001      │         │
              └──────┬────────────┘         │
                     │                      │
                     ▼                      │
              ┌───────────────────┐        │
              │ Stop scanner      │        │
              └──────┬────────────┘        │
                     │                     │
                     ▼                     │
              ┌───────────────────┐       │
              │ Redirect browser  │       │
              │ ke URL hasil      │       │
              │ decode            │       │
              └──────┬────────────┘       │
                     │                    │
                     └────────┬───────────┘
                              │
                              ▼
                   ┌──────────────────────┐
                   │ index.php terima     │
                   │ parameter ?code=     │
                   │ TBL-001              │
                   └──────────┬───────────┘
                              │
                              ▼
                   ┌──────────────────────┐
                   │ Query database:      │
                   │ SELECT * FROM tables │
                   │ WHERE code='TBL-001' │
                   └──────────┬───────────┘
                              │
                              ▼
                       ┌─────────────┐
                       │ Data meja   │      NO
                       │ ditemukan?  │──────────┐
                       └──────┬──────┘          │
                             │ YES               │
                             ▼                   ▼
                  ┌──────────────────┐    ┌─────────────┐
                  │ Simpan ke        │    │ Redirect ke │
                  │ Session:         │    │ index.php   │
                  │ selected_table_id│    │ dengan error│
                  │ = 1              │    └─────────────┘
                  └──────┬───────────┘
                         │
                         ▼
                  ┌──────────────────┐
                  │ Tampilkan Menu   │
                  │ dengan Meja      │
                  │ sudah terpilih   │
                  │ "Meja 1"         │
                  └──────┬───────────┘
                         │
                         ▼
                  ┌──────────────────┐
                  │ Customer bisa    │
                  │ mulai order menu │
                  └──────┬───────────┘
                         │
                         ▼
                    ┌──────────┐
                    │   END    │
                    └──────────┘
```

**Penjelasan untuk Laporan:**
```
Gambar 3.3 menunjukkan alur proses scan QR Code oleh customer. 
Proses dimulai ketika customer duduk di meja dan membuka halaman 
scan.php menggunakan smartphone. Browser akan meminta izin akses 
kamera kepada customer.

Jika customer mengizinkan, kamera akan aktif dan library html5-qrcode 
mulai melakukan scanning dengan frame rate 10 fps. Customer mengarahkan 
kamera ke QR Code yang ada di meja. Ketika QR Code terdeteksi, system 
melakukan decode dan mendapatkan URL: BASE_URL/index.php?code=TBL-001.

Browser kemudian melakukan redirect ke URL tersebut. Halaman index.php 
menerima parameter code dan melakukan query ke database untuk mencari 
data meja dengan code TBL-001. Jika ditemukan, ID meja disimpan dalam 
session sebagai selected_table_id. 

Halaman menu kemudian ditampilkan dengan meja sudah terpilih secara 
otomatis, sehingga customer dapat langsung mulai melakukan pemesanan 
tanpa perlu memilih meja secara manual.

Jika customer menolak akses kamera, system menyediakan fallback option 
yaitu pilih meja secara manual melalui dropdown.
```

---

## 📊 DIAGRAM 4: SEQUENCE DIAGRAM - GENERATE QR CODE
**Judul untuk Skripsi:** "Gambar 3.4 Sequence Diagram Proses Generate QR Code"

```
Admin      tables.php    generate_qr.php   endroid/qr-code    MySQL Database    Browser
  │              │                │                │                 │              │
  │─1.Login─────→│                │                │                 │              │
  │              │                │                │                 │              │
  │─2.Buka Halaman Meja──→        │                │                 │              │
  │              │                │                │                 │              │
  │              │─3.Query SELECT * FROM tables───────────────────→ │              │
  │              │                │                │                 │              │
  │              │←─4.Return list of tables───────────────────────── │              │
  │              │                │                │                 │              │
  │←─5.Display Table List─────────│                │                 │              │
  │              │                │                │                 │              │
  │─6.Click "Lihat QR"───→        │                │                 │              │
  │              │                │                │                 │              │
  │              │─7.Open Modal & Request QR────→  │                 │              │
  │              │    GET /api/generate_qr.php?code=TBL-001          │              │
  │              │                │                │                 │              │
  │              │                │─8.Query SELECT * FROM tables────→│              │
  │              │                │   WHERE code='TBL-001'           │              │
  │              │                │                │                 │              │
  │              │                │←─9.Return table data────────────│              │
  │              │                │   {id:1, name:'Meja 1',         │              │
  │              │                │    code:'TBL-001'}              │              │
  │              │                │                │                 │              │
  │              │                │─10.Build URL───│                 │              │
  │              │                │   $url = BASE_URL/index.php?code=TBL-001       │
  │              │                │                │                 │              │
  │              │                │─11.Create QrCode object────→    │              │
  │              │                │   new QrCode(                   │              │
  │              │                │     data: $url,                 │              │
  │              │                │     size: 300,                  │              │
  │              │                │     margin: 10                  │              │
  │              │                │   )                             │              │
  │              │                │                │                 │              │
  │              │                │←─12.Return QR matrix──────────  │              │
  │              │                │                │                 │              │
  │              │                │─13.Generate PNG────→            │              │
  │              │                │   $writer->write($qrCode)       │              │
  │              │                │                │                 │              │
  │              │                │←─14.Return PNG binary───────    │              │
  │              │                │                │                 │              │
  │              │←─15.HTTP Response (image/png)───│                 │              │
  │              │                │                │                 │              │
  │              │─16.Display QR Code in Modal────────────────────────────────────→│
  │              │                │                │                 │              │
  │←─17.View QR Code──────────────────────────────────────────────────────────────│
  │              │                │                │                 │              │
  │─18.Download/Print QR──────────────────────────────────────────────────────────│
  │              │                │                │                 │              │
```

**Penjelasan untuk Laporan:**
```
Gambar 3.4 menunjukkan sequence diagram proses generate QR Code 
secara detail. Diagram ini menampilkan interaksi antar komponen 
dari awal sampai akhir proses.

Proses dimulai dari admin yang login dan membuka halaman management 
meja (tables.php). System melakukan query ke database MySQL untuk 
mengambil semua data meja dan menampilkannya dalam bentuk tabel.

Admin kemudian mengklik tombol "Lihat QR" pada salah satu meja. 
Halaman tables.php membuka modal dan mengirim request ke API 
generate_qr.php dengan parameter code meja (contoh: TBL-001).

API generate_qr.php melakukan query ke database untuk mengambil data 
meja berdasarkan code. Setelah data ditemukan, API membangun URL 
lengkap yang akan di-encode: BASE_URL/index.php?code=TBL-001.

URL tersebut kemudian dikirim ke library endroid/qr-code dengan 
memanggil constructor new QrCode() dengan parameter data (URL), 
size (300 pixel), dan margin (10 pixel). Library menghasilkan 
QR matrix yang kemudian di-convert menjadi PNG image oleh PngWriter.

PNG binary yang dihasilkan dikirim kembali sebagai HTTP response 
dengan content-type image/png. Browser menerima response dan 
menampilkan QR Code dalam modal. Admin dapat melihat, download, 
atau print QR Code tersebut.
```

---

## 📊 DIAGRAM 5: SEQUENCE DIAGRAM - SCAN QR CODE
**Judul untuk Skripsi:** "Gambar 3.5 Sequence Diagram Proses Scan QR Code"

```
Customer   scan.php   html5-qrcode   QR_Image   index.php   MySQL DB   Session   Browser
   │           │            │            │           │          │         │         │
   │─1.Open scan.php──→     │            │           │          │         │         │
   │           │            │            │           │          │         │         │
   │           │─2.Load html5-qrcode library───→     │          │         │         │
   │           │            │            │           │          │         │         │
   │           │─3.Request Camera Permission────────────────────────────────────→  │
   │           │            │            │           │          │         │         │
   │←─4.Browser Prompt: "Allow Camera Access?"────────────────────────────────────│
   │           │            │            │           │          │         │         │
   │─5.Click "Allow"────────────────────────────────────────────────────────────→ │
   │           │            │            │           │          │         │         │
   │           │←─6.Camera Access Granted───────────────────────────────────────── │
   │           │            │            │           │          │         │         │
   │           │─7.Initialize Scanner───→            │          │         │         │
   │           │   Html5Qrcode.getCameras()          │          │         │         │
   │           │   .start(cameraId, config)          │          │         │         │
   │           │            │            │           │          │         │         │
   │           │←─8.Camera Started────── │           │          │         │         │
   │           │   Status: "Kamera aktif - Arahkan ke QR Code"  │         │         │
   │           │            │            │           │          │         │         │
   │─9.Point camera to QR Code─→         │           │          │         │         │
   │           │            │            │           │          │         │         │
   │           │            │─10.Scanning (fps:10)──→           │         │         │
   │           │            │   Continuous scanning │           │         │         │
   │           │            │            │           │          │         │         │
   │           │            │←─11.QR Detected────────│          │         │         │
   │           │            │            │           │          │         │         │
   │           │            │─12.Decode QR Code─────→           │         │         │
   │           │            │            │           │          │         │         │
   │           │            │←─13.Decoded Text───────│          │         │         │
   │           │            │   "http://localhost/cafe_ordering/ │         │         │
   │           │            │    public/index.php?code=TBL-001"  │         │         │
   │           │            │            │           │          │         │         │
   │           │←─14.onScanSuccess()──── │           │          │         │         │
   │           │            │            │           │          │         │         │
   │           │─15.Stop Scanner────────→            │          │         │         │
   │           │            │            │           │          │         │         │
   │           │─16.window.location.href = decoded URL──────────────────────────→  │
   │           │            │            │           │          │         │         │
   │           │            │            │           │          │         │         │
   │           │            │            │           │──────────────────────────→  │
   │           │            │            │  ←─17.Request: index.php?code=TBL-001── │
   │           │            │            │           │          │         │         │
   │           │            │            │           │─18.Query database────→      │
   │           │            │            │           │   SELECT * FROM tables      │
   │           │            │            │           │   WHERE code='TBL-001'      │
   │           │            │            │           │          │         │         │
   │           │            │            │           │←─19.Return table data──     │
   │           │            │            │           │   {id:1, name:'Meja 1',     │
   │           │            │            │           │    code:'TBL-001'}          │
   │           │            │            │           │          │         │         │
   │           │            │            │           │─20.Validate table exists──→ │
   │           │            │            │           │          │         │         │
   │           │            │            │           │─21.Store in session────────→│
   │           │            │            │           │   $_SESSION['selected_      │
   │           │            │            │           │   table_id'] = 1            │
   │           │            │            │           │          │         │         │
   │           │            │            │           │←─22.Session stored──────────│
   │           │            │            │           │          │         │         │
   │           │            │            │           │─23.Render menu page────────→│
   │           │            │            │           │   with table pre-selected   │
   │           │            │            │           │   Display: "Meja 1"         │
   │           │            │            │           │          │         │         │
   │←─24.View Menu Page with "Meja 1" selected─────────────────────────────────────│
   │           │            │            │           │          │         │         │
```

**Penjelasan untuk Laporan:**
```
Gambar 3.5 menunjukkan sequence diagram proses scan QR Code oleh 
customer secara mendetail. Proses dimulai ketika customer membuka 
halaman scan.php di smartphone.

Halaman scan.php memuat library html5-qrcode dan meminta izin akses 
kamera kepada customer melalui browser prompt. Setelah customer 
mengklik "Allow", kamera diaktifkan dan scanner diinisialisasi dengan 
konfigurasi fps 10 frames per second dan qrbox 250x250 pixel.

Customer mengarahkan kamera ke QR Code yang ada di meja. Library 
html5-qrcode melakukan scanning secara continuous hingga QR Code 
terdeteksi. Setelah QR Code terdeteksi, library melakukan decode 
dan menghasilkan URL lengkap: 
http://localhost/cafe_ordering/public/index.php?code=TBL-001

Fungsi callback onScanSuccess() dipanggil dan scanner dihentikan 
untuk mencegah multiple scanning. Browser kemudian melakukan redirect 
ke URL hasil decode.

Halaman index.php menerima request dengan parameter code=TBL-001. 
System melakukan query ke database MySQL untuk mencari data meja 
dengan code tersebut. Setelah data ditemukan dan divalidasi, ID meja 
disimpan dalam session dengan key 'selected_table_id'.

Halaman menu kemudian di-render dengan meja sudah terpilih secara 
otomatis. Customer dapat melihat "Meja 1" sudah terseleksi dan dapat 
langsung mulai melakukan pemesanan tanpa perlu memilih meja manual.
```

---

## 🔄 DIAGRAM 6: DATA FLOW DIAGRAM (DFD) LEVEL 0
**Judul untuk Skripsi:** "Gambar 3.6 Data Flow Diagram Level 0 - Sistem QR Code"

```
                                    ┌──────────────────┐
                              ┌────→│   Admin User     │────┐
                              │     └──────────────────┘    │
                              │                             │
                      Data Meja Baru                    QR Code Image
                              │                             │
                              │                             │
                              ▼                             ▼
                    ┌─────────────────────────────────────────┐
                    │                                         │
                    │     SISTEM CAFE ORDERING                │
                    │     DENGAN QR CODE                      │
                    │                                         │
                    │  (Proses: Generate, Scan, Store,        │
                    │   Validate, Display)                    │
                    │                                         │
                    └─────────────────┬───────────────────────┘
                                      │
                                      │
                    ┌─────────────────┴───────────────────┐
                    │                                     │
                    ▼                                     ▼
          ┌──────────────────┐                  ┌──────────────┐
          │  Customer User   │                  │   Database   │
          └──────────────────┘                  │   (MySQL)    │
                    ▲                           └──────────────┘
                    │                                     ▲
                    │                                     │
              Menu dengan                          Data Query/
              Meja Terpilih                        Store
```

**Penjelasan untuk Laporan:**
```
Gambar 3.6 menunjukkan Data Flow Diagram Level 0 yang merepresentasikan 
sistem QR Code secara keseluruhan sebagai satu proses tunggal. 

Admin User memberikan input berupa data meja baru (nama dan code meja) 
ke dalam sistem. Sistem memproses data tersebut dan menghasilkan 
QR Code Image sebagai output yang diberikan kembali ke Admin.

Customer User melakukan scanning QR Code dan sistem memberikan output 
berupa halaman menu dengan meja sudah terpilih secara otomatis.

Database (MySQL) berfungsi sebagai data store yang menyimpan semua 
data meja, order, dan transaksi. Sistem melakukan query dan store 
data dari/ke database sesuai kebutuhan.
```

---

## 🔄 DIAGRAM 7: DATA FLOW DIAGRAM (DFD) LEVEL 1
**Judul untuk Skripsi:** "Gambar 3.7 Data Flow Diagram Level 1 - Detail Proses QR Code"

```
                    ┌──────────────┐
                    │  Admin User  │
                    └───┬──────────┘
                        │
                   1. Data Meja
                   (name, code)
                        │
                        ▼
            ┌───────────────────────┐
            │  P1: Manage Tables    │
            │  (CRUD Operations)    │
            └───┬───────────────┬───┘
                │               │
           2. Store         3. Request
           Table Data       Generate QR
                │               │
                ▼               ▼
        ┌───────────┐   ┌─────────────────┐
        │ D1: Tables│   │ P2: Generate QR │
        │  Database │   │     Code        │
        └───┬───────┘   └───┬──────────┬──┘
            │               │          │
            │          4. Table   5. QR Code
            │             Data       Image
            │               │          │
            │               ▼          ▼
            │           ┌──────────────────┐
            │           │   Admin User     │
            │           └──────────────────┘
            │
            │           ┌──────────────┐
            │           │ Customer User│
            │           └───┬──────────┘
            │               │
            │          6. Scanned
            │          QR Code (URL)
            │               │
            │               ▼
            │       ┌───────────────────┐
            │       │ P3: Decode QR &   │
            │       │  Validate Table   │
            │       └───┬───────────┬───┘
            │           │           │
            │      7. Query    8. Table ID
            │      Table Code      │
            │           │           │
            └───────────┘           ▼
                                ┌───────────┐
                                │ D2: Session│
                                │  Storage  │
                                └───┬───────┘
                                    │
                               9. Selected
                               Table Info
                                    │
                                    ▼
                            ┌────────────────┐
                            │ P4: Display    │
                            │     Menu       │
                            └────────┬───────┘
                                     │
                                10. Menu Page
                                with Table
                                     │
                                     ▼
                            ┌────────────────┐
                            │ Customer User  │
                            └────────────────┘
```

**Penjelasan untuk Laporan:**
```
Gambar 3.7 menunjukkan Data Flow Diagram Level 1 yang memecah proses 
sistem QR Code menjadi 4 sub-proses utama:

**P1: Manage Tables**
Admin input data meja baru (nama dan code) yang kemudian disimpan 
ke database tables (D1). Proses ini juga mengirim request ke P2 
untuk generate QR Code.

**P2: Generate QR Code**
Menerima data meja dari P1, kemudian menggunakan library endroid/qr-code 
untuk generate QR Code image. Output berupa PNG image dikembalikan 
ke Admin User.

**P3: Decode QR & Validate Table**
Customer melakukan scan QR Code yang menghasilkan URL dengan parameter 
code. Proses ini melakukan query ke database D1 untuk validasi apakah 
table code tersebut valid. Jika valid, table ID disimpan ke session 
storage (D2).

**P4: Display Menu**
Mengambil informasi selected table dari session storage D2 dan 
menampilkan halaman menu dengan meja sudah terpilih secara otomatis 
ke Customer User.
```

---

## 📊 DIAGRAM 8: ENTITY RELATIONSHIP DIAGRAM (ERD)
**Judul untuk Skripsi:** "Gambar 3.8 Entity Relationship Diagram - Fokus QR Code System"

```
┌─────────────────────────┐
│       TABLES            │
├─────────────────────────┤
│ 🔑 id (PK)              │
│    name VARCHAR(50)     │
│ 🔒 code VARCHAR(20) UK  │◄──────┐
│    created_at TIMESTAMP │       │
└─────────────┬───────────┘       │
              │                   │
              │ 1                 │
              │                   │
              │                   │
              │ *                 │
              ▼                   │
┌─────────────────────────┐       │
│       ORDERS            │       │
├─────────────────────────┤       │
│ 🔑 id (PK)              │       │
│ 🔗 table_id (FK)        │───────┘
│    order_number         │
│    total_amount         │
│    status               │
│    created_at           │
└─────────────┬───────────┘
              │
              │ 1
              │
              │
              │ *
              ▼
┌─────────────────────────┐
│    ORDER_ITEMS          │
├─────────────────────────┤
│ 🔑 id (PK)              │
│ 🔗 order_id (FK)        │
│ 🔗 product_id (FK)      │
│    quantity             │
│    price                │
└─────────────────────────┘


KETERANGAN:
🔑 = Primary Key
🔗 = Foreign Key
🔒 = Unique Key
UK = Unique Constraint
1  = One (relation)
*  = Many (relation)
```

**Penjelasan untuk Laporan:**
```
Gambar 3.8 menunjukkan Entity Relationship Diagram yang fokus pada 
struktur database untuk sistem QR Code. 

Entitas utama adalah TABLES yang memiliki attribute:
- id: Primary key, auto increment
- name: Nama meja (contoh: "Meja 1")
- code: Kode unik meja (contoh: "TBL-001") dengan unique constraint
- created_at: Timestamp pembuatan

Attribute 'code' merupakan attribute penting yang di-encode ke dalam 
QR Code dan digunakan untuk proses scanning dan validasi.

Relasi TABLES ke ORDERS adalah one-to-many (1:*), artinya satu meja 
dapat memiliki banyak order. ORDERS memiliki foreign key table_id 
yang mereferensi ke TABLES.id.

Relasi ORDERS ke ORDER_ITEMS juga one-to-many, dimana satu order 
dapat memiliki banyak item pesanan.

Dengan struktur database ini, sistem dapat melacak history order 
per meja dan melakukan analytics seperti meja mana yang paling 
sering digunakan atau paling banyak order.
```

---

## 🎨 DIAGRAM 9: DEPLOYMENT DIAGRAM
**Judul untuk Skripsi:** "Gambar 3.9 Deployment Diagram - Sistem QR Code"

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT DEVICE                            │
│                                                                  │
│  ┌────────────────────┐            ┌────────────────────┐      │
│  │  Admin Computer    │            │  Customer Phone    │      │
│  │                    │            │                    │      │
│  │  ┌──────────────┐  │            │  ┌──────────────┐  │      │
│  │  │   Browser    │  │            │  │   Browser    │  │      │
│  │  │  (Chrome)    │  │            │  │  (Safari/    │  │      │
│  │  │              │  │            │  │   Chrome)    │  │      │
│  │  │ - tables.php │  │            │  │ - scan.php   │  │      │
│  │  │ - generate_  │  │            │  │ - index.php  │  │      │
│  │  │   qr/        │  │            │  │              │  │      │
│  │  └──────┬───────┘  │            │  └──────┬───────┘  │      │
│  └─────────┼──────────┘            └─────────┼──────────┘      │
│            │                                  │                 │
└────────────┼──────────────────────────────────┼─────────────────┘
             │                                  │
             │         HTTP/HTTPS               │
             │         (Port 80/443)            │
             │                                  │
             └──────────────┬───────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                      APPLICATION SERVER                          │
│                    (XAMPP / Apache 2.4)                          │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                  PHP 8.x Runtime                          │  │
│  │  ┌────────────────────────────────────────────────────┐  │  │
│  │  │            PHP Application                         │  │  │
│  │  │                                                    │  │  │
│  │  │  Components:                                       │  │  │
│  │  │  • /admin/api/generate_qr.php                      │  │  │
│  │  │  • /admin/tables.php                               │  │  │
│  │  │  • /public/scan.php                                │  │  │
│  │  │  • /public/index.php                               │  │  │
│  │  │  • /config/config.php                              │  │  │
│  │  │                                                    │  │  │
│  │  │  Libraries (via Composer):                         │  │  │
│  │  │  • endroid/qr-code v6.x                            │  │  │
│  │  │  • GD Extension (for image processing)             │  │  │
│  │  │                                                    │  │  │
│  │  │  Frontend Libraries (via CDN):                     │  │  │
│  │  │  • html5-qrcode v2.3.8                             │  │  │
│  │  │  • TailwindCSS                                     │  │  │
│  │  └────────────────────────────────────────────────────┘  │  │
│  └──────────────────────────┬───────────────────────────────┘  │
│                             │                                  │
└─────────────────────────────┼──────────────────────────────────┘
                              │
                              │ PDO
                              │ (Port 3306)
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       DATABASE SERVER                            │
│                      (MySQL 8.0 / MariaDB)                       │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │           Database: cafe_ordering                        │  │
│  │                                                          │  │
│  │  Tables:                                                 │  │
│  │  • tables (id, name, code)                               │  │
│  │  • orders (id, table_id, status, total)                  │  │
│  │  • order_items (id, order_id, product_id, qty)           │  │
│  │  • products (id, name, price, stock)                     │  │
│  │  • users (id, username, password, role)                  │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

**Penjelasan untuk Laporan:**
```
Gambar 3.9 menunjukkan deployment diagram yang menggambarkan arsitektur 
deployment sistem QR Code pada infrastructure fisik.

**Client Device Layer:**
Terdiri dari dua jenis device:
1. Admin Computer: Digunakan admin untuk manage meja dan generate 
   QR Code melalui browser desktop (Chrome/Firefox)
2. Customer Phone: Digunakan customer untuk scan QR Code dan order 
   menu melalui mobile browser (Safari/Chrome)

**Application Server Layer:**
Menggunakan XAMPP sebagai web server dengan Apache 2.4 dan PHP 8.x. 
Komponen PHP application terdiri dari beberapa file utama:
- generate_qr.php: API untuk generate QR Code
- tables.php: Interface management meja
- scan.php: Interface scan QR Code
- index.php: Halaman menu customer

Library yang digunakan:
- Backend: endroid/qr-code (via Composer) dan GD extension
- Frontend: html5-qrcode (via CDN) untuk scanning

**Database Server Layer:**
Menggunakan MySQL 8.0 atau MariaDB dengan database cafe_ordering 
yang berisi 5 tabel utama: tables, orders, order_items, products, 
dan users.

Komunikasi antar layer:
- Client ke Application Server: HTTP/HTTPS (port 80/443)
- Application Server ke Database: PDO (port 3306)
```

---

## 📝 TIPS MEMBUAT DIAGRAM di Draw.io / Lucidchart:

### **1. Untuk Flowchart:**
- Gunakan shape: Rectangle (process), Diamond (decision), Oval (start/end)
- Warna konsisten: Blue untuk process, Yellow untuk decision, Green untuk start/end
- Arrow jelas dengan label

### **2. Untuk Sequence Diagram:**
- Gunakan object/entity di atas
- Lifeline vertikal dengan garis putus-putus
- Arrow horizontal untuk message passing
- Number setiap step

### **3. Untuk DFD:**
- Circle untuk process
- Rectangle untuk external entity
- Parallel lines untuk data store
- Arrow untuk data flow dengan label

### **4. Untuk ERD:**
- Rectangle untuk entity
- Diamond untuk relationship
- Crow's foot notation untuk cardinality
- Underline untuk primary key

---

## ✅ CHECKLIST DIAGRAM untuk SKRIPSI:

- [ ] **Gambar 3.1:** Arsitektur Sistem QR Code (Overview) ⭐⭐⭐
- [ ] **Gambar 3.2:** Flowchart Generate QR Code (Admin) ⭐⭐⭐
- [ ] **Gambar 3.3:** Flowchart Scan QR Code (Customer) ⭐⭐⭐
- [ ] **Gambar 3.4:** Sequence Diagram Generate QR ⭐⭐
- [ ] **Gambar 3.5:** Sequence Diagram Scan QR ⭐⭐⭐
- [ ] **Gambar 3.6:** DFD Level 0 ⭐
- [ ] **Gambar 3.7:** DFD Level 1 ⭐⭐
- [ ] **Gambar 3.8:** ERD Database ⭐⭐
- [ ] **Gambar 3.9:** Deployment Diagram ⭐

**Prioritas Tinggi (WAJIB):** Gambar 3.1, 3.2, 3.3, 3.5, 3.8  
**Prioritas Sedang:** Gambar 3.4, 3.7, 3.9  
**Prioritas Rendah (Opsional):** Gambar 3.6

---

**🎯 Dengan 9 diagram ini, dosen Anda akan mudah memahami implementasi 
QR Code System secara lengkap dan menyeluruh!**

**Semoga membantu! 📊✨**
