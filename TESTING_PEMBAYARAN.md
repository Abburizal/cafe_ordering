# Panduan Testing Notifikasi Pembayaran

## Persiapan
1. Pastikan XAMPP MySQL dan Apache sudah running
2. Database `cafe_ordering` sudah ter-setup
3. Ada data meja dan produk di database

## Skenario Testing

### Test 1: Pembayaran dengan QRIS

1. **Buka aplikasi**: `http://localhost/cafe_ordering/public/`
2. **Scan QR Meja** atau masukkan ID meja
3. **Pilih Menu**: Tambahkan beberapa produk ke keranjang
4. **Ke Keranjang**: Klik icon keranjang
5. **Checkout**: Klik tombol "Lanjut ke Pembayaran"
6. **Pilih QRIS**: Klik tombol "Bayar dengan QRIS"

**Expected Result:**
- ✅ Muncul QR Code untuk scan
- ✅ Tampil detail: Nomor meja, Total bayar, Kode order
- ✅ Ada tombol hijau "Konfirmasi Pembayaran Sudah Dilakukan"
- ✅ Ada link "Cek Status Pesanan Saya"

7. **Konfirmasi**: Klik tombol "Konfirmasi Pembayaran Sudah Dilakukan"

**Expected Result:**
- ✅ Redirect ke halaman konfirmasi sukses
- ✅ Animasi confetti muncul
- ✅ Tampil pesan "Pembayaran Dikonfirmasi!"
- ✅ Detail order lengkap (kode, meja, metode, total)
- ✅ Status: "Pesanan sedang diproses dapur"
- ✅ Ada tombol "Cek Status" dan "Menu"

### Test 2: Pembayaran Tunai

1. **Ulangi step 1-5** dari Test 1
2. **Pilih Tunai**: Klik tombol "Bayar di Kasir (Tunai)"

**Expected Result:**
- ✅ Muncul modal sukses hijau
- ✅ Icon check circle dengan animasi
- ✅ Pesan "Pesanan Berhasil Dibuat!"
- ✅ Detail order (kode, meja, total)
- ✅ Box kuning dengan instruksi pembayaran:
  - "Pesanan sedang diproses dapur"
  - "Waiter akan datang ke meja"
  - "Lakukan pembayaran tunai sebesar [total]"
  - "Waiter akan mengkonfirmasi"
- ✅ Tombol "Cek Status Pesanan" (biru)
- ✅ Tombol "Kembali ke Menu" (ungu)

### Test 3: Cek Database

Setelah melakukan pembayaran, cek di database:

```sql
-- Cek order terbaru
SELECT * FROM orders ORDER BY id DESC LIMIT 5;

-- Cek detail item
SELECT oi.*, p.name 
FROM order_items oi 
JOIN products p ON oi.product_id = p.id 
WHERE oi.order_id = [ID_ORDER_TERAKHIR];
```

**Expected:**
- ✅ Order tersimpan dengan status yang benar:
  - QRIS setelah konfirmasi: `processing`
  - Tunai sebelum konfirmasi waiter: `pending`
- ✅ Field `payment_method` sesuai (`qris` atau `cash`)
- ✅ Total order sesuai perhitungan
- ✅ Order items tersimpan lengkap

### Test 4: Flow Lengkap

**Scenario A - Customer QRIS:**
1. Customer scan QR meja → pilih menu → checkout
2. Pilih QRIS → muncul QR Code
3. Customer scan dengan app pembayaran (simulasi)
4. Customer klik "Konfirmasi Pembayaran"
5. Status berubah `processing`
6. Dapur terima order

**Scenario B - Customer Tunai:**
1. Customer scan QR meja → pilih menu → checkout
2. Pilih Tunai → muncul notifikasi sukses
3. Customer baca instruksi
4. Waiter datang ke meja
5. Customer bayar tunai ke waiter
6. Waiter konfirmasi di admin (manual/nanti)
7. Status berubah `processing`
8. Dapur terima order

## Checklist Fitur

### Tampilan
- [ ] Design responsive (mobile & desktop)
- [ ] Icon Feather terender dengan benar
- [ ] Warna dan styling sesuai tema
- [ ] Animasi berjalan smooth
- [ ] Font Inter ter-load

### Fungsionalitas
- [ ] Order tersimpan ke database
- [ ] Order items tersimpan lengkap
- [ ] Status order update dengan benar
- [ ] Session cart ter-clear setelah checkout
- [ ] Redirect sesuai flow

### Error Handling
- [ ] Keranjang kosong → error message
- [ ] Nomor meja tidak ada → error message
- [ ] Database error → error message dengan rollback
- [ ] Product tidak ditemukan → error message

## Troubleshooting

### QR Code tidak muncul
- Cek vendor autoload sudah ter-install: `composer install`
- Cek library endroid/qr-code ada di vendor

### Database error
- Cek koneksi database di config/config.php
- Pastikan tabel `orders` dan `order_items` ada
- Cek foreign key constraint

### Session error
- Pastikan session_start() dipanggil
- Cek permission folder session PHP
- Clear browser cache

### Styling tidak muncul
- Cek koneksi internet (Tailwind CDN)
- Cek console browser untuk error
- Refresh dengan Ctrl+F5

## Demo untuk Bimbingan

Siapkan skenario:
1. Demo flow QRIS lengkap
2. Demo flow Tunai lengkap
3. Tunjukkan database sebelum/sesudah
4. Jelaskan perbedaan dengan Midtrans nanti
5. Tunjukkan kode yang siap diubah untuk integrasi

## Next Steps (Integrasi Midtrans)

1. Register akun Midtrans & dapatkan API keys
2. Install Midtrans SDK: `composer require midtrans/midtrans-php`
3. Ganti generate QR Code dengan Midtrans QRIS API
4. Setup webhook/notification handler
5. Update status otomatis dari callback Midtrans
6. Testing dengan Sandbox Midtrans
7. Production deployment dengan Production keys
