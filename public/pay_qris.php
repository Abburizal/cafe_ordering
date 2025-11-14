<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Load vendor untuk QR Code
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

// 1. Mulai sesi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Ambil data dari sesi
$cart = $_SESSION['cart'] ?? [];
$table_id = $_SESSION['table_id'] ?? null;
$table_number = $_SESSION['table_number'] ?? null;
$total_amount = 0; // Ini adalah variabel PHP, namanya boleh apa saja
$error_message = '';

// 3. **PERBAIKAN: Normalisasi Sesi Meja**
//    Logika ini disalin dari checkout.php untuk memastikan data meja konsisten
//    jika sesi hanya memiliki table_id (dari scan QR lama).
if (!$table_number && $table_id) {
    try {
        $stmt = $pdo->prepare("SELECT name FROM tables WHERE id = ?");
        $stmt->execute([$table_id]);
        $table = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($table) {
            $table_number = $table['name']; // Hasilnya cth: "MEJA 1"
        } else {
            $table_number = 'Meja ' . $table_id;
        }
    } catch (PDOException $e) {
        $table_number = 'Meja ' . $table_id;
        error_log("PDOException in pay_qris.php (normalisation): " . $e->getMessage());
    }
    $_SESSION['table_number'] = $table_number;
}

// 4. Validasi
if (empty($cart)) {
    $error_message = "Keranjang Anda kosong.";
} elseif (!$table_number) {
    $error_message = "Nomor meja tidak terdeteksi. Silakan scan QR meja.";
}

$product_details = [];
$order_id = null;
$qr_code_data_uri = null;

// 5. Proses Order jika valid
if (!$error_message) {
    try {
        // Hitung total dan siapkan detail produk
        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products)) {
             throw new Exception("Produk di keranjang tidak ditemukan di database.");
        }
        
        foreach($products as $p) {
            $qty = $cart[$p['id']];
            $total_amount += $p['price'] * $qty;
            $product_details[$p['id']] = ['price' => $p['price'], 'qty' => $qty];
        }

        // Mulai transaksi database
        $pdo->beginTransaction();
    
        // 1. Generate order code
        $order_code = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        // 2. Buat order
        // Database memiliki kolom table_number (NOT NULL) dan table_id (nullable)
        $stmt = $pdo->prepare("INSERT INTO orders (order_code, table_number, table_id, total, payment_method, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$order_code, $table_number, $table_id, $total_amount, 'qris', 'pending']);
        $order_id = $pdo->lastInsertId();
    
        // 3. Masukkan order items
        $stmt_items = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
        foreach($product_details as $product_id => $details) {
            $stmt_items->execute([$order_id, $product_id, $details['qty'], $details['price']]);
        }
    
        $pdo->commit();

        // 4. Buat QR Code (Simulasi)
        // Di aplikasi nyata, Anda akan memanggil payment gateway API
        // dan mendapatkan string QRIS dari mereka.
        // Di sini kita buat QR code palsu yang berisi detail order.
        $qris_string = "QRIS_SIMULASI:ORDER_{$order_id}:TOTAL_{$total_amount}";
        
        $result = (new Builder(
            writer: new PngWriter(),
            data: $qris_string,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        ))->build();

        $qr_code_data_uri = $result->getDataUri();

        // 4. Kosongkan keranjang setelah order berhasil
        unset($_SESSION['cart']);

    } catch (PDOException $e) {
        $pdo->rollBack();
        // **PERBAIKAN: Tampilkan error database yang sebenarnya**
        $error_message = "Terjadi kesalahan database saat membuat order: " . $e->getMessage();
        error_log("Database error in pay_qris.php: " . $e->getMessage());
    } catch (Exception $e) {
        // Menangkap error lainnya (spt produk tidak ditemukan)
        $error_message = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bayar dengan QRIS - RestoKu</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
  <div class="max-w-md w-full mx-auto bg-white p-6 sm:p-8 rounded-2xl shadow-2xl text-center">
    
    <?php if ($error_message): ?>
        <!-- Tampilan Error -->
        <div class="text-center p-10 border-2 border-dashed border-red-300 rounded-xl bg-red-50">
            <i data-feather="alert-octagon" class="w-12 h-12 text-red-500 mx-auto mb-4"></i>
            <h1 class="text-xl text-red-800 font-semibold mb-2">Order Gagal Dibuat</h1>
            <p class="text-sm text-gray-700 mb-4"><?= htmlspecialchars($error_message) ?></p>
            <a href="checkout.php" class="mt-4 inline-block px-6 py-2 bg-indigo-500 text-white font-semibold rounded-full hover:bg-indigo-600 transition shadow-md">
                Kembali ke Checkout
            </a>
        </div>
    <?php else: ?>
        <!-- Tampilan Sukses (QRIS) -->
        <i data-feather="check-circle" class="w-16 h-16 text-green-500 mx-auto mb-4"></i>
        <h1 class="text-3xl font-extrabold text-indigo-700 mb-2">
          Order Diterima!
        </h1>
        <p class="text-gray-600 mb-6">Silakan scan QR Code di bawah ini untuk menyelesaikan pembayaran.</p>

        <div class="mb-6 p-5 bg-indigo-50 rounded-xl shadow-inner border-l-4 border-indigo-500 text-left">
            <div class="text-lg font-bold text-gray-800 mb-2 flex justify-between items-center">
                <span>Nomor Meja:</span>
                <strong class="text-indigo-900"><?= htmlspecialchars($table_number) ?></strong>
            </div>
            <div class="font-bold text-gray-800 flex justify-between items-center">
                <span class="text-xl">Total Bayar:</span>
                <div class="text-4xl font-extrabold text-orange-600">
                    <?= currency($total_amount) ?>
                </div>
            </div>
        </div>

        <!-- Tampilkan QR Code -->
        <div class="flex justify-center mb-6">
            <img src="<?= $qr_code_data_uri ?>" alt="QR Code Pembayaran" class="border-4 border-gray-300 rounded-lg shadow-md">
        </div>

        <p class="text-sm text-gray-500 mb-4">
          Setelah membayar, klik tombol di bawah untuk konfirmasi pembayaran.
        </p>

        <!-- Tombol Konfirmasi Pembayaran -->
        <form action="confirm_payment.php" method="POST" class="mb-4">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <input type="hidden" name="payment_method" value="qris">
            <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition shadow-lg flex items-center justify-center space-x-2">
                <i data-feather="check-circle" class="w-5 h-5"></i>
                <span>Konfirmasi Pembayaran Sudah Dilakukan</span>
            </button>
        </form>

        <a href="order_status.php?order_id=<?= $order_id ?>" class="w-full inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-lg">
          Cek Status Pesanan Saya
        </a>
    <?php endif; ?>

  </div>
  <script>feather.replace();</script>
</body>
</html>
