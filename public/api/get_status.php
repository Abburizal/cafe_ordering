<?php
// /public/api/get_status.php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_GET['order_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID missing']);
    exit;
}

$order_id = $conn->real_escape_string($_GET['order_id']);
$sql = "SELECT status FROM orders WHERE id = $order_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['status' => $row['status']]);
} else {
    echo json_encode(['status' => 'not_found']);
}
$conn->close();
?>