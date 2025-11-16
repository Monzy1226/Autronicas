<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    // Get total items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventory");
    $totalItems = $stmt->fetch()['total'];
    
    // Get total value
    $stmt = $pdo->query("SELECT SUM(quantity * unit_price) as total_value FROM inventory");
    $totalValue = $stmt->fetch()['total_value'] ?? 0;
    
    // Get total categories
    $stmt = $pdo->query("SELECT COUNT(DISTINCT category) as total FROM inventory");
    $totalCategories = $stmt->fetch()['total'];
    
    // Get low stock items (quantity <= min_quantity)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventory WHERE quantity <= min_quantity");
    $lowStock = $stmt->fetch()['total'];
    
    // Get low stock items details
    $stmt = $pdo->query("SELECT description, quantity FROM inventory WHERE quantity <= min_quantity ORDER BY description ASC");
    $lowStockItems = $stmt->fetchAll();
    
    // Get category breakdown
    $stmt = $pdo->query("SELECT category, SUM(quantity) as current, SUM(min_quantity) as minimum, SUM(quantity * unit_price) as value FROM inventory GROUP BY category ORDER BY category ASC");
    $categoryBreakdown = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_items' => intval($totalItems),
            'total_value' => floatval($totalValue),
            'total_categories' => intval($totalCategories),
            'low_stock' => intval($lowStock),
            'low_stock_items' => $lowStockItems,
            'category_breakdown' => $categoryBreakdown
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>

