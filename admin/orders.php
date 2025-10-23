<?php

require_once '../config/config.php';
require_once '../app/helpers.php';

// Pastikan admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ==========================================================
// 1. PHP AJAX ENDPOINT untuk Polling (Hanya untuk pending orders)
// ==========================================================
if (isset($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
    header('Content-Type: application/json');
    try {
        // Ambil data pesanan yang masih 'pending' atau 'processing'
        $stmt = $pdo->query("SELECT o.id, o.order_code, o.status, o.created_at, t.name AS table_name 
                             FROM orders o 
                             LEFT JOIN tables t ON o.table_id = t.id
                             WHERE o.status IN ('pending', 'processing')
                             ORDER BY o.created_at DESC");
        $ajax_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ambil detail item untuk pesanan pending/processing
        $order_ids = array_column($ajax_orders, 'id');
        $order_items = [];
        if (!empty($order_ids)) {
            $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
            $stmt_items = $pdo->prepare("SELECT oi.order_id, oi.qty, p.name AS product_name
                                         FROM order_items oi
                                         JOIN products p ON oi.product_id = p.id
                                         WHERE oi.order_id IN ($placeholders)");
            $stmt_items->execute($order_ids);
            $raw_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            foreach ($raw_items as $item) {
                if (!isset($order_items[$item['order_id']])) {
                    $order_items[$item['order_id']] = [];
                }
                $order_items[$item['order_id']][] = $item;
            }
        }
        
        // Gabungkan item ke pesanan
        $response_data = [];
        foreach($ajax_orders as $order) {
            $order['items'] = $order_items[$order['id']] ?? [];
            $response_data[] = $order;
        }

        echo json_encode(['success' => true, 'orders' => $response_data]);
    } catch (PDOException $e) {
        error_log("AJAX Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit;
}


$message = '';

// === Ubah Status Pesanan ===
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['order_id'];
    $status = $_POST['status'];

    $valid_status = ['pending', 'processing', 'done', 'cancelled'];
    if (!in_array($status, $valid_status)) {
        $message = "⚠️ Status tidak valid!";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);
            $message = "✅ Status pesanan #$id berhasil diubah menjadi <b>$status</b>!";
        } catch (PDOException $e) {
             error_log("Error updating status: " . $e->getMessage());
             $message = "❌ Gagal memperbarui status!";
        }
    }
}

// === Filter Pesanan Berdasarkan Status (Untuk tampilan HTML) ===
$filter = $_GET['status'] ?? 'semua';
$sql = "SELECT o.*, t.name AS table_name FROM orders o 
        LEFT JOIN tables t ON o.table_id = t.id";

$where = '';
$params = [];

if ($filter !== 'semua') {
    $where = " WHERE o.status = ?";
    $params[] = $filter;
}

$sql .= $where . " ORDER BY o.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    $orders = [];
    $message = "❌ Gagal memuat data pesanan!";
}


// --- FECTH ORDER ITEMS (Untuk tampilan HTML) ---
$order_items = [];
$order_ids = array_column($orders, 'id');

if (!empty($order_ids)) {
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    
    try {
        $stmt_items = $pdo->prepare("SELECT oi.order_id, oi.qty, oi.price, p.name AS product_name
                                     FROM order_items oi
                                     JOIN products p ON oi.product_id = p.id
                                     WHERE oi.order_id IN ($placeholders)");
        $stmt_items->execute($order_ids);
        $raw_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        foreach ($raw_items as $item) {
            if (!isset($order_items[$item['order_id']])) {
                $order_items[$item['order_id']] = [];
            }
            // Tambahkan item ke array order_items
            $order_items[$item['order_id']][] = $item;
        }
    } catch (PDOException $e) {
         error_log("Error fetching order items: " . $e->getMessage());
    }
}

// Inject order_items into $orders array for easy access in HTML and JS
foreach ($orders as $key => $order) {
    $orders[$key]['items'] = $order_items[$order['id']] ?? [];
}

$adminName = e($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Pesanan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; }
    @keyframes popup {
      0% { transform: scale(0.8) translateX(100%); opacity: 0; }
      100% { transform: scale(1) translateX(0); opacity: 1; }
    }
    .popup { animation: popup 0.4s ease-out; }
    .modal-overlay { background-color: rgba(0, 0, 0, 0.6); }
    .modal-content { animation: modal-fade-in 0.3s ease-out; }
    @keyframes modal-fade-in {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .new-order-flash {
        animation: flash 1s infinite alternate;
    }
    @keyframes flash {
        from { background-color: #fefcbf; }
        to { background-color: #f7eed7; }
    }
  </style>
</head>
<body class="bg-stone-100 min-h-screen">

  <!-- 2. AUDIO ELEMENT untuk Notifikasi Suara -->
  <audio id="notificationSound" preload="auto">
      <!-- Menggunakan Base64 data URI untuk suara 'Beep' sederhana -->
      <source src="data:audio/mp3;base64,SUQzBAAAAAAAAgEVAAAEVFRTUzAAAABJdmV0b3I6IExhdmYgNjMuMC4xMDAgU2VyaWVzIHRvb2xzIGZvciBMSlAtTVAzIFByb2plY3QgKENPUEVSQUQgQ1JFQVRJWkpPV04gU09MVVRJT04p" type="audio/mp3">
  </audio>

  <!-- Navbar (tetap sama) -->
  <header class="fixed top-3 left-0 right-0 z-50 flex justify-center">
    <div class="flex items-center justify-between w-[95%] md:w-[80%] lg:w-[70%] bg-white/80 backdrop-blur-lg border border-gray-200 rounded-3xl shadow-xl px-5 py-3 relative">
      <div class="flex items-center space-x-2">
        <i data-feather="coffee" class="h-6 w-6 text-indigo-600 stroke-[2]"></i>
        <span class="text-xl font-bold text-gray-800 hidden sm:inline">Admin Resto</span>
      </div>
      <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
        <a href="dashboard.php" class="hover:text-indigo-600 transition">Dashboard</a>
        <a href="product.php" class="hover:text-indigo-600 transition">Produk</a>
        <a href="orders.php" class="text-indigo-600 font-semibold border-b-2 border-indigo-600 pb-1">Orders</a>
      </nav>
      <div class="hidden md:flex items-center space-x-3">
        <button id="requestNotificationBtn" class="px-4 py-2 rounded-xl bg-green-500 text-white text-sm hover:bg-green-600 font-medium transition shadow-md" title="Minta Izin Notifikasi">
            <i data-feather="bell" class="w-4 h-4 inline mr-1"></i> Izin Notif
        </button>
        <a href="logout.php" class="px-4 py-2 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 text-sm hover:bg-indigo-100 font-medium transition">
            <i data-feather="log-out" class="w-4 h-4 inline mr-1"></i> Sign out
        </a>
      </div>
      <button id="menuBtn" class="md:hidden flex items-center p-2 rounded-lg hover:bg-gray-100 focus:outline-none">
        <i data-feather="menu" id="menuIcon" class="h-6 w-6 text-gray-700"></i>
      </button>
      <div id="dropdownMenu" class="hidden absolute top-16 right-4 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-2 text-sm z-50">
        <a href="dashboard.php" class="block px-4 py-2 hover:bg-gray-100 flex items-center"><i data-feather="grid" class="w-4 h-4 mr-2"></i> Dashboard</a>
        <a href="product.php" class="block px-4 py-2 hover:bg-gray-100 flex items-center"><i data-feather="package" class="w-4 h-4 mr-2"></i> Produk</a>
        <a href="orders.php" class="block px-4 py-2 bg-indigo-50 font-semibold text-indigo-700 flex items-center"><i data-feather="file-text" class="w-4 h-4 mr-2"></i> Orders</a>
        <hr class="my-1">
        <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600 flex items-center"><i data-feather="log-out" class="w-4 h-4 mr-2"></i> Logout</a>
      </div>
    </div>
  </header>

  <!-- Konten -->
  <main class="pt-28 px-4 md:px-10 max-w-7xl mx-auto">
    <!-- Message Pop-up (tetap sama) -->
    <?php if ($message): ?>
    <div id="popup" class="popup fixed top-5 right-5 bg-indigo-600 text-white px-5 py-3 rounded-xl shadow-xl text-sm font-semibold z-50">
      <?= $message ?>
    </div>
    <script>
      const popup = document.getElementById('popup');
      setTimeout(() => {
        popup.style.opacity = '0';
        popup.style.transform = 'scale(0.9)';
        setTimeout(() => popup.remove(), 400);
      }, 3000);
    </script>
    <?php endif; ?>
    
    <!-- Header dan Filter (tetap sama) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
      <h2 class="text-3xl font-extrabold text-gray-800 mb-4 sm:mb-0 flex items-center">
        <i data-feather="clipboard" class="w-7 h-7 mr-3 text-indigo-600"></i>
        Manajemen Pesanan
      </h2>
      <form method="GET" class="flex gap-2 bg-white p-2 rounded-xl shadow-md border">
        <select name="status" class="border-none rounded-lg px-3 py-1 text-sm focus:ring-0 outline-none bg-white">
          <option value="semua" <?= $filter==='semua'?'selected':'' ?>>Semua Pesanan</option>
          <option value="pending" <?= $filter==='pending'?'selected':'' ?>>Pending</option>
          <option value="processing" <?= $filter==='processing'?'selected':'' ?>>Diproses</option>
          <option value="done" <?= $filter==='done'?'selected':'' ?>>Selesai</option>
          <option value="cancelled" <?= $filter==='cancelled'?'selected':'' ?>>Dibatalkan</option>
        </select>
        <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded-lg font-semibold text-sm transition shadow-md">
          <i data-feather="filter" class="w-4 h-4 inline mr-1"></i> Filter
        </button>
      </form>
    </div>

    <!-- Tabel Pesanan -->
    <div class="overflow-x-auto bg-white rounded-xl shadow-2xl border border-gray-100">
      <table class="min-w-full text-sm">
        <thead class="bg-indigo-50 text-indigo-800 uppercase font-semibold">
          <tr>
            <th class="py-3 px-4 text-left">Kode Order</th>
            <th class="py-3 px-4 text-left">Meja</th>
            <th class="py-3 px-4 text-left">Total</th>
            <th class="py-3 px-4 text-left">Metode</th>
            <th class="py-3 px-4 text-left">Status</th>
            <th class="py-3 px-4 text-left">Waktu</th>
            <th class="py-3 px-4 text-center">Detail</th>
            <th class="py-3 px-4 text-center">Ubah Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr><td colspan="8" class="py-10 text-center text-gray-500 bg-white">
                <i data-feather="box" class="w-8 h-8 mx-auto mb-2"></i> 
                <p>😴 Belum ada pesanan untuk filter ini...</p>
            </td></tr>
          <?php else: foreach ($orders as $o): ?>
          <tr class="border-t hover:bg-indigo-50/50 transition duration-150" id="order-row-<?= e($o['id']) ?>">
            <td class="py-3 px-4 font-medium text-gray-700"><?= e($o['order_code']) ?></td>
            <td class="py-3 px-4 font-bold text-gray-800"><?= e($o['table_name'] ?? 'N/A') ?></td>
            <td class="py-3 px-4 text-blue-600 font-bold"><?= currency($o['total']) ?></td>
            <td class="py-3 px-4 text-gray-600"><?= ucfirst($o['payment_method']) ?></td>
            <td class="py-3 px-4">
              <?php 
                $status_class = [
                    'pending' => 'bg-gray-100 text-gray-600',
                    'processing' => 'bg-yellow-100 text-yellow-600',
                    'done' => 'bg-green-100 text-green-600',
                    'cancelled' => 'bg-red-100 text-red-600'
                ];
                $status_text = [
                    'pending'=>'Pending','processing'=>'Diproses','done'=>'Selesai','cancelled'=>'Dibatalkan'
                ];
              ?>
              <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $status_class[$o['status']] ?>">
                <?= $status_text[$o['status']] ?? ucfirst($o['status']) ?>
              </span>
            </td>
            <td class="py-3 px-4 text-gray-500 text-xs"><?= date('d M H:i', strtotime($o['created_at'])) ?></td>
            
            <!-- Tombol Detail -->
            <td class="py-3 px-4 text-center">
                <button 
                    onclick="showOrderDetails(<?= htmlspecialchars(json_encode($o), ENT_QUOTES, 'UTF-8') ?>)"
                    class="text-indigo-600 hover:text-indigo-800 font-medium text-sm transition"
                    title="Lihat Detail Pesanan"
                >
                    <i data-feather="eye" class="w-5 h-5 inline"></i>
                </button>
            </td>

            <!-- Aksi Status -->
            <td class="py-3 px-4 text-center">
              <form method="POST" class="flex items-center gap-1 justify-center">
                <input type="hidden" name="order_id" value="<?= e($o['id']) ?>">
                <select name="status" class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                  <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>Pending</option>
                  <option value="processing" <?= $o['status']=='processing'?'selected':'' ?>>Diproses</option>
                  <option value="done" <?= $o['status']=='done'?'selected':'' ?>>Selesai</option>
                  <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>Dibatalkan</option>
                </select>
                <button type="submit" name="update_status" class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-1 rounded-lg font-semibold transition shadow-sm">
                  <i data-feather="save" class="w-3 h-3 inline"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
    <!-- Status Notifikasi (untuk debugging/informasi) -->
    <div class="mt-4 p-3 bg-white rounded-lg shadow-sm text-xs text-gray-500">
        <p>Status Notifikasi Web: <strong id="notificationStatus" class="text-red-500">Izin belum diberikan</strong></p>
        <p>Update Terakhir: <strong id="lastUpdate">Tidak ada</strong></p>
    </div>
  </main>

  <!-- Detail Modal (tetap sama) -->
  <div id="orderDetailModal" class="fixed inset-0 hidden items-center justify-center z-[100] modal-overlay transition-opacity duration-300" onclick="closeModal(event)">
      <div class="bg-white rounded-xl shadow-2xl p-6 w-11/12 max-w-lg modal-content" onclick="event.stopPropagation()">
          <div class="flex justify-between items-center border-b pb-3 mb-4">
              <h3 class="text-xl font-bold text-indigo-700 flex items-center">
                <i data-feather="list" class="w-5 h-5 mr-2"></i>
                Detail Pesanan: <span id="modal-order-code" class="ml-2 text-gray-600 font-semibold text-base"></span>
              </h3>
              <button onclick="document.getElementById('orderDetailModal').classList.add('hidden');" class="text-gray-400 hover:text-gray-600 transition">
                  <i data-feather="x" class="w-6 h-6"></i>
              </button>
          </div>

          <div id="modal-summary" class="bg-indigo-50 p-3 rounded-lg text-sm mb-4 space-y-1">
              <p>Meja: <strong id="modal-table"></strong></p>
              <p>Metode: <strong id="modal-method"></strong></p>
              <p>Waktu: <strong id="modal-time"></strong></p>
              <p>Status: <strong id="modal-status-text" class="px-2 py-0.5 rounded-full text-xs font-semibold"></strong></p>
          </div>

          <h4 class="text-lg font-semibold text-gray-700 mb-2">Item Dipesan:</h4>
          
          <div class="max-h-60 overflow-y-auto mb-4 border rounded-lg">
              <table class="min-w-full text-sm">
                  <thead class="bg-gray-100 sticky top-0">
                      <tr>
                          <th class="py-2 px-3 text-left">Menu</th>
                          <th class="py-2 px-3 text-center">Qty</th>
                          <th class="py-2 px-3 text-right">Subtotal</th>
                      </tr>
                  </thead>
                  <tbody id="modal-items-body">
                      <!-- Item details will be inserted here -->
                  </tbody>
              </table>
          </div>
          
          <div class="flex justify-between items-center pt-3 border-t">
              <p class="text-lg font-bold text-gray-800">TOTAL:</p>
              <p class="text-3xl font-extrabold text-indigo-700" id="modal-total"></p>
          </div>
      </div>
  </div>


  <script>
    feather.replace();

    // ==========================================================
    // NOTIFIKASI & POLLING LOGIC
    // ==========================================================

    const notificationSound = document.getElementById('notificationSound');
    // Menyimpan ID pesanan yang sedang 'pending' atau 'processing' di memori
    let knownOrderIds = new Set(<?= json_encode(array_column(array_filter($orders, function($o) {
        return in_array($o['status'], ['pending', 'processing']);
    }), 'id')) ?>.map(id => parseInt(id)));

    const POLLING_INTERVAL = 5000; // Cek setiap 5 detik
    const NOTIFICATION_TITLE = "🔔 PESANAN BARU MASUK!";
    
    const notificationStatusElement = document.getElementById('notificationStatus');
    const lastUpdateElement = document.getElementById('lastUpdate');

    // Minta Izin Notifikasi Web
    function requestNotificationPermission() {
        if (!("Notification" in window)) {
            console.error("Browser tidak mendukung notifikasi.");
            notificationStatusElement.textContent = "Browser tidak mendukung";
            notificationStatusElement.className = "text-red-500";
            return;
        }

        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                console.log("Izin notifikasi diberikan.");
                notificationStatusElement.textContent = "Izin Diberikan";
                notificationStatusElement.className = "text-green-500";
            } else if (permission === "denied") {
                console.log("Izin notifikasi ditolak.");
                notificationStatusElement.textContent = "Izin Ditolak";
                notificationStatusElement.className = "text-red-500";
            } else {
                notificationStatusElement.textContent = "Izin belum diberikan";
                notificationStatusElement.className = "text-yellow-500";
            }
        });
    }

    // Tampilkan Notifikasi Web
    function showWebNotification(order) {
        if (Notification.permission === "granted") {
            const body = `Kode: ${order.order_code} | Meja: ${order.table_name || 'N/A'}\nItem: ${order.items.map(i => i.qty + 'x ' + i.product_name).join(', ')}`;
            
            // Notifikasi Web
            new Notification(NOTIFICATION_TITLE, {
                body: body,
                icon: 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" /></svg>')
            });
        }
    }

    // Main Polling Function
    async function checkNewOrders() {
        try {
            // Panggil endpoint AJAX yang baru dibuat
            const response = await fetch('orders.php?is_ajax=1', { 
                method: 'GET',
                headers: {'Accept': 'application/json'}
            });
            const data = await response.json();

            if (data.success && data.orders) {
                const currentOrderIds = new Set(data.orders.map(order => parseInt(order.id)));
                const newOrders = [];
                const newPendingOrders = [];
                
                // Cari Pesanan Baru
                data.orders.forEach(order => {
                    if (!knownOrderIds.has(parseInt(order.id))) {
                        newOrders.push(order);
                        knownOrderIds.add(parseInt(order.id));
                        
                        // Tandai sebagai pending baru untuk notifikasi
                        if (order.status === 'pending') {
                            newPendingOrders.push(order);
                        }
                    }
                });

                // Proses Notifikasi jika ada pesanan pending baru
                if (newPendingOrders.length > 0) {
                    notificationSound.play().catch(e => console.error("Gagal memutar suara:", e));
                    newPendingOrders.forEach(order => {
                        showWebNotification(order);
                    });
                }
                
                // Update tampilan (Opsional: Tambahkan highlight ke baris baru/pending)
                updateUiOrders(data.orders);

                lastUpdateElement.textContent = new Date().toLocaleTimeString('id-ID');
            }
        } catch (error) {
            console.error('Error saat polling:', error);
            lastUpdateElement.textContent = "Gagal (Error)";
        }
    }

    // Fungsi untuk mengupdate UI (hanya highlight baris, tidak me-refresh tabel)
    function updateUiOrders(currentOrders) {
        currentOrders.forEach(order => {
            const row = document.getElementById(`order-row-${order.id}`);
            if (row) {
                // Tambahkan efek flash jika pending atau processing
                if (order.status === 'pending' || order.status === 'processing') {
                    row.classList.add('new-order-flash');
                } else {
                    row.classList.remove('new-order-flash');
                }
            }
        });
    }

    // ==========================================================
    // INITIALIZATION & EVENT LISTENERS
    // ==========================================================

    document.addEventListener('DOMContentLoaded', () => {
        // Cek status notifikasi saat dimuat
        requestNotificationPermission();

        // Tombol Izin Notifikasi
        document.getElementById('requestNotificationBtn').addEventListener('click', requestNotificationPermission);
        
        // Mulai Polling setelah DOM siap
        // Cek segera setelah load (untuk initial highlight)
        checkNewOrders(); 
        
        // Polling loop
        setInterval(checkNewOrders, POLLING_INTERVAL);
    });
    
    // ==========================================================
    // FUNGSI UTILITY LAMA (tetap dipertahankan)
    // ==========================================================
    
    // Mapping status untuk tampilan
    const statusTextMap = {
        'pending': 'Pending',
        'processing': 'Diproses',
        'done': 'Selesai',
        'cancelled': 'Dibatalkan'
    };
    const statusClassMap = {
        'pending': 'bg-gray-100 text-gray-600',
        'processing': 'bg-yellow-100 text-yellow-600',
        'done': 'bg-green-100 text-green-600',
        'cancelled': 'bg-red-100 text-red-600'
    };

    function currencyFormat(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    function dateFormat(dateString) {
        const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    /**
     * Menampilkan modal dengan detail pesanan
     * @param {object} order - Objek pesanan yang sudah di-encode dari PHP
     */
    function showOrderDetails(order) {
        const modal = document.getElementById('orderDetailModal');
        
        // Update Summary
        document.getElementById('modal-order-code').textContent = order.order_code;
        document.getElementById('modal-table').textContent = order.table_name || 'N/A';
        document.getElementById('modal-method').textContent = order.payment_method.charAt(0).toUpperCase() + order.payment_method.slice(1);
        document.getElementById('modal-time').textContent = dateFormat(order.created_at);
        document.getElementById('modal-total').textContent = currencyFormat(order.total);
        
        // Update Status Badge
        const statusBadge = document.getElementById('modal-status-text');
        statusBadge.textContent = statusTextMap[order.status];
        statusBadge.className = statusClassMap[order.status] + ' px-2 py-0.5 rounded-full text-xs font-semibold';

        // Update Items
        const itemsBody = document.getElementById('modal-items-body');
        itemsBody.innerHTML = ''; // Kosongkan isi sebelumnya

        if (order.items.length === 0) {
            itemsBody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-gray-500">Tidak ada item ditemukan.</td></tr>';
        } else {
            order.items.forEach(item => {
                const row = `
                    <tr class="border-b last:border-b-0 hover:bg-gray-50">
                        <td class="py-2 px-3 font-medium text-gray-800">${item.product_name}</td>
                        <td class="py-2 px-3 text-center text-gray-600">${item.qty}</td>
                        <td class="py-2 px-3 text-right font-medium text-indigo-600">${currencyFormat(item.qty * item.price)}</td>
                    </tr>
                `;
                itemsBody.insertAdjacentHTML('beforeend', row);
            });
        }

        // Tampilkan modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(event) {
        if (event.target.id === 'orderDetailModal') {
             document.getElementById('orderDetailModal').classList.add('hidden');
        }
    }


    // Toggle menu mobile (tetap dipertahankan)
    const menuBtn = document.getElementById('menuBtn');
    const dropdown = document.getElementById('dropdownMenu');
    let open = false;
    menuBtn.addEventListener('click', () => {
        open = !open;
        dropdown.classList.toggle('hidden', !open);
    });
  </script>
</body>
</html>
