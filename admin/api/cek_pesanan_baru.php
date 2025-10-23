<?php
// /admin/api/cek_pesanan_baru.php
require_once '../../config/config.php';
header('Content-Type: application/json');

// Cek pesanan dengan status 'pending'
$sql = "SELECT COUNT(id) AS new_orders FROM orders WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo json_encode(['new_orders' => (int)$row['new_orders']]);
$conn->close();
?>