# 📱 Tahapan Implementasi QR Code - Cafe Ordering System

## 📋 Daftar Isi
1. [Overview Sistem QR Code](#overview-sistem-qr-code)
2. [Arsitektur dan Flow](#arsitektur-dan-flow)
3. [Komponen Utama](#komponen-utama)
4. [Tahapan Implementasi](#tahapan-implementasi)
5. [Library yang Digunakan](#library-yang-digunakan)
6. [Flow Data](#flow-data)
7. [Testing dan Validasi](#testing-dan-validasi)

---

## 🎯 Overview Sistem QR Code

### Tujuan Implementasi
Sistem QR Code diimplementasikan untuk memudahkan customer melakukan **check-in ke meja** secara otomatis dengan cara **scan QR Code** yang terdapat di setiap meja, tanpa harus memilih meja secara manual.

### Fitur Utama
- ✅ **Generate QR Code** untuk setiap meja
- ✅ **Scan QR Code** menggunakan kamera smartphone
- ✅ **Auto Check-in** ke meja yang dipilih
- ✅ **Download QR Code** dalam format PNG
- ✅ **Print QR Code** untuk semua meja sekaligus
- ✅ **View QR Code** individual dari management meja

---

## 🏗️ Arsitektur dan Flow

### Diagram Flow System
```
┌──────────────┐
│ Admin Panel  │
│ (tables.php) │
└──────┬───────┘
       │
       │ 1. Buat/Kelola Data Meja
       ↓
┌──────────────────────┐
│ Database: tables     │
│ - id, name, code     │
└──────┬───────────────┘
       │
       │ 2. Generate QR Code
       ↓
┌──────────────────────────────┐
│ QR Code Generator            │
│ (generate_qr/index.php)      │
│ (api/generate_qr.php)        │
└──────┬───────────────────────┘
       │
       │ 3. Encode URL + Code
       ↓
┌─────────────────────────────┐
│ QR Code Image (PNG)         │
│ Data: BASE_URL/index.php?   │
│       code=TBL-001          │
└─────┬───────────────────────┘
      │
      │ 4. Print/Display di Meja
      ↓
┌─────────────────────────────┐
│ Customer Scan QR            │
│ (scan.php - html5-qrcode)   │
└─────┬───────────────────────┘
      │
      │ 5. Decode QR → Get URL
      ↓
┌─────────────────────────────┐
│ Redirect ke Menu            │
│ index.php?code=TBL-001      │
└─────┬───────────────────────┘
      │
      │ 6. Auto Select Table
      ↓
┌─────────────────────────────┐
│ Customer View Menu          │
│ (dengan meja sudah terpilih)│
└─────────────────────────────┘
```

---

## 🔧 Komponen Utama

### 1. **Database Table: `tables`**
**Lokasi:** Database `cafe_ordering`

**Schema:**
```sql
CREATE TABLE tables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Sample Data:**
```sql
INSERT INTO tables (name, code) VALUES
('Meja 1', 'TBL-001'),
('Meja 2', 'TBL-002'),
('Meja 3', 'TBL-003');
```

---

### 2. **Admin Panel - Management Meja**
**File:** `/admin/tables.php`

**Fungsi:**
- ✅ CRUD Meja (Create, Read, Update, Delete)
- ✅ Validasi duplikat nama dan code meja
- ✅ Button "Lihat QR" untuk view QR Code individual
- ✅ Button "Lihat Semua QR Code" redirect ke generate_qr/

**Code Snippet:**
```php
// Button Lihat QR (Line 212)
<button onclick="showQR('<?= $table['code'] ?>')" 
        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
    📱 Lihat QR
</button>

// Link ke Generate QR Semua Meja (Line 180)
<a href="generate_qr/" target="_blank" 
   class="bg-white text-indigo-600 hover:bg-gray-100 px-6 py-3 rounded-lg">
    📱 Lihat Semua QR Code
</a>
```

---

### 3. **API Generate QR Code**
**File:** `/admin/api/generate_qr.php`

**Input:** `?code=TBL-001` (via GET parameter)

**Output:** PNG Image (binary)

**Library:** `endroid/qr-code` (version 6.x)

**Code Implementation:**
```php
<?php
require_once '../../config/config.php';
require_once '../../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Get code from parameter
$code = $_GET['code'];

// Create URL yang akan di-encode
$url = BASE_URL . '/index.php?code=' . urlencode($code);

// Generate QR Code dengan constructor params (v6 syntax)
$qrCode = new QrCode(
    data: $url,
    size: 300,
    margin: 10
);

$writer = new PngWriter();
$result = $writer->write($qrCode);

// Output as PNG image
header('Content-Type: ' . $result->getMimeType());
echo $result->getString();
```

**Penjelasan Parameter:**
- `data`: URL yang akan di-encode (contoh: `http://localhost/cafe_ordering/public/index.php?code=TBL-001`)
- `size`: Ukuran QR Code dalam pixel (300x300)
- `margin`: Margin putih di sekitar QR Code (10px)

---

### 4. **Generate All QR Code Page**
**File:** `/admin/generate_qr/index.php`

**Fungsi:**
- ✅ Menampilkan QR Code untuk semua meja dalam grid
- ✅ Print-friendly (button Print Semua)
- ✅ QR Code dapat di-save as image (klik kanan)
- ✅ Responsive design (grid 3 kolom di desktop)

**Code Implementation:**
```php
<?php
// Ambil semua data meja
$tables = $pdo->query("SELECT * FROM tables ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

foreach ($tables as $table):
    // Tentukan URL
    $url = BASE_URL . '/index.php?code=' . htmlspecialchars($table['code']);
    
    // Generate QR Code
    $qrCode = new QrCode(
        data: $url,
        size: 300,
        margin: 10
    );
    
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    // Tampilkan sebagai Data URI (inline image)
    echo '<img src="' . $result->getDataUri() . '" 
               alt="QR Code ' . htmlspecialchars($table['name']) . '">';
endforeach;
?>
```

**Features:**
- Button "Print Semua" untuk print seluruh QR Code
- Grid layout responsive
- Error handling jika GD extension tidak aktif
- Print CSS untuk page-break yang optimal

---

### 5. **Download QR Code Individual**
**File:** `/admin/download_qr.php`

**Input:** `?id=1` (ID meja dari database)

**Output:** Download PNG file dengan nama `QR_TBL-001.png`

**Code Implementation:**
```php
<?php
require_once '../config/config.php';
require_admin();

$id = (int)$_GET['id'];

// Get table data
$stmt = $pdo->prepare("SELECT * FROM tables WHERE id = ?");
$stmt->execute([$id]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);

// Generate QR Code
$qrUrl = BASE_URL . "/public/index.php?code=" . $table['code'];

$qrCode = new QrCode($qrUrl);
$qrCode->setSize(400);
$qrCode->setMargin(10);

$writer = new PngWriter();
$result = $writer->write($qrCode);

// Set headers for download
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="QR_' . $table['code'] . '.png"');
echo $result->getString();
```

**Perbedaan dengan API:**
- API (`generate_qr.php`): Display inline, input=code
- Download (`download_qr.php`): Force download, input=id, ukuran lebih besar (400px)

---

### 6. **QR Code Scanner**
**File:** `/public/scan.php`

**Library:** `html5-qrcode` (JavaScript library from unpkg CDN)

**Fungsi:**
- ✅ Akses kamera smartphone
- ✅ Real-time QR Code scanning
- ✅ Auto-redirect ke menu setelah scan berhasil
- ✅ Fallback option: pilih meja manual

**Code Implementation:**
```javascript
// Initialize Scanner
html5QrCode = new Html5Qrcode("reader");

// Get cameras (prefer back camera on mobile)
Html5Qrcode.getCameras().then(cameras => {
    const backCamera = cameras.find(camera => 
        camera.label.toLowerCase().includes('back')
    );
    
    // Start scanning
    html5QrCode.start(
        backCamera ? backCamera.id : cameras[0].id,
        {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        onScanSuccess,
        onScanError
    );
});

// Handle scan success
function onScanSuccess(decodedText) {
    // Stop scanning
    html5QrCode.stop();
    
    // Redirect ke menu
    if (decodedText.includes('?code=')) {
        window.location.href = decodedText;
    } else if (decodedText.startsWith('TBL-')) {
        window.location.href = `index.php?code=${decodedText}`;
    }
}
```

**Features:**
- Auto-detect back camera pada smartphone
- FPS: 10 frames/second (optimal untuk scanning)
- QR box: 250x250px (scan area)
- Prevent multiple scans dengan flag `isScanning`

---

## 🚀 Tahapan Implementasi

### **Fase 1: Setup Library** ✅
**Step 1.1:** Install Composer Dependencies
```bash
composer require endroid/qr-code
```

**Step 1.2:** Verify Installation
```bash
ls vendor/endroid/qr-code
```

**Output:** Folder library berhasil ter-install

---

### **Fase 2: Database Setup** ✅
**Step 2.1:** Create Table `tables`
```sql
CREATE TABLE tables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Step 2.2:** Insert Sample Data
```sql
INSERT INTO tables (name, code) VALUES
('Meja 1', 'TBL-001'),
('Meja 2', 'TBL-002'),
('Meja 3', 'TBL-003');
```

**Step 2.3:** Verify
```sql
SELECT * FROM tables;
```

---

### **Fase 3: Backend - Generate QR Code API** ✅
**Step 3.1:** Buat file `/admin/api/generate_qr.php`

**Step 3.2:** Implement QR Code generation logic
- Import library: `use Endroid\QrCode\QrCode;`
- Get parameter `code` dari URL
- Build URL: `BASE_URL . '/index.php?code=' . $code`
- Generate QR Code dengan size 300px
- Output sebagai PNG image

**Step 3.3:** Test API
```
URL: http://localhost/cafe_ordering/admin/api/generate_qr.php?code=TBL-001
Expected: QR Code image (PNG) muncul
```

---

### **Fase 4: Admin Panel - View QR Code** ✅
**Step 4.1:** Edit `/admin/tables.php`

**Step 4.2:** Tambahkan button "Lihat QR"
```php
<button onclick="showQR('<?= $table['code'] ?>')">
    📱 Lihat QR
</button>
```

**Step 4.3:** Buat modal untuk display QR Code
```javascript
function showQR(code) {
    const modal = document.getElementById('qrModal');
    const qrContainer = document.getElementById('qrCodeContainer');
    
    // Load QR Code dari API
    qrContainer.innerHTML = `
        <img src="api/generate_qr.php?code=${encodeURIComponent(code)}" 
             alt="QR Code">
    `;
    
    modal.style.display = 'flex';
}
```

**Step 4.4:** Test
- Klik button "Lihat QR" → Modal muncul dengan QR Code

---

### **Fase 5: Generate All QR Code Page** ✅
**Step 5.1:** Buat folder `/admin/generate_qr/`

**Step 5.2:** Buat file `index.php` di folder tersebut

**Step 5.3:** Fetch semua data meja dari database
```php
$tables = $pdo->query("SELECT * FROM tables ORDER BY id")->fetchAll();
```

**Step 5.4:** Loop untuk generate QR Code semua meja
```php
foreach ($tables as $table) {
    $url = BASE_URL . '/index.php?code=' . $table['code'];
    $qrCode = new QrCode(data: $url, size: 300, margin: 10);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    echo '<img src="' . $result->getDataUri() . '">';
}
```

**Step 5.5:** Implement print functionality
- Button "Print Semua"
- CSS untuk print media
- Page-break-inside: avoid

**Step 5.6:** Test
```
URL: http://localhost/cafe_ordering/admin/generate_qr/
Expected: Semua QR Code muncul dalam grid
```

---

### **Fase 6: Download QR Code** ✅
**Step 6.1:** Buat file `/admin/download_qr.php`

**Step 6.2:** Implement download logic
- Get ID meja dari parameter
- Fetch data meja
- Generate QR Code dengan size lebih besar (400px)
- Set header untuk force download
- Output dengan nama file `QR_TBL-001.png`

**Step 6.3:** Test
```
URL: http://localhost/cafe_ordering/admin/download_qr.php?id=1
Expected: File PNG ter-download otomatis
```

---

### **Fase 7: QR Code Scanner** ✅
**Step 7.1:** Buat file `/public/scan.php`

**Step 7.2:** Include library `html5-qrcode` dari CDN
```html
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
```

**Step 7.3:** Implement scanner logic
- Initialize `Html5Qrcode`
- Request camera permission
- Start scanning dengan FPS 10
- Handle scan success: redirect ke menu
- Handle scan error: fallback ke pilih manual

**Step 7.4:** UI/UX
- Loading state saat inisialisasi kamera
- Success message saat QR terbaca
- Error message jika kamera tidak tersedia
- Button fallback "Pilih Meja Manual"

**Step 7.5:** Test
- Buka `scan.php` di smartphone
- Izinkan akses kamera
- Scan QR Code → harus redirect ke menu

---

### **Fase 8: Integration dengan Index Page** ✅
**Step 8.1:** Edit `/public/index.php`

**Step 8.2:** Detect parameter `?code=` dari URL
```php
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Query meja berdasarkan code
    $stmt = $pdo->prepare("SELECT * FROM tables WHERE code = ?");
    $stmt->execute([$code]);
    $table = $stmt->fetch();
    
    if ($table) {
        // Auto-select meja
        $_SESSION['selected_table_id'] = $table['id'];
    }
}
```

**Step 8.3:** Display selected table
```php
if (isset($_SESSION['selected_table_id'])) {
    echo "Meja Terpilih: " . htmlspecialchars($table['name']);
}
```

**Step 8.4:** Test end-to-end flow
1. Generate QR Code untuk Meja 1
2. Scan QR Code
3. Verify redirect ke index.php?code=TBL-001
4. Verify meja sudah terpilih otomatis

---

### **Fase 9: Error Handling & Validation** ✅
**Step 9.1:** Check GD Extension
```php
if (!extension_loaded('gd')) {
    die('GD extension tidak aktif. Silakan aktifkan di php.ini');
}
```

**Step 9.2:** Validate QR Code parameter
```php
if (!isset($_GET['code'])) {
    http_response_code(400);
    die('Missing code parameter');
}
```

**Step 9.3:** Handle invalid code
```php
if (!$table) {
    die('Meja dengan kode tersebut tidak ditemukan');
}
```

**Step 9.4:** Try-catch untuk QR generation
```php
try {
    $result = $writer->write($qrCode);
    echo $result->getString();
} catch (\Exception $e) {
    die('Error generating QR Code: ' . $e->getMessage());
}
```

---

### **Fase 10: Testing & Documentation** ✅
**Step 10.1:** Unit Testing
- Test API dengan berbagai code
- Test scanner dengan berbagai smartphone
- Test print functionality

**Step 10.2:** Integration Testing
- Test full flow: Generate → Print → Scan → Menu
- Test dengan multiple users
- Test error scenarios

**Step 10.3:** Create Documentation
- `CARA_MEMBUAT_QR_CODE.md` ✅
- `FITUR_BARCODE_CHECKIN.md` ✅
- `TESTING_BARCODE.md` ✅

---

## 📚 Library yang Digunakan

### **1. Backend: endroid/qr-code**
**Version:** 6.x (latest)

**Install:**
```bash
composer require endroid/qr-code
```

**Documentation:** https://github.com/endroid/qr-code

**Features:**
- Generate QR Code dalam berbagai format (PNG, SVG, PDF, EPS)
- Customizable size, margin, color
- Error correction level
- Support logo embedding

**Syntax Version 6:**
```php
// Old syntax (v5 and below)
$qrCode = QrCode::create($data);

// New syntax (v6)
$qrCode = new QrCode(
    data: $url,
    size: 300,
    margin: 10
);
```

**Writer Options:**
- `PngWriter` - Output PNG (yang kita pakai)
- `SvgWriter` - Output SVG vector
- `PdfWriter` - Output PDF document
- `EpsWriter` - Output EPS for print

---

### **2. Frontend: html5-qrcode**
**Version:** 2.3.8

**CDN:**
```html
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
```

**Repository:** https://github.com/mebjas/html5-qrcode

**Features:**
- Cross-browser QR Code scanning
- Support camera dan file upload
- Auto camera selection (back/front)
- Customizable scan region
- Real-time scanning

**Browser Support:**
- ✅ Chrome/Edge (Android & Desktop)
- ✅ Safari (iOS & macOS)
- ✅ Firefox
- ✅ Opera

**Permissions Required:**
- Camera access (`navigator.mediaDevices.getUserMedia`)

---

## 📊 Flow Data

### **1. Generate QR Code Flow**
```
Admin Input → Database → Generate QR API → QR Code Image
```

**Detail:**
1. Admin buat meja baru di `tables.php`
2. Input: `name` = "Meja 1", `code` = "TBL-001"
3. Data tersimpan di database table `tables`
4. Admin klik "Lihat QR" atau buka `generate_qr/`
5. System call `api/generate_qr.php?code=TBL-001`
6. API build URL: `BASE_URL/index.php?code=TBL-001`
7. Library generate QR Code dari URL tersebut
8. Output: PNG image 300x300px

---

### **2. Scan QR Code Flow**
```
QR Code Image → Camera → Scanner → Decode URL → Redirect
```

**Detail:**
1. Customer buka `scan.php` di smartphone
2. Browser request camera permission
3. Camera stream aktif
4. html5-qrcode library scan QR Code
5. QR Code terbaca: `http://localhost/cafe_ordering/public/index.php?code=TBL-001`
6. JavaScript redirect: `window.location.href = decodedText`
7. Browser buka URL tersebut
8. `index.php` detect parameter `?code=TBL-001`
9. Query database: `SELECT * FROM tables WHERE code = 'TBL-001'`
10. Get table ID dan auto-select
11. Customer langsung masuk menu dengan meja terpilih

---

### **3. Data Structure**

**Database → QR Code:**
```
tables.code (TBL-001) 
    → URL: BASE_URL/index.php?code=TBL-001
    → Encode ke QR Code
    → Image PNG
```

**QR Code → Menu:**
```
Scan QR Code 
    → Decode: BASE_URL/index.php?code=TBL-001
    → GET parameter: code = TBL-001
    → Query: SELECT * FROM tables WHERE code = 'TBL-001'
    → Result: {id: 1, name: 'Meja 1', code: 'TBL-001'}
    → Session: selected_table_id = 1
```

---

## 🧪 Testing dan Validasi

### **Test Case 1: Generate QR Code**
**Input:** Code = "TBL-001"

**Expected Output:**
- QR Code image (PNG) muncul
- Size: 300x300 pixels
- Data encoded: URL lengkap dengan parameter code

**Validation:**
```bash
# Test API
curl http://localhost/cafe_ordering/admin/api/generate_qr.php?code=TBL-001 -o test.png

# Check file
file test.png
# Output: test.png: PNG image data, 300 x 300, 8-bit/color RGB, non-interlaced
```

---

### **Test Case 2: Scan QR Code**
**Steps:**
1. Buka `scan.php` di smartphone
2. Izinkan camera access
3. Scan QR Code

**Expected Result:**
- QR Code terbaca dalam < 2 detik
- Redirect ke `index.php?code=TBL-001`
- Meja terpilih otomatis

**Validation:**
- Check URL di address bar
- Check session: `selected_table_id` harus terisi
- Check UI: "Meja Terpilih: Meja 1" muncul

---

### **Test Case 3: Print QR Code**
**Steps:**
1. Buka `generate_qr/index.php`
2. Klik button "Print Semua"
3. Print/Save as PDF

**Expected Result:**
- Print preview muncul
- Semua QR Code tampil dengan layout rapi
- No-print elements (button, header) tidak muncul
- Each QR card tidak terpotong (page-break-inside: avoid)

**Validation:**
- Check print preview
- Check PDF hasil print
- Verify QR Code masih scannable di print

---

### **Test Case 4: Error Handling**

#### **4.1: GD Extension Not Loaded**
```php
// Disable GD di php.ini
;extension=gd
```
**Expected:** Error message: "GD extension tidak aktif"

#### **4.2: Invalid Code**
```
URL: api/generate_qr.php?code=INVALID-CODE
```
**Expected:** QR Code tetap generate (valid QR, tapi meja tidak ditemukan saat scan)

#### **4.3: Missing Code Parameter**
```
URL: api/generate_qr.php
```
**Expected:** HTTP 400 - "Missing code parameter"

#### **4.4: Camera Not Available**
**Steps:** Buka `scan.php` di device tanpa camera
**Expected:** Error message: "Tidak ada kamera terdeteksi"

#### **4.5: Camera Permission Denied**
**Steps:** Deny camera permission saat browser prompt
**Expected:** Error message: "Error mengakses kamera"

---

## ✅ Checklist Implementasi

### **Backend**
- [x] Install library `endroid/qr-code`
- [x] Create API `/admin/api/generate_qr.php`
- [x] Create download endpoint `/admin/download_qr.php`
- [x] Create generate page `/admin/generate_qr/index.php`
- [x] Implement error handling
- [x] Add middleware auth check

### **Frontend**
- [x] Create scanner page `/public/scan.php`
- [x] Include `html5-qrcode` library
- [x] Implement camera access
- [x] Handle scan success/error
- [x] Add fallback option
- [x] Responsive design

### **Database**
- [x] Table `tables` dengan field `code`
- [x] Unique constraint pada `code`
- [x] Sample data untuk testing

### **Integration**
- [x] Button "Lihat QR" di `tables.php`
- [x] Link "Lihat Semua QR" di `tables.php`
- [x] Auto-select table di `index.php` dari parameter `?code=`
- [x] Session management

### **Testing**
- [x] Unit test API
- [x] Integration test full flow
- [x] Cross-browser testing
- [x] Mobile testing (iOS & Android)
- [x] Print testing

### **Documentation**
- [x] User guide: `CARA_MEMBUAT_QR_CODE.md`
- [x] Feature doc: `FITUR_BARCODE_CHECKIN.md`
- [x] Testing doc: `TESTING_BARCODE.md`
- [x] Implementation guide: `TAHAPAN_IMPLEMENTASI_QR_CODE.md` (this file)

---

## 🎯 Key Learnings

### **1. Library Version Compatibility**
**Problem:** Syntax berbeda antara endroid/qr-code v5 dan v6

**Solution:**
```php
// v5 (old)
$qrCode = QrCode::create($data);

// v6 (current)
$qrCode = new QrCode(
    data: $url,
    size: 300,
    margin: 10
);
```

### **2. GD Extension Requirement**
**Problem:** QR Code tidak generate jika GD extension disabled

**Solution:**
- Check dengan `extension_loaded('gd')`
- Show helpful error message
- Dokumentasi cara enable GD di XAMPP

### **3. Camera Permission on Mobile**
**Problem:** Browser butuh HTTPS untuk camera access (except localhost)

**Solution:**
- Development: Gunakan localhost (allowed)
- Production: WAJIB HTTPS
- Add fallback option jika camera tidak bisa diakses

### **4. Print CSS**
**Problem:** Print output berantakan, element terpotong

**Solution:**
```css
@media print {
    .no-print { display: none; }
    .qr-card { page-break-inside: avoid; }
}
```

---

## 🚀 Future Enhancements

### **Planned Features:**
- [ ] Dynamic QR Code (bisa update tanpa print ulang)
- [ ] QR Code analytics (track scan count)
- [ ] Bulk download all QR as ZIP
- [ ] Custom QR Code design (logo, color)
- [ ] QR Code expiration/time limit
- [ ] Multi-language support
- [ ] QR Code with promo/discount

### **Technical Improvements:**
- [ ] Progressive Web App (PWA) untuk offline scanning
- [ ] WebSocket untuk real-time table status
- [ ] Cache QR Code image
- [ ] CDN untuk faster loading
- [ ] Auto-generate QR saat create table baru

---

## 📞 Troubleshooting

### **Issue 1: QR Code tidak muncul**
**Symptom:** Blank image atau error message

**Debug Steps:**
1. Check GD extension: `php -m | grep gd`
2. Check error log: `tail -f xampp/apache/logs/error.log`
3. Test library: Create simple test file
4. Check permissions: File write permission

**Solution:**
```bash
# Enable GD di php.ini
extension=gd

# Restart Apache
sudo apachectl restart
```

---

### **Issue 2: Scanner tidak bisa akses kamera**
**Symptom:** "Permission denied" atau "Camera not found"

**Debug Steps:**
1. Check browser permissions (chrome://settings/content/camera)
2. Check HTTPS requirement (localhost OK, production butuh HTTPS)
3. Test camera dengan native camera app
4. Try different browser

**Solution:**
- Grant camera permission di browser settings
- Use HTTPS di production
- Fallback ke pilih meja manual

---

### **Issue 3: QR Code terbaca tapi tidak redirect**
**Symptom:** Scan sukses tapi tetap di halaman scanner

**Debug Steps:**
1. Check console log (F12)
2. Verify decoded URL
3. Check JavaScript error
4. Test URL manual di browser

**Solution:**
```javascript
// Add debug log
console.log('Decoded:', decodedText);

// Check URL format
if (!decodedText.includes('http')) {
    console.error('Invalid URL format');
}
```

---

## 📚 References

### **Documentation:**
- endroid/qr-code: https://github.com/endroid/qr-code
- html5-qrcode: https://github.com/mebjas/html5-qrcode
- QR Code Spec: https://www.qrcode.com/en/about/standards.html

### **Related Files:**
- `CARA_MEMBUAT_QR_CODE.md` - User guide
- `FITUR_BARCODE_CHECKIN.md` - Feature spec
- `TESTING_BARCODE.md` - Testing guide
- `composer.json` - Dependencies
- `config/config.php` - BASE_URL config

---

**Last Updated:** 2025-01-20  
**Author:** System Documentation  
**Version:** 1.0

---

**Happy Scanning! 📱✨**
