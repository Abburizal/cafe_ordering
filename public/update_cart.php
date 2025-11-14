<?php
/**
 * Update Cart Handler
 * Menangani update quantity dan delete item dari keranjang
 */
require_once __DIR__ . '/../config/config.php';

// Cek sesi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi cart jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';
$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: cart.php');
    exit;
}

switch ($action) {
    case 'increase':
        // Tambah quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        }
        break;
        
    case 'decrease':
        // Kurangi quantity, minimal 1
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]--;
            // Jika quantity jadi 0 atau kurang, hapus item
            if ($_SESSION['cart'][$product_id] <= 0) {
                unset($_SESSION['cart'][$product_id]);
            }
        }
        break;
        
    case 'delete':
        // Hapus item dari keranjang
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        break;
        
    case 'clear':
        // Kosongkan seluruh keranjang
        $_SESSION['cart'] = [];
        break;
        
    default:
        // Action tidak valid
        break;
}

// Redirect kembali ke cart
header('Location: cart.php');
exit;
