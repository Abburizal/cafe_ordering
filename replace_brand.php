<?php
// Script untuk mengganti semua "RestoKu" menjadi "Kantin Akademi MD"

$files = [
    'admin/tables.php',
    'cafe_ordering.sql',
    'admin/scripts/reset_admin_password.php'
];

foreach ($files as $file) {
    $filepath = __DIR__ . '/' . $file;
    
    if (file_exists($filepath)) {
        $content = file_get_contents($filepath);
        $newContent = str_replace('RestoKu', 'Kantin Akademi MD', $content);
        $newContent = str_replace('restoku', 'kantin akademi md', $newContent);
        
        file_put_contents($filepath, $newContent);
        echo "✅ Updated: $file\n";
    } else {
        echo "❌ Not found: $file\n";
    }
}

echo "\n✅ Rebranding selesai!\n";
?>
