<?php
/**
 * API untuk upload gambar produk
 */
require_once '../../config/config.php';
require_once '../../app/helpers.php';
require_once '../../app/middleware.php';
require_once '../../app/image_handler.php';

require_admin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validasi file upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

try {
    $imageHandler = new ImageHandler();
    $result = $imageHandler->upload($_FILES['image']);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'filename' => $result['filename'],
            'url' => $imageHandler->get_image_url($result['filename']),
            'thumbnail_url' => $imageHandler->get_image_url($result['filename'], true)
        ]);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
