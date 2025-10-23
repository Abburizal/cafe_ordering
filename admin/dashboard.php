<?php

require_once '../config/config.php';
require_once '../app/helpers.php';
require_once '../app/middleware.php';

require_admin();

// Ambil data ringkasan
try {
    $totalProduk = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totalPesanan = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

    // Pendapatan hari ini (hanya hitung pesanan yang 'done' atau 'processing' jika ada)
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE DATE(created_at)=? AND status IN ('done', 'processing')");
    $stmt->execute([$today]);
    $pendapatanHari = $stmt->fetchColumn() ?? 0;

    // Pendapatan bulan ini
    $month = date('Y-m');
    $stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE DATE_FORMAT(created_at,'%Y-%m')=? AND status IN ('done', 'processing')");
    $stmt->execute([$month]);
    $pendapatanBulan = $stmt->fetchColumn() ?? 0;
    
    // Pesanan Terbaru
    $orders = $pdo->query("SELECT o.*, t.name AS table_name FROM orders o
                           LEFT JOIN tables t ON o.table_id = t.id
                           ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Tangani error database
    error_log("Database error in dashboard: " . $e->getMessage());
    $totalProduk = 0;
    $totalPesanan = 0;
    $pendapatanHari = 0;
    $pendapatanBulan = 0;
    $orders = [];
    $message = "❌ Gagal memuat data dashboard.";
}


$adminName = e($_SESSION['username']);

// Status mapping for Indonesian display
$statusMap = [
    'pending' => 'Pending',
    'processing' => 'Diproses',
    'done' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];
$statusClassMap = [
    'pending' => 'bg-gray-100 text-gray-600',
    'processing' => 'bg-yellow-100 text-yellow-600',
    'done' => 'bg-green-100 text-green-600',
    'cancelled' => 'bg-red-100 text-red-600'
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; }
    @keyframes popup {
      0% { transform: scale(0.8) translateY(100%); opacity: 0; }
      100% { transform: scale(1) translateY(0); opacity: 1; }
    }
    .popup { animation: popup 0.4s ease-out; }
    .card-icon {
        background: linear-gradient(135deg, #6366f1 0%, #a5b4fc 100%);
    }
  </style>
</head>
<body class="bg-stone-100 min-h-screen">

  <!-- Navbar -->
  <header class="fixed top-3 left-0 right-0 z-50 flex justify-center">
    <div class="flex items-center justify-between w-[95%] md:w-[80%] lg:w-[70%] bg-white/80 backdrop-blur-lg border border-gray-200 rounded-3xl shadow-xl px-5 py-3 relative">
      
      <!-- Logo -->
      <div class="flex items-center space-x-2">
        <i data-feather="trello" class="h-6 w-6 text-indigo-600 stroke-[2]"></i>
        <span class="text-xl font-bold text-gray-800 hidden sm:inline">Admin Resto</span>
      </div>

      <!-- Menu desktop -->
      <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
        <a href="dashboard.php" class="text-indigo-600 font-semibold border-b-2 border-indigo-600 pb-1">Dashboard</a>
        <a href="product.php" class="hover:text-indigo-600 transition">Produk</a>
        <a href="orders.php" class="hover:text-indigo-600 transition">Orders</a>
      </nav>

      <!-- Aksi kanan -->
      <div class="hidden md:flex items-center space-x-3">
        <a href="logout.php" class="px-4 py-2 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm hover:bg-red-100 font-medium transition shadow-sm">
            <i data-feather="log-out" class="w-4 h-4 inline mr-1"></i> Sign out
        </a>
      </div>

      <!-- Tombol menu mobile -->
      <button id="menuBtn" class="md:hidden flex items-center p-2 rounded-lg hover:bg-gray-100 focus:outline-none">
        <i data-feather="menu" id="menuIcon" class="h-6 w-6 text-gray-700"></i>
      </button>

      <!-- Dropdown mobile -->
      <div id="dropdownMenu" class="hidden absolute top-16 right-4 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-2 text-sm z-50">
        <a href="dashboard.php" class="block px-4 py-2 bg-indigo-50 font-semibold text-indigo-700 flex items-center"><i data-feather="grid" class="w-4 h-4 mr-2"></i> Dashboard</a>
        <a href="product.php" class="block px-4 py-2 hover:bg-gray-100 flex items-center"><i data-feather="package" class="w-4 h-4 mr-2"></i> Produk</a>
        <a href="orders.php" class="block px-4 py-2 hover:bg-gray-100 flex items-center"><i data-feather="file-text" class="w-4 h-4 mr-2"></i> Orders</a>
        <hr class="my-1">
        <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600 flex items-center"><i data-feather="log-out" class="w-4 h-4 mr-2"></i> Logout</a>
      </div>
    </div>
  </header>

  <!-- Konten -->
  <main class="pt-28 px-4 md:px-10 max-w-7xl mx-auto">
    <!-- Pesan Sukses/Error (jika ada) -->
    <?php if (isset($message)): ?>
    <div id="popup" class="popup fixed top-5 right-5 bg-red-600 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold z-50">
      <?= $message ?>
    </div>
    <?php endif; ?>

    <h2 class="text-3xl font-extrabold mb-8 text-gray-800 flex items-center">
        <i data-feather="bar-chart-2" class="w-7 h-7 mr-3 text-indigo-600"></i>
        Ringkasan & Metrik
    </h2>

    <!-- Kartu Ringkasan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
      
      <!-- Card 1: Pendapatan Hari Ini -->
      <div class="bg-white p-5 rounded-xl shadow-lg border border-gray-100 flex items-center justify-between transition hover:shadow-xl">
        <div>
          <p class="text-sm font-medium text-gray-500 mb-1">Pendapatan Hari Ini</p>
          <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= currency($pendapatanHari) ?></h3>
        </div>
        <div class="card-icon p-3 rounded-full text-white">
            <i data-feather="trending-up" class="w-6 h-6 stroke-[2.5]"></i>
        </div>
      </div>
      
      <!-- Card 2: Pendapatan Bulan Ini -->
      <div class="bg-white p-5 rounded-xl shadow-lg border border-gray-100 flex items-center justify-between transition hover:shadow-xl">
        <div>
          <p class="text-sm font-medium text-gray-500 mb-1">Pendapatan Bulan Ini</p>
          <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= currency($pendapatanBulan) ?></h3>
        </div>
        <div class="card-icon p-3 rounded-full text-white">
            <i data-feather="dollar-sign" class="w-6 h-6 stroke-[2.5]"></i>
        </div>
      </div>
      
      <!-- Card 3: Jumlah Produk -->
      <div class="bg-white p-5 rounded-xl shadow-lg border border-gray-100 flex items-center justify-between transition hover:shadow-xl">
        <div>
          <p class="text-sm font-medium text-gray-500 mb-1">Jumlah Produk</p>
          <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $totalProduk ?></h3>
        </div>
        <div class="card-icon p-3 rounded-full text-white">
            <i data-feather="package" class="w-6 h-6 stroke-[2.5]"></i>
        </div>
      </div>
      
      <!-- Card 4: Total Pesanan -->
      <div class="bg-white p-5 rounded-xl shadow-lg border border-gray-100 flex items-center justify-between transition hover:shadow-xl">
        <div>
          <p class="text-sm font-medium text-gray-500 mb-1">Total Pesanan</p>
          <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $totalPesanan ?></h3>
        </div>
        <div class="card-icon p-3 rounded-full text-white">
            <i data-feather="file-text" class="w-6 h-6 stroke-[2.5]"></i>
        </div>
      </div>
    </div>

    <!-- Pesanan Terbaru -->
    <div class="mt-8">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i data-feather="clock" class="w-5 h-5 mr-2 text-indigo-500"></i>
            5 Pesanan Terbaru
        </h2>
        <a href="orders.php" class="text-sm text-indigo-600 font-medium hover:text-indigo-800 transition">Lihat Semua →</a>
      </div>

      <div class="overflow-x-auto bg-white rounded-xl shadow-2xl border border-gray-100">
        <table class="min-w-full text-sm">
          <thead class="bg-indigo-50 text-indigo-800 uppercase font-semibold">
            <tr>
              <th class="py-3 px-4 text-left">Kode Order</th>
              <th class="py-3 px-4 text-left">Meja</th>
              <th class="py-3 px-4 text-left">Total</th>
              <th class="py-3 px-4 text-left">Status</th>
              <th class="py-3 px-4 text-left">Metode</th>
              <th class="py-3 px-4 text-left">Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="6" class="py-10 text-center text-gray-500">
                    <i data-feather="inbox" class="w-6 h-6 mx-auto mb-2"></i> 
                    <p>Tidak ada pesanan terbaru.</p>
                </td></tr>
            <?php else: foreach ($orders as $o): ?>
              <tr class="border-t hover:bg-indigo-50/50 transition duration-150">
                <td class="py-3 px-4 font-medium text-gray-700"><?= e($o['order_code']) ?></td>
                <td class="py-3 px-4 font-semibold text-gray-800"><?= e($o['table_name'] ?? 'N/A') ?></td>
                <td class="py-3 px-4 text-blue-600 font-bold"><?= currency($o['total']) ?></td>
                <td class="py-3 px-4">
                  <span class="px-3 py-1 rounded-full text-xs font-semibold 
                    <?= $statusClassMap[$o['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= $statusMap[$o['status']] ?? ucfirst($o['status']) ?>
                  </span>
                </td>
                <td class="py-3 px-4 text-gray-600"><?= ucfirst($o['payment_method']) ?></td>
                <td class="py-3 px-4 text-gray-500 text-xs"><?= date('d M H:i', strtotime($o['created_at'])) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Popup Selamat Datang -->
  <div id="popup" class="popup fixed bottom-5 right-5 bg-indigo-600 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold z-50">
    👋 Selamat datang, **<?= $adminName ?>**! Semangat bekerja 😄
  </div>

  <script>
    feather.replace();

    // Utility function for currency formatting (PHP helper is not accessible in JS)
    function currency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    // Popup auto-hide
    const popup = document.getElementById('popup');
    if (popup) {
      setTimeout(() => {
        popup.style.opacity = '0';
        popup.style.transform = 'scale(0.9)';
        setTimeout(() => popup.remove(), 400);
      }, 3000); // Tampilkan sedikit lebih lama
    }

    // Dropdown mobile toggle
    const menuBtn = document.getElementById('menuBtn');
    const dropdown = document.getElementById('dropdownMenu');
    let open = false;
    
    // Toggling function
    const toggleDropdown = () => {
        open = !open;
        if (open) {
            dropdown.classList.remove('hidden');
            // Adding a timeout to allow CSS transition if needed
            setTimeout(() => {
                dropdown.style.opacity = '1';
                dropdown.style.transform = 'translateY(0)';
            }, 10);
        } else {
            dropdown.style.opacity = '0';
            dropdown.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                dropdown.classList.add('hidden');
            }, 300); // Match transition duration
        }
    };
    
    // Initial setup for transition
    dropdown.style.transition = 'all 0.3s ease';
    dropdown.style.opacity = '0';
    dropdown.style.transform = 'translateY(-10px)';

    menuBtn.addEventListener('click', toggleDropdown);

    // Close dropdown when clicking outside
    document.addEventListener('click', (event) => {
        const isClickInside = dropdown.contains(event.target) || menuBtn.contains(event.target);
        if (!isClickInside && open) {
            toggleDropdown();
        }
    });

  </script>
</body>
</html>
