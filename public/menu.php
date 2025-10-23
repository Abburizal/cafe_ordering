<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Cek apakah sesi sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pastikan nomor meja ada di sesi (kunci 'table_number')
if (!isset($_SESSION['table_number'])) {
    // Pengamanan: Jika langsung akses menu tanpa scan QR
    header('Location: index.php?error=no_table');
    exit;
}

// Dukungan fallback jika sebelumnya menggunakan 'table_id'
$table_id = $_SESSION['table_number'] ?? $_SESSION['table_id'] ?? null;

try {
    // Asumsi $pdo sudah tersedia dari config.php
    // Ambil semua produk
    $stmt = $pdo->query("SELECT * FROM products ORDER BY name");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error jika koneksi/query gagal
    $products = [];
    error_log("Database error in menu.php: " . $e->getMessage());
}


$table_id = $_SESSION['table_id'] ?? null;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Makanan & Minuman - RestoKu</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <style>
    /* Tambahkan font Inter */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body {
        font-family: 'Inter', sans-serif;
    }
    /* Kelas animasi untuk kartu produk */
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-4px); /* Sedikit naik saat hover */
        box-shadow: 0 15px 25px -5px rgba(0, 0, 0, 0.1), 0 5px 10px -5px rgba(0, 0, 0, 0.04); /* Bayangan lebih besar */
        border-color: #4f46e5; /* Border biru saat hover */
    }
    /* Style untuk input kuantitas */
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
  <div class="max-w-6xl mx-auto">
    
    <!-- Header dan Keranjang -->
    <div class="flex justify-between items-center mb-8 bg-white p-4 rounded-xl shadow-lg sticky top-0 z-10">
      <h1 class="text-3xl font-extrabold text-indigo-700 flex items-center">
        <i data-feather="coffee" class="w-7 h-7 mr-3 text-orange-500"></i>
        Daftar Menu
      </h1>
      <a href="cart.php" class="flex items-center space-x-2 px-4 py-2 bg-orange-500 text-white font-semibold rounded-full shadow-lg hover:bg-orange-600 transition transform hover:scale-105">
        <i data-feather="shopping-cart" class="w-5 h-5"></i>
        <span class="hidden sm:inline">Keranjang</span>
      </a>
    </div>

    <!-- Informasi Meja -->
    <?php if (!$table_id): ?>
      <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-lg shadow-md flex items-center">
        <i data-feather="alert-triangle" class="w-5 h-5 mr-3"></i>
        <span>Nomor meja tidak terdeteksi. Silakan kembali dan scan QR meja terlebih dahulu.</span>
      </div>
    <?php else: ?>
      <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-800 rounded-lg shadow-md flex items-center">
        <i data-feather="check-circle" class="w-5 h-5 mr-3"></i>
        <span>Meja Anda: <strong class="text-green-900"><?= e($table_id) ?></strong>. Selamat menikmati!</span>
      </div>
    <?php endif; ?>

    <!-- Grid Produk -->
    <?php if (empty($products)): ?>
      <div class="text-center p-10 bg-white rounded-xl shadow-lg">
        <i data-feather="frown" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
        <p class="text-xl text-gray-600">Saat ini menu sedang kosong.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach($products as $p): ?>
          <!-- PERUBAHAN: Menambahkan class 'group' untuk mengaktifkan group-hover pada gambar -->
          <div class="group bg-white rounded-2xl shadow-xl product-card overflow-hidden border border-gray-100">
            
            <!-- Gambar Produk -->
            <?php 
            $image_path = "assets/images/" . ($p['image'] ?? '');
            $image_url = file_exists(__DIR__ . "/" . $image_path) ? $image_path : 'https://placehold.co/600x320/eeeeee/333333?text=NO+IMAGE';
            ?>
            <!-- PERUBAHAN: Mengubah tinggi gambar menjadi h-36 untuk mobile dan sm:h-40 untuk desktop -->
            <img src="<?= e($image_url) ?>" 
                 alt="<?= e($p['name']) ?>" 
                 class="w-full h-36 sm:h-40 object-cover transition duration-500 group-hover:scale-110">

            <!-- Info Produk -->
            <div class="p-5">
              <h3 class="font-bold text-xl text-gray-900 mb-1 truncate"><?= e($p['name']) ?></h3>
              <p class="text-sm text-gray-500 min-h-[3rem] line-clamp-2"><?= e($p['description'] ?? 'Deskripsi produk belum tersedia.') ?></p>
              
              <div class="mt-4 text-indigo-600 font-extrabold text-2xl"><?= currency($p['price']) ?></div>
              
              <form action="add_cart.php" method="post" class="mt-5 flex items-stretch gap-3">
                <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
                <input type="number" name="qty" value="1" min="1" 
                        class="w-16 text-center text-lg px-2 py-2 border-2 border-gray-300 rounded-xl focus:border-indigo-500 transition shadow-inner"
                        aria-label="Kuantitas">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl transition transform hover:scale-[1.02] shadow-md flex items-center justify-center">
                  <i data-feather="plus" class="w-5 h-5 mr-1"></i>
                  Tambah
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
<script>feather.replace();</script>
</body>
</html>
