<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Validasi request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: menu.php');
    exit;
}

$order_id = $_POST['order_id'] ?? null;
$payment_method = $_POST['payment_method'] ?? 'unknown';

if (!$order_id) {
    die("Order ID tidak ditemukan.");
}

$success = false;
$error_message = '';
$order_data = null;

try {
    // Ambil data order (table_number sudah ada di tabel orders)
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order_data) {
        throw new Exception("Order tidak ditemukan.");
    }

    // Update status pembayaran
    // Dalam prototype ini, kita set status menjadi 'processing' (pesanan dikonfirmasi dan diproses)
    $stmt = $pdo->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
    $stmt->execute([$order_id]);

    $success = true;

} catch (PDOException $e) {
    $error_message = "Terjadi kesalahan database: " . $e->getMessage();
    error_log("Confirm payment error: " . $e->getMessage());
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Konfirmasi Pembayaran - RestoKu</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body { font-family: 'Inter', sans-serif; }
    
    @keyframes confetti {
        0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
        100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }
    
    .confetti {
        position: fixed;
        width: 10px;
        height: 10px;
        background: #f0f;
        position: absolute;
        animation: confetti 3s linear infinite;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .slide-in-up {
        animation: slideInUp 0.6s ease-out;
    }
    
    @keyframes pulse-grow {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    .pulse-grow {
        animation: pulse-grow 2s ease-in-out infinite;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
  <div class="max-w-md w-full mx-auto bg-white p-6 sm:p-8 rounded-2xl shadow-2xl text-center slide-in-up">
    
    <?php if ($success): ?>
        <!-- Tampilan Sukses Konfirmasi -->
        <div class="mb-6">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 pulse-grow">
                <i data-feather="check" class="w-12 h-12 text-green-600"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-green-700 mb-2">
                Pembayaran Dikonfirmasi!
            </h1>
            <p class="text-gray-600">
                Terima kasih! Pembayaran Anda sudah kami terima.
            </p>
        </div>

        <!-- Detail Order -->
        <div class="mb-6 p-5 bg-green-50 rounded-xl shadow-inner border-l-4 border-green-500 text-left">
            <div class="text-sm font-semibold text-gray-500 mb-3">Detail Pesanan</div>
            <div class="space-y-2">
                <div class="flex justify-between text-gray-800">
                    <span>Kode Order:</span>
                    <strong class="text-green-800"><?= htmlspecialchars($order_data['order_code']) ?></strong>
                </div>
                <div class="flex justify-between text-gray-800">
                    <span>Nomor Meja:</span>
                    <strong><?= htmlspecialchars($order_data['table_number'] ?? 'N/A') ?></strong>
                </div>
                <div class="flex justify-between text-gray-800">
                    <span>Metode Pembayaran:</span>
                    <strong class="uppercase"><?= htmlspecialchars($payment_method) ?></strong>
                </div>
                <div class="h-px bg-green-200 my-3"></div>
                <div class="flex justify-between text-lg font-extrabold text-gray-900">
                    <span>Total Dibayar:</span>
                    <strong class="text-green-600"><?= currency($order_data['total']) ?></strong>
                </div>
            </div>
        </div>

        <!-- Status Pesanan -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-center space-x-2 text-blue-800">
                <i data-feather="clock" class="w-5 h-5"></i>
                <span class="font-semibold">Pesanan Anda sedang diproses dapur</span>
            </div>
            <p class="text-sm text-gray-600 mt-2">Mohon tunggu, pesanan akan segera diantar ke meja Anda.</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="order_status.php?order_id=<?= $order_id ?>" class="flex-1 inline-flex items-center justify-center space-x-2 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg hover:bg-blue-700 transition transform hover:scale-105">
                <i data-feather="eye" class="w-5 h-5"></i>
                <span>Cek Status</span>
            </a>
            <a href="menu.php" class="flex-1 inline-flex items-center justify-center space-x-2 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 transition transform hover:scale-105">
                <i data-feather="home" class="w-5 h-5"></i>
                <span>Menu</span>
            </a>
        </div>

    <?php else: ?>
        <!-- Tampilan Error -->
        <div class="text-center p-10 border-2 border-dashed border-red-300 rounded-xl bg-red-50">
            <i data-feather="alert-octagon" class="w-12 h-12 text-red-500 mx-auto mb-4"></i>
            <h1 class="text-xl text-red-800 font-semibold mb-2">Konfirmasi Gagal</h1>
            <p class="text-sm text-gray-700 mb-4"><?= htmlspecialchars($error_message) ?></p>
            <a href="menu.php" class="mt-4 inline-block px-6 py-2 bg-indigo-500 text-white font-semibold rounded-full hover:bg-indigo-600 transition shadow-md">
                Kembali ke Menu
            </a>
        </div>
    <?php endif; ?>

  </div>
  
  <script>
    feather.replace();
    
    // Efek confetti sederhana (opsional)
    <?php if ($success): ?>
    function createConfetti() {
        const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#6c5ce7'];
        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                document.body.appendChild(confetti);
                
                setTimeout(() => confetti.remove(), 5000);
            }, i * 30);
        }
    }
    
    // Jalankan confetti saat halaman load
    window.addEventListener('load', createConfetti);
    <?php endif; ?>
  </script>
</body>
</html>
