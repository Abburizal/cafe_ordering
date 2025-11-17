<?php
require_once '../config/config.php';
require_once '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Pastikan admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Handle Add Table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_table'])) {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    
    if (!empty($name) && !empty($code)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tables (name, code) VALUES (?, ?)");
            $stmt->execute([$name, $code]);
            $success = "Meja berhasil ditambahkan!";
        } catch (PDOException $e) {
            $error = "Gagal menambahkan meja: " . $e->getMessage();
        }
    } else {
        $error = "Nama dan kode meja harus diisi!";
    }
}

// Handle Delete Table
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tables WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Meja berhasil dihapus!";
    } catch (PDOException $e) {
        $error = "Gagal menghapus meja: " . $e->getMessage();
    }
}

// Handle Update Table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_table'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    
    if (!empty($name) && !empty($code)) {
        try {
            $stmt = $pdo->prepare("UPDATE tables SET name = ?, code = ? WHERE id = ?");
            $stmt->execute([$name, $code, $id]);
            $success = "Meja berhasil diupdate!";
        } catch (PDOException $e) {
            $error = "Gagal mengupdate meja: " . $e->getMessage();
        }
    }
}

// Get all tables
$tables = $pdo->query("SELECT * FROM tables ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Meja - Admin RestoKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal.active { display: flex; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-indigo-600">RestoKu Admin</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-700 hover:text-indigo-600 font-medium">Dashboard</a>
                    <a href="product.php" class="text-gray-700 hover:text-indigo-600 font-medium">Produk</a>
                    <a href="orders.php" class="text-gray-700 hover:text-indigo-600 font-medium">Pesanan</a>
                    <a href="tables.php" class="text-indigo-600 font-bold">Meja</a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Kelola Meja</h2>
                <p class="text-gray-600 mt-1">Tambah, edit, atau hapus meja dan generate QR Code</p>
            </div>
            <button onclick="openAddModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg transition">
                + Tambah Meja
            </button>
        </div>

        <!-- Messages -->
        <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl shadow-lg p-6 mb-6 text-white">
            <h3 class="text-xl font-bold mb-3">Generate QR Code untuk Semua Meja</h3>
            <p class="mb-4">Download atau cetak QR Code untuk semua meja sekaligus</p>
            <a href="generate_qr/" target="_blank" class="inline-block bg-white text-indigo-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition">
                Lihat Semua QR Code
            </a>
        </div>

        <!-- Tables List -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Meja</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Meja</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QR Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($tables as $table): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?= $table['id'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                            <?= htmlspecialchars($table['name']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <code class="bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($table['code']) ?></code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="showQRCode(<?= $table['id'] ?>, '<?= htmlspecialchars($table['name']) ?>', '<?= htmlspecialchars($table['code']) ?>')" 
                                    class="bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-1 rounded-lg font-medium transition">
                                Lihat QR
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <button onclick="openEditModal(<?= $table['id'] ?>, '<?= htmlspecialchars($table['name']) ?>', '<?= htmlspecialchars($table['code']) ?>')" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg transition">
                                Edit
                            </button>
                            <button onclick="confirmDelete(<?= $table['id'] ?>, '<?= htmlspecialchars($table['name']) ?>')" 
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg transition">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Table Modal -->
    <div id="addModal" class="modal items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
            <h3 class="text-2xl font-bold mb-6">Tambah Meja Baru</h3>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Meja</label>
                    <input type="text" name="name" required placeholder="Contoh: MEJA 1" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Kode Meja</label>
                    <input type="text" name="code" required placeholder="Contoh: TBL-001" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-sm text-gray-500 mt-1">Format: TBL-XXX (harus unik)</p>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" name="add_table" 
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-lg font-semibold transition">
                        Tambah
                    </button>
                    <button type="button" onclick="closeAddModal()" 
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-semibold transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Table Modal -->
    <div id="editModal" class="modal items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
            <h3 class="text-2xl font-bold mb-6">Edit Meja</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Meja</label>
                    <input type="text" name="name" id="edit_name" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Kode Meja</label>
                    <input type="text" name="code" id="edit_code" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex space-x-3">
                    <button type="submit" name="update_table" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                        Update
                    </button>
                    <button type="button" onclick="closeEditModal()" 
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-semibold transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div id="qrModal" class="modal items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4 text-center">
            <h3 class="text-2xl font-bold mb-4" id="qr_table_name"></h3>
            <div id="qr_code_container" class="mb-4"></div>
            <p class="text-sm text-gray-600 mb-4">Klik kanan dan "Save Image As..." untuk download</p>
            <button onclick="closeQRModal()" 
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold transition">
                Tutup
            </button>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
        }
        
        function openEditModal(id, name, code) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_code').value = code;
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        
        function confirmDelete(id, name) {
            if (confirm(`Apakah Anda yakin ingin menghapus ${name}?`)) {
                window.location.href = `tables.php?delete=${id}`;
            }
        }
        
        function showQRCode(id, name, code) {
            document.getElementById('qr_table_name').textContent = name;
            
            // Generate QR Code using API
            const url = '<?= BASE_URL ?>/index.php?code=' + encodeURIComponent(code);
            const qrContainer = document.getElementById('qr_code_container');
            qrContainer.innerHTML = `<img src="api/generate_qr.php?code=${encodeURIComponent(code)}" alt="QR Code" class="mx-auto rounded-lg shadow-lg">`;
            
            document.getElementById('qrModal').classList.add('active');
        }
        
        function closeQRModal() {
            document.getElementById('qrModal').classList.remove('active');
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>
