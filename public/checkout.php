<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Cek apakah sesi sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$cart = $_SESSION['cart'] ?? [];
$table_id = $_SESSION['table_id'] ?? null;
$total = 0;
$error_message = '';

if (empty($cart)) {
    $error_message = "Keranjang Anda kosong. Silakan kembali ke menu untuk mulai memesan.";
} elseif (!$table_id) {
    $error_message = "Nomor meja tidak terdeteksi. Silakan kembali ke halaman utama dan scan QR meja terlebih dahulu.";
}

if (!$error_message) {
    try {
        // hitung total
        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($products as $p) {
            $total += $p['price'] * ($cart[$p['id']] ?? 0); // Handle safety just in case
        }
    } catch (PDOException $e) {
        $error_message = "Terjadi kesalahan database saat menghitung total.";
        error_log("Database error in checkout.php: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout Pembayaran - RestoKu</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body {
        font-family: 'Inter', sans-serif;
    }
    /* Animasi Tombol dan Shadow */
    .payment-button {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .payment-button:hover {
        transform: scale(1.02);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.15); /* Shadow lebih kuat saat hover */
    }
    /* Keyframe untuk efek shimmer pada Total */
    @keyframes shimmer {
        0% { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }
    .shimmer-text {
        background: linear-gradient(90deg, #f79937 0%, #ff5722 50%, #f79937 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-size: 800px 100%;
        animation: shimmer 5s infinite linear;
    }
  </style>
</head>

<body class="bg-gradient-to-br from-indigo-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
  <!-- PERUBAHAN: max-w-xl diubah menjadi max-w-md agar lebih kecil dan responsif -->
  <div class="max-w-md w-full mx-auto bg-white p-6 sm:p-8 rounded-2xl shadow-2xl">
    
    <!-- Header -->
    <div class="flex justify-between items-center border-b pb-4 mb-6">
      <h1 class="text-3xl font-extrabold text-indigo-700 flex items-center">
        <i data-feather="dollar-sign" class="w-7 h-7 mr-3 text-orange-500"></i>
        Konfirmasi Checkout
      </h1>
      <a href="cart.php" class="text-sm text-gray-600 hover:text-indigo-600 transition flex items-center">
        <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i>
        Kembali ke Keranjang
      </a>
    </div>

    <!-- Konten Error / Checkout -->
    <?php if ($error_message): ?>
        <div class="text-center p-10 border-2 border-dashed border-red-300 rounded-xl bg-red-50">
            <i data-feather="alert-octagon" class="w-12 h-12 text-red-500 mx-auto mb-4"></i>
            <p class="text-xl text-red-800 font-semibold mb-4"><?= htmlspecialchars($error_message) ?></p>
            <a href="menu.php" class="mt-4 inline-block px-6 py-2 bg-indigo-500 text-white font-semibold rounded-full hover:bg-indigo-600 transition shadow-md transform hover:scale-105">
                Cek Menu
            </a>
        </div>
    <?php else: ?>
        
        <!-- Detail Pemesanan -->
        <div class="mb-6 p-5 bg-indigo-50 rounded-xl shadow-inner border-l-4 border-indigo-500">
            <div class="text-lg font-bold text-gray-800 mb-2 flex justify-between items-center">
                <span>Nomor Meja:</span>
                <strong class="text-indigo-900"><?= htmlspecialchars($table_id) ?></strong>
            </div>
            <div class="h-px bg-gray-200 my-3"></div>
            <div class="font-bold text-gray-800 flex justify-between items-center">
                <span class="text-xl">Total Pembayaran:</span>
                <!-- PERUBAHAN: Menambahkan shimmer-text dan membuat font size responsif -->
                <div class="text-4xl sm:text-xl font-extrabold shimmer-text text-orange-600">
                    <?= currency($total) ?>
                </div>
            </div>
        </div>

        <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Pilih Metode Pembayaran</h2>

        <!-- Opsi Pembayaran QRIS -->
        <form action="pay_qris.php" method="post" class="mb-4">
          <input type="hidden" name="action" value="qris">
          <button type="submit" name="pay" value="qris" class="payment-button w-full flex items-center justify-between p-4 bg-blue-600 text-white font-bold rounded-xl shadow-md transition">
            <div class="flex items-center space-x-3">
                <i data-feather="maximize" class="w-6 h-6"></i>
                <span class="text-xl">Bayar dengan QRIS</span>
            </div>
            <span class="text-sm opacity-80">(Paling Cepat)</span>
          </button>
        </form>

        <!-- Opsi Pembayaran Tunai / Pesan Saja -->
        <form action="tunai.php" method="post">
          <input type="hidden" name="action" value="cash">
          <button type="submit" name="pay" value="cash" class="payment-button w-full flex items-center justify-between p-4 bg-green-600 text-white font-bold rounded-xl shadow-md transition">
            <div class="flex items-center space-x-3">
                <i data-feather="dollar-sign" class="w-6 h-6"></i>
                <span class="text-xl">Bayar Tunai</span>
            </div>
            <span class="text-sm opacity-80">(Pesan dulu, Bayar di Meja)</span>
          </button>
        </form>
        
        <p class="mt-6 text-sm text-center text-gray-500">
            Pesanan Anda akan segera diproses setelah pembayaran dikonfirmasi.
        </p>

    <?php endif; ?>
  </div>
<script>feather.replace();</script>
</body>
</html>
