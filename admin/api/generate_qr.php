<?php
require_once '../../config/config.php';
require_once '../../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Check if code parameter exists
if (!isset($_GET['code'])) {
    http_response_code(400);
    die('Missing code parameter');
}

$code = $_GET['code'];

// Create URL for the QR code
$url = BASE_URL . '/index.php?code=' . urlencode($code);

// Generate QR Code
$qrCode = QrCode::create($url)
    ->setSize(300)
    ->setMargin(10);

$writer = new PngWriter();
$result = $writer->write($qrCode);

// Output as PNG image
header('Content-Type: ' . $result->getMimeType());
echo $result->getString();
