<?php
/**
 * API untuk registrasi FCM Token dari admin
 */
require_once '../../config/config.php';
require_once '../../app/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Cek apakah admin sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$token = $input['token'] ?? '';
$device_type = $input['device_type'] ?? 'web';
$device_name = $input['device_name'] ?? '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token required']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Cek apakah token sudah ada
    $stmt = $pdo->prepare("SELECT id FROM admin_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing token
        $stmt = $pdo->prepare("UPDATE admin_tokens SET is_active=1, last_used_at=NOW() WHERE token=?");
        $stmt->execute([$token]);
    } else {
        // Insert new token
        $stmt = $pdo->prepare("INSERT INTO admin_tokens (user_id, token, device_type, device_name, last_used_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $token, $device_type, $device_name]);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Token registered successfully'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
