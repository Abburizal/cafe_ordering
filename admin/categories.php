<?php
require_once '../config/config.php';
require_once '../app/helpers.php';
require_once '../app/middleware.php';
require_once '../app/validator.php';

require_admin();

$message = '';
$error = '';

// Tambah kategori baru
if (isset($_POST['add_category'])) {
    $name = Validator::sanitize_string($_POST['name']);
    $description = Validator::sanitize_string($_POST['description']);
    $icon = Validator::sanitize_string($_POST['icon']);
    $display_order = (int)$_POST['display_order'];
    
    if (!Validator::validate_required($name)) {
        $error = "Nama kategori wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon, display_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $icon, $display_order]);
            $message = "✅ Kategori berhasil ditambahkan!";
        } catch (PDOException $e) {
            $error = "❌ Gagal menambahkan kategori. Nama mungkin sudah ada.";
        }
    }
}

// Update kategori
if (isset($_POST['update_category'])) {
    $id = (int)$_POST['id'];
    $name = Validator::sanitize_string($_POST['name']);
    $description = Validator::sanitize_string($_POST['description']);
    $icon = Validator::sanitize_string($_POST['icon']);
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, description=?, icon=?, display_order=?, is_active=? WHERE id=?");
        $stmt->execute([$name, $description, $icon, $display_order, $is_active, $id]);
        $message = "✅ Kategori berhasil diupdate!";
    } catch (PDOException $e) {
        $error = "❌ Gagal update kategori.";
    }
}

// Hapus kategori (soft delete)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Set is_active = 0
    $stmt = $pdo->prepare("UPDATE categories SET is_active=0 WHERE id=?");
    $stmt->execute([$id]);
    $message = "✅ Kategori berhasil dinonaktifkan!";
    
    header('Location: categories.php?msg=' . urlencode($message));
    exit;
}

// Ambil semua kategori
$categories = $pdo->query("SELECT * FROM categories ORDER BY display_order, name")->fetchAll(PDO::FETCH_ASSOC);

// Jika ada pesan dari redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-orange-600">🏷️ Kategori Produk</h1>
            <div class="flex gap-4">
                <a href="dashboard.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition">Dashboard</a>
                <a href="product.php" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition">Produk</a>
                <a href="logout.php" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Notifikasi -->
        <?php if ($message): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6">
                <?= e($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form Tambah Kategori -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">➕ Tambah Kategori Baru</h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="name" placeholder="Nama Kategori *" required 
                    class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
                <input type="text" name="description" placeholder="Deskripsi" 
                    class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
                <input type="text" name="icon" placeholder="Icon (emoji)" 
                    class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
                <input type="number" name="display_order" placeholder="Urutan" value="0" 
                    class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
                <button type="submit" name="add_category" 
                    class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                    Tambah
                </button>
            </form>
        </div>

        <!-- Daftar Kategori -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-orange-500 to-yellow-500">
                <h2 class="text-xl font-bold text-white">📋 Daftar Kategori (<?= count($categories) ?>)</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Icon</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Urutan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm"><?= $cat['id'] ?></td>
                            <td class="px-6 py-4 text-2xl"><?= e($cat['icon']) ?></td>
                            <td class="px-6 py-4 font-medium"><?= e($cat['name']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?= e($cat['description']) ?></td>
                            <td class="px-6 py-4 text-sm"><?= $cat['display_order'] ?></td>
                            <td class="px-6 py-4">
                                <?php if ($cat['is_active']): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Aktif</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <button onclick="editCategory(<?= $cat['id'] ?>, '<?= e($cat['name']) ?>', '<?= e($cat['description']) ?>', '<?= e($cat['icon']) ?>', <?= $cat['display_order'] ?>, <?= $cat['is_active'] ?>)"
                                        class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded transition">
                                        Edit
                                    </button>
                                    <?php if ($cat['is_active']): ?>
                                    <a href="?delete=<?= $cat['id'] ?>" 
                                        onclick="return confirm('Nonaktifkan kategori ini?')"
                                        class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs rounded transition">
                                        Nonaktifkan
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
            <h3 class="text-2xl font-bold mb-4">Edit Kategori</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Nama Kategori</label>
                        <input type="text" name="name" id="edit_name" required 
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Deskripsi</label>
                        <input type="text" name="description" id="edit_description" 
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Icon (Emoji)</label>
                        <input type="text" name="icon" id="edit_icon" 
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Urutan Tampilan</label>
                        <input type="number" name="display_order" id="edit_display_order" 
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="edit_is_active" class="mr-2">
                        <label class="text-gray-700">Aktif</label>
                    </div>
                </div>
                <div class="flex gap-4 mt-6">
                    <button type="submit" name="update_category" 
                        class="flex-1 px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                        Update
                    </button>
                    <button type="button" onclick="closeModal()" 
                        class="flex-1 px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editCategory(id, name, description, icon, display_order, is_active) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_icon').value = icon;
            document.getElementById('edit_display_order').value = display_order;
            document.getElementById('edit_is_active').checked = is_active == 1;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</body>
</html>
