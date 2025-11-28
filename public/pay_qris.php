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
  <title>Bayar dengan QRIS - Kantin Akademi MD</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
  <div class="max-w-md w-full mx-auto bg-white p-8 sm:p-10 rounded-2xl shadow-2xl text-center">
    
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
        <i data-feather="check-circle" class="w-16 h-16 text-green-500 mx-auto mb-6"></i>
        <h1 class="text-3xl font-extrabold text-indigo-700 mb-4">
          Order Diterima!
        </h1>

        <!-- Total Bayar - Tipografi Diperbaiki -->
        <div class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl shadow-md border-2 border-blue-200">
            <div class="text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide">Total Pembayaran</div>
            <div class="flex items-baseline justify-center space-x-1">
                <span class="text-2xl font-bold text-blue-600">Rp</span>
                <span class="text-5xl font-bold text-blue-600"><?= number_format($total_amount, 0, ',', '.') ?></span>
            </div>
            <div class="text-sm text-gray-600 mt-3 font-medium">
                Meja: <span class="font-bold text-indigo-900"><?= htmlspecialchars($table_number) ?></span>
            </div>
        </div>

        <!-- Tampilkan QR Code -->
        <div class="mb-8">
            <img id="qrCodeImage" src="<?= $qr_code_data_uri ?>" alt="QR Code Pembayaran" class="mx-auto border-4 border-gray-300 rounded-2xl shadow-lg">
        </div>

        <!-- Instruksi Pembayaran - UX Diperbaiki -->
        <div class="mb-8 bg-orange-50 border-l-4 border-orange-400 p-5 rounded-lg text-left">
            <div class="flex items-start space-x-3">
                <i data-feather="info" class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-sm text-gray-700">
                    <p class="font-semibold text-orange-800 mb-2">Cara Bayar:</p>
                    <ol class="list-decimal list-inside space-y-1.5 text-gray-700">
                        <li>Simpan QR code dengan tombol di bawah, atau</li>
                        <li>Buka aplikasi e-wallet (GoPay, OVO, Dana, dll)</li>
                        <li>Pilih menu Bayar/Scan dan upload gambar QR</li>
                        <li>Konfirmasi pembayaran di aplikasi</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi - UX Diperbaiki -->
        <div class="space-y-3 mb-6">
            <button onclick="downloadQRCode()" class="w-full px-6 py-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-xl hover:from-orange-600 hover:to-orange-700 transition shadow-lg flex items-center justify-center space-x-2">
                <i data-feather="download" class="w-5 h-5"></i>
                <span>Simpan QR ke Galeri</span>
            </button>
            
            <button onclick="shareQRCode()" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold rounded-xl hover:from-blue-600 hover:to-blue-700 transition shadow-lg flex items-center justify-center space-x-2">
                <i data-feather="share-2" class="w-5 h-5"></i>
                <span>Bayar dengan E-Wallet</span>
            </button>
        </div>

        <div class="border-t pt-6 space-y-3">
            <!-- Tombol Konfirmasi Pembayaran -->
            <form action="confirm_payment.php" method="POST">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                <input type="hidden" name="payment_method" value="qris">
                <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition shadow-lg flex items-center justify-center space-x-2">
                    <i data-feather="check-circle" class="w-5 h-5"></i>
                    <span>Sudah Bayar, Konfirmasi Sekarang</span>
                </button>
            </form>

            <a href="order_status.php?order_id=<?= $order_id ?>" class="w-full inline-flex items-center justify-center space-x-2 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition">
                <i data-feather="eye" class="w-5 h-5"></i>
                <span>Cek Status Pesanan</span>
            </a>
        </div>
    <?php endif; ?>

  </div>
  <script>
    feather.replace();

    // Fungsi download QR code
    function downloadQRCode() {
        const qrImage = document.getElementById('qrCodeImage');
        const link = document.createElement('a');
        link.href = qrImage.src;
        link.download = 'QR_Pembayaran_<?= $order_id ?>.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Fungsi share QR code (Web Share API)
    async function shareQRCode() {
        const qrImage = document.getElementById('qrCodeImage');
        
        try {
            // Coba gunakan Web Share API jika tersedia
            if (navigator.share && navigator.canShare) {
                // Convert data URI to blob
                const response = await fetch(qrImage.src);
                const blob = await response.blob();
                const file = new File([blob], 'QR_Pembayaran.png', { type: 'image/png' });
                
                await navigator.share({
                    title: 'QR Pembayaran',
                    text: 'Scan QR ini untuk membayar pesanan',
                    files: [file]
                });
            } else {
                // Fallback: download saja
                downloadQRCode();
                alert('QR code berhasil disimpan! Silakan buka aplikasi e-wallet Anda dan upload gambar QR.');
            }
        } catch (err) {
            if (err.name !== 'AbortError') {
                downloadQRCode();
                alert('QR code berhasil disimpan! Silakan buka aplikasi e-wallet Anda dan upload gambar QR.');
            }
        }
    }
  </script>
</body>
</html>
