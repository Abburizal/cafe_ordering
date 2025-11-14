<?php
/**
 * Script untuk setup produk contoh
 */

require_once __DIR__ . '/../../config/config.php';

echo "=== SETUP PRODUK CONTOH ===\n\n";

try {
    $pdo->beginTransaction();
    
    // Data produk contoh yang proper
    $products = [
        // MAKANAN
        ['name' => 'Nasi Goreng Spesial', 'price' => 25000, 'description' => 'Nasi goreng dengan telur, ayam, dan sayuran', 'stock' => 50, 'image' => 'nasi-goreng.jpg'],
        ['name' => 'Mie Goreng', 'price' => 20000, 'description' => 'Mie goreng pedas dengan topping ayam', 'stock' => 40, 'image' => 'mie-goreng.jpg'],
        ['name' => 'Ayam Geprek', 'price' => 22000, 'description' => 'Ayam crispy dengan sambal geprek level 1-5', 'stock' => 30, 'image' => 'ayam-geprek.jpg'],
        ['name' => 'Soto Ayam', 'price' => 18000, 'description' => 'Soto ayam kuah bening dengan nasi', 'stock' => 35, 'image' => 'soto-ayam.jpg'],
        ['name' => 'Gado-Gado', 'price' => 15000, 'description' => 'Sayuran dengan saus kacang', 'stock' => 25, 'image' => 'gado-gado.jpg'],
        
        // MINUMAN
        ['name' => 'Es Teh Manis', 'price' => 5000, 'description' => 'Teh manis dingin segar', 'stock' => 100, 'image' => 'es-teh.jpg'],
        ['name' => 'Es Jeruk', 'price' => 8000, 'description' => 'Jeruk peras asli dingin', 'stock' => 80, 'image' => 'es-jeruk.jpg'],
        ['name' => 'Kopi Hitam', 'price' => 10000, 'description' => 'Kopi hitam premium', 'stock' => 60, 'image' => 'kopi-hitam.jpg'],
        ['name' => 'Cappuccino', 'price' => 18000, 'description' => 'Kopi cappuccino dengan foam lembut', 'stock' => 50, 'image' => 'cappuccino.jpg'],
        ['name' => 'Thai Tea', 'price' => 12000, 'description' => 'Thai tea original dengan susu', 'stock' => 70, 'image' => 'thai-tea.jpg'],
        
        // SNACK
        ['name' => 'Kentang Goreng', 'price' => 12000, 'description' => 'French fries crispy dengan saus', 'stock' => 40, 'image' => 'kentang-goreng.jpg'],
        ['name' => 'Pisang Goreng', 'price' => 10000, 'description' => 'Pisang goreng crispy 5 pcs', 'stock' => 30, 'image' => 'pisang-goreng.jpg'],
        
        // DESSERT
        ['name' => 'Es Krim Vanilla', 'price' => 15000, 'description' => 'Es krim vanilla premium 2 scoop', 'stock' => 25, 'image' => 'es-krim.jpg'],
        ['name' => 'Pancake Coklat', 'price' => 20000, 'description' => 'Pancake dengan topping coklat dan strawberry', 'stock' => 20, 'image' => 'pancake.jpg'],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO products (name, price, description, stock, image) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($products as $product) {
        try {
            $stmt->execute([
                $product['name'],
                $product['price'],
                $product['description'],
                $product['stock'],
                $product['image']
            ]);
            echo "✅ Berhasil menambahkan: {$product['name']} - Rp " . number_format($product['price'], 0, ',', '.') . "\n";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "⚠️  Skip: {$product['name']} sudah ada\n";
            } else {
                throw $e;
            }
        }
    }
    
    $pdo->commit();
    
    echo "\n🎉 SELESAI! Total produk: " . $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() . "\n";
    echo "\n⚠️  NOTE: Gambar produk perlu diupload ke folder public/assets/images/products/\n";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
