<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Cek apakah sesi sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ambil kode order dan pesan dari URL
$order_code = $_GET['order'] ?? null;
$message = $_GET['msg'] ?? "Terima kasih! Pesanan Anda sedang diproses.";

if (!$order_code) {
    die("Kode order tidak ditemukan. <a href='menu.php'>Kembali</a>");
}

// Ambil data order
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code=?");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order tidak ditemukan. <a href='menu.php'>Kembali</a>");
    }

    // Jika status belum selesai (pending / processing), update menjadi 'done' dan hapus cart.
    // Ini mengasumsikan halaman ini hanya diakses setelah pembayaran dipastikan sukses.
    if ($order['status'] !== 'done') {
        $pdo->prepare("UPDATE orders SET status='done', updated_at = NOW() WHERE order_code=?")->execute([$order_code]);
        unset($_SESSION['cart']);
    }
} catch (PDOException $e) {
    error_log("Database error in success.php: " . $e->getMessage());
    die("Terjadi kesalahan database saat memproses order.");
}


// Dapatkan nama metode pembayaran yang lebih bagus untuk tampilan
$payment_name = ($order['payment_method'] === 'cash') ? 'Tunai (Cash)' : 'QRIS';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pembayaran Berhasil - <?= e($order_code) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    /* Tambahkan font Inter */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body {
        font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-green-50 min-h-screen flex items-center justify-center p-4 sm:p-6">
  <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-6 sm:p-8 text-center border-t-4 border-green-500">
    
    <!-- Icon Konfirmasi -->
    <div class="text-green-500 flex justify-center mb-6">
        <i data-feather="check-circle" class="w-16 h-16 stroke-[1.5]"></i>
    </div>
    
    <h1 class="text-3xl font-extrabold text-green-700 mb-2">PESANAN BERHASIL DIBUAT!</h1>
    <p class="text-gray-600 mb-6 text-base sm:text-lg font-medium">
        <?= htmlspecialchars($message) ?>
    </p>

    <!-- Detail Order yang Menonjol -->
    <div class="bg-green-50 p-5 rounded-xl text-left space-y-3 mb-6 shadow-inner border border-green-200">
        <div class="flex justify-between items-center border-b pb-2">
            <p class="text-sm font-medium text-gray-700">Kode Order:</p>
            <p class="text-lg font-bold text-green-800"><?= htmlspecialchars($order['order_code']) ?></p>
        </div>
        <div class="flex justify-between items-center">
            <p class="text-sm font-medium text-gray-700">Nomor Meja:</p>
            <p class="text-lg font-bold text-green-800"><?= htmlspecialchars($order['table_id']) ?></p>
        </div>
        <div class="flex justify-between items-center">
            <p class="text-sm font-medium text-gray-700">Metode Pembayaran:</p>
            <p class="text-lg font-bold text-green-800"><?= e($payment_name) ?></p>
        </div>
    </div>
    
    <!-- Total Pembayaran -->
    <div class="mb-8 p-4 bg-indigo-100 rounded-xl shadow-md border-l-4 border-indigo-500">
        <div class="flex justify-between items-center">
            <p class="text-lg font-semibold text-gray-800">Total Tagihan:</p>
            <p class="text-xl font-extrabold text-indigo-700"><?= currency($order['total']) ?></p>
        </div>
    </div>


    <!-- Tombol Kembali -->
    <a href="menu.php" class="inline-flex items-center space-x-2 px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 transition transform hover:scale-105 text-xl">
        <i data-feather="coffee" class="w-6 h-6"></i>
        <span>Pesan Lagi</span>
    </a>
  </div>
<script>feather.replace();</script>
</body>
</html>
