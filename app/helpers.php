<?php
// app/helpers.php

// Pastikan file config sudah di-include sebelum helper ini
if (!isset($pdo)) {
    die("Config belum di-load. Pastikan include config.php sebelum helpers.php");
}

/**
 * Escape HTML untuk keamanan output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect ke halaman tertentu
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Format rupiah (contoh: Rp 12.000)
 */
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Ambil role user yang sedang login
 */
function userRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Cek apakah user adalah admin
 */
function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

/**
 * Generate kode order unik (contoh: ORD-20251017-XYZ123)
 */
function generateOrderCode() {
    $rand = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    return 'ORD-' . date('Ymd') . '-' . $rand;
}

/**
 * Hitung total harga keranjang
 */
function cartTotal($pdo, $cart) {
    $total = 0;
    if (empty($cart)) return 0;

    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $qty = $cart[$p['id']] ?? 0;
        $total += ($p['price'] * $qty);
    }

    return $total;
}

/**
 * Simpan order baru ke database
 */
function createOrder($pdo, $user_id, $table_id, $cart, $payment_method, $total, $midtrans_id = null) {
    $order_code = generateOrderCode();

    // Insert ke tabel orders
    $stmt = $pdo->prepare("INSERT INTO orders (order_code, user_id, table_id, total, payment_method, midtrans_id, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$order_code, $user_id, $table_id, $total, $payment_method, $midtrans_id]);
    $order_id = $pdo->lastInsertId();

    // Insert detail item
    $placeholders = implode(',', array_fill(0, count($cart), '(?, ?, ?, ?)'));
    $values = [];
    $stmt2 = $pdo->prepare("SELECT id, price FROM products WHERE id IN (" . implode(',', array_fill(0, count($cart), '?')) . ")");
    $stmt2->execute(array_keys($cart));
    $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $qty = $cart[$p['id']];
        $values[] = $order_id;
        $values[] = $p['id'];
        $values[] = $qty;
        $values[] = $p['price'];
    }

    $sql = "INSERT INTO order_items (order_id, product_id, qty, price) VALUES $placeholders";
    $stmt3 = $pdo->prepare($sql);
    $stmt3->execute($values);

    // **PENAMBAHAN BARIS UNTUK PUSH NOTIFICATION**
    // Panggil notifikasi setelah order berhasil dibuat
    try {
        // Ambil nama meja untuk notifikasi
        $table_name = 'Take Away'; // Default
        if ($table_id) {
            $stmt_table = $pdo->prepare("SELECT name FROM tables WHERE id = ?");
            $stmt_table->execute([$table_id]);
            $table_name = $stmt_table->fetchColumn() ?? 'Meja ' . $table_id;
        }

        // Kirim notifikasi
        send_admin_notification([
            'order_code'   => $order_code,
            'table_number' => $table_name,
            'total'        => $total
        ], $order_code);

    } catch (Exception $e) {
        // Jangan hentikan proses order jika notifikasi gagal
        // Cukup catat errornya
        error_log('Gagal mengirim push notification: ' . $e->getMessage());
    }
    // **AKHIR DARI PENAMBAHAN**

    return $order_code;
}

/**
 * Ambil daftar pesanan (untuk admin)
 */
function getOrders($pdo, $status = null) {
    $sql = "SELECT o.*, t.name AS table_name, u.name AS customer_name 
            FROM orders o 
            LEFT JOIN tables t ON o.table_id = t.id 
            LEFT JOIN users u ON o.user_id = u.id";
    if ($status) {
        $sql .= " WHERE o.status = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Update status pesanan
 */
function updateOrderStatus($pdo, $order_code, $status) {
    $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE order_code=?");
    return $stmt->execute([$status, $order_code]);
}

/**
 * Hitung total pendapatan hari ini
 */
function getTodayIncome($pdo) {
    $stmt = $pdo->query("SELECT SUM(total) FROM orders WHERE DATE(created_at)=CURDATE() AND status IN ('processing','done')");
    return $stmt->fetchColumn() ?: 0;
}

/**
 * Hitung total pendapatan bulanan
 */
function getMonthlyIncome($pdo) {
    $stmt = $pdo->query("SELECT YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as total 
                         FROM orders WHERE status IN ('processing','done') 
                         GROUP BY YEAR(created_at), MONTH(created_at)
                         ORDER BY year DESC, month DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Format Rupiah
function currency($number) {
    if (!is_numeric($number)) return 'Rp 0';
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Redirect helper

// Debug helper (optional)
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Mengirim notifikasi Push Notification ke Admin via FCM.
 * @param array $data Data payload notifikasi.
 * @param string $orderId ID Pesanan untuk konten notifikasi.
 */
function send_admin_notification($data, $orderId) {
    // Ganti dengan Kunci Server FCM Anda
    $fcm_server_key = 'YOUR_FIREBASE_SERVER_KEY_HERE';

    if (empty($fcm_server_key) || $fcm_server_key === 'YOUR_FIREBASE_SERVER_KEY_HERE') {
        error_log('FCM Server Key is not configured. Notification skipped.');
        return;
    }

    // Gunakan koneksi DB global jika tersedia
    global $pdo;
    if (!isset($pdo) || !$pdo) {
        error_log('PDO connection not available. Notification skipped.');
        return;
    }

    // Ambil semua token admin dari database
    // Sesuaikan nama tabel/kolom jika struktur DB Anda berbeda.
    try {
        $stmt = $pdo->query("SELECT fcm_token FROM admin_tokens WHERE fcm_token IS NOT NULL AND fcm_token <> ''");
        $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log('Failed to fetch admin tokens: ' . $e->getMessage());
        return;
    }

    if (empty($tokens)) {
        error_log('No FCM tokens registered for admin.');
        return;
    }

    $url = 'https://fcm.googleapis.com/fcm/send';

    $notification = [
        'title' => 'PESANAN BARU! 🔔',
        'body'  => isset($data['table_number']) ? "Pesanan #{$orderId} dari Meja: {$data['table_number']} telah dibuat." : "Pesanan #{$orderId} telah dibuat.",
        'icon'  => '/assets/logo.png'
    ];

    $fields = [
        'registration_ids' => $tokens,
        'notification' => $notification,
        'data' => $data,
        'priority' => 'high'
    ];

    $headers = [
        'Authorization: key=' . $fcm_server_key,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('cURL Error (FCM): ' . curl_error($ch));
    } else {
        // Opsional: log hasil respon FCM untuk debugging
        error_log('FCM response: ' . $result);
    }
    curl_close($ch);
}

// ============================================================================
// CSRF PROTECTION FUNCTIONS
// ============================================================================

/**
 * Generate CSRF token and store in session
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field (for forms)
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Get CSRF token meta tag (for AJAX)
 */
function csrf_meta() {
    $token = generate_csrf_token();
    return '<meta name="csrf-token" content="' . $token . '">';
}

// ============================================================================
// ERROR HANDLING & LOGGING
// ============================================================================

/**
 * Log error to file with context
 */
function log_error($message, $context = []) {
    $log_dir = __DIR__ . '/../logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $_SESSION['username'] ?? 'guest';
    $url = $_SERVER['REQUEST_URI'] ?? '';
    
    $log_entry = sprintf(
        "[%s] [%s] [%s] [%s] %s %s\n",
        $timestamp,
        $ip,
        $user,
        $url,
        $message,
        !empty($context) ? json_encode($context) : ''
    );
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

/**
 * Get user-friendly error messages
 */
function get_error_message($error_code) {
    $messages = [
        'db_error' => 'Maaf, terjadi gangguan sistem. Silakan coba lagi.',
        'invalid_input' => 'Data yang Anda masukkan tidak valid.',
        'not_found' => 'Data tidak ditemukan.',
        'unauthorized' => 'Anda tidak memiliki akses ke halaman ini.',
        'forbidden' => 'Akses ditolak.',
        'server_error' => 'Terjadi kesalahan server. Silakan hubungi administrator.',
        'invalid_csrf' => 'Token keamanan tidak valid. Silakan refresh halaman.',
        'session_expired' => 'Sesi Anda telah berakhir. Silakan login kembali.',
        'file_too_large' => 'Ukuran file terlalu besar. Maksimal 2MB.',
        'invalid_file_type' => 'Tipe file tidak didukung.',
        'upload_failed' => 'Upload file gagal. Silakan coba lagi.'
    ];
    
    return $messages[$error_code] ?? 'Terjadi kesalahan. Silakan coba lagi.';
}

// ============================================================================
// VALIDATION HELPERS
// ============================================================================

/**
 * Validate image upload
 * Supports: JPG, JPEG, PNG, GIF, WebP, AVIF, BMP, SVG, TIFF
 */
function validate_image_upload($file, $max_size = 5242880) {
    $errors = [];
    
    // Check if file is uploaded
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Tidak ada file yang diupload.';
        return $errors;
    }
    
    // Check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = get_error_message('upload_failed');
        return $errors;
    }
    
    // Check file size (default 5MB, lebih besar dari sebelumnya 2MB)
    if ($file['size'] > $max_size) {
        $size_mb = round($max_size / 1024 / 1024, 1);
        $errors[] = "Ukuran file terlalu besar. Maksimal {$size_mb}MB.";
    }
    
    // Check MIME type - Support semua format gambar modern
    $allowed_types = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif',
        'image/webp',
        'image/avif',
        'image/bmp',
        'image/x-ms-bmp',
        'image/svg+xml',
        'image/tiff',
        'image/x-icon',
        'image/vnd.microsoft.icon'
    ];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowed_types)) {
        $errors[] = 'Tipe file tidak didukung. Format yang didukung: JPG, PNG, GIF, WebP, AVIF, BMP, SVG, TIFF, ICO';
    }
    
    // Check file extension
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'svg', 'tiff', 'tif', 'ico'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_extensions)) {
        $errors[] = 'Ekstensi file tidak valid. Gunakan: ' . implode(', ', $allowed_extensions);
    }
    
    return $errors;
}

/**
 * Sanitize filename
 */
function sanitize_filename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    return $filename;
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Generate unique filename
 */
function generate_unique_filename($original_filename) {
    $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
    $basename = pathinfo($original_filename, PATHINFO_FILENAME);
    $basename = sanitize_filename($basename);
    return $basename . '_' . time() . '_' . uniqid() . '.' . $ext;
}

/**
 * Format date to Indonesian
 */
function format_date_id($date, $format = 'long') {
    $timestamp = strtotime($date);
    
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $days = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    
    if ($format === 'long') {
        $day = $days[date('l', $timestamp)];
        $date_num = date('d', $timestamp);
        $month = $months[date('n', $timestamp)];
        $year = date('Y', $timestamp);
        $time = date('H:i', $timestamp);
        
        return "$day, $date_num $month $year - $time";
    } else {
        $date_num = date('d', $timestamp);
        $month = $months[date('n', $timestamp)];
        $year = date('Y', $timestamp);
        
        return "$date_num $month $year";
    }
}

/**
 * Time ago helper (e.g., "5 menit yang lalu")
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' menit yang lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam yang lalu';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' hari yang lalu';
    } else {
        return format_date_id($datetime, 'short');
    }
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}
