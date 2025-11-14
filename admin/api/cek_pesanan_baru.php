<?php
// /admin/api/cek_pesanan_baru.php

// Panggil config dan middleware SEBELUM output apapun
require_once '../../config/config.php';
require_once '../../app/middleware.php';

// Lindungi endpoint, hanya admin yang bisa akses
require_admin(); 

// Set header setelah logic
header('Content-Type: application/json');

try {
    // Mode initialization - return latest order ID
    if (isset($_GET['init']) && $_GET['init'] == '1') {
        $stmt = $pdo->query("SELECT MAX(id) AS latest_id FROM orders");
        $latest_id = $stmt->fetchColumn();
        
        echo json_encode([
            'latest_order_id' => (int)$latest_id,
            'status' => 'initialized'
        ]);
        exit;
    }
    
    // Mode check new orders - dengan parameter last_id
    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    
    // Cari order baru setelah last_id
    $stmt = $pdo->prepare("
        SELECT o.id, o.order_code, o.total, o.created_at, o.payment_method, o.status,
               COALESCE(o.table_number, t.name, 'N/A') AS table_name
        FROM orders o
        LEFT JOIN tables t ON o.table_id = t.id
        WHERE o.id > ? AND o.status = 'pending'
        ORDER BY o.id ASC
    ");
    $stmt->execute([$last_id]);
    $new_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return response
    echo json_encode([
        'ada_pesanan_baru' => count($new_orders) > 0,
        'pesanan_baru' => $new_orders,
        'jumlah' => count($new_orders),
        'last_checked_id' => $last_id
    ]);

} catch (PDOException $e) {
    // Tangani error jika query gagal
    echo json_encode([
        'ada_pesanan_baru' => false,
        'pesanan_baru' => [],
        'error' => $e->getMessage()
    ]);
}
?>