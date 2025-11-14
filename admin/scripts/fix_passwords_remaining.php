<?php
/**
 * Script untuk fix password yang masih plain text
 * Jalankan: php admin/scripts/fix_passwords_remaining.php
 */

require_once __DIR__ . '/../../config/config.php';

echo "=== FIX PASSWORD PLAIN TEXT ===\n\n";

try {
    // Cari user dengan password yang panjangnya kurang dari 20 karakter (kemungkinan plain text)
    $stmt = $pdo->query("SELECT id, email, name, password, LENGTH(password) as pwd_len FROM users WHERE LENGTH(password) < 20");
    $users_to_fix = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users_to_fix)) {
        echo "✅ Semua password sudah di-hash dengan baik!\n";
        exit(0);
    }
    
    echo "Ditemukan " . count($users_to_fix) . " user dengan password plain text:\n\n";
    
    foreach ($users_to_fix as $user) {
        echo "ID: {$user['id']}\n";
        echo "Email: {$user['email']}\n";
        echo "Name: {$user['name']}\n";
        echo "Password plain text: {$user['password']}\n";
        
        // Hash password
        $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
        
        // Update database
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hashed, $user['id']]);
        
        echo "✅ Password berhasil di-hash!\n";
        echo "---\n\n";
    }
    
    echo "\n🎉 Semua password berhasil diperbaiki!\n";
    echo "Silakan login dengan username dan password yang sama.\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
