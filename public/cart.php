<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Cek apakah sesi sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;

try {
    if ($cart) {
        $ids = array_keys($cart);
        // Buat placeholder untuk query IN
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map produk dengan kuantitas dari keranjang
        foreach($products as $p) {
            $qty = $cart[$p['id']];
            $subtotal = $p['price'] * $qty;
            $items[] = [
                'product' => $p, 
                'qty' => $qty, 
                'subtotal' => $subtotal
            ];
            $total += $subtotal;
        }
    }
} catch (PDOException $e) {
    // Handle error jika koneksi/query gagal
    error_log("Database error in cart.php: " . $e->getMessage());
}

// **PERBAIKAN:** Ambil NAMA meja (table_number) untuk tampilan,
// fallback ke table_id jika table_number tidak ada.
$table_display = $_SESSION['table_number'] ?? ($_SESSION['table_id'] ?? 'Tidak Ditemukan');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Keranjang Belanja - RestoKu</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<style>
    /* Tambahkan font Inter */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body {
        font-family: 'Inter', sans-serif;
    }
    /* Style untuk input kuantitas (jika ada form update nanti) */
    input[type="number"] {
        appearance: none;
        -moz-appearance: textfield;
    }
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
</head>

<body class="bg-gradient-to-br from-indigo-50 to-orange-50 min-h-screen p-4 sm:p-6">
<div class="max-w-4xl mx-auto bg-white p-6 sm:p-8 rounded-2xl shadow-2xl">
    
    <!-- Header -->
    <div class="flex justify-between items-center border-b pb-4 mb-6">
    <h1 class="text-3xl font-extrabold text-indigo-700 flex items-center">
        <i data-feather="shopping-cart" class="w-7 h-7 mr-3 text-orange-500"></i>
        Keranjang Belanja
    </h1>
    <a href="menu.php" class="text-sm text-gray-600 hover:text-indigo-600 transition flex items-center">
        <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i>
        Kembali ke Menu
    </a>
    </div>

    <!-- Informasi Meja -->
    <div class="mb-6 p-3 bg-indigo-50 text-indigo-800 rounded-lg text-sm font-medium shadow-inner">
        <!-- **PERBAIKAN:** Menampilkan nama meja (cth: "MEJA 1") bukan ID ("1") -->
        Anda sedang memesan untuk Meja: <strong class="text-indigo-900"><?= e($table_display) ?></strong>
    </div>

    <!-- Konten Keranjang -->
    <?php if (empty($items)): ?>
    <div class="text-center p-10 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50">
        <i data-feather="package" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
        <p class="text-xl text-gray-600 font-semibold">Keranjang Anda kosong.</p>
        <a href="menu.php" class="mt-4 inline-block px-6 py-2 bg-orange-500 text-white font-semibold rounded-full hover:bg-orange-600 transition shadow-md transform hover:scale-105">
            Mulai Pesan
        </a>
    </div>
    <?php else: ?>
    
    <!-- Tabel Keranjang (Responsive via styling) -->
    <div class="overflow-x-auto shadow-lg rounded-xl border border-gray-100 mb-6">
        <table class="w-full text-left bg-white">
        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
            <th class="p-4 rounded-tl-xl">Produk</th>
            <th class="p-4 text-center w-40">Qty</th>
            <th class="p-4 text-right w-32">Subtotal</th>
            <th class="p-4 text-center rounded-tr-xl w-20">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $it): ?>
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="p-4 font-medium text-gray-900">
                    <?= htmlspecialchars($it['product']['name']) ?>
                    <p class="text-xs text-gray-500 mt-1"><?= currency($it['product']['price']) ?> per item</p>
                </td>
                <td class="p-4">
                    <!-- Tombol + / - untuk Update Quantity -->
                    <div class="flex items-center justify-center space-x-2">
                        <!-- Tombol Minus -->
                        <a href="update_cart.php?action=decrease&id=<?= $it['product']['id'] ?>" 
                           class="w-8 h-8 flex items-center justify-center bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition transform hover:scale-110 active:scale-95"
                           title="Kurangi">
                            <i data-feather="minus" class="w-4 h-4"></i>
                        </a>
                        
                        <!-- Display Quantity -->
                        <span class="w-12 text-center font-bold text-lg text-gray-800"><?= $it['qty'] ?></span>
                        
                        <!-- Tombol Plus -->
                        <a href="update_cart.php?action=increase&id=<?= $it['product']['id'] ?>" 
                           class="w-8 h-8 flex items-center justify-center bg-green-100 hover:bg-green-200 text-green-600 rounded-lg transition transform hover:scale-110 active:scale-95"
                           title="Tambah">
                            <i data-feather="plus" class="w-4 h-4"></i>
                        </a>
                    </div>
                </td>
                <td class="p-4 text-right font-semibold text-indigo-600"><?= currency($it['subtotal']) ?></td>
                <td class="p-4 text-center">
                    <!-- Tombol Hapus Item -->
                    <a href="update_cart.php?action=delete&id=<?= $it['product']['id'] ?>" 
                       class="inline-flex items-center justify-center w-9 h-9 bg-red-500 hover:bg-red-600 text-white rounded-lg transition transform hover:scale-110 active:scale-95"
                       title="Hapus item"
                       onclick="return confirm('Yakin ingin menghapus <?= htmlspecialchars($it['product']['name']) ?> dari keranjang?')">
                        <i data-feather="trash-2" class="w-4 h-4"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>

    <!-- Total dan Checkout -->
    <div class="flex flex-col sm:flex-row justify-between items-center p-5 bg-indigo-100 rounded-xl shadow-inner border-l-4 border-indigo-500">
        <div class="text-lg font-bold text-gray-800 mb-4 sm:mb-0">
            Total Pembayaran:
        </div>
        <div class="text-4xl font-extrabold text-indigo-700">
            <?= currency($total) ?>
        </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-3">
        <!-- Tombol Kosongkan Keranjang -->
        <a href="update_cart.php?action=clear" 
           class="flex items-center space-x-2 px-5 py-2 bg-red-500 text-white font-semibold rounded-xl shadow-lg hover:bg-red-600 transition transform hover:scale-105"
           onclick="return confirm('Yakin ingin mengosongkan seluruh keranjang?')">
            <i data-feather="trash-2" class="w-5 h-5"></i>
            <span>Kosongkan Keranjang</span>
        </a>
        
        <!-- Tombol Checkout -->
        <form action="checkout.php" method="get">
        <button type="submit" class="flex items-center space-x-2 px-6 py-3 bg-green-600 text-white font-bold rounded-xl shadow-lg hover:bg-green-700 transition transform hover:scale-105 text-lg">
            <i data-feather="credit-card" class="w-6 h-6"></i>
            <span>Lanjutkan Checkout</span>
        </button>
        </form>
    </div>

    <?php endif; ?>
</div>
<script>feather.replace();</script>
</body>
</html>
