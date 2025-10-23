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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .animated-card:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .card-image {
            transition: transform 0.5s ease;
        }
        .animated-card:hover .card-image {
            transform: scale(1.1) rotate(-3deg);
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
    </div>

    <div class="card-container flex flex-wrap items-stretch justify-center max-w-6xl w-full">

        <?php foreach ($tables as $table) : ?>
        <div class="flex-shrink-0 m-4 relative overflow-hidden bg-<?= $table['color'] ?>-500 rounded-2xl max-w-xs shadow-lg animated-card w-full sm:w-64">
            <a href="index.php?code=<?= htmlspecialchars($table['code']) ?>" class="block h-full">
                <svg class="absolute bottom-0 left-0 mb-8" viewBox="0 0 375 283" fill="none"
                    style="transform: scale(1.5); opacity: 0.1;">
                    <rect x="159.52" y="175" width="152" height="152" rx="8" transform="rotate(-45 159.52 175)" fill="white" />
                    <rect y="107.48" width="152" height="152" rx="8" transform="rotate(-45 0 107.48)" fill="white" />
                </svg>
                <div class="relative pt-10 px-10 flex items-center justify-center">
                    <div class="block absolute w-48 h-48 bottom-0 left-0 -mb-24 ml-3"
                        style="background: radial-gradient(black, transparent 60%); transform: rotate3d(0, 0, 1, 20deg) scale3d(1, 0.6, 1); opacity: 0.2;">
                    </div>
                    <img class="relative w-40 card-image" src="assets/meja.png" alt="Gambar Meja Restoran">
                </div>
                <div class="relative text-white px-6 pb-6 mt-6">
                    <div class="flex justify-between items-end">
                        <span class="block font-bold text-2xl">Menu Untuk</span>
                        <span class="block bg-white rounded-full text-<?= $table['color'] ?>-600 text-sm font-extrabold px-4 py-2 leading-none flex items-center shadow-lg border-2 border-white">
                            <?= htmlspecialchars($table['display']) ?>
                        </span>
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