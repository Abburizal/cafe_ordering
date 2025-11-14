<?php
/**
 * Script untuk mengupdate password yang ada dari plain text menjadi hashed
 * JALANKAN SEKALI SAJA!
 */

require_once __DIR__ . '/../../config/config.php';

echo "=== FIX PASSWORD SECURITY ===\n\n";

try {
    // Ambil semua user yang passwordnya belum di-hash (panjang kurang dari 60 karakter)
    $stmt = $pdo->query("SELECT id, name, password FROM users WHERE LENGTH(password) < 60");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "✅ Semua password sudah dalam format hash yang aman!\n";
        exit;
    }
    
    echo "⚠️  Ditemukan " . count($users) . " user dengan password plain text\n\n";
    
    $pdo->beginTransaction();
    
    foreach ($users as $user) {
        $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hashed, $user['id']]);
        
        echo "✅ Updated: {$user['name']} (ID: {$user['id']})\n";
        echo "   Old: {$user['password']}\n";
        echo "   New: [HASHED]\n\n";
    }
    
    $pdo->commit();
    
    echo "\n🎉 BERHASIL! Semua password telah di-hash dengan aman.\n";
    echo "📝 Catat password asli user sebelum di-hash:\n\n";
    
    foreach ($users as $user) {
        echo "- Username: {$user['name']} → Password: {$user['password']}\n";
    }
    
    echo "\n⚠️  PENTING: Beritahu user untuk menggunakan password asli mereka untuk login!\n";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
