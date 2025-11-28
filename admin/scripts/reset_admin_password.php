<?php
/**
 * Script untuk membuat/reset password admin
 * Jalankan dari browser: admin/scripts/reset_admin_password.php
 */

require_once '../../config/config.php';

// Konfigurasi admin default
$admin_username = 'admin';
$admin_password = 'admin123';  // Password default
$admin_email = 'admin@kantinakademimd.com';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>Reset Password Admin</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8'>";

echo "<h1 class='text-2xl font-bold text-indigo-600 mb-6'>🔧 Reset Password Admin</h1>";

try {
    // Cek apakah user admin sudah ada
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ? AND role = 'admin'");
    $stmt->execute([$admin_username]);
    $existing_admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Hash password dengan password_hash
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    if ($existing_admin) {
        // Update password admin yang sudah ada
        $stmt = $pdo->prepare("UPDATE users SET password = ?, email = ? WHERE name = ? AND role = 'admin'");
        $stmt->execute([$hashed_password, $admin_email, $admin_username]);
        
        echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>
                <p class='font-bold'>✅ Password admin berhasil direset!</p>
              </div>";
        
        echo "<div class='bg-blue-50 border border-blue-200 rounded p-4 mb-4'>
                <p class='font-semibold mb-2'>Admin sudah ada. Password telah diupdate:</p>
                <p><strong>ID:</strong> {$existing_admin['id']}</p>
              </div>";
    } else {
        // Buat user admin baru
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$admin_username, $admin_email, $hashed_password]);
        
        echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>
                <p class='font-bold'>✅ User admin baru berhasil dibuat!</p>
              </div>";
    }
    
    // Tampilkan kredensial
    echo "<div class='bg-indigo-50 border-2 border-indigo-300 rounded-lg p-6 mb-6'>
            <h2 class='text-xl font-bold text-indigo-800 mb-4'>🔑 Kredensial Login Admin</h2>
            <div class='space-y-2 bg-white p-4 rounded border border-indigo-200 font-mono'>
                <p><strong class='text-gray-700'>Username:</strong> <span class='text-indigo-600 font-bold'>{$admin_username}</span></p>
                <p><strong class='text-gray-700'>Password:</strong> <span class='text-indigo-600 font-bold'>{$admin_password}</span></p>
                <p><strong class='text-gray-700'>Email:</strong> <span class='text-gray-600'>{$admin_email}</span></p>
            </div>
            <p class='text-sm text-gray-600 mt-4'>⚠️ Simpan kredensial ini dengan aman!</p>
          </div>";
    
    // Verifikasi password
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ? AND role = 'admin'");
    $stmt->execute([$admin_username]);
    $check_admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($check_admin && password_verify($admin_password, $check_admin['password'])) {
        echo "<div class='bg-green-50 border border-green-300 rounded p-4 mb-4'>
                <p class='text-green-700'>✅ <strong>Verifikasi:</strong> Password hash berhasil dan valid!</p>
              </div>";
    } else {
        echo "<div class='bg-red-50 border border-red-300 rounded p-4 mb-4'>
                <p class='text-red-700'>❌ <strong>Warning:</strong> Password verification gagal!</p>
              </div>";
    }
    
    // Tampilkan semua admin
    $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users WHERE role = 'admin' ORDER BY id");
    $all_admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='mt-6'>
            <h3 class='text-lg font-bold text-gray-800 mb-3'>👥 Daftar Admin</h3>
            <div class='overflow-x-auto'>
                <table class='min-w-full bg-white border border-gray-300'>
                    <thead class='bg-gray-200'>
                        <tr>
                            <th class='px-4 py-2 border'>ID</th>
                            <th class='px-4 py-2 border'>Username</th>
                            <th class='px-4 py-2 border'>Email</th>
                            <th class='px-4 py-2 border'>Created At</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    foreach ($all_admins as $admin) {
        echo "<tr>
                <td class='px-4 py-2 border text-center'>{$admin['id']}</td>
                <td class='px-4 py-2 border'>{$admin['name']}</td>
                <td class='px-4 py-2 border'>{$admin['email']}</td>
                <td class='px-4 py-2 border text-sm'>{$admin['created_at']}</td>
              </tr>";
    }
    
    echo "      </tbody>
                </table>
            </div>
          </div>";
    
    echo "<div class='mt-8 flex gap-4'>
            <a href='../login.php' class='px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold transition'>
                ➜ Login Sekarang
            </a>
            <a href='../dashboard.php' class='px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold transition'>
                Dashboard
            </a>
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4'>
            <p class='font-bold'>❌ Error Database:</p>
            <p class='text-sm mt-2'>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

echo "<div class='mt-8 p-4 bg-yellow-50 border border-yellow-300 rounded'>
        <p class='text-sm text-yellow-800'>
            <strong>⚠️ Catatan Keamanan:</strong> Setelah login berhasil, segera ganti password default melalui menu profile atau register_admin.php
        </p>
      </div>";

echo "</div>
</body>
</html>";
?>
