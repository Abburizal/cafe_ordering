<?php
require_once '../config/config.php';
require_once '../app/middleware.php';

require_admin();

if (!isset($_GET['id'])) {
    die('ID meja tidak valid');
}

$id = (int)$_GET['id'];

// Get table data
$stmt = $pdo->prepare("SELECT * FROM tables WHERE id = ?");
$stmt->execute([$id]);
$table = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table) {
    die('Meja tidak ditemukan');
}

// Generate QR Code dengan library
require_once '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

try {
    // URL yang akan di-encode dalam QR
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $qrUrl = $baseUrl . "/cafe_ordering/public/menu.php?table=" . $table['code'];
    
    // Create QR Code
    $qrCode = new QrCode($qrUrl);
    $qrCode->setSize(400);
    $qrCode->setMargin(10);
    $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::High);
    
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    // Set headers for download
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="QR_' . $table['code'] . '.png"');
    echo $result->getString();
    
} catch (Exception $e) {
    die('Error generating QR Code: ' . $e->getMessage());
}
