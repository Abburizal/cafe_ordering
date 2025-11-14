<?php
require_once '../config/config.php';
require_once '../app/helpers.php';
require_once '../app/middleware.php';

require_admin();

$order_id = $_GET['id'] ?? null;
$message = '';
$error = '';

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $valid_status = ['pending', 'processing', 'done', 'cancelled'];
    
    if (in_array($new_status, $valid_status)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            $message = "✅ Status berhasil diupdate";
        } catch (PDOException $e) {
            $error = "❌ Gagal update: " . $e->getMessage();
        }
    }
}

// Get order data
try {
    $stmt = $pdo->prepare("
        SELECT o.*, t.name AS table_name 
        FROM orders o 
        LEFT JOIN tables t ON o.table_id = t.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Location: orders.php');
        exit;
    }
    
    $stmt_items = $pdo->prepare("
        SELECT oi.*, p.name AS product_name 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt_items->execute([$order_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
    $order = null;
    $items = [];
}

$adminName = e($_SESSION['username']);

$statusMap = ['pending' => 'Menunggu', 'processing' => 'Diproses', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];
$statusColorMap = ['pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300', 'processing' => 'bg-blue-100 text-blue-800 border-blue-300', 'done' => 'bg-green-100 text-green-800 border-green-300', 'cancelled' => 'bg-red-100 text-red-800 border-red-300'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Pesanan #<?= htmlspecialchars($order['order_code'] ?? '') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; }
    @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .slide-in { animation: slideIn 0.4s ease-out; }
    @media print { .no-print { display: none !important; } body { background: white; } }
  </style>
</head>
<body class="bg-stone-100 min-h-screen">

  <!-- Navbar -->
  <header class="no-print fixed top-3 left-0 right-0 z-50 flex justify-center">
    <div class="flex items-center justify-between w-[95%] md:w-[80%] lg:w-[70%] bg-white/80 backdrop-blur-lg border border-gray-200 rounded-3xl shadow-xl px-5 py-3">
      <div class="flex items-center space-x-2">
        <i data-feather="trello" class="h-6 w-6 text-indigo-600"></i>
        <span class="text-xl font-bold text-gray-800 hidden sm:inline">Admin Resto</span>
      </div>
      <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
        <a href="dashboard.php" class="hover:text-indigo-600 transition">Dashboard</a>
        <a href="product.php" class="hover:text-indigo-600 transition">Produk</a>
        <a href="orders.php" class="text-indigo-600 font-semibold border-b-2 border-indigo-600 pb-1">Orders</a>
      </nav>
      <div class="hidden md:flex items-center space-x-3">
        <span class="text-sm text-gray-600">Hi, <?= $adminName ?></span>
        <a href="logout.php" class="px-4 py-2 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm hover:bg-red-100 font-medium transition">
            <i data-feather="log-out" class="w-4 h-4 inline mr-1"></i> Keluar
        </a>
      </div>
    </div>
  </header>

  <main class="pt-24 pb-12 px-4 max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 slide-in">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-800 flex items-center">
          <i data-feather="file-text" class="mr-3 text-indigo-600"></i>
          Detail Pesanan
        </h1>
        <p class="text-gray-600 mt-1">Order #<?= htmlspecialchars($order['order_code'] ?? '') ?></p>
      </div>
      <div class="no-print flex gap-3">
        <a href="orders.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition flex items-center">
          <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition flex items-center">
          <i data-feather="printer" class="w-4 h-4 mr-2"></i> Print
        </button>
      </div>
    </div>

    <?php if ($message): ?>
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded-xl slide-in">
      <i data-feather="check-circle" class="w-5 h-5 inline mr-2"></i> <?= $message ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-xl slide-in">
      <i data-feather="alert-circle" class="w-5 h-5 inline mr-2"></i> <?= $error ?>
    </div>
    <?php endif; ?>

    <?php if ($order): ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200 slide-in">
          <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-6">
            <div>
              <h2 class="text-2xl font-bold text-gray-800">Order #<?= htmlspecialchars($order['order_code']) ?></h2>
              <p class="text-gray-500 text-sm mt-2 flex items-center">
                <i data-feather="clock" class="w-4 h-4 inline mr-1"></i>
                <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
              </p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-bold border-2 <?= $statusColorMap[$order['status']] ?>">
              <?= $statusMap[$order['status']] ?>
            </span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
            <div class="bg-indigo-50 p-4 rounded-xl">
              <p class="text-indigo-600 text-sm font-semibold mb-1">Nomor Meja</p>
              <p class="text-xl font-bold text-gray-800 flex items-center">
                <i data-feather="map-pin" class="w-5 h-5 inline mr-2 text-indigo-600"></i>
                <?= htmlspecialchars($order['table_number'] ?? $order['table_name'] ?? 'N/A') ?>
              </p>
            </div>
            <div class="bg-orange-50 p-4 rounded-xl">
              <p class="text-orange-600 text-sm font-semibold mb-1">Metode Pembayaran</p>
              <p class="text-xl font-bold text-gray-800 flex items-center uppercase">
                <i data-feather="credit-card" class="w-5 h-5 inline mr-2 text-orange-600"></i>
                <?= htmlspecialchars($order['payment_method']) ?>
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200 slide-in">
          <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i data-feather="shopping-cart" class="mr-2 text-indigo-600"></i> Item Pesanan
          </h3>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b-2 border-gray-300">
                  <th class="text-left py-3 px-2 text-gray-700 font-bold">Produk</th>
                  <th class="text-center py-3 px-2 text-gray-700 font-bold">Qty</th>
                  <th class="text-right py-3 px-2 text-gray-700 font-bold">Harga</th>
                  <th class="text-right py-3 px-2 text-gray-700 font-bold">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $total = 0;
                foreach ($items as $item): 
                  $subtotal = $item['qty'] * $item['price'];
                  $total += $subtotal;
                ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                  <td class="py-4 px-2"><span class="font-medium text-gray-800"><?= htmlspecialchars($item['product_name']) ?></span></td>
                  <td class="py-4 px-2 text-center"><span class="inline-block px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full font-bold text-sm"><?= $item['qty'] ?>x</span></td>
                  <td class="py-4 px-2 text-right text-gray-600 font-medium"><?= currency($item['price']) ?></td>
                  <td class="py-4 px-2 text-right font-bold text-gray-800"><?= currency($subtotal) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="border-t-2 border-gray-300 bg-indigo-50">
                  <td colspan="3" class="py-4 px-2 text-right font-bold text-gray-800 text-lg">TOTAL</td>
                  <td class="py-4 px-2 text-right font-extrabold text-2xl text-indigo-600"><?= currency($total) ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Sidebar -->
      <div class="space-y-6">
        <div class="no-print bg-white rounded-2xl shadow-lg p-6 border border-gray-200 slide-in">
          <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <i data-feather="refresh-cw" class="mr-2 text-indigo-600"></i> Update Status
          </h3>
          <form method="POST" class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Status Saat Ini</label>
              <div class="px-4 py-3 rounded-xl border-2 <?= $statusColorMap[$order['status']] ?> font-bold text-center">
                <?= $statusMap[$order['status']] ?>
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Ubah Ke</label>
              <select name="status" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium">
                <option value="">-- Pilih Status --</option>
                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Menunggu</option>
                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Diproses</option>
                <option value="done" <?= $order['status'] === 'done' ? 'selected' : '' ?>>Selesai</option>
                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
              </select>
            </div>
            <button type="submit" name="update_status" class="w-full px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg flex items-center justify-center">
              <i data-feather="check-circle" class="w-5 h-5 inline mr-2"></i> Update Status
            </button>
          </form>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200 slide-in">
          <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <i data-feather="activity" class="mr-2 text-indigo-600"></i> Timeline
          </h3>
          <div class="space-y-4">
            <div class="flex items-start">
              <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white mr-3 flex-shrink-0">
                <i data-feather="check" class="w-5 h-5"></i>
              </div>
              <div>
                <p class="font-bold text-gray-800">Pesanan Dibuat</p>
                <p class="text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
              </div>
            </div>
            <?php if ($order['status'] !== 'pending'): ?>
            <div class="flex items-start">
              <div class="w-10 h-10 rounded-full <?= in_array($order['status'], ['processing', 'done']) ? 'bg-blue-500' : 'bg-gray-300' ?> flex items-center justify-center text-white mr-3 flex-shrink-0">
                <i data-feather="loader" class="w-5 h-5"></i>
              </div>
              <div>
                <p class="font-bold text-gray-800">Diproses</p>
                <p class="text-sm text-gray-500"><?= $order['updated_at'] ? date('d M Y, H:i', strtotime($order['updated_at'])) : '-' ?></p>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($order['status'] === 'done'): ?>
            <div class="flex items-start">
              <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white mr-3 flex-shrink-0">
                <i data-feather="check-circle" class="w-5 h-5"></i>
              </div>
              <div>
                <p class="font-bold text-gray-800">Selesai</p>
                <p class="text-sm text-gray-500"><?= $order['updated_at'] ? date('d M Y, H:i', strtotime($order['updated_at'])) : '-' ?></p>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($order['status'] === 'cancelled'): ?>
            <div class="flex items-start">
              <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white mr-3 flex-shrink-0">
                <i data-feather="x" class="w-5 h-5"></i>
              </div>
              <div>
                <p class="font-bold text-gray-800">Dibatalkan</p>
                <p class="text-sm text-gray-500"><?= $order['updated_at'] ? date('d M Y, H:i', strtotime($order['updated_at'])) : '-' ?></p>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="no-print bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl shadow-lg p-6 border-2 border-orange-200 slide-in">
          <h3 class="text-base font-bold text-gray-800 mb-3">Quick Actions</h3>
          <div class="space-y-2">
            <a href="orders.php" class="block w-full px-4 py-2.5 bg-white text-gray-700 rounded-xl hover:bg-gray-50 transition text-center font-semibold shadow-sm">
              <i data-feather="list" class="w-4 h-4 inline mr-2"></i> Semua Pesanan
            </a>
            <a href="dashboard.php" class="block w-full px-4 py-2.5 bg-white text-gray-700 rounded-xl hover:bg-gray-50 transition text-center font-semibold shadow-sm">
              <i data-feather="home" class="w-4 h-4 inline mr-2"></i> Dashboard
            </a>
          </div>
        </div>
      </div>

    </div>
    <?php endif; ?>
  </main>

  <script>
    feather.replace();
  </script>
</body>
</html>
