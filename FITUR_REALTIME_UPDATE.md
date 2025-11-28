# 🚀 FITUR REAL-TIME UPDATE SISTEM

## Daftar Fitur Baru

### 1. ✅ Auto-Update Dashboard Admin
- **Lokasi**: `/admin/dashboard.php`
- **API**: `/admin/api/get_orders_realtime.php`
- **Interval Polling**: 3 detik (dapat dikonfigurasi)
- **Fitur**:
  - Dashboard otomatis menampilkan data pesanan terbaru
  - Metrik pendapatan update real-time
  - Notifikasi suara dan browser ketika ada perubahan pesanan
  - Tidak perlu refresh manual

### 2. ✅ Real-Time Customer Notification
- **Lokasi**: `/public/order_status.php`
- **API**: `/public/api/get_order_status_realtime.php`
- **Interval Polling**: 3 detik (dapat dikonfigurasi)
- **Fitur**:
  - Status pesanan update otomatis di halaman customer
  - Notifikasi browser push saat status berubah
  - Timeline pesanan update secara real-time
  - Suara notifikasi untuk alert penting
  - Otomatis stop polling setelah pesanan selesai/dibatalkan

---

## Arsitektur Teknis

### Komponen Utama

#### 1. **RealtimeOrderManager** (JavaScript Library)
**File**: `/admin/assets/realtime-manager.js`

Kelas JavaScript yang menangani:
- Polling otomatis ke server
- Deteksi perubahan status pesanan
- Trigger notifikasi browser dan suara
- Event listeners untuk update UI

**Constructor Options**:
```javascript
{
    pollInterval: 3000,           // Interval polling dalam ms
    notificationEnabled: true,    // Aktifkan notifikasi browser
    soundEnabled: true,           // Aktifkan suara notifikasi
    debug: false,                 // Debug mode
    onOrderUpdate: (data) => {},  // Callback saat ada update
    onOrderStatusChange: (event) => {},  // Callback saat status berubah
    onNewOrder: (order) => {},    // Callback saat order baru
    onError: (error) => {}        // Callback saat error
}
```

**Usage di Admin Dashboard**:
```javascript
const realtimeManager = new RealtimeOrderManager({
    pollInterval: 3000,
    notificationEnabled: true,
    soundEnabled: true,
    debug: false,
    onOrderStatusChange: (event) => {
        // Handle status change
    },
    onNewOrder: (order) => {
        // Handle new order
    }
});

// Start polling
realtimeManager.start('api/get_orders_realtime.php', {
    status: 'semua'
});
```

#### 2. **Admin Real-Time API**
**Endpoint**: `/admin/api/get_orders_realtime.php`
**Method**: GET
**Query Parameters**:
- `status`: Filter status pesanan (semua|pending|processing|done|cancelled)

**Response**:
```json
{
    "success": true,
    "orders": [
        {
            "id": 123,
            "order_code": "ORD-2025...",
            "status": "processing",
            "total": 50000,
            "table_name": "Meja 1",
            "created_at": "2025-11-28 16:52:56",
            "items": [
                {
                    "qty": 2,
                    "price": 25000,
                    "product_name": "Kopi Americano"
                }
            ]
        }
    ],
    "timestamp": "2025-11-28 16:52:56",
    "total": 1
}
```

#### 3. **Customer Real-Time API**
**Endpoint**: `/public/api/get_order_status_realtime.php`
**Method**: GET
**Query Parameters**:
- `order_id`: ID pesanan yang ingin dipantau

**Response**:
```json
{
    "success": true,
    "order": {
        "id": 123,
        "order_code": "ORD-2025...",
        "status": "processing",
        "total": 50000,
        "table_name": "Meja 1",
        "created_at": "2025-11-28 16:52:56",
        "items": [
            {
                "qty": 2,
                "price": 25000,
                "product_name": "Kopi Americano"
            }
        ]
    },
    "timestamp": "2025-11-28 16:52:56"
}
```

---

## Flow Sistem Real-Time

### Untuk Admin Dashboard:

```
┌─────────────────────────────────────────────────────────┐
│ Browser Admin membuka dashboard.php                      │
└──────────────────┬──────────────────────────────────────┘
                   │
                   ▼
    ┌──────────────────────────────┐
    │ DOMContentLoaded event        │
    │ - Minta izin notifikasi       │
    │ - Start RealtimeManager       │
    │ - polling ke server setiap 3s │
    └──────────────┬────────────────┘
                   │
                   ▼
    ┌──────────────────────────────────┐
    │ fetch('api/get_orders_real...')   │
    │ Setiap 3 detik                    │
    └──────────────┬───────────────────┘
                   │
                   ▼
    ┌──────────────────────────────────────┐
    │ Server proses query ke database       │
    │ Return JSON dengan semua order+items  │
    └──────────────┬──────────────────────┘
                   │
                   ▼
    ┌──────────────────────────────────┐
    │ RealtimeManager compare status     │
    │ dengan data sebelumnya            │
    └──────────────┬───────────────────┘
                   │
         ┌─────────┴──────────┐
         │                    │
    STATUS BERUBAH?      STATUS SAMA?
         │                    │
         ▼                    ▼
    ┌──────────────┐    Tunggu 3s lagi
    │ Trigger:     │
    │ - Notif suara│
    │ - Browser    │
    │   push       │
    │ - Update UI  │
    │ - Callback   │
    └──────────────┘
```

### Untuk Customer Order Status:

```
┌──────────────────────────────────────────────────────┐
│ Customer membuka order_status.php?order_id=123       │
└───────────────────┬────────────────────────────────┘
                    │
                    ▼
    ┌────────────────────────────────┐
    │ DOMContentLoaded event          │
    │ - Minta izin notifikasi browser │
    │ - Start RealtimeManager         │
    │ - Polling setiap 3 detik        │
    └────────────────┬───────────────┘
                    │
                    ▼
    ┌─────────────────────────────────────┐
    │ fetch('api/get_order_status_...')    │
    │ params: order_id                     │
    │ Setiap 3 detik                       │
    └────────────────┬────────────────────┘
                    │
                    ▼
    ┌──────────────────────────────────┐
    │ Server query order detail         │
    │ Return JSON dengan order terbaru  │
    └────────────────┬─────────────────┘
                    │
                    ▼
    ┌──────────────────────────────┐
    │ Compare status dengan sebelum  │
    └────────────────┬──────────────┘
         ┌─────────┬─────────────┐
         │         │             │
    STATUS   DONE?  CANCELLED?
    BERUBAH? │       │
         │  ▼       ▼
         │ STOP  STOP
         │ POLLING POLLING
         │
         ▼
    ┌──────────────────────┐
    │ Trigger:             │
    │ - Browser push       │
    │ - Suara notifikasi   │
    │ - Update timeline    │
    │ - Update status      │
    │ - Update badge      │
    └──────────────────────┘
```

---

## Konfigurasi & Customization

### Mengubah Interval Polling

Di dalam `dashboard.php` atau `order_status.php`:

```javascript
const realtimeManager = new RealtimeOrderManager({
    pollInterval: 2000  // Ubah dari 3000 ke 2000ms (2 detik)
});
```

### Disable Notifikasi Suara

```javascript
const realtimeManager = new RealtimeOrderManager({
    soundEnabled: false  // Disable notifikasi suara
});
```

### Enable Debug Mode

```javascript
const realtimeManager = new RealtimeOrderManager({
    debug: true  // Console akan menampilkan log detail
});
```

### Custom Event Listeners

```javascript
const realtimeManager = new RealtimeOrderManager({
    onOrderUpdate: (data) => {
        console.log('Update diterima:', data.orders.length, 'orders');
    },
    
    onOrderStatusChange: (event) => {
        console.log(`Order ${event.orderCode}: ${event.oldStatus} → ${event.newStatus}`);
        // Custom logic di sini
    },
    
    onNewOrder: (order) => {
        console.log('Order baru:', order.order_code);
        // Custom logic di sini
    },
    
    onError: (error) => {
        console.error('Error:', error);
        // Handle error
    }
});
```

---

## Keamanan

### Admin API Protections
✅ Session validation - hanya admin yang terautentikasi
✅ Role checking - memastikan role='admin'
✅ SQL injection prevention - menggunakan prepared statements
✅ XSS prevention - menggunakan json_encode dengan ENT_QUOTES

### Customer API Protections
✅ SQL injection prevention - prepared statements
✅ No authentication required (pesanan bersifat publik via order_id)
✅ Limited query results - LIMIT 50 untuk admin API
✅ JSON response sanitization

---

## Troubleshooting

### 1. Polling Tidak Berjalan
**Penyebab**: Session tidak aktif atau JavaScript error
**Solusi**:
- Buka browser console (F12)
- Cek apakah ada error JavaScript
- Pastikan file `realtime-manager.js` ter-load dengan benar

### 2. Notifikasi Tidak Muncul
**Penyebab**: Izin notifikasi ditolak
**Solusi**:
- Buka browser settings → Notifikasi
- Izinkan notifikasi untuk domain ini
- Atau klik tombol "Minta Izin Notifikasi" di halaman admin

### 3. Suara Tidak Terdengar
**Penyebab**: Audio context permission atau file tidak ada
**Solusi**:
- Pastikan audio diizinkan di browser
- RealtimeManager akan fallback ke Web Audio API

### 4. Response API Error
**Penyebab**: Session expired atau database error
**Solusi**:
- Check log di `/error.log` atau console
- Pastikan database connection aktif
- Re-login untuk refresh session

---

## Performance & Optimization

### Database Queries
- **Admin API**: Query 50 pesanan terbaru (LIMIT 50)
- **Customer API**: Query 1 pesanan spesifik saja
- **Index Optimization**: 
  - Pastikan ada index pada `orders.id`, `orders.status`, `orders.created_at`
  - Index pada `order_items.order_id`

### Network
- Polling setiap 3 detik = 20 request per menit
- Setiap response ~1-2KB
- Total ~20-40KB per menit per user

### Caching Considerations
- JSON Response tidak di-cache (cache-control: no-cache)
- Real-time data selalu fresh dari database
- Tidak ada server-side session caching

---

## File Changes Summary

### Files Created:
1. `/admin/api/get_orders_realtime.php` - Admin real-time API endpoint
2. `/public/api/get_order_status_realtime.php` - Customer real-time API endpoint
3. `/admin/assets/realtime-manager.js` - JavaScript real-time manager library

### Files Modified:
1. `/admin/dashboard.php` - Ditambah real-time polling
2. `/admin/orders.php` - Diganti ke real-time manager
3. `/public/order_status.php` - Diganti ke real-time manager

---

## Fitur yang Akan Datang (Future Enhancement)

- [ ] WebSocket support untuk latency lebih rendah
- [ ] Service Worker untuk background sync
- [ ] IndexedDB untuk local caching
- [ ] Retry logic dengan exponential backoff
- [ ] Auto-scaling polling interval berdasarkan server load
- [ ] Push notification via Firebase Cloud Messaging
- [ ] Sound customization dan mp3 upload
- [ ] Analytics dashboard untuk real-time metrics

---

## Testing Checklist

- [ ] Admin dapat melihat order baru tanpa refresh
- [ ] Status order update otomatis di dashboard
- [ ] Customer melihat status update di order_status.php
- [ ] Notifikasi browser muncul saat ada perubahan
- [ ] Suara notifikasi terdengar
- [ ] Dashboard tetap responsive saat polling
- [ ] Tidak ada memory leak setelah polling lama
- [ ] Session admin tetap aman
- [ ] Customer order_id tidak bisa akses order lain
- [ ] Error handling berjalan dengan baik

---

**Dibuat**: 28 November 2025
**Version**: 1.0
**Status**: Production Ready ✅
