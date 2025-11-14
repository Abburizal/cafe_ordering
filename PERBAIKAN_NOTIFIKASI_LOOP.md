# PERBAIKAN NOTIFIKASI LOOP - PESANAN BARU

## 🐛 MASALAH YANG DIPERBAIKI

**Problem**: Notifikasi "Ada Pesanan Baru" terus muncul (nge-loop) setelah user klik "OK"

**Penyebab**:
1. Ada 2 script notification yang berjalan bersamaan dan saling conflict:
   - `notification.js` (NotificationManager class)
   - Inline polling script di `orders.php` (line 653-689)
2. Keduanya melakukan hal yang sama:
   - Polling API `cek_pesanan_baru.php`
   - Menampilkan alert/notifikasi
   - Auto reload halaman
3. Setelah user klik "OK" → halaman reload → script jalan lagi → notifikasi muncul lagi (LOOP)

---

## ✅ SOLUSI YANG DITERAPKAN

### 1. **Hapus Redundant Script**

**File**: `admin/orders.php`

**Perubahan**:
```php
// SEBELUM (line 652-689):
<!-- added: simple polling script untuk cek pesanan baru setiap 15 detik -->
<script>
  let currentPendingOrders = 0;
  async function cekPesananBaru() {
    // ... polling code ...
    alert('Ada pesanan baru!');
    location.reload(); // <-- INI YANG BIKIN LOOP!
  }
  cekPesananBaru();
</script>

// SESUDAH (simplified):
<!-- Notification handled by notification.js -->
<script src="assets/notification.js"></script>
```

**Hasil**: Hanya 1 notification system yang berjalan

---

### 2. **Perbaiki NotificationManager**

**File**: `admin/assets/notification.js`

#### A. **Prevent Duplicate Notifications**

```javascript
showInPageNotification(order) {
    // Check if notification already exists
    const existingNotif = document.querySelector(`[data-order-id="${order.id}"]`);
    if (existingNotif) {
        return; // Don't show duplicate
    }
    
    // Add data-order-id attribute
    notif.setAttribute('data-order-id', order.id);
    // ...
}
```

#### B. **No Page Reload - Update Table via AJAX**

```javascript
reloadOrderList() {
    // Jangan reload halaman, tapi reload konten tabel via AJAX
    if (typeof reloadOrders === 'function') {
        reloadOrders();
    } else {
        this.updateOrderTable(); // <-- AJAX update, no reload!
    }
}

async updateOrderTable() {
    // Fetch updated orders without page reload
    const response = await fetch(window.location.href);
    const html = await response.text();
    
    // Parse and update only the table
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newTable = doc.querySelector('table');
    const currentTable = document.querySelector('table');
    
    if (newTable && currentTable) {
        currentTable.innerHTML = newTable.innerHTML;
        feather.replace();
    }
}
```

#### C. **Initialize Last Order ID Properly**

```javascript
loadLastOrderId() {
    const stored = localStorage.getItem('lastOrderId');
    if (stored) {
        this.lastOrderId = parseInt(stored);
    } else {
        // Jika belum ada, ambil order ID terakhir dari server saat init
        this.fetchLatestOrderId();
    }
}

async fetchLatestOrderId() {
    const response = await fetch('api/cek_pesanan_baru.php?init=1');
    const data = await response.json();
    if (data.latest_order_id) {
        this.saveLastOrderId(data.latest_order_id);
    }
}
```

#### D. **Auto-close Notifications**

```javascript
// Browser notification
if ('Notification' in window && Notification.permission === 'granted') {
    const notification = new Notification(title, {
        body: message,
        tag: 'order-' + order.id,
        requireInteraction: false // Auto close
    });
    
    // Auto close after 5 seconds
    setTimeout(() => notification.close(), 5000);
}

// In-page notification - auto remove after 8 seconds
setTimeout(() => {
    if (notif.parentElement) {
        notif.style.opacity = '0';
        notif.style.transform = 'translateX(400px)';
        setTimeout(() => notif.remove(), 300);
    }
}, 8000);
```

---

### 3. **Update API Endpoint**

**File**: `admin/api/cek_pesanan_baru.php`

**Perubahan**:

```php
// SEBELUM: Hanya return count
echo json_encode(['new_orders' => (int)$count]);

// SESUDAH: Support 2 mode
// Mode 1: Init - return latest order ID
if (isset($_GET['init']) && $_GET['init'] == '1') {
    $stmt = $pdo->query("SELECT MAX(id) AS latest_id FROM orders");
    $latest_id = $stmt->fetchColumn();
    
    echo json_encode([
        'latest_order_id' => (int)$latest_id,
        'status' => 'initialized'
    ]);
    exit;
}

// Mode 2: Check new orders - dengan parameter last_id
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$stmt = $pdo->prepare("
    SELECT o.id, o.order_code, o.total, o.created_at,
           COALESCE(o.table_number, t.name, 'N/A') AS table_name
    FROM orders o
    LEFT JOIN tables t ON o.table_id = t.id
    WHERE o.id > ? AND o.status = 'pending'
    ORDER BY o.id ASC
");
$stmt->execute([$last_id]);
$new_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'ada_pesanan_baru' => count($new_orders) > 0,
    'pesanan_baru' => $new_orders,
    'jumlah' => count($new_orders)
]);
```

**Fitur Baru**:
- Support `?init=1` untuk get latest order ID saat pertama load
- Support `?last_id=X` untuk cek order baru setelah ID tertentu
- Return detail order lengkap (bukan hanya count)

---

## 🎯 CARA KERJA SETELAH PERBAIKAN

### Flow Notifikasi (Tanpa Loop):

1. **Initial Load** (Pertama kali buka halaman):
   ```
   NotificationManager.init()
   ↓
   Check localStorage.lastOrderId
   ↓
   Jika tidak ada → fetch API ?init=1
   ↓
   Save lastOrderId (contoh: 100)
   ↓
   Start polling setiap 10 detik
   ```

2. **Polling (Setiap 10 detik)**:
   ```
   fetch API ?last_id=100
   ↓
   Server check: SELECT * WHERE id > 100
   ↓
   Ada order baru (id: 101, 102)?
   ↓
   YES → Return pesanan_baru: [order 101, 102]
   ↓
   Save lastOrderId = 102
   ↓
   Show notification (in-page + browser)
   ↓
   Update table via AJAX (NO RELOAD!)
   ↓
   Polling continue dengan last_id=102
   ```

3. **User Klik "OK" atau Close Notifikasi**:
   ```
   Notifikasi hilang
   ↓
   lastOrderId tetap = 102 (tersimpan)
   ↓
   Polling continue dengan last_id=102
   ↓
   Tidak ada order > 102?
   ↓
   Tidak tampilkan notifikasi lagi ✅
   ```

**KEY POINT**: 
- ✅ lastOrderId tersimpan di localStorage
- ✅ Tidak ada page reload
- ✅ Table update via AJAX
- ✅ Notifikasi tidak muncul lagi untuk order yang sama

---

## 📋 FILE YANG DIUBAH

1. ✅ `admin/orders.php` - Hapus inline polling script
2. ✅ `admin/assets/notification.js` - Perbaiki NotificationManager
3. ✅ `admin/api/cek_pesanan_baru.php` - Update API endpoint

---

## 🧪 TESTING

### Test Scenario 1: New Order Notification

1. Login sebagai admin
2. Buka halaman Orders
3. Buat order baru dari customer (buka tab baru)
4. Tunggu max 10 detik
5. **Expected**: 
   - ✅ Notifikasi muncul 1x
   - ✅ Table auto update
   - ✅ Tidak ada page reload
   - ✅ Setelah close notifikasi, tidak muncul lagi

### Test Scenario 2: Multiple New Orders

1. Admin di halaman Orders
2. Customer buat 3 order sekaligus
3. **Expected**:
   - ✅ Notifikasi muncul untuk 3 order
   - ✅ Sound play 1x
   - ✅ Setelah semua ditutup, tidak muncul lagi

### Test Scenario 3: Page Refresh

1. Ada notifikasi order baru
2. User refresh halaman (F5)
3. **Expected**:
   - ✅ Notifikasi TIDAK muncul lagi
   - ✅ lastOrderId tetap tersimpan
   - ✅ Hanya order baru berikutnya yang muncul notifikasinya

---

## ⚠️ CATATAN PENTING

### Browser Compatibility

- **Desktop Notification**: Perlu user klik "Allow" saat pertama kali
- **In-page Notification**: Selalu muncul (fallback)
- **LocalStorage**: Supported di semua modern browsers

### Limitations

- **Polling Interval**: 10 detik (bisa diubah di constructor)
- **Auto-close**: 
  - Browser notification: 5 detik
  - In-page notification: 8 detik
- **Storage**: localStorage (per browser, per domain)

### Tips

1. **Clear localStorage** jika ingin reset:
   ```javascript
   localStorage.removeItem('lastOrderId');
   ```

2. **Disable notifications** sementara:
   ```javascript
   window.notificationManager.stopChecking();
   ```

3. **Enable kembali**:
   ```javascript
   window.notificationManager.startChecking();
   ```

---

## 🚀 IMPROVEMENT FUTURE

### Yang bisa ditambahkan nanti:

1. **WebSocket** instead of polling (real-time)
2. **Push Notifications** via Service Worker
3. **Notification History** log
4. **Sound customization** (pilih suara notifikasi)
5. **Filter notifications** by status/table
6. **Admin preferences** (enable/disable, interval, sound)

---

**Terakhir diupdate**: 7 November 2025  
**Status**: ✅ FIXED - Notifikasi tidak lagi nge-loop
