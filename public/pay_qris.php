<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Cek apakah sesi sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ambil metode pembayaran (misalnya ?method=cash atau ?method=qris)
$payment_method = $_GET['method'] ?? 'qris_mock';

// Ambil cart dan meja dari session
$cart = $_SESSION['cart'] ?? [];
$table_id = $_SESSION['table_id'] ?? null;

if (empty($cart) || !$table_id) {
    die("Sesi tidak valid. <a href='menu.php'>Kembali</a>");
}

// Ambil produk & hitung total
try {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($products as $p) {
        $total += $p['price'] * $cart[$p['id']];
    }

    // Buat order baru
    $order_code = generateOrderCode();
    $status = ($payment_method === 'cash') ? 'processing' : 'pending';
    $insert = $pdo->prepare("INSERT INTO orders (order_code, user_id, table_id, total, payment_method, status)
                             VALUES (?, NULL, ?, ?, ?, ?)");
    $insert->execute([$order_code, $table_id, $total, $payment_method, $status]);
    $order_id = $pdo->lastInsertId();

    // Simpan order_items
    $insertItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
    foreach ($products as $p) {
        $qty = $cart[$p['id']];
        $insertItem->execute([$order_id, $p['id'], $qty, $p['price']]);
    }
} catch (PDOException $e) {
    error_log("Database error in pay_qris.php during order creation: " . $e->getMessage());
    die("Terjadi kesalahan database saat membuat order.");
}


// Kalau metode tunai, langsung dianggap berhasil
if ($payment_method === 'cash') {
    unset($_SESSION['cart']);
    header("Location: success.php?order=" . urlencode($order_code) . "&msg=" . urlencode("Pembayaran tunai diterima. Silakan tunggu waiter."));
    exit;
}

// ======================
// MODE QRIS (Prototype)
// ======================
$simulate = $_POST['simulate'] ?? null;

// Path QR statis
$qr_path = 'assets/qr.jpg';
if (!file_exists(__DIR__ . '/' . $qr_path)) {
    // Gunakan placeholder jika file tidak ada
    $qr_path = 'https://placehold.co/250x250/2563EB/ffffff?text=QR+Code';
}

// Jika user klik simulate
if ($simulate) {
    if ($simulate === 'success') {
        $pdo->prepare("UPDATE orders SET status = 'processing' WHERE id = ?")->execute([$order_id]);
        unset($_SESSION['cart']);
        header('Location: success.php?order=' . urlencode($order_code));
        exit;
    } elseif ($simulate === 'cancel') {
        $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?")->execute([$order_id]);
        header('Location: menu.php?msg=' . urlencode('Pembayaran dibatalkan.'));
        exit;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pembayaran QRIS (Prototype)</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
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
<body class="bg-gradient-to-br from-indigo-50 to-orange-50 min-h-screen flex items-center justify-center p-4 sm:p-6">
  <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-6 sm:p-8 text-center">
    
    <div class="mb-6 flex justify-center">
        <i data-feather="qr-code" class="w-10 h-10 text-indigo-600"></i>
    </div>
    
    <h1 class="text-3xl font-extrabold text-indigo-700 mb-2">Pembayaran QRIS</h1>
    <p class="text-sm text-gray-500 mb-6">
      Scan QR berikut menggunakan aplikasi pembayaran kamu, atau gunakan tombol simulasi.
    </p>

    <!-- QR Code Section -->
    <div class="mb-6 p-4 bg-gray-100 rounded-xl inline-block shadow-inner">
      <img src="<?= htmlspecialchars($qr_path) ?>" alt="QRIS Prototype" class="rounded-lg shadow-md transition duration-300 hover:scale-105" width="250">
    </div>
    
    <!-- Total Pembayaran yang Menonjol -->
    <div class="mb-6 p-4 bg-orange-50 border-l-4 border-orange-500 rounded-xl shadow-md">
        <div class="flex justify-between items-center">
            <p class="text-lg font-semibold text-gray-700">Total Pembayaran:</p>
            <p class="text-xl font-extrabold text-indigo-700"><?= currency($total) ?></p>
        </div>
    </div>


    <!-- Detail Order -->
    <div class="text-left space-y-2 mb-6 p-4 bg-indigo-50 rounded-xl">
      <p class="text-sm text-indigo-800">Kode Order: <strong class="font-bold"><?= htmlspecialchars($order_code) ?></strong></p>
      <p class="text-sm text-indigo-800">Nomor Meja: <strong class="font-bold"><?= htmlspecialchars($table_id) ?></strong></p>
      <p class="text-sm text-indigo-800">Status: <strong class="text-yellow-600 font-bold">Pending</strong></p>
    </div>
    
    <!-- Tombol Simulasi -->
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <form method="post" class="w-full sm:w-auto">
        <input type="hidden" name="simulate" value="success">
        <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-xl shadow-lg hover:bg-green-700 transition transform hover:scale-[1.02]">
          Simulate Paid ✅
        </button>
      </form>

      <form method="post" class="w-full sm:w-auto">
        <input type="hidden" name="simulate" value="cancel">
        <button type="submit" class="w-full px-6 py-3 bg-red-500 text-white font-semibold rounded-xl shadow-lg hover:bg-red-600 transition transform hover:scale-[1.02]">
          Simulate Cancel ✖️
        </button>
      </form>
    </div>

    <div class="mt-6 text-sm">
      <a href="menu.php" class="text-indigo-600 hover:text-indigo-800 transition font-medium flex items-center justify-center">
        <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i>
        Kembali ke Menu
      </a>
    </div>
  </div>
<script>feather.replace();</script>
</body>
</html>
