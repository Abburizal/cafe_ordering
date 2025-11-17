<?php
// Note: This is a modified file for presentation. Ensure helper, config, and middleware files are correctly implemented in your environment.
require_once '../config/config.php';
require_once '../app/helpers.php';
require_once '../app/middleware.php';

require_admin();

$message = '';

// === Tambah Produk ===
if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $image = $_FILES['image']['name'] ?? '';
    // Gunakan nilai default jika stock atau description tidak disediakan di form
    $stock = (int)($_POST['stock'] ?? 100); 
    $description = trim($_POST['description'] ?? NULL);

    if ($name !== '' && $price !== '' && $image !== '') {
        // Assume file move logic is correct for presentation
        $targetDir = "../public/assets/images/";
        // Tambahkan timestamp atau random string ke nama file agar unik
        $uniqueImageName = time() . '_' . basename($image); 
        $targetFile = $targetDir . $uniqueImageName;
        @move_uploaded_file($_FILES['image']['tmp_name'], $targetFile); 

        // Menambahkan kolom is_active=1 secara default
        $stmt = $pdo->prepare("INSERT INTO products (name, price, image, stock, description, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $price, $uniqueImageName, $stock, $description]);
        $message = "🎉 Produk baru berhasil ditambahkan!";
    } else {
        $message = "⚠️ Nama, Harga, dan Gambar wajib diisi!";
    }
}

// === Arsipkan / Nonaktifkan Produk (Soft Delete) ===
if (isset($_GET['archive'])) {
    $id = (int)$_GET['archive'];

    // Cek apakah produk ini memiliki riwayat pesanan (optional, tapi baik untuk notifikasi)
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $checkStmt->execute([$id]);
    $count = $checkStmt->fetchColumn();
    
    // TIDAK melakukan DELETE, melainkan UPDATE status is_active menjadi 0 (Nonaktif)
    $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
    $stmt->execute([$id]);

    if ($count > 0) {
        $message = "📦 Produk **berhasil diarsip** dan disembunyikan dari menu. $count riwayat pesanan terkait tetap aman tersimpan.";
    } else {
        $message = "📦 Produk berhasil diarsip dan disembunyikan dari menu.";
    }
}

// === Aktifkan Kembali Produk ===
if (isset($_GET['activate'])) {
    $id = (int)$_GET['activate'];
    $stmt = $pdo->prepare("UPDATE products SET is_active = 1 WHERE id = ?");
    $stmt->execute([$id]);
    $message = "✅ Produk berhasil diaktifkan kembali!";
}

// === Edit Produk ===
if (isset($_POST['edit'])) {
    $id = (int)$_POST['product_id'];
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $stock = (int)($_POST['stock'] ?? 0); 
    $description = trim($_POST['description'] ?? NULL);
    $currentImage = trim($_POST['current_image'] ?? '');
    
    $uniqueImageName = $currentImage;

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image']['name'];
        $targetDir = "../public/assets/images/";
        // Tambahkan timestamp atau random string ke nama file agar unik
        $uniqueImageName = time() . '_' . basename($image); 
        $targetFile = $targetDir . $uniqueImageName;
        
        // Pindahkan file baru
        @move_uploaded_file($_FILES['image']['tmp_name'], $targetFile); 
    }

    if ($name !== '' && $price !== '') {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, image = ?, stock = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $price, $uniqueImageName, $stock, $description, $id]);
        $message = "✏️ Produk **" . e($name) . "** berhasil diperbarui!";
    } else {
        $message = "⚠️ Nama dan Harga wajib diisi untuk mengedit produk!";
    }
}


// Ambil semua produk (aktif dan non-aktif, untuk ditampilkan di admin panel)
$products = $pdo->query("SELECT * FROM products ORDER BY is_active DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$adminName = e($_SESSION['username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Produk - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes popup {
            0% { transform: scale(0.8) translateY(100%); opacity: 0; }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }
        .popup { animation: popup 0.4s ease-out; }
        .input-style {
            @apply border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 outline-none transition-colors duration-200 shadow-sm;
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
                <a href="dashboard.php" class="hover:text-indigo-600 transition">Dashboard</a>
                <a href="product.php" class="text-indigo-600 font-semibold border-b-2 border-indigo-600 pb-1">Produk</a>
                <a href="orders.php" class="hover:text-indigo-600 transition">Orders</a>
                <a href="tables.php" class="hover:text-indigo-600 transition">Meja</a>
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
                <a href="dashboard.php" class="block px-4 py-2 hover:bg-gray-100 flex items-center"><i data-feather="grid" class="w-4 h-4 mr-2"></i> Dashboard</a>
                <a href="product.php" class="block px-4 py-2 bg-indigo-50 font-semibold text-indigo-700 flex items-center"><i data-feather="package" class="w-4 h-4 mr-2"></i> Produk</a>
                <a href="orders.php" class="block px-4 py-2 hover:bg-gray-100 flex items-center"><i data-feather="file-text" class="w-4 h-4 mr-2"></i> Orders</a>
                <a href="tables.php" class="block px-4 py-2 hover:bg-gray-100 flex items-center"><i data-feather="grid" class="w-4 h-4 mr-2"></i> Meja</a>
                <hr class="my-1">
                <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600 flex items-center"><i data-feather="log-out" class="w-4 h-4 mr-2"></i> Logout</a>
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="pt-24 pb-12 px-4 md:px-8 max-w-7xl mx-auto">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-8">Manajemen Produk</h2>

        <!-- Form Tambah Produk (Card Style) - Diperbarui dengan Stok & Deskripsi -->
        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-gray-100 mb-10">
            <h3 class="text-xl font-bold mb-5 text-gray-800 flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Tambah Produk Baru</span>
            </h3>
            <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6 items-end">
                <input type="text" name="name" placeholder="Nama Produk" required class="input-style col-span-1">
                
                <div class="col-span-1">
                    <label for="price" class="text-xs font-medium text-gray-500 mb-1 block">Harga (Rp)</label>
                    <input type="number" name="price" placeholder="e.g., 15000000" required class="input-style w-full" id="price">
                </div>

                <div class="col-span-1">
                    <label for="stock_add" class="text-xs font-medium text-gray-500 mb-1 block">Stok</label>
                    <input type="number" name="stock" placeholder="Stok (default 100)" class="input-style w-full" id="stock_add" value="100">
                </div>

                <div class="col-span-1">
                    <label for="description_add" class="text-xs font-medium text-gray-500 mb-1 block">Deskripsi (Opsional)</label>
                    <textarea name="description" placeholder="Deskripsi singkat produk..." rows="1" class="input-style w-full resize-none" id="description_add"></textarea>
                </div>

                <div class="col-span-2">
                    <label for="image" class="text-xs font-medium text-gray-500 mb-1 block">Gambar Produk</label>
                    <!-- Custom file input style -->
                    <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/avif,image/bmp,image/svg+xml,image/tiff" required
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0 file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100
                                file:cursor-pointer rounded-xl border border-gray-300 p-1.5" />
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, GIF, WebP, AVIF, BMP, SVG, TIFF (Max 5MB)</p>
                </div>
                
                <button type="submit" name="add" class="col-span-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-4 py-2.5 font-semibold transition-all shadow-md hover:shadow-lg transform active:scale-[.98]">
                    ➕ Tambah
                </button>
            </form>
        </div>

        <!-- Tabel Produk (Modern Table Style) -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-4 md:p-6 overflow-x-auto">
            <h3 class="text-xl font-bold mb-5 text-gray-800">Daftar Produk</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider rounded-tl-xl">ID</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Gambar</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama Produk</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Harga</th>
                        <th class="py-3 px-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider rounded-tr-xl">Status & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-10 text-lg font-medium bg-white">
                            😴 Belum ada produk yang ditambahkan.
                        </td>
                    </tr>
                <?php else: $i = 1; foreach ($products as $p): 
                    // Menandai baris jika produk tidak aktif
                    $rowClass = $p['is_active'] == 0 ? 'bg-gray-100 text-gray-500 italic' : 'hover:bg-blue-50/50 transition duration-150';
                ?>
                    <tr class="<?= $rowClass ?>">
                        <td class="py-4 px-4 text-sm font-medium text-gray-500"><?= $i++ ?></td>
                        <td class="py-4 px-4">
                            <!-- Placeholder image URL is used here since we can't guarantee existence of uploaded images -->
                            <img src="../public/assets/images/<?= e($p['image']) ?>" 
                                onerror="this.onerror=null; this.src='https://placehold.co/60x60/f3f4f6/374151?text=IMG'"
                                alt="<?= e($p['name']) ?>" class="w-14 h-14 rounded-lg object-cover shadow-md">
                        </td>
                        <td class="py-4 px-4 text-base font-semibold text-gray-800 max-w-xs truncate">
                            <?= e($p['name']) ?>
                            <?php if ($p['is_active'] == 0): ?>
                                <span class="ml-2 text-xs font-bold bg-gray-300 text-gray-700 px-2 py-0.5 rounded-full">ARSIP</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-4 text-base <?= $p['is_active'] == 1 ? 'text-blue-600 font-extrabold' : 'text-gray-500 font-medium' ?>">Rp<?= number_format($p['price'], 0, ',', '.') ?></td>
                        <td class="py-4 px-4 text-center space-x-2 flex items-center justify-center">
                            <!-- Tombol Edit (Baru) -->
                            <button 
                                data-id="<?= $p['id'] ?>" 
                                data-name="<?= e($p['name']) ?>" 
                                data-price="<?= $p['price'] ?>"
                                data-stock="<?= $p['stock'] ?>"
                                data-description="<?= e($p['description'] ?? '') ?>"
                                data-image="<?= e($p['image']) ?>"
                                class="edit-btn text-blue-500 hover:text-blue-700 font-semibold px-3 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 transition duration-150 text-sm">
                                Edit
                            </button>
                            
                            <?php if ($p['is_active'] == 1): ?>
                                <!-- Tombol Hapus diganti menjadi Tombol Arsip -->
                                <button data-id="<?= $p['id'] ?>" data-name="<?= e($p['name']) ?>" data-action="archive" class="delete-btn text-red-500 hover:text-red-700 font-semibold px-3 py-1 rounded-lg bg-red-50 hover:bg-red-100 transition duration-150 text-sm">
                                    Arsip
                                </button>
                            <?php else: ?>
                                <!-- Tombol Aktifkan kembali -->
                                <button data-id="<?= $p['id'] ?>" data-name="<?= e($p['name']) ?>" data-action="activate" class="delete-btn text-green-600 hover:text-green-700 font-semibold px-3 py-1 rounded-lg bg-green-50 hover:bg-green-100 transition duration-150 text-sm">
                                    Aktifkan
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <!-- Edit Product Modal (BARU) -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-[99] items-center justify-center transition-opacity duration-300">
        <div class="bg-white rounded-3xl shadow-2xl p-6 md:p-8 w-11/12 max-w-lg popup">
            <h4 class="text-2xl font-bold text-blue-600 mb-6 flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Produk: <span id="editProductName" class="font-bold"></span></span>
            </h4>

            <form method="POST" enctype="multipart/form-data" id="editProductForm" class="space-y-4">
                <input type="hidden" name="product_id" id="editProductId">
                <input type="hidden" name="current_image" id="editCurrentImage">

                <!-- Current Image Preview -->
                <div class="flex flex-col items-center">
                    <img id="editImagePreview" src="" alt="Gambar Produk Saat Ini" class="w-24 h-24 object-cover rounded-xl shadow-md mb-2 border border-gray-200"
                        onerror="this.onerror=null; this.src='https://placehold.co/96x96/f3f4f6/374151?text=IMG'">
                    <p class="text-xs text-gray-500">Gambar saat ini.</p>
                </div>

                <!-- Nama Produk -->
                <div>
                    <label for="editName" class="text-sm font-medium text-gray-700 mb-1 block">Nama Produk</label>
                    <input type="text" name="name" id="editName" required class="input-style w-full">
                </div>

                <!-- Harga & Stok Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Harga -->
                    <div>
                        <label for="editPrice" class="text-sm font-medium text-gray-700 mb-1 block">Harga (Rp)</label>
                        <input type="number" name="price" id="editPrice" required class="input-style w-full">
                    </div>
                    <!-- Stok -->
                    <div>
                        <label for="editStock" class="text-sm font-medium text-gray-700 mb-1 block">Stok</label>
                        <input type="number" name="stock" id="editStock" class="input-style w-full">
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="editDescription" class="text-sm font-medium text-gray-700 mb-1 block">Deskripsi</label>
                    <textarea name="description" id="editDescription" rows="3" placeholder="Deskripsi singkat produk..." class="input-style w-full resize-none"></textarea>
                </div>

                <!-- Gambar Baru (Optional) -->
                <div>
                    <label for="editImage" class="text-sm font-medium text-gray-700 mb-1 block">Ganti Gambar (Opsional)</label>
                    <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/avif,image/bmp,image/svg+xml,image/tiff" id="editImage"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0 file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100
                                file:cursor-pointer rounded-xl border border-gray-300 p-1.5" />
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, GIF, WebP, AVIF, BMP, SVG, TIFF (Max 5MB)</p>
                </div>
                
                <!-- Tombol Aksi -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelEdit" class="px-4 py-2 text-gray-600 rounded-xl hover:bg-gray-100 transition font-medium">Batal</button>
                    <button type="submit" name="edit" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold shadow-md active:scale-[.98]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Custom Action Confirmation Modal (Digunakan untuk Arsip dan Aktifkan) -->
    <div id="actionModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-[99] items-center justify-center transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-11/12 max-w-sm popup">
            <h4 id="modalTitle" class="text-xl font-bold text-red-600 mb-3 flex items-center space-x-2">
                <!-- Icon and Title updated by JS -->
            </h4>
            <p id="modalMessage" class="text-gray-700 mb-6"></p>
            <div class="flex justify-end space-x-3">
                <button id="cancelAction" class="px-4 py-2 text-gray-600 rounded-xl hover:bg-gray-100 transition font-medium">Batal</button>
                <a href="#" id="confirmAction" class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-semibold shadow-md active:scale-[.98]">
                    Konfirmasi
                </a>
            </div>
        </div>
    </div>

    <!-- Popup kecil untuk pesan (Message) -->
    <?php if ($message): ?>
    <?php 
        $isError = strpos($message, 'GAGAL') !== false;
        $bgColor = $isError ? 'bg-red-600' : (strpos($message, 'arsip') !== false ? 'bg-orange-500' : 'bg-blue-600');
        // Tambahkan warna untuk pesan edit
        $bgColor = strpos($message, 'diperbarui') !== false ? 'bg-green-600' : $bgColor; 
    ?>
    <div id="popup" class="popup fixed bottom-5 right-5 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-semibold z-[100] flex items-center space-x-2 <?= $bgColor ?>">
        <span><?= e($message) ?></span>
        <button onclick="document.getElementById('popup').remove()" class="text-white/80 hover:text-white ml-2">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <script>
        // Auto-hide popup after 5 seconds
        const popup = document.getElementById('popup');
        setTimeout(() => {
            popup.style.opacity = '0';
            popup.style.transform = 'translateY(20px)';
            setTimeout(() => popup.remove(), 500);
        }, 5000); 
    </script>
    <?php endif; ?>

    <script>
        // Dropdown mobile toggle
        const menuBtn = document.getElementById('menuBtn');
        const dropdown = document.getElementById('dropdownMenu');
        let open = false;

        menuBtn.addEventListener('click', () => {
            open = !open;
            if (open) {
                dropdown.classList.remove('hidden');
                setTimeout(() => {
                    dropdown.classList.add('opacity-100');
                    dropdown.classList.remove('opacity-0');
                    dropdown.style.transform = 'translateY(0)';
                }, 10); 
            } else {
                dropdown.classList.add('opacity-0');
                dropdown.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 300); 
            }
        });

        // --- Custom Edit Modal Logic (BARU) ---
        const editBtns = document.querySelectorAll('.edit-btn');
        const editModal = document.getElementById('editModal');
        const cancelEdit = document.getElementById('cancelEdit');
        const editProductName = document.getElementById('editProductName');
        const editProductId = document.getElementById('editProductId');
        const editName = document.getElementById('editName');
        const editPrice = document.getElementById('editPrice');
        const editStock = document.getElementById('editStock');
        const editDescription = document.getElementById('editDescription');
        const editCurrentImage = document.getElementById('editCurrentImage');
        const editImagePreview = document.getElementById('editImagePreview');

        editBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const price = btn.getAttribute('data-price');
                const stock = btn.getAttribute('data-stock');
                const description = btn.getAttribute('data-description');
                const image = btn.getAttribute('data-image');
                
                const imagePath = `../public/assets/images/${image}`;

                // Populate modal fields
                editProductName.textContent = name;
                editProductId.value = id;
                editName.value = name;
                editPrice.value = price;
                editStock.value = stock;
                editDescription.value = description;
                editCurrentImage.value = image;
                
                // Set image preview (with fallback)
                editImagePreview.src = imagePath;
                // Fallback is handled by onerror attribute in HTML for simplicity
                
                // Show modal
                editModal.classList.remove('hidden');
                editModal.classList.add('flex');
            });
        });

        const hideEditModal = () => {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
        };

        cancelEdit.addEventListener('click', (e) => {
            e.preventDefault();
            hideEditModal();
        });

        // Hide edit modal if user clicks outside of it
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                hideEditModal();
            }
        });


        // --- Custom Action Modal Logic (for Archive/Activate) ---
        const actionBtns = document.querySelectorAll('.delete-btn');
        const actionModal = document.getElementById('actionModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const confirmAction = document.getElementById('confirmAction');
        const cancelAction = document.getElementById('cancelAction');

        actionBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const action = btn.getAttribute('data-action');
                let title, message, urlParam, btnClass;

                if (action === 'archive') {
                    title = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg><span>Arsip Produk</span>`;
                    modalTitle.classList.remove('text-green-600');
                    modalTitle.classList.add('text-red-600');
                    message = `Apakah Anda yakin ingin mengarsip produk <strong>${name}</strong>? Produk akan disembunyikan dari menu utama, tetapi riwayat pesanan tetap aman.`;
                    urlParam = `?archive=${id}`;
                    btnClass = 'bg-red-600 hover:bg-red-700';
                    confirmAction.textContent = 'Ya, Arsipkan';
                } else if (action === 'activate') {
                    title = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.27a11.971 11.971 0 00-6.726-3.868 12.001 12.001 0 00-1.732 0 11.971 11.971 0 00-6.726 3.868m16.634 0a12.001 12.001 0 01-1.416 3.993 12.004 12.004 0 01-.482 1.096m-16.197 0a12.004 12.004 0 00-.482-1.096 12.003 12.003 0 01-1.416-3.993m16.634 0a12.001 12.001 0 00-1.416-3.993 12.004 12.004 0 00-.482-1.096" /></svg><span>Aktifkan Produk</span>`;
                    modalTitle.classList.remove('text-red-600');
                    modalTitle.classList.add('text-green-600');
                    message = `Anda yakin ingin mengaktifkan kembali produk <strong>${name}</strong>? Produk akan muncul kembali di menu utama.`;
                    urlParam = `?activate=${id}`;
                    btnClass = 'bg-green-600 hover:bg-green-700';
                    confirmAction.textContent = 'Ya, Aktifkan';
                }

                // Set modal content
                modalTitle.innerHTML = title;
                modalMessage.innerHTML = message;
                confirmAction.href = urlParam;
                confirmAction.className = `px-4 py-2 text-white rounded-xl ${btnClass} transition font-semibold shadow-md active:scale-[.98]`;
                
                // Show modal
                actionModal.classList.remove('hidden');
                actionModal.classList.add('flex');
            });
        });

        const hideModal = () => {
            actionModal.classList.add('hidden');
            actionModal.classList.remove('flex');
        };

        cancelAction.addEventListener('click', (e) => {
            e.preventDefault();
            hideModal();
        });

        // Hide modal if user clicks outside of it
        actionModal.addEventListener('click', (e) => {
            if (e.target === actionModal) {
                hideModal();
            }
        });

        // Mobile menu toggle
        const menuBtn = document.getElementById('menuBtn');
        const dropdownMenu = document.getElementById('dropdownMenu');
        
        if (menuBtn && dropdownMenu) {
            menuBtn.addEventListener('click', () => {
                dropdownMenu.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!menuBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        }

        // Initialize Feather Icons
        feather.replace();
    </script>

</body>
</html>
