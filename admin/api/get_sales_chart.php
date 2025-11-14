<?php
/**
 * API untuk mendapatkan data chart penjualan
 */
require_once '../../config/config.php';
require_once '../../app/helpers.php';
require_once '../../app/middleware.php';

require_admin();

header('Content-Type: application/json');

$period = $_GET['period'] ?? 'week'; // week, month, year

try {
    $data = [];
    
    switch ($period) {
        case 'week':
            // Data 7 hari terakhir
            $stmt = $pdo->query("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_sales
                FROM orders
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    AND status != 'cancelled'
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'month':
            // Data 30 hari terakhir
            $stmt = $pdo->query("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_sales
                FROM orders
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND status != 'cancelled'
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'year':
            // Data per bulan dalam 1 tahun
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as date,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_sales
                FROM orders
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    AND status != 'cancelled'
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY date ASC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    
    // Format data untuk chart
    $labels = [];
    $sales = [];
    $orders = [];
    
    foreach ($data as $row) {
        $labels[] = $row['date'];
        $sales[] = (float)$row['total_sales'];
        $orders[] = (int)$row['total_orders'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'sales' => $sales,
            'orders' => $orders
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
