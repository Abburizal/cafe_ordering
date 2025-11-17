<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// 1. Pastikan session aktif (config.php mungkin sudah memanggil session_start())
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Inisialisasi variabel error
$error_message = '';

// 3. Penanganan Input Meja (Standarisasi Sesi)
//    Semua metode (QR, link, tes) sekarang akan mengambil data dari DB
//    dan mengatur 'table_id' (ID numerik) dan 'table_number' (Nama string)
try {
    // Opsi 1: Dipindai dari QR Code (cth: index.php?code=TBL-001)
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $stmt = $pdo->prepare("SELECT id, name FROM tables WHERE code = ?");
        $stmt->execute([$code]);
        $table = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($table) {
            $_SESSION['table_id'] = (int)$table['id'];
            $_SESSION['table_number'] = $table['name']; // Cth: "MEJA 1"
            header('Location: menu.php');
            exit;
        } else {
            $error_message = "Kode meja tidak valid. Silakan scan ulang atau pilih meja dari daftar.";
        }
    }

    // Opsi 2: Simulasi/Testing via ID (cth: index.php?table=1)
    if (isset($_GET['table'])) {
        $table_id = (int)$_GET['table'];
        $stmt = $pdo->prepare("SELECT id, name FROM tables WHERE id = ?");
        $stmt->execute([$table_id]);
        $table = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($table) {
            $_SESSION['table_id'] = (int)$table['id'];
            $_SESSION['table_number'] = $table['name'];
            header('Location: menu.php');
            exit;
        } else {
            $error_message = "Meja dengan ID '$table_id' tidak ditemukan.";
        }
    }
    
    // Opsi 3: Patch lama via Nama (cth: index.php?table_number=MEJA 1)
    if (isset($_GET['table_number'])) {
        $table_name = htmlspecialchars($_GET['table_number'], ENT_QUOTES, 'UTF-8');
        $stmt = $pdo->prepare("SELECT id, name FROM tables WHERE name = ?");
        $stmt->execute([$table_name]);
        $table = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($table) {
            $_SESSION['table_id'] = (int)$table['id'];
            $_SESSION['table_number'] = $table['name'];
            header('Location: menu.php');
            exit;
        } else {
             $error_message = "Meja dengan nama '$table_name' tidak ditemukan.";
        }
    }

} catch (PDOException $e) {
    $error_message = "Kesalahan database. Tidak dapat memverifikasi meja.";
    error_log("PDOException in index.php (Handling GET): " . $e->getMessage());
}

// 4. Pengambilan Data Meja (Dinamis dari DB)
//    Ini menggantikan array $tables yang di-hardcode
$tables = [];
$colors = ['orange', 'teal', 'purple', 'red', 'blue', 'green', 'pink', 'indigo', 'yellow', 'cyan', 'fuchsia'];

try {
    // Asumsi tabel 'tables' memiliki kolom 'id', 'code', dan 'name' (untuk tampilan "MEJA 1")
    $stmt = $pdo->query("SELECT id, code, name FROM tables ORDER BY id ASC");
    $db_tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($db_tables as $index => $table) {
        $tables[] = [
            'code' => $table['code'],
            'display' => $table['name'], // 'name' dari DB digunakan sebagai 'display'
            'color' => $colors[$index % count($colors)] // Terapkan warna secara berurutan
        ];
    }

    if (empty($tables) && empty($error_message)) {
        // Hanya tampilkan error ini jika tidak ada error lain
        $error_message = "Tidak ada meja yang terdaftar di sistem.";
    }

} catch (PDOException $e) {
    $error_message = "Gagal memuat daftar meja dari database.";
    error_log("PDOException in index.php (Fetching tables): " . $e->getMessage());
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Meja - RestoKu</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        /* Tambahkan font Inter */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: transparent; 
        }
        .animated-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
        }
        .animated-card:hover {
            transform: translateY(-12px) scale(1.05);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 2px rgba(255, 255, 255, 0.3);
        }
        .card-image {
            transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .animated-card:hover .card-image {
            transform: scale(1.15) rotate(-5deg);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animated-card:hover {
            animation: float 3s ease-in-out infinite;
        }
        .floating-navbar {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .fixed-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #e0f2fe, #f0e6ff, #fef2f4); 
            animation: subtle-shift 60s infinite alternate;
        }
        @keyframes subtle-shift {
            0% { background-position: 0% 0%; }
            100% { background-position: 100% 100%; }
        }
    </style>
</head>

<body class="min-h-screen flex flex-col text-gray-800 pt-20"> 
<div class="fixed-bg"></div>

<nav class="floating-navbar fixed top-0 left-0 right-0 z-20 shadow-xl rounded-b-xl px-4 sm:px-6 lg:px-8 py-3 transition duration-300">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        
        <a href="index.php" class="text-2xl font-extrabold text-indigo-700 flex items-center group">
            <i data-feather="tablet" class="w-6 h-6 mr-2 text-orange-500 transition-transform duration-300 group-hover:rotate-12"></i>
            RestoKu
        </a>

        <button onclick="alert('Jika Anda mengalami masalah, silakan hubungi pelayan terdekat. Terima kasih!')" 
                class="flex items-center space-x-2 px-3 py-1.5 sm:px-4 sm:py-2 bg-pink-500 text-white font-semibold rounded-full shadow-md hover:bg-pink-600 transition transform hover:scale-105 text-sm">
            <i data-feather="help-circle" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            <span class="hidden sm:inline">Bantuan</span>
        </button>
        
    </div>
</nav>

<main class="flex-1 flex flex-col items-center p-4 pb-8 relative z-10"> 

    <?php if (!empty($error_message)): ?>
    <div class="w-full max-w-lg mx-auto p-4 mb-6 text-center bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-lg" role="alert">
        <div class="flex items-center justify-center">
            <i data-feather="alert-triangle" class="w-6 h-6 mr-2"></i>
            <span class="font-semibold"><?= htmlspecialchars($error_message) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="text-center mb-10 pt-10">
        <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">
            <i data-feather="grid" class="inline-block w-8 h-8 mr-2 text-indigo-500"></i>
            Pilih Meja Anda
        </h1>
        <p class="mt-3 text-xl text-gray-600 max-w-md mx-auto">
            Ketuk kartu meja di bawah atau scan QR Code di meja Anda untuk mulai memesan.
        </p>
        
        <!-- Scan QR Code Button -->
        <div class="mt-6">
            <a href="scan.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-full shadow-lg transform transition hover:scale-105">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                Scan QR Code Meja
            </a>
        </div>
    </div>

    <div class="card-container flex flex-wrap items-stretch justify-center max-w-6xl w-full">

        <?php foreach ($tables as $table) : ?>
        <div class="flex-shrink-0 m-4 relative overflow-hidden bg-gradient-to-br from-<?= $table['color'] ?>-400 to-<?= $table['color'] ?>-600 rounded-3xl max-w-xs shadow-2xl animated-card w-full sm:w-72 border-2 border-white/20">
            <a href="index.php?code=<?= htmlspecialchars($table['code']) ?>" class="block h-full">
                <!-- Decorative Background -->
                <svg class="absolute bottom-0 left-0 mb-8 opacity-10" viewBox="0 0 375 283" fill="none"
                    style="transform: scale(1.5);">
                    <rect x="159.52" y="175" width="152" height="152" rx="8" transform="rotate(-45 159.52 175)" fill="white" />
                    <rect y="107.48" width="152" height="152" rx="8" transform="rotate(-45 0 107.48)" fill="white" />
                </svg>
                
                <!-- Table Image -->
                <div class="relative pt-8 px-8 flex items-center justify-center">
                    <div class="block absolute w-48 h-48 bottom-0 left-0 -mb-24 ml-3"
                        style="background: radial-gradient(black, transparent 60%); transform: rotate3d(0, 0, 1, 20deg) scale3d(1, 0.6, 1); opacity: 0.15;">
                    </div>
                    <img class="relative w-36 card-image drop-shadow-2xl" src="assets/meja.png" alt="Gambar Meja Restoran">
                </div>
                
                <!-- Table Info Section -->
                <div class="relative text-white px-6 pb-8 mt-4">
                    <!-- Table Number Badge - More Prominent -->
                    <div class="flex justify-center mb-4">
                        <div class="bg-white/95 backdrop-blur-sm rounded-2xl px-6 py-3 shadow-2xl border-2 border-white transform -rotate-2 hover:rotate-0 transition-transform">
                            <div class="text-center">
                                <p class="text-xs font-semibold text-<?= $table['color'] ?>-500 uppercase tracking-wider mb-1">Table</p>
                                <p class="text-2xl font-black text-<?= $table['color'] ?>-700 leading-none">
                                    <?= htmlspecialchars($table['display']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Call to Action -->
                    <div class="text-center">
                        <p class="text-white/90 font-semibold text-sm mb-2">Tap untuk mulai pesan</p>
                        <div class="flex items-center justify-center space-x-2 text-white/80 text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            <span>Lihat Menu</span>
                        </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
        
        <div class="p-10 w-full text-center text-gray-500 text-sm mt-10">created by satriyo nugroho</div>
        <div class="h-96 w-full"></div> 

    </div>
</main>

<script>
    feather.replace();
    
    // Fungsi alert kustom Anda (tidak diubah)
    window.alert = function(message) {
        console.log("ALERT BLOCKED: ", message);
        const nav = document.querySelector('.floating-navbar');
        const msgBox = document.createElement('div');
        msgBox.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-xl shadow-2xl z-50 border border-gray-200 text-gray-800 text-center transition-all duration-300 scale-100 opacity-100';
        msgBox.innerHTML = `
            <div class="flex justify-center mb-3">
                <i data-feather="info" class="w-6 h-6 text-indigo-500"></i>
            </div>
            <p class="mb-4">${message}</p>
            <button onclick="this.parentNode.remove()" class="bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 transition">Tutup</button>
        `;
        document.body.appendChild(msgBox);
        feather.replace(); // Memastikan ikon di dalam modal muncul
    };
</script>
</body>
</html>