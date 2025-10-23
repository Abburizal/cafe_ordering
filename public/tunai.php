<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Pastikan ada cart dan nomor meja
$cart = $_SESSION['cart'] ?? [];
$table_id = $_SESSION['table_id'] ?? null;

// --- Validasi dan Error Handling ---
if (empty($cart)) {
    // Jika keranjang kosong, redirect ke menu
    header('Location: menu.php');
    exit;
}
if (!$table_id) {
    // Jika ID meja tidak terdeteksi, redirect ke halaman utama/scan
    die("Nomor meja tidak terdeteksi. Silakan scan QR meja terlebih dahulu.");
}

try {
    // Hitung total harga
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($products as $p) {
        $total += $p['price'] * $cart[$p['id']];
    }

    // --- Pembuatan Order (Transaksional) ---
    
    $order_code = generateOrderCode(); // Asumsi fungsi ini sudah didefinisikan di helpers.php
    $payment_method = 'cash';
    $status = 'pending'; // Status awal: menunggu konfirmasi/pembayaran waiter

    $pdo->beginTransaction();

    // 1. Buat order baru
    $insert = $pdo->prepare("INSERT INTO orders (order_code, user_id, table_id, total, payment_method, status)
                             VALUES (?, NULL, ?, ?, ?, ?)");
    $insert->execute([$order_code, $table_id, $total, $payment_method, $status]);
    $order_id = $pdo->lastInsertId();

    // 2. Simpan item pesanan
    $itemInsert = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
    foreach ($products as $p) {
        $qty = $cart[$p['id']];
        $itemInsert->execute([$order_id, $p['id'], $qty, $p['price']]);
    }
    
    // 3. Bersihkan keranjang
    unset($_SESSION['cart']);

    $pdo->commit(); // Selesaikan transaksi
    
    // Set flag sukses untuk ditampilkan di UI
    $success = true;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); // Batalkan jika terjadi error
    }
    error_log("Order creation failed: " . $e->getMessage());
    $error_message = "Gagal membuat pesanan. Silakan coba lagi atau hubungi staf.";
    $success = false;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Berhasil Dibuat</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body {
        font-family: 'Inter', sans-serif;
    }
    /* Modal styles */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 50;
    }
    .modal-content {
        animation: fadeInScale 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
  </style>
</head>

<body class="bg-gradient-to-br from-indigo-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
    
    <?php if ($success): ?>
    <!-- Modal Sukses (Selalu ditampilkan jika transaksi sukses) -->
    <div id="successModal" class="modal-backdrop fixed inset-0 flex items-center justify-center transition-opacity duration-300">
        <!-- PERUBAHAN: max-w-md diubah menjadi max-w-sm (384px) untuk membuatnya lebih kecil di mobile/desktop -->
        <div class="modal-content bg-white rounded-2xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center border-t-8 border-green-500">
            
            <i data-feather="check-circle" class="w-16 h-16 text-green-500 mx-auto mb-4 animate-bounce-once"></i>
            
            <h1 class="text-3xl font-extrabold mb-2 text-green-700">Pesanan Berhasil Dibuat!</h1>
            
            <p class="text-gray-600 mb-6">
                Pesanan kamu dengan pembayaran **Tunai** telah berhasil dicatat.
            </p>

            <!-- Detail Order -->
            <div class="mb-6 text-left text-base bg-green-50 p-4 rounded-xl border border-green-200">
                <div class="flex justify-between font-medium text-gray-800">
                    <span>Kode Order:</span>
                    <strong class="text-green-800"><?= htmlspecialchars($order_code) ?></strong>
                </div>
                <div class="flex justify-between mt-2 text-sm text-gray-700">
                    <span>Nomor Meja:</span>
                    <strong><?= htmlspecialchars($table_id) ?></strong>
                </div>
                <div class="h-px bg-green-200 my-3"></div>
                <div class="flex justify-between text-lg font-extrabold text-gray-900">
                    <span>Total:</span>
                    <strong class="text-orange-600"><?= currency($total) ?></strong>
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-6">
                Silakan tunggu **waiter** kami untuk datang ke meja Anda dan mengonfirmasi pembayaran tunai ini.
            </p>

            <a href="menu.php" class="inline-flex items-center space-x-2 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 transition transform hover:scale-105">
              <i data-feather="home" class="w-5 h-5"></i>
              <span>Kembali ke Menu</span>
            </a>
        </div>
    </div>
    <?php else: ?>
    <!-- Tampilan Error Jika Transaksi Gagal -->
        <!-- PERUBAHAN: max-w-lg diubah menjadi max-w-md untuk konsistensi ukuran card -->
        <div class="text-center p-10 border-2 border-dashed border-red-300 rounded-xl bg-red-50 max-w-md w-full">
            <i data-feather="x-octagon" class="w-12 h-12 text-red-500 mx-auto mb-4"></i>
            <p class="text-xl text-red-800 font-semibold mb-4">stok habis</p>
            <p class="text-gray-700 mb-6"><?= htmlspecialchars($error_message ?? 'Terjadi kesalahan tidak terduga.') ?></p>
            <a href="cart.php" class="mt-4 inline-block px-6 py-2 bg-indigo-500 text-white font-semibold rounded-full hover:bg-indigo-600 transition shadow-md transform hover:scale-105">
                Coba Lagi di Keranjang
            </a>
        </div>
    <?php endif; ?>

<script>
    feather.replace();
    // Jika modal sukses ada, pastikan selalu terlihat (untuk mengabaikan body content di belakangnya)
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('successModal');
        if (modal) {
            // Kita biarkan modal terbuka karena ini adalah halaman konfirmasi akhir
            // dan tidak ada konten lain yang perlu diakses user di halaman ini.
        }
    });
</script>
</body>
</html>
