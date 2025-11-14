# Notifikasi Pembayaran - Prototype

## Fitur yang Ditambahkan

### 1. **Pembayaran QRIS** (`pay_qris.php`)
   - Menampilkan QR Code untuk pembayaran (simulasi)
   - Tombol "Konfirmasi Pembayaran Sudah Dilakukan"
   - Setelah scan QR, pelanggan klik tombol untuk konfirmasi
   - Order tersimpan dengan status `pending`

### 2. **Pembayaran Tunai** (`tunai.php`)
   - Notifikasi pesanan berhasil dibuat
   - Instruksi yang jelas untuk menunggu waiter
   - Detail pembayaran yang harus dilakukan
   - Link ke status pesanan dan menu
   - Order tersimpan dengan status `pending`

### 3. **Konfirmasi Pembayaran** (`confirm_payment.php`)
   - Halaman konfirmasi setelah pembayaran dilakukan
   - Update status order dari `pending` ke `processing`
   - Tampilan sukses dengan animasi (confetti)
   - Detail order dan informasi status pesanan
   - Link ke cek status pesanan dan kembali ke menu

## Flow Pembayaran

### Flow QRIS:
1. Customer memilih "Bayar dengan QRIS" di checkout
2. Sistem generate QR Code (simulasi)
3. Customer scan QR Code
4. Customer klik "Konfirmasi Pembayaran Sudah Dilakukan"
5. Status order: `pending` → `processing`
6. Pesanan diteruskan ke dapur

### Flow Tunai:
1. Customer memilih "Bayar di Kasir (Tunai)" di checkout
2. Sistem create order dengan status `pending`
3. Tampil notifikasi sukses dengan instruksi
4. Waiter datang ke meja untuk terima pembayaran
5. Waiter konfirmasi di sistem admin (nanti)
6. Status order: `pending` → `processing`

## Perubahan Database

Status order yang digunakan:
- `pending`: Order baru dibuat, menunggu konfirmasi pembayaran
- `processing`: Pembayaran dikonfirmasi, pesanan sedang diproses dapur
- `done`: Pesanan selesai
- `cancelled`: Pesanan dibatalkan

## File yang Dimodifikasi/Dibuat

1. **public/pay_qris.php** - Ditambahkan tombol konfirmasi pembayaran
2. **public/tunai.php** - Diperbaiki notifikasi dan instruksi pembayaran
3. **public/confirm_payment.php** - File baru untuk halaman konfirmasi pembayaran

## Untuk Integrasi Midtrans Nanti

Saat integrasi dengan Midtrans:
1. Ganti generate QR Code dengan API Midtrans
2. Ganti tombol konfirmasi manual dengan webhook/callback Midtrans
3. Status otomatis berubah dari notifikasi Midtrans
4. Tambah field `midtrans_id` untuk tracking transaksi

## Testing

Untuk test prototype ini:
1. Buka aplikasi dan scan QR meja
2. Tambahkan produk ke keranjang
3. Checkout dan pilih metode pembayaran
4. Test flow QRIS: scan QR → klik konfirmasi
5. Test flow Tunai: lihat notifikasi dan instruksi
6. Cek status pesanan setelah konfirmasi

## Catatan
- Ini adalah prototype untuk demo/bimbingan
- QR Code yang dihasilkan adalah simulasi
- Konfirmasi pembayaran masih manual
- Siap untuk integrasi Midtrans
