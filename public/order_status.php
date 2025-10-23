<?php
session_start();
require '../config/config.php';
// Gunakan koneksi $pdo

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("ID Pesanan tidak ditemukan.");
}

$order_id = $_GET['order_id'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// Terjemahkan status
$status_map = [
    'pending' => 'Menunggu Pembayaran',
    'processing' => 'Sedang Diproses',
    'ready' => 'Siap Diambil (Selesai)',
    'cancelled' => 'Dibatalkan'
];
$current_status = $status_map[$order['status']] ?? $order['status'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Status Pesanan #<?= $order_id ?></title>
    <link href="/path/to/compiled/tailwind.css" rel="stylesheet"> </head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 max-w-lg">
        <h1 class="text-2xl font-bold mb-4">Status Pesanan #<?= $order_id ?></h1>
        <p class="text-lg">Nomor Meja: <span class="font-semibold"><?= htmlspecialchars($order['table_number']) ?></span></p>
        <p class="text-lg mb-4">Total Pembayaran: <span class="font-semibold">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span></p>
        
        <div class="p-4 rounded-lg shadow-md 
            <?php if ($order['status'] == 'ready') echo 'bg-green-100 border-l-4 border-green-500'; ?>
            <?php if ($order['status'] == 'processing') echo 'bg-blue-100 border-l-4 border-blue-500'; ?>
            <?php if ($order['status'] == 'pending') echo 'bg-yellow-100 border-l-4 border-yellow-500'; ?>
        ">
            <h2 class="text-xl font-bold">Status Saat Ini:</h2>
            <p id="status-pesanan" data-status="<?= htmlspecialchars($order['status']) ?>" class="text-2xl font-extrabold mt-2"><?= $current_status ?></p>
            <?php if ($order['status'] == 'ready'): ?>
                <p class="mt-2 text-green-700">Silakan ambil pesanan Anda di konter/kasir. Terima kasih!</p>
            <?php endif; ?>
        </div>
        
        <a href="menu.php" class="mt-4 inline-block text-blue-500 hover:text-blue-700">← Kembali ke Menu</a>
    </div>

    <script>
        const orderId = <?php echo json_encode($order_id); ?>;
        const statusElement = document.getElementById('status-pesanan');

        // mapping status mentah ke teks bahasa Indonesia (sama seperti di PHP)
        const statusMap = {
            'pending': 'Menunggu Pembayaran',
            'processing': 'Sedang Diproses',
            'ready': 'Siap Diambil (Selesai)',
            'cancelled': 'Dibatalkan'
        };

        function cekStatus() {
            fetch(`api/get_status.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.status) return;
                    const remoteStatus = data.status;
                    const currentRaw = statusElement.dataset.status || '';

                    if (remoteStatus && remoteStatus !== currentRaw) {
                        // update atribut raw dan teks yang terlihat
                        statusElement.dataset.status = remoteStatus;
                        statusElement.textContent = statusMap[remoteStatus] || remoteStatus;
                        // efek visual singkat
                        statusElement.classList.add('bg-yellow-200');
                        setTimeout(() => statusElement.classList.remove('bg-yellow-200'), 1000);
                    }

                    // lanjut polling jika belum selesai (anggap 'ready' atau 'cancelled' = selesai)
                    if (remoteStatus !== 'ready' && remoteStatus !== 'cancelled') {
                        setTimeout(cekStatus, 5000);
                    }
                })
                .catch(err => console.error(err));
        }

        // mulai polling setelah halaman dimuat
        setTimeout(cekStatus, 5000);
    </script>
</body>
</html>