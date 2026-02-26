<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$last_id = (int)($_GET['last_id'] ?? 0);

try {
    // Get new orders since last_id
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_code,
            o.table_number,
            o.total,
            o.status,
            o.created_at
        FROM orders o
        WHERE o.id > ? AND o.status = 'pending'
        ORDER BY o.id DESC
    ");
    $stmt->execute([$last_id]);
    $newOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $newOrderCount = count($newOrders);
    
    if ($newOrderCount > 0) {
        // Get current max ID
        $maxStmt = $pdo->query("SELECT MAX(id) as max_id FROM orders");
        $maxResult = $maxStmt->fetch(PDO::FETCH_ASSOC);
        
        // Get table name from first new order
        $tableName = $newOrders[0]['table_number'] ?? 'Customer';
        
        echo json_encode([
            'success' => true,
            'new_orders' => $newOrderCount,
            'current_last_id' => (int)$maxResult['max_id'],
            'table_name' => $tableName,
            'orders' => $newOrders
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'new_orders' => 0
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
