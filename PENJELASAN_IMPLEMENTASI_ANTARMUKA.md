# 📝 Penjelasan Implementasi Antarmuka (Interface)

## Pengantar

Sistem Cafe Ordering ini memiliki 24 halaman antarmuka yang terbagi menjadi dua bagian utama: **Customer Interface** (15 halaman) untuk pelanggan dan **Admin Interface** (9 halaman) untuk pengelola. Setiap halaman dirancang dengan pendekatan *user-friendly* dan menggunakan framework Tailwind CSS untuk tampilan yang modern dan responsif. Berikut adalah penjelasan detail untuk setiap implementasi antarmuka:

---

## A. CUSTOMER INTERFACE (Halaman Pelanggan)

### 1. Implementasi Halaman Scan QR Code & Landing Page

Proses pemesanan dimulai ketika pelanggan memindai QR Code yang tersedia di meja. Sistem dirancang dengan alur **Guest Checkout**, di mana pelanggan tidak diwajibkan login atau registrasi di awal. Saat QR Code dipindai, sistem secara otomatis menangkap **Session ID** meja dan langsung mengarahkan pelanggan ke halaman daftar menu. Halaman ini menggunakan teknologi kamera device untuk membaca QR Code dengan cepat dan akurat. Jika QR Code tidak valid atau tidak ditemukan, sistem akan menampilkan pesan error dan meminta pelanggan untuk scan ulang. Dapat dilihat pada **Gambar 4.8 Implementasi Halaman Scan QR Code**.

**Gambar 4.8 Implementasi Halaman Scan QR Code**

---

### 2. Implementasi Halaman Daftar Menu (Product Catalog)

Setelah berhasil scan QR Code, pelanggan diarahkan ke halaman daftar menu yang menampilkan semua produk yang tersedia. Halaman ini menampilkan informasi lengkap setiap produk meliputi **gambar, nama, deskripsi, dan harga**. Pelanggan dapat langsung memilih jumlah (*quantity*) produk yang diinginkan menggunakan input numerik, kemudian menekan tombol **"Tambah"** untuk memasukkan produk ke keranjang belanja. Di bagian atas halaman terdapat informasi **nomor meja** yang sedang digunakan serta tombol akses cepat ke **keranjang belanja**. Produk ditampilkan dalam bentuk **grid card** yang responsif, sehingga mudah dilihat baik di perangkat mobile maupun desktop. Dapat dilihat pada **Gambar 4.9 Implementasi Halaman Daftar Menu**.

**Gambar 4.9 Implementasi Halaman Daftar Menu**

---

### 3. Implementasi Halaman Keranjang Belanja

Halaman keranjang belanja menampilkan **ringkasan semua produk** yang telah dipilih oleh pelanggan. Setiap item ditampilkan dengan detail meliputi **nama produk, harga satuan, quantity, dan subtotal**. Pelanggan dapat melakukan **update quantity** dengan mengubah jumlah item langsung di halaman ini, atau **menghapus item** yang tidak diinginkan menggunakan tombol hapus. Di bagian bawah halaman ditampilkan **total keseluruhan** yang harus dibayar secara real-time. Tersedia dua tombol aksi: **"Lanjutkan Belanja"** untuk kembali ke menu, dan **"Checkout"** untuk melanjutkan ke proses pembayaran. Halaman ini juga menampilkan **validasi** jika keranjang kosong, dengan memberikan opsi untuk kembali ke halaman menu. Dapat dilihat pada **Gambar 4.10 Implementasi Halaman Keranjang Belanja**.

**Gambar 4.10 Implementasi Halaman Keranjang Belanja**

---

### 4. Implementasi Halaman Checkout

Halaman checkout berfungsi sebagai **konfirmasi akhir pesanan** sebelum pembayaran. Pada halaman ini ditampilkan **ringkasan lengkap pesanan** meliputi daftar item, quantity, harga per item, dan total pembayaran. Pelanggan dapat memilih **metode pembayaran** yang diinginkan: **QRIS** (pembayaran digital menggunakan e-wallet) atau **Cash/Tunai** (pembayaran di kasir). Terdapat juga informasi **nomor meja** yang akan digunakan untuk pengantaran pesanan. Setelah memilih metode pembayaran dan mengkonfirmasi pesanan, sistem akan generate **Kode Order unik** dengan format `ORD-YYYYMMDD-XXXXXX` yang dapat digunakan untuk tracking. Halaman ini dilengkapi dengan tombol **"Kembali"** untuk revisi pesanan dan tombol **"Konfirmasi Pesanan"** untuk melanjutkan pembayaran. Dapat dilihat pada **Gambar 4.11 Implementasi Halaman Checkout**.

**Gambar 4.11 Implementasi Halaman Checkout**

---

### 5. Implementasi Halaman Pembayaran QRIS

Halaman pembayaran QRIS menampilkan **QR Code pembayaran** yang di-generate oleh payment gateway Midtrans. Pelanggan dapat melakukan pembayaran dengan cara **scan QR Code** menggunakan aplikasi e-wallet seperti GoPay, OVO, Dana, ShopeePay, atau LinkAja. QR Code yang ditampilkan bersifat **dinamis** dan khusus untuk transaksi tersebut dengan nominal yang sudah ditentukan. Halaman ini menampilkan **countdown timer** sebagai batas waktu pembayaran (biasanya 15-30 menit), serta informasi **detail pesanan** meliputi order code, total pembayaran, dan instruksi pembayaran. Sistem melakukan **auto-refresh** untuk mengecek status pembayaran secara berkala. Jika pembayaran berhasil, pelanggan otomatis diarahkan ke **halaman success**. Tersedia juga tombol **"Batalkan Pesanan"** jika pelanggan ingin membatalkan transaksi. Dapat dilihat pada **Gambar 4.12 Implementasi Halaman Pembayaran QRIS**.

**Gambar 4.12 Implementasi Halaman Pembayaran QRIS**

---

### 6. Implementasi Halaman Pembayaran Tunai

Halaman pembayaran tunai menampilkan **konfirmasi pesanan** untuk metode pembayaran cash di kasir. Pada halaman ini ditampilkan **kode pesanan (Order Code)** yang dapat ditunjukkan ke kasir, beserta **detail lengkap pesanan** dan **total yang harus dibayar**. Pelanggan diminta untuk **menunjukkan halaman ini** atau menyebutkan kode pesanan kepada kasir untuk proses pembayaran. Setelah kasir menerima pembayaran, kasir akan **mengkonfirmasi pembayaran** melalui sistem admin, dan status pesanan otomatis berubah menjadi **"Processing"** (sedang diproses dapur). Halaman ini juga dilengkapi dengan tombol **"Konfirmasi Sudah Bayar"** yang dapat di-klik pelanggan setelah selesai membayar di kasir untuk menuju ke halaman tracking pesanan. Dapat dilihat pada **Gambar 4.13 Implementasi Halaman Pembayaran Tunai**.

**Gambar 4.13 Implementasi Halaman Pembayaran Tunai**

---

### 7. Implementasi Halaman Konfirmasi Pembayaran

Halaman konfirmasi pembayaran ditampilkan sebagai **halaman transisi** setelah pelanggan melakukan pembayaran (baik QRIS maupun tunai). Halaman ini memberikan **feedback visual** kepada pelanggan bahwa pembayaran telah **diterima oleh sistem** dan sedang dalam proses verifikasi. Ditampilkan **loading indicator** atau animasi untuk menunjukkan bahwa sistem sedang memproses data pembayaran. Pada halaman ini juga terdapat informasi **kode pesanan**, **status pembayaran**, dan **estimasi waktu** verifikasi. Setelah verifikasi selesai, pelanggan otomatis diarahkan ke **halaman status pesanan** untuk melakukan tracking real-time. Halaman ini penting untuk memberikan **user experience** yang baik dengan memberikan respons visual bahwa proses pembayaran tidak diabaikan. Dapat dilihat pada **Gambar 4.14 Implementasi Halaman Konfirmasi Pembayaran**.

**Gambar 4.14 Implementasi Halaman Konfirmasi Pembayaran**

---

### 8. Implementasi Halaman Status Pesanan

Halaman status pesanan adalah **dashboard tracking** yang memungkinkan pelanggan memantau pesanan secara **real-time**. Halaman ini menampilkan **timeline progress** pesanan dengan visualisasi tahapan: **Pending** (menunggu konfirmasi) → **Processing** (sedang diproses dapur) → **Done** (selesai & siap diantar). Setiap tahapan ditandai dengan **icon status** dan **warna badge** yang berbeda untuk memudahkan identifikasi. Ditampilkan juga **detail lengkap pesanan** meliputi order code, daftar item yang dipesan, total pembayaran, nomor meja, dan metode pembayaran. Halaman ini menggunakan **auto-refresh** atau **AJAX polling** setiap beberapa detik untuk update status terbaru dari server tanpa perlu reload halaman. Ketika status berubah menjadi **"Done"**, muncul notifikasi bahwa pesanan sudah siap dan akan segera diantarkan ke meja. Dapat dilihat pada **Gambar 4.15 Implementasi Halaman Status Pesanan**.

**Gambar 4.15 Implementasi Halaman Status Pesanan**

---

### 9. Implementasi Halaman Pembayaran Berhasil (Success)

Halaman success ditampilkan ketika **pembayaran telah berhasil diverifikasi** oleh sistem. Halaman ini menampilkan **pesan sukses** dengan visual yang menarik (icon centang/checkmark berwarna hijau) untuk memberikan **positive feedback** kepada pelanggan. Ditampilkan informasi **kode pesanan**, **total pembayaran**, **metode pembayaran** yang digunakan, dan **waktu transaksi**. Terdapat pesan terima kasih dan informasi bahwa pesanan sedang **diproses oleh dapur**. Pelanggan diberikan **dua pilihan aksi**: tombol **"Lihat Status Pesanan"** untuk tracking real-time, atau tombol **"Kembali ke Menu"** untuk melakukan pemesanan tambahan. Halaman ini juga menampilkan **ringkasan pesanan** secara lengkap sebagai bukti transaksi digital. Pelanggan dapat **screenshot** halaman ini sebagai bukti pembayaran. Dapat dilihat pada **Gambar 4.16 Implementasi Halaman Pembayaran Berhasil**.

**Gambar 4.16 Implementasi Halaman Pembayaran Berhasil**

---

### 10. Implementasi Halaman Pembayaran Dibatalkan (Cancel)

Halaman cancel ditampilkan ketika pelanggan **membatalkan proses pembayaran** atau ketika pembayaran **gagal/expired**. Halaman ini menampilkan **pesan notifikasi** dengan visual yang jelas (icon warning/silang berwarna merah atau oranye) untuk menandakan bahwa transaksi tidak selesai. Ditampilkan informasi **alasan pembatalan** (timeout, user cancel, payment failed), **kode pesanan** yang dibatalkan, dan **total yang seharusnya dibayar**. Sistem otomatis mengubah status pesanan menjadi **"Cancelled"** di database untuk keperluan pencatatan. Pelanggan diberikan **opsi untuk mencoba lagi**: tombol **"Coba Lagi"** untuk kembali ke halaman checkout dan melakukan pemesanan ulang, atau tombol **"Kembali ke Menu"** untuk memilih produk yang berbeda. Halaman ini penting untuk memberikan **clear communication** kepada pelanggan mengenai status transaksi yang gagal. Dapat dilihat pada **Gambar 4.17 Implementasi Halaman Pembayaran Dibatalkan**.

**Gambar 4.17 Implementasi Halaman Pembayaran Dibatalkan**

---

### 11. Implementasi Halaman Riwayat Pesanan

Halaman riwayat pesanan menampilkan **daftar semua pesanan** yang pernah dilakukan oleh pelanggan dari device yang sama (berdasarkan session atau cookies). Setiap pesanan ditampilkan dalam bentuk **card** dengan informasi meliputi **order code**, **tanggal & waktu** pemesanan, **status pesanan** (Pending, Processing, Done, Cancelled), **total pembayaran**, dan **metode pembayaran**. Pelanggan dapat melihat **detail lengkap** setiap pesanan dengan meng-klik card tersebut. Status pesanan ditandai dengan **warna badge** yang berbeda: hijau untuk Done, biru untuk Processing, kuning untuk Pending, dan merah untuk Cancelled. Halaman ini memiliki **filter** berdasarkan status dan **pencarian** berdasarkan order code. Riwayat pesanan diurutkan dari yang **terbaru ke terlama** (descending by date). Fitur ini berguna untuk pelanggan yang ingin **melacak pesanan lama** atau **repeat order** dengan produk yang sama. Dapat dilihat pada **Gambar 4.18 Implementasi Halaman Riwayat Pesanan**.

**Gambar 4.18 Implementasi Halaman Riwayat Pesanan**

---

## B. ADMIN INTERFACE (Halaman Administrator)

### 12. Implementasi Halaman Login Admin

Halaman login admin adalah **gerbang utama** untuk mengakses dashboard pengelolaan sistem. Halaman ini menampilkan **form login** dengan dua field input: **username/email** dan **password**. Sistem menggunakan **prepared statement** untuk mencegah SQL Injection dan **password hashing** menggunakan algoritma bcrypt untuk keamanan. Terdapat opsi **"Remember Me"** (checkbox) untuk menyimpan session lebih lama. Setelah login berhasil, sistem akan **membuat session** dan menyimpan informasi admin (user_id, username, role) untuk keperluan authorization di halaman-halaman admin lainnya. Jika kredensial salah, ditampilkan **pesan error** yang jelas. Terdapat juga link **"Registrasi Admin"** untuk membuat akun admin baru (biasanya hanya untuk super admin). Halaman login menggunakan **middleware** untuk mengecek apakah user sudah login - jika sudah, akan langsung redirect ke dashboard. Dapat dilihat pada **Gambar 4.19 Implementasi Halaman Login Admin**.

**Gambar 4.19 Implementasi Halaman Login Admin**

---

### 13. Implementasi Halaman Registrasi Admin

Halaman registrasi admin digunakan untuk **menambahkan akun admin baru** ke dalam sistem. Form registrasi terdiri dari beberapa field: **username**, **email**, **password**, **konfirmasi password**, dan **role** (admin/kasir). Sistem melakukan **validasi** di sisi server untuk memastikan: email valid dan belum terdaftar, username unik, password memenuhi kriteria minimal (panjang, kombinasi karakter), dan password konfirmasi match. Password di-**hash** menggunakan fungsi `password_hash()` PHP sebelum disimpan ke database untuk keamanan. Setelah registrasi berhasil, admin baru dapat langsung **login** menggunakan kredensial yang telah dibuat. Terdapat link **"Kembali ke Login"** untuk user yang sudah punya akun. Halaman ini biasanya dibatasi aksesnya hanya untuk **super admin** atau digunakan saat **setup awal** sistem. Dapat dilihat pada **Gambar 4.20 Implementasi Halaman Registrasi Admin**.

**Gambar 4.20 Implementasi Halaman Registrasi Admin**

---

### 14. Implementasi Halaman Dashboard Admin

Halaman dashboard adalah **pusat kontrol** sistem yang menampilkan **overview statistik** dan **ringkasan aktivitas** cafe. Dashboard menampilkan beberapa **widget kartu** dengan informasi penting: **total pesanan hari ini**, **omzet hari ini**, **jumlah pesanan pending**, **jumlah pesanan processing**, dan **total produk aktif**. Terdapat juga **grafik visualisasi** omzet per hari/minggu/bulan menggunakan library Chart.js untuk memudahkan analisis tren penjualan. Bagian **pesanan terbaru** menampilkan daftar order terkini dengan status real-time, lengkap dengan detail meja dan total pembayaran. Terdapat **quick action buttons** untuk akses cepat ke fungsi-fungsi penting seperti tambah produk, lihat pesanan, kelola meja. Dashboard menggunakan **AJAX** untuk auto-refresh data statistik setiap beberapa detik tanpa reload halaman. Di bagian atas terdapat **navigation bar** dengan menu: Dashboard, Produk, Kategori, Pesanan, Meja, dan tombol Logout. Dapat dilihat pada **Gambar 4.21 Implementasi Halaman Dashboard Admin**.

**Gambar 4.21 Implementasi Halaman Dashboard Admin**

---

### 15. Implementasi Halaman Manajemen Produk

Halaman manajemen produk adalah fitur **CRUD lengkap** untuk mengelola menu makanan dan minuman. Halaman ini terbagi menjadi dua bagian utama: **form tambah/edit produk** di bagian atas, dan **tabel daftar produk** di bagian bawah. Form input terdiri dari field: **nama produk**, **harga**, **deskripsi**, **stok**, **upload gambar**, dan **status aktif** (checkbox). Sistem melakukan **validasi** untuk memastikan semua field wajib terisi dan format harga valid (numeric). Upload gambar menggunakan **sanitize filename** untuk keamanan, dan gambar disimpan dengan **timestamp prefix** untuk menghindari nama duplikat. Tabel produk menampilkan semua data dengan kolom: ID, Gambar (thumbnail), Nama, Harga, Stok, Deskripsi (truncated), Status (badge aktif/arsip), dan Aksi. Tersedia **4 aksi** untuk setiap produk: **Edit** (mengisi form dengan data produk), **Arsip/Aktifkan** (soft delete dengan toggle is_active), **Hapus Permanen** (hard delete, hanya jika produk tidak ada di order_items), dan **Lihat Detail**. Produk yang diarsip tetap tersimpan di database namun **tidak tampil** di halaman menu customer. Terdapat fitur **pencarian** dan **filter status** (aktif/arsip/semua) untuk memudahkan navigasi. Dapat dilihat pada **Gambar 4.22 Implementasi Halaman Manajemen Produk**.

**Gambar 4.22 Implementasi Halaman Manajemen Produk**

---

### 16. Implementasi Halaman Manajemen Kategori

Halaman manajemen kategori berfungsi untuk **mengorganisir produk** berdasarkan jenis atau kelompok tertentu (Makanan, Minuman, Snack, Dessert, Coffee, Special). Halaman ini memiliki layout serupa dengan manajemen produk: **form input kategori** di atas dan **tabel daftar kategori** di bawah. Form terdiri dari field: **nama kategori**, **deskripsi**, **icon** (emoji atau icon class), **urutan tampil** (display_order untuk sorting), dan **status aktif**. Setiap kategori dapat memiliki **icon emoji** (🍽️ untuk Makanan, ☕ untuk Coffee, dll) yang ditampilkan di menu customer untuk visual yang lebih menarik. Field **display_order** menentukan urutan tampilan kategori di halaman menu - semakin kecil angkanya, semakin atas posisinya. Tabel kategori menampilkan: ID, Nama, Icon, Deskripsi, Urutan, Status, dan Aksi. Tersedia aksi: **Edit**, **Aktifkan/Nonaktifkan**, dan **Hapus**. Kategori yang di-nonaktifkan tidak akan tampil di filter menu customer. Sistem menampilkan **jumlah produk** per kategori untuk mempermudah monitoring. Fitur kategori ini mendukung **future development** untuk filtering dan grouping produk di halaman menu customer. Dapat dilihat pada **Gambar 4.23 Implementasi Halaman Manajemen Kategori**.

**Gambar 4.23 Implementasi Halaman Manajemen Kategori**

---

### 17. Implementasi Halaman Manajemen Pesanan

Halaman manajemen pesanan adalah **pusat kendali** untuk memproses semua order yang masuk. Halaman ini menampilkan **tabel pesanan** dengan filter status: **Semua**, **Pending** (menunggu konfirmasi), **Processing** (sedang diproses), **Done** (selesai), dan **Cancelled** (dibatalkan). Setiap baris pesanan menampilkan: **Order Code** (clickable untuk detail), **Tanggal & Waktu**, **Nomor Meja**, **Total**, **Metode Pembayaran** (badge Cash/QRIS), **Status** (badge berwarna), dan **Aksi**. Terdapat **dua aksi utama**: **Update Status** (dropdown untuk mengubah status: Pending→Processing→Done atau Cancel), dan **Lihat Detail** (redirect ke halaman detail order). Sistem menggunakan **real-time update** dengan auto-refresh setiap 10-15 detik untuk menampilkan pesanan baru yang masuk. Terdapat **notifikasi sound** atau **desktop notification** ketika ada pesanan baru (pending). Admin dapat melakukan **bulk action** untuk update status beberapa pesanan sekaligus. Halaman ini juga menampilkan **ringkasan cepat**: total pesanan hari ini, total omzet, dan pesanan yang butuh action (pending/processing). Filter tanggal memungkinkan admin untuk melihat **laporan historis** pesanan. Dapat dilihat pada **Gambar 4.24 Implementasi Halaman Manajemen Pesanan**.

**Gambar 4.24 Implementasi Halaman Manajemen Pesanan**

---

### 18. Implementasi Halaman Detail Pesanan

Halaman detail pesanan menampilkan **informasi lengkap** dari satu order secara komprehensif. Halaman ini dibagi menjadi beberapa **section card**: **Info Order** (order code, tanggal, status, payment method), **Info Meja** (nomor meja, table ID), **Daftar Item** (tabel produk yang dipesan dengan kolom: nama produk, harga satuan, quantity, subtotal), dan **Total Pembayaran** (sum of all subtotals). Setiap item dalam order ditampilkan dengan detail lengkap termasuk **gambar thumbnail produk** untuk memudahkan identifikasi visual. Di bagian atas terdapat **timeline status** yang menunjukkan progress order dari pending hingga done dengan **timestamp** setiap perubahan status. Admin dapat melakukan **update status** langsung dari halaman ini dengan dropdown select dan tombol "Update". Terdapat juga tombol **"Print"** untuk mencetak nota/struk pesanan, dan tombol **"Kirim Notifikasi"** untuk mengirim push notification ke customer (jika ada). Section **Activity Log** menampilkan histori semua perubahan yang terjadi pada order (created, payment confirmed, processing started, completed) dengan timestamp dan user yang melakukan aksi. Halaman ini sangat penting untuk **customer service** ketika ada komplain atau inquiry mengenai pesanan. Dapat dilihat pada **Gambar 4.25 Implementasi Halaman Detail Pesanan**.

**Gambar 4.25 Implementasi Halaman Detail Pesanan**

---

### 19. Implementasi Halaman Manajemen Meja

Halaman manajemen meja berfungsi untuk **mengelola data meja** dan **generate QR Code** untuk setiap meja. Halaman ini terdiri dari **form input meja** dengan field: **nama meja** (contoh: "MEJA 1", "VIP 1") dan **kode meja** (unique code untuk QR, contoh: "TBL-001"). Kode meja harus **unique** karena digunakan sebagai identifier saat customer scan QR. Tabel meja menampilkan kolom: ID, Nama Meja, Kode, QR Code (preview thumbnail), dan Aksi. Tersedia **4 aksi** per meja: **Edit** (update nama/code), **Generate QR** (create QR code image), **Download QR** (download PNG/PDF), dan **Hapus**. Fitur **Generate QR** akan membuat QR Code image yang berisi URL: `https://cafe.com/menu.php?table=TBL-001`. QR Code bisa di-generate secara **batch** untuk semua meja sekaligus dengan tombol "Generate All QR". Terdapat opsi untuk **print QR dengan label** meja untuk ditempel di setiap meja fisik cafe. Halaman ini juga menampilkan **status meja**: sedang dipakai (ada order aktif) atau available (tidak ada order). Admin dapat melihat **riwayat penggunaan** meja dan **statistik** meja terlaris. Sistem menggunakan library **PHP QR Code** atau **endroid/qr-code** untuk generate QR image. Dapat dilihat pada **Gambar 4.26 Implementasi Halaman Manajemen Meja**.

**Gambar 4.26 Implementasi Halaman Manajemen Meja**

---

## C. PROSES & UTILITY PAGES (Halaman Pendukung)

### 20. Implementasi Proses Tambah ke Keranjang

Halaman `add_cart.php` adalah **backend process** yang tidak memiliki tampilan UI. File ini memproses **request POST** dari form "Tambah ke Keranjang" di halaman menu. Sistem menerima dua parameter: **product_id** dan **qty** (quantity). Proses yang dilakukan: (1) **Validasi** product_id dan qty (harus numeric, qty minimal 1), (2) **Inisialisasi** session cart jika belum ada (`$_SESSION['cart'] = []`), (3) Jika product_id sudah ada di cart, **tambahkan qty** ke qty existing (`$_SESSION['cart'][$product_id] += $qty`), (4) Jika product_id belum ada, **buat entry baru** (`$_SESSION['cart'][$product_id] = $qty`), (5) **Redirect** ke halaman cart.php dengan pesan success. Session cart menggunakan struktur **array associative** dengan format: `[product_id => qty]`. Contoh: `[11 => 2, 14 => 3]` artinya produk ID 11 qty 2, produk ID 14 qty 3. File ini juga bisa dikembangkan dengan **AJAX** untuk update cart tanpa reload halaman (add to cart via JavaScript fetch). Dapat dilihat pada **Gambar 4.27 Flow Diagram Proses Tambah ke Keranjang**.

**Gambar 4.27 Flow Diagram Proses Tambah ke Keranjang**

---

### 21. Implementasi Proses Update Keranjang

Halaman `update_cart.php` adalah **backend process** untuk mengubah quantity atau menghapus item dari keranjang. File ini memproses request POST dengan parameter: **product_id** dan **action** (update/delete). Untuk **action update**: sistem menerima parameter tambahan **qty**, kemudian mengubah `$_SESSION['cart'][$product_id]` menjadi nilai qty baru. Jika qty = 0 atau negatif, item akan **dihapus otomatis** dari cart. Untuk **action delete**: sistem menggunakan `unset($_SESSION['cart'][$product_id])` untuk menghapus item. Setelah update selesai, sistem **redirect** kembali ke cart.php dengan pesan feedback (success/error). File ini juga melakukan **validasi** untuk memastikan product_id valid dan ada di session cart. Sistem dapat ditingkatkan dengan **real-time validation** untuk mengecek stock availability sebelum update qty. Proses ini penting untuk memberikan **flexibility** kepada customer dalam mengatur pesanan sebelum checkout. Dapat dilihat pada **Gambar 4.28 Flow Diagram Proses Update Keranjang**.

**Gambar 4.28 Flow Diagram Proses Update Keranjang**

---

### 22. Implementasi Webhook Midtrans (Notification Handler)

Halaman `midtrans_notify.php` adalah **webhook endpoint** yang menerima **callback notification** dari payment gateway Midtrans saat terjadi perubahan status pembayaran. File ini tidak memiliki tampilan UI karena diakses secara **server-to-server** oleh Midtrans. Proses yang dilakukan: (1) Menerima **POST request** dari Midtrans berisi data transaksi (transaction_id, order_id, status_code, transaction_status), (2) **Validasi signature** untuk memastikan request benar-benar dari Midtrans (security), (3) **Parse notification** untuk mendapatkan status pembayaran (settlement/success, pending, deny, cancel, expire), (4) **Update status order** di database sesuai status pembayaran: settlement → update status jadi "processing", pending → tetap "pending", cancel/expire → update jadi "cancelled", (5) **Log** semua notification untuk audit trail, (6) **Return HTTP 200 OK** ke Midtrans sebagai konfirmasi notification diterima. File ini sangat **critical** karena merupakan satu-satunya cara sistem mengetahui bahwa pembayaran QRIS telah berhasil. Endpoint ini harus di-**whitelist** di Midtrans dashboard dan menggunakan **HTTPS** untuk keamanan. Dapat dilihat pada **Gambar 4.29 Flow Diagram Webhook Midtrans**.

**Gambar 4.29 Flow Diagram Webhook Midtrans**

---

### 23. Implementasi Proses Logout Admin

Halaman `logout.php` adalah **utility process** untuk menghapus session admin dan mengarahkan kembali ke halaman login. Proses yang dilakukan sangat sederhana namun penting untuk **security**: (1) **Start session** dengan `session_start()` untuk mengakses session variables, (2) **Hapus semua session variables** dengan `session_unset()`, (3) **Destroy session** dengan `session_destroy()` untuk menghapus session ID, (4) Opsional: **Hapus session cookie** dengan `setcookie(session_name(), '', time()-3600)`, (5) **Redirect** ke halaman login.php dengan pesan "Anda telah logout". File ini harus dilindungi dari **CSRF attack** dengan menggunakan token atau dengan memastikan request berasal dari domain yang sama. Best practice adalah menggunakan **POST method** untuk logout (bukan GET) agar tidak bisa di-trigger oleh link eksternal. Setelah logout, user tidak bisa mengakses halaman admin lagi kecuali login ulang. Dapat dilihat pada **Gambar 4.30 Flow Diagram Proses Logout**.

**Gambar 4.30 Flow Diagram Proses Logout**

---

## D. RANGKUMAN IMPLEMENTASI

Keseluruhan sistem terdiri dari **24 halaman** yang saling terintegrasi membentuk **alur pemesanan yang seamless**. Implementasi menggunakan **pattern MVC** dengan separation of concerns: halaman view (UI), halaman process (logic), helper functions, dan middleware authentication. Setiap halaman dirancang dengan prinsip **responsive design** menggunakan Tailwind CSS sehingga dapat diakses dengan baik dari berbagai device (mobile, tablet, desktop).

**Keunggulan implementasi:**
1. **Guest Checkout** - Customer tidak perlu registrasi
2. **Real-time Status** - Update pesanan secara live
3. **Multi Payment** - Mendukung QRIS dan Cash
4. **QR Code Integration** - Mudah untuk scan dan order
5. **Admin Dashboard** - Kontrol penuh pengelolaan cafe
6. **Responsive UI** - Optimal di semua device
7. **Security First** - Prepared statement, password hash, CSRF protection
8. **User Friendly** - Navigasi intuitif dan error handling yang jelas

Implementasi ini telah melalui **testing** untuk memastikan semua fitur berjalan dengan baik dan memberikan **user experience** yang optimal baik untuk customer maupun admin.

---

**Catatan:** Semua gambar (Gambar 4.8 - 4.30) merujuk kepada screenshot implementasi aktual dari sistem yang sudah berjalan.

**Dokumentasi dibuat:** 2026-02-03  
**Total Implementasi:** 24 halaman (15 Customer + 9 Admin)  
**Framework:** PHP + Tailwind CSS + Feather Icons  
**Database:** MySQL/MariaDB  
**Payment Gateway:** Midtrans
