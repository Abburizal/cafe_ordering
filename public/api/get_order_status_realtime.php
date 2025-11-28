<?php
/**
 * Real-time API untuk Customer - Status Pesanan
 * Mengembalikan status terbaru dari pesanan customer
 * Endpoint: public/api/get_order_status_realtime.php?order_id=123
 */

require_once '../../config/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Order ID required'
        ]);
        http_response_code(400);
        exit;
    }
    
    $order_id = (int)$_GET['order_id'];
    
    // Ambil data pesanan
    $stmt = $pdo->prepare("
        SELECT o.id, o.order_code, o.status, o.total, o.payment_method,
               o.table_number, o.table_id, o.created_at, o.updated_at,
               t.name AS table_name
        FROM orders o
        LEFT JOIN tables t ON o.table_id = t.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode([
            'success' => false,
            'error' => 'Order not found',
            'order_id' => $order_id
        ]);
        http_response_code(404);
        exit;
    }
    
    // Ambil item pesanan
    $stmt_items = $pdo->prepare("
        SELECT oi.qty, oi.price, p.name AS product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt_items->execute([$order_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    
    $order['items'] = $items;
    
    echo json_encode([
        'success' => true,
        'order' => $order,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    error_log("Customer Real-time API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    http_response_code(500);
}
