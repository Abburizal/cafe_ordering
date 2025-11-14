<?php
/**
 * Script untuk setup data meja default
 */

require_once __DIR__ . '/../../config/config.php';

echo "=== SETUP DATA MEJA ===\n\n";

try {
    // Cek apakah sudah ada data meja
    $count = $pdo->query("SELECT COUNT(*) FROM tables")->fetchColumn();
    
    if ($count > 0) {
        echo "⚠️  Sudah ada {$count} meja di database.\n";
        echo "Tetap menambahkan meja baru...\n\n";
    }
    
    // Data meja default (10 meja)
    $tables = [];
    for ($i = 1; $i <= 10; $i++) {
        $tables[] = [
            'name' => "MEJA $i",
            'code' => "TBL-" . str_pad($i, 3, '0', STR_PAD_LEFT)
        ];
    }
    
    // Tambah meja khusus
    $tables[] = ['name' => 'VIP 1', 'code' => 'TBL-VIP1'];
    $tables[] = ['name' => 'VIP 2', 'code' => 'TBL-VIP2'];
    $tables[] = ['name' => 'TAKE AWAY', 'code' => 'TBL-TAKEAWAY'];
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO tables (name, code) VALUES (?, ?)");
    
    foreach ($tables as $table) {
        try {
            $stmt->execute([$table['name'], $table['code']]);
            echo "✅ Berhasil menambahkan: {$table['name']} (Code: {$table['code']})\n";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                echo "⚠️  Skip: {$table['name']} sudah ada\n";
            } else {
                throw $e;
            }
        }
    }
    
    $pdo->commit();
    
    echo "\n🎉 SELESAI! Total meja yang tersedia:\n";
    $all_tables = $pdo->query("SELECT * FROM tables ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($all_tables as $table) {
        echo "   - {$table['name']} → Code: {$table['code']}\n";
    }
    
    echo "\n📱 Untuk generate QR Code, akses: http://localhost/cafe_ordering/admin/generate_qr/\n";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
