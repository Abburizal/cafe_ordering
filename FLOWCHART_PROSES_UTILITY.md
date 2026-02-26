# FLOWCHART PROSES & UTILITY PAGES

Dokumentasi flowchart untuk proses-proses utility dalam sistem cafe ordering.

---

## 20. Flow Diagram Proses Tambah ke Keranjang (add_cart.php)

```mermaid
flowchart TD
    Start([START]) --> GetParams[Terima Parameter<br/>product_id & qty]
    GetParams --> ValidateID{product_id<br/>valid?}
    
    ValidateID -->|Tidak| RedirectMenu[Redirect ke menu.php]
    RedirectMenu --> End1([END])
    
    ValidateID -->|Ya| CheckSession{Session cart<br/>ada?}
    CheckSession -->|Tidak| InitCart[Inisialisasi<br/>$_SESSION'cart' = array]
    CheckSession -->|Ya| CheckProduct{Produk sudah<br/>ada di cart?}
    InitCart --> CheckProduct
    
    CheckProduct -->|Ya| AddQty[Tambahkan qty ke<br/>jumlah yang sudah ada]
    CheckProduct -->|Tidak| NewItem[Buat item baru<br/>di cart dengan qty]
    
    AddQty --> SaveSession[Simpan ke Session]
    NewItem --> SaveSession
    SaveSession --> RedirectCart[Redirect ke cart.php]
    RedirectCart --> End2([END])
```

**Penjelasan Proses:**
1. Sistem menerima parameter `product_id` dan `qty` dari form
2. Validasi `product_id`, jika tidak valid redirect ke menu
3. Cek apakah session cart sudah ada, jika belum inisialisasi array kosong
4. Cek apakah produk sudah ada di keranjang:
   - Jika ya: tambahkan qty ke jumlah yang sudah ada
   - Jika tidak: buat entry baru dengan qty yang diberikan
5. Simpan perubahan ke session dan redirect ke halaman cart

---

## 21. Flow Diagram Proses Update Keranjang (update_cart.php)

```mermaid
flowchart TD
    Start([START]) --> GetParams[Terima Parameter<br/>action & product_id]
    GetParams --> ValidateID{product_id<br/>valid?}
    
    ValidateID -->|Tidak| RedirectCart1[Redirect ke cart.php]
    RedirectCart1 --> End1([END])
    
    ValidateID -->|Ya| CheckAction{Cek Action}
    
    CheckAction -->|increase| Increase[Tambah quantity<br/>+1]
    CheckAction -->|decrease| Decrease{Kurangi quantity<br/>-1}
    CheckAction -->|delete| Delete[Hapus item dengan<br/>unset]
    CheckAction -->|clear| Clear[Kosongkan seluruh cart]
    CheckAction -->|invalid| RedirectCart2[Redirect ke cart.php]
    
    Decrease -->|qty <= 0| Delete
    Decrease -->|qty > 0| UpdateQty[Update quantity]
    
    Increase --> SaveSession[Simpan ke Session]
    UpdateQty --> SaveSession
    Delete --> SaveSession
    Clear --> SaveSession
    SaveSession --> RedirectCart3[Redirect ke cart.php]
    RedirectCart2 --> End2([END])
    RedirectCart3 --> End3([END])
```

**Penjelasan Proses:**
1. Sistem menerima parameter `action` dan `product_id`
2. Validasi `product_id`, jika tidak valid redirect ke cart
3. Proses berdasarkan action:
   - **increase**: Tambah quantity sebanyak 1
   - **decrease**: Kurangi quantity 1, jika hasil <= 0 maka hapus item
   - **delete**: Hapus item menggunakan `unset()`
   - **clear**: Kosongkan seluruh keranjang
4. Simpan perubahan ke session dan redirect kembali ke cart

---

## 22. Flow Diagram Webhook Midtrans (midtrans_notify.php)

```mermaid
flowchart TD
    Start([START]) --> Receive[Terima Notification<br/>dari Midtrans]
    Receive --> GetJSON[Parse JSON Body<br/>Request]
    GetJSON --> ValidateSignature{Validasi<br/>Signature Key?}
    
    ValidateSignature -->|Gagal| LogError[Log Error:<br/>Invalid Signature]
    LogError --> Return400[Return HTTP 400<br/>Bad Request]
    Return400 --> End1([END])
    
    ValidateSignature -->|Berhasil| GetOrderID[Ambil order_id &<br/>transaction_status]
    GetOrderID --> CheckStatus{Cek Transaction<br/>Status}
    
    CheckStatus -->|settlement| UpdatePaid[Update status order<br/>menjadi 'paid']
    CheckStatus -->|pending| UpdatePending[Update status order<br/>menjadi 'pending']
    CheckStatus -->|cancel/deny/expire| UpdateCancelled[Update status order<br/>menjadi 'cancelled']
    
    UpdatePaid --> SaveDB[Simpan perubahan<br/>ke Database]
    UpdatePending --> SaveDB
    UpdateCancelled --> SaveDB
    
    SaveDB --> LogSuccess[Log Success<br/>Transaction Updated]
    LogSuccess --> Return200[Return HTTP 200 OK]
    Return200 --> End2([END])
```

**Penjelasan Proses:**
1. Sistem menerima HTTP POST notification dari server Midtrans
2. Parse JSON body untuk mendapatkan data transaksi
3. **Validasi Signature Key** untuk keamanan:
   - Hitung hash menggunakan server key
   - Bandingkan dengan signature yang dikirim Midtrans
   - Jika tidak cocok, tolak request dengan HTTP 400
4. Ambil `order_id` dan `transaction_status` dari notification
5. Update status order di database berdasarkan status transaksi:
   - **settlement**: Order berhasil dibayar → status 'paid'
   - **pending**: Menunggu pembayaran → status 'pending'
   - **cancel/deny/expire**: Pembayaran gagal/dibatalkan → status 'cancelled'
6. Log hasil proses dan return HTTP 200 OK ke Midtrans

---

## 23. Flow Diagram Proses Logout Admin (logout.php)

```mermaid
flowchart TD
    Start([START]) --> CheckSession{Session<br/>sudah aktif?}
    
    CheckSession -->|Tidak| StartSession[Mulai Session<br/>session_start]
    CheckSession -->|Ya| Unset[Hapus semua session variables<br/>session_unset]
    StartSession --> Unset
    
    Unset --> Destroy[Hancurkan session ID<br/>session_destroy]
    Destroy --> ClearCookie{Cookie session<br/>ada?}
    
    ClearCookie -->|Ya| DeleteCookie[Hapus cookie session<br/>setcookie dengan expired]
    ClearCookie -->|Tidak| Redirect[Redirect ke login.php]
    DeleteCookie --> Redirect
    
    Redirect --> End([END])
```

**Penjelasan Proses:**
1. Cek apakah session sudah aktif, jika belum panggil `session_start()`
2. **Hapus semua session variables** menggunakan `session_unset()`:
   - Menghapus semua variabel seperti user_id, username, role, dll
3. **Hancurkan session ID** menggunakan `session_destroy()`:
   - Menghapus file session dari server
   - Session ID tidak bisa digunakan lagi
4. (Opsional) Hapus cookie session dari browser jika ada
5. Redirect user ke halaman login.php
6. User harus login ulang untuk mengakses admin area

---

## Keterangan Simbol Flowchart

- **Oval**: Start/End
- **Persegi Panjang**: Proses/Aksi
- **Belah Ketupat**: Decision/Kondisi
- **Panah**: Alur proses
- **Persegi Panjang Sudut Membulat**: Input/Output

---

## File Terkait

1. `public/add_cart.php` - Proses tambah item ke keranjang
2. `public/update_cart.php` - Proses update dan hapus item keranjang
3. `public/midtrans_notify.php` - Webhook notification dari Midtrans
4. `admin/logout.php` - Proses logout admin

---

**Catatan:**
- Semua proses menggunakan PHP session untuk menyimpan data keranjang
- Midtrans notification handler memerlukan validasi signature untuk keamanan
- Logout process memastikan tidak ada session yang tersisa di server
