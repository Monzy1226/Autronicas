<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get all inventory items
            $stmt = $pdo->query("SELECT * FROM inventory ORDER BY description ASC");
            $items = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $items]);
            break;
            
        case 'POST':
            // Create new inventory item
            $data = json_decode(file_get_contents('php://input'), true);
            
            $code = trim($data['code'] ?? '');
            $description = trim($data['description'] ?? '');
            $category = trim($data['category'] ?? '');
            $quantity = intval($data['quantity'] ?? 0);
            $minQuantity = intval($data['minQuantity'] ?? 0);
            $unitPrice = floatval($data['unitPrice'] ?? 0);
            $srpPrivate = floatval($data['srpPrivate'] ?? 0);
            $srpLgu = floatval($data['srpLGU'] ?? 0);
            
            if (empty($code) || empty($description) || empty($category)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Code, description, and category are required']);
                exit();
            }
            
            $stmt = $pdo->prepare("INSERT INTO inventory (code, description, category, quantity, min_quantity, unit_price, srp_private, srp_lgu) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $description, $category, $quantity, $minQuantity, $unitPrice, $srpPrivate, $srpLgu]);
            
            $id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM inventory WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();
            
            echo json_encode(['success' => true, 'message' => 'Item added successfully', 'data' => $item]);
            break;
            
        case 'PUT':
            // Update inventory item
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id'] ?? 0);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
                exit();
            }
            
            $code = trim($data['code'] ?? '');
            $description = trim($data['description'] ?? '');
            $category = trim($data['category'] ?? '');
            $quantity = intval($data['quantity'] ?? 0);
            $minQuantity = intval($data['minQuantity'] ?? 0);
            $unitPrice = floatval($data['unitPrice'] ?? 0);
            $srpPrivate = floatval($data['srpPrivate'] ?? 0);
            $srpLgu = floatval($data['srpLGU'] ?? 0);
            
            $stmt = $pdo->prepare("UPDATE inventory SET code = ?, description = ?, category = ?, quantity = ?, min_quantity = ?, unit_price = ?, srp_private = ?, srp_lgu = ? WHERE id = ?");
            $stmt->execute([$code, $description, $category, $quantity, $minQuantity, $unitPrice, $srpPrivate, $srpLgu, $id]);
            
            $stmt = $pdo->prepare("SELECT * FROM inventory WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();
            
            echo json_encode(['success' => true, 'message' => 'Item updated successfully', 'data' => $item]);
            break;
            
        case 'DELETE':
            // Delete inventory item
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id'] ?? 0);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
                exit();
            }
            
            $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>

