<?php
require_once '../../config/config.php';
require_once '../../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

// Check if code parameter exists
if (!isset($_GET['code'])) {
    http_response_code(400);
    die('Missing code parameter');
}

$code = $_GET['code'];

// Create URL for the QR code
$url = BASE_URL . '/index.php?code=' . urlencode($code);

// Generate QR Code using Builder (endroid/qr-code v5+)
$result = Builder::create()
    ->writer(new PngWriter())
    ->data($url)
    ->encoding(new Encoding('UTF-8'))
    ->size(300)
    ->margin(10)
    ->build();

// Output as PNG image
header('Content-Type: ' . $result->getMimeType());
echo $result->getString();
