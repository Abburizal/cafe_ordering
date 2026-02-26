<?php
/**
 * Database Configuration - InfinityFree Compatible
 */

// Database configuration for InfinityFree
$host = "sql300.infinityfree.com";
$db   = "if0_40547438_cafe_ordering";
$user = "if0_40547438";
$pass = "NGPrn9phhvw";  // Gunakan password cPanel Anda

// Error handling
if (!$host || !$db || !$user || !$pass) {
    error_log("Database configuration incomplete. Check database credentials.");
    die("Database configuration error. Contact administrator.");
}

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Log successful connection
    error_log("Database connected successfully: $host / $db");
    
} catch (PDOException $e) {
    error_log("PDO Connection Error: " . $e->getMessage());
    die("Database connection failed. Please check your database configuration.");
}
?>