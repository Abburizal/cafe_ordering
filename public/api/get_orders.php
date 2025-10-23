<?php
// Minimal API untuk mengambil beberapa order berdasarkan ID
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (!is_array($ids) || count($ids) === 0) {
    echo json_encode(['orders' => []]);
    exit;
}

// cast ke integer dan unique
$ids = array_values(array_unique(array_map('intval', $ids)));

try {
    // Ambil orders
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, order_code, table_id, total, status, created_at FROM orders WHERE id IN ($placeholders) ORDER BY FIELD(id," . $placeholders . ")");
    // Bind parameters twice for ORDER FIELD (some PDO drivers嫌) -> simpler: run without FIELD ordering
    $stmt = $pdo->prepare("SELECT id, order_code, table_id, total, status, created_at FROM orders WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$orders) {
        echo json_encode(['orders' => []]);
        exit;
    }

    // Ambil order_items dengan nama produk
    $order_ids = array_column($orders, 'id');
    $ph2 = implode(',', array_fill(0, count($order_ids), '?'));
    $stmt = $pdo->prepare("SELECT oi.*, p.name AS product_name, p.price AS product_price FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id IN ($ph2) ORDER BY oi.id ASC");
    $stmt->execute($order_ids);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kelompokkan items berdasarkan order_id
    $grouped = [];
    foreach ($items as $it) {
        $oid = $it['order_id'];
        if (!isset($grouped[$oid])) $grouped[$oid] = [];
        $grouped[$oid][] = [
            'id' => (int)$it['id'],
            'product_id' => (int)$it['product_id'],
            'product_name' => $it['product_name'] ?? 'Item',
            'quantity' => (int)$it['quantity'],
            'price' => $it['price'] ?? $it['product_price'],
            'price_formatted' => function_exists('currency') ? currency($it['price'] ?? $it['product_price']) : ($it['price'] ?? $it['product_price'])
        ];
    }

    // Susun response
    $out = [];
    foreach ($orders as $o) {
        $oid = (int)$o['id'];
        $out[] = [
            'id' => $oid,
            'order_code' => $o['order_code'],
            'table_id' => $o['table_id'],
            'total' => $o['total'],
            'total_formatted' => function_exists('currency') ? currency($o['total']) : $o['total'],
            'status' => $o['status'],
            'created_at' => $o['created_at'],
            'items' => $grouped[$oid] ?? []
        ];
    }

    echo json_encode(['orders' => $out]);

} catch (PDOException $e) {
    error_log("API get_orders error: " . $e->getMessage());
    echo json_encode(['orders' => []]);
    exit;
}
