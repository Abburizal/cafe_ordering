<?php
/**
 * Real-time API untuk Admin Dashboard
 * Mengembalikan data pesanan yang diperbarui secara real-time
 * Endpoint: admin/api/get_orders_realtime.php?status=semua|pending|processing|done|cancelled
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/config.php';
require_once '../../app/helpers.php';

header('Content-Type: application/json; charset=utf-8');

// Validasi session admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    http_response_code(401);
    exit;
}

try {
    $filter = $_GET['status'] ?? 'semua';
    
    // Query berdasarkan filter
    $sql = "SELECT o.id, o.order_code, o.status, o.total, o.payment_method, 
                   o.table_number, o.table_id, o.created_at, o.updated_at,
                   t.name AS table_name
            FROM orders o 
            LEFT JOIN tables t ON o.table_id = t.id";
    
    $params = [];
    
    if ($filter !== 'semua') {
        $sql .= " WHERE o.status = ?";
        $params[] = $filter;
    }
    
    $sql .= " ORDER BY o.created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil item untuk setiap pesanan
    $order_ids = array_column($orders, 'id');
    $order_items = [];
    
    if (!empty($order_ids)) {
        $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
        $stmt_items = $pdo->prepare("
            SELECT oi.order_id, oi.qty, oi.price, p.name AS product_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id IN ($placeholders)
        ");
        $stmt_items->execute($order_ids);
        $raw_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($raw_items as $item) {
            if (!isset($order_items[$item['order_id']])) {
                $order_items[$item['order_id']] = [];
            }
            $order_items[$item['order_id']][] = $item;
        }
    }
    
    // Gabungkan items ke orders
    foreach ($orders as $key => $order) {
        $orders[$key]['items'] = $order_items[$order['id']] ?? [];
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'timestamp' => date('Y-m-d H:i:s'),
        'total' => count($orders)
    ]);
    
} catch (PDOException $e) {
    error_log("Real-time API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    http_response_code(500);
}
