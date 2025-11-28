<?php
require_once '../../config/config.php';
require_once '../../vendor/autoload.php'; // Panggil autoloader composer

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Pastikan admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Ambil semua data meja
$tables = $pdo->query("SELECT * FROM tables ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Generate QR Code Meja - Kantin Akademi MD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none; }
            .qr-card { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8 no-print">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">QR Code Semua Meja</h1>
                    <p class="text-gray-600">Klik kanan pada gambar dan pilih 'Save Image As...' atau gunakan tombol Print</p>
                </div>
                <div class="space-x-3">
                    <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        🖨️ Print Semua
                    </button>
                    <a href="../tables.php" class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        ← Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- QR Code Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($tables as $table): ?>
                <div class="qr-card bg-white border-2 border-gray-200 p-6 rounded-xl shadow-lg text-center hover:shadow-2xl transition">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 px-4 rounded-lg mb-4">
                        <h2 class="font-bold text-xl"><?= htmlspecialchars($table['name']) ?></h2>
                    </div>
                    <?php
                        // Tentukan URL berdasarkan BASE_URL dari config
                        $url = BASE_URL . '/index.php?code=' . htmlspecialchars($table['code']);
                        
                        // Generate QR Code (endroid/qr-code v6 - readonly class with constructor params)
                        $qrCode = new QrCode(
                            data: $url,
                            size: 300,
                            margin: 10
                        );
                        
                        $writer = new PngWriter();
                        try {
                            $result = $writer->write($qrCode);

                            // Tampilkan sebagai Data URI
                            echo '<img src="' . $result->getDataUri() . '" alt="QR Code ' . htmlspecialchars($table['name']) . '" class="mx-auto rounded-lg shadow-md mb-4">';
                        } catch (\Exception $e) {
                            // Show a helpful non-fatal error message so the admin can enable GD
                            $errMsg = htmlspecialchars($e->getMessage());
                            echo '<div class="mb-4 p-4 rounded-lg bg-red-50 border-l-4 border-red-400 text-red-700">';
                            echo '<strong>Gagal membuat QR:</strong> ' . $errMsg;
                            echo '<p class="text-sm mt-2">Jika pesan error terkait ekstensi GD, aktifkan ekstensi GD di XAMPP: buka <code>Config -> php.ini</code> pada XAMPP Control Panel atau edit <code>C:\\xampp\\php\\php.ini</code>, cari baris <code>;extension=gd</code> atau <code>;extension=gd2</code> dan hapus titik koma di depannya, lalu restart Apache.</p>';
                            echo '<p class="text-sm mt-1">Atau jalankan file <code>phpinfo()</code> untuk memverifikasi. (File sementara: <code>test_gd.php</code>)</p>';
                            echo '</div>';
                        }
                    ?>
                    <div class="bg-gray-50 py-2 px-4 rounded-lg mb-2">
                        <p class="text-sm font-semibold text-gray-700">Kode: <code class="bg-gray-200 px-2 py-1 rounded"><?= htmlspecialchars($table['code']) ?></code></p>
                    </div>
                    <p class="text-xs text-gray-500 break-all px-2">Scan untuk check-in</p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-600 text-sm no-print">
            <p>© <?= date('Y') ?> Kantin Akademi MD - Sistem QR Code Check-in</p>
        </div>
    </div>
</body>
</html>