<?php
session_start();
require '../config/config.php';
require '../app/helpers.php';

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = (int)$_GET['order_id'];

// Get order with items
$stmt = $pdo->prepare("
    SELECT o.*, 
           COALESCE(o.table_number, t.name, 'N/A') AS table_name
    FROM orders o
    LEFT JOIN tables t ON o.table_id = t.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$stmt_items = $pdo->prepare("
    SELECT oi.*, p.name AS product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Status mapping
$status_map = [
    'pending' => 'Menunggu Konfirmasi',
    'processing' => 'Sedang Diproses',
    'done' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];
$current_status = $status_map[$order['status']] ?? $order['status'];

$status_colors = [
    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
    'processing' => 'bg-blue-100 text-blue-800 border-blue-300',
    'done' => 'bg-green-100 text-green-800 border-green-300',
    'cancelled' => 'bg-red-100 text-red-800 border-red-300'
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan #<?= $order['order_code'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        .pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .slide-in { animation: slideIn 0.5s ease-out; }
        
        .timeline-dot {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
        }
        
        .timeline-line {
            position: absolute;
            left: 23px;
            top: 60px;
            width: 2px;
            height: calc(100% - 60px);
            background: #e5e7eb;
        }
        
        .timeline-line.active {
            background: linear-gradient(to bottom, #6366f1 0%, #e5e7eb 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-orange-50 min-h-screen py-8 px-4">
    
    <div class="max-w-2xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8 slide-in">
            <a href="menu.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-semibold mb-4 transition">
                <i data-feather="arrow-left" class="w-5 h-5 mr-2"></i>
                Kembali ke Menu
            </a>
            <h1 class="text-3xl font-extrabold text-gray-800 flex items-center">
                <i data-feather="shopping-bag" class="mr-3 text-indigo-600"></i>
                Status Pesanan
            </h1>
            <p class="text-gray-600 mt-2">Kode: <span class="font-bold text-indigo-600"><?= htmlspecialchars($order['order_code']) ?></span></p>
        </div>

        <!-- Order Info Card -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 border border-gray-200 slide-in">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Nomor Meja</p>
                    <p class="text-2xl font-bold text-gray-800 flex items-center">
                        <i data-feather="map-pin" class="w-6 h-6 mr-2 text-indigo-600"></i>
                        <?= htmlspecialchars($order['table_name']) ?>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 mb-1">Total</p>
                    <p class="text-2xl font-extrabold text-orange-600">
                        <?= currency($order['total']) ?>
                    </p>
                </div>
            </div>
            
            <div class="pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-1">Waktu Order</p>
                <p class="text-gray-700 font-medium">
                    <i data-feather="clock" class="w-4 h-4 inline mr-1"></i>
                    <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                </p>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 border border-gray-200 slide-in">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Status Saat Ini</h2>
            <div id="status-badge" class="px-6 py-4 rounded-xl border-2 <?= $status_colors[$order['status']] ?> font-bold text-center text-xl">
                <span id="status-text"><?= $current_status ?></span>
            </div>
            
            <!-- Status Messages -->
            <div id="status-message" class="mt-4 p-4 rounded-lg bg-blue-50 border border-blue-200">
                <?php if ($order['status'] === 'pending'): ?>
                    <p class="text-blue-800 text-center">
                        <i data-feather="clock" class="w-5 h-5 inline mr-2"></i>
                        Pesanan Anda sedang menunggu konfirmasi pembayaran
                    </p>
                <?php elseif ($order['status'] === 'processing'): ?>
                    <p class="text-blue-800 text-center">
                        <i data-feather="loader" class="w-5 h-5 inline mr-2 pulse"></i>
                        Pesanan Anda sedang diproses oleh dapur
                    </p>
                <?php elseif ($order['status'] === 'done'): ?>
                    <p class="text-green-800 text-center">
                        <i data-feather="check-circle" class="w-5 h-5 inline mr-2"></i>
                        Pesanan Anda sudah selesai! Silakan ambil di kasir
                    </p>
                <?php elseif ($order['status'] === 'cancelled'): ?>
                    <p class="text-red-800 text-center">
                        <i data-feather="x-circle" class="w-5 h-5 inline mr-2"></i>
                        Pesanan Anda telah dibatalkan
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline Status -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 border border-gray-200 slide-in">
            <h2 class="text-lg font-bold text-gray-800 mb-6">Timeline Pesanan</h2>
            
            <div class="relative">
                <!-- Pending -->
                <div class="flex items-start mb-8 relative">
                    <div class="timeline-line <?= in_array($order['status'], ['processing', 'done']) ? 'active' : '' ?>"></div>
                    <div class="timeline-dot <?= $order['status'] === 'pending' ? 'bg-yellow-500 text-white' : ($order['status'] !== 'cancelled' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600') ?>">
                        <i data-feather="clock" class="w-6 h-6"></i>
                    </div>
                    <div class="ml-6 flex-1">
                        <h3 class="font-bold text-gray-800">Pesanan Dibuat</h3>
                        <p class="text-sm text-gray-600"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
                        <p class="text-sm text-gray-500 mt-1">Menunggu konfirmasi pembayaran</p>
                    </div>
                </div>

                <!-- Processing -->
                <div class="flex items-start mb-8 relative">
                    <div class="timeline-line <?= $order['status'] === 'done' ? 'active' : '' ?>"></div>
                    <div class="timeline-dot <?= $order['status'] === 'processing' ? 'bg-blue-500 text-white pulse' : ($order['status'] === 'done' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600') ?>">
                        <i data-feather="<?= $order['status'] === 'processing' ? 'loader' : 'check' ?>" class="w-6 h-6"></i>
                    </div>
                    <div class="ml-6 flex-1">
                        <h3 class="font-bold text-gray-800">Sedang Diproses</h3>
                        <p class="text-sm text-gray-600">
                            <?php if (in_array($order['status'], ['processing', 'done'])): ?>
                                <?= $order['updated_at'] ? date('d M Y, H:i', strtotime($order['updated_at'])) : '-' ?>
                            <?php else: ?>
                                Menunggu...
                            <?php endif; ?>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">Pesanan sedang disiapkan dapur</p>
                    </div>
                </div>

                <!-- Done -->
                <div class="flex items-start">
                    <div class="timeline-dot <?= $order['status'] === 'done' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' ?>">
                        <i data-feather="check-circle" class="w-6 h-6"></i>
                    </div>
                    <div class="ml-6 flex-1">
                        <h3 class="font-bold text-gray-800">Selesai</h3>
                        <p class="text-sm text-gray-600">
                            <?php if ($order['status'] === 'done'): ?>
                                <?= $order['updated_at'] ? date('d M Y, H:i', strtotime($order['updated_at'])) : '-' ?>
                            <?php else: ?>
                                Menunggu...
                            <?php endif; ?>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">Pesanan siap diambil</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 border border-gray-200 slide-in">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i data-feather="list" class="mr-2 text-indigo-600"></i>
                Detail Pesanan
            </h2>
            
            <div class="space-y-3">
                <?php foreach ($items as $item): ?>
                <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full font-bold text-sm">
                            <?= $item['qty'] ?>x
                        </span>
                        <span class="font-medium text-gray-800"><?= htmlspecialchars($item['product_name']) ?></span>
                    </div>
                    <span class="font-bold text-gray-800"><?= currency($item['price'] * $item['qty']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4 pt-4 border-t-2 border-gray-300 flex justify-between items-center">
                <span class="text-lg font-bold text-gray-800">TOTAL</span>
                <span class="text-2xl font-extrabold text-indigo-600"><?= currency($order['total']) ?></span>
            </div>
        </div>

        <!-- Actions -->
        <div class="grid grid-cols-2 gap-4 slide-in">
            <a href="menu.php" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition font-semibold text-center flex items-center justify-center">
                <i data-feather="arrow-left" class="w-5 h-5 mr-2"></i>
                Menu
            </a>
            <button onclick="location.reload()" class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold flex items-center justify-center">
                <i data-feather="refresh-cw" class="w-5 h-5 mr-2"></i>
                Refresh
            </button>
        </div>

        <!-- Auto Refresh Indicator -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <i data-feather="rotate-cw" class="w-4 h-4 inline mr-1"></i>
            Status akan diupdate otomatis setiap 5 detik
        </div>

    </div>

    <script>
        feather.replace();
        
        const orderId = <?= $order_id ?>;
        const statusBadge = document.getElementById('status-badge');
        const statusText = document.getElementById('status-text');
        const statusMessage = document.getElementById('status-message');

        const statusMap = {
            'pending': 'Menunggu Konfirmasi',
            'processing': 'Sedang Diproses',
            'done': 'Selesai',
            'cancelled': 'Dibatalkan'
        };

        const statusColors = {
            'pending': 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'processing': 'bg-blue-100 text-blue-800 border-blue-300',
            'done': 'bg-green-100 text-green-800 border-green-300',
            'cancelled': 'bg-red-100 text-red-800 border-red-300'
        };

        const statusMessages = {
            'pending': '<i data-feather="clock" class="w-5 h-5 inline mr-2"></i>Pesanan Anda sedang menunggu konfirmasi pembayaran',
            'processing': '<i data-feather="loader" class="w-5 h-5 inline mr-2 pulse"></i>Pesanan Anda sedang diproses oleh dapur',
            'done': '<i data-feather="check-circle" class="w-5 h-5 inline mr-2"></i>Pesanan Anda sudah selesai! Silakan ambil di kasir',
            'cancelled': '<i data-feather="x-circle" class="w-5 h-5 inline mr-2"></i>Pesanan Anda telah dibatalkan'
        };

        let currentStatus = '<?= $order['status'] ?>';

        async function checkStatus() {
            try {
                const response = await fetch(`api/get_status.php?order_id=${orderId}`);
                const data = await response.json();
                
                if (data.status && data.status !== currentStatus) {
                    // Status changed!
                    currentStatus = data.status;
                    
                    // Update badge
                    statusBadge.className = 'px-6 py-4 rounded-xl border-2 font-bold text-center text-xl ' + statusColors[currentStatus];
                    statusText.textContent = statusMap[currentStatus];
                    
                    // Update message
                    statusMessage.innerHTML = '<p class="text-center">' + statusMessages[currentStatus] + '</p>';
                    
                    // Add flash effect
                    statusBadge.classList.add('animate-pulse');
                    setTimeout(() => statusBadge.classList.remove('animate-pulse'), 1000);
                    
                    // Reload icons
                    feather.replace();
                    
                    // Show notification
                    showNotification('Status Updated', `Pesanan Anda sekarang: ${statusMap[currentStatus]}`);
                }
                
                // Continue polling if not done or cancelled
                if (currentStatus !== 'done' && currentStatus !== 'cancelled') {
                    setTimeout(checkStatus, 5000);
                } else {
                    // If done, show confetti or celebration
                    if (currentStatus === 'done') {
                        setTimeout(() => location.reload(), 2000);
                    }
                }
            } catch (error) {
                console.error('Error checking status:', error);
                setTimeout(checkStatus, 10000); // Retry after 10 seconds
            }
        }

        function showNotification(title, message) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, { body: message, icon: '/favicon.ico' });
            }
        }

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Start polling after 5 seconds
        setTimeout(checkStatus, 5000);
    </script>
</body>
</html>