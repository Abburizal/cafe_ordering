<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT MAX(id) as last_id FROM orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'last_id' => (int)($result['last_id'] ?? 0)
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
