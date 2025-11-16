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
            // Get all sales or filter by date/range
            $date = $_GET['date'] ?? null;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            
            if ($date) {
                $stmt = $pdo->prepare("SELECT * FROM sales WHERE date = ? ORDER BY job_order_no DESC");
                $stmt->execute([$date]);
            } elseif ($startDate && $endDate) {
                $stmt = $pdo->prepare("SELECT * FROM sales WHERE date >= ? AND date <= ? ORDER BY job_order_no DESC");
                $stmt->execute([$startDate, $endDate]);
            } else {
                $stmt = $pdo->query("SELECT * FROM sales ORDER BY job_order_no DESC");
            }
            
            $sales = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $sales]);
            break;
            
        case 'POST':
            // Create new sale
            $data = json_decode(file_get_contents('php://input'), true);
            
            $vehiclePlate = trim($data['vehiclePlate'] ?? '');
            $laborTotal = floatval($data['laborTotal'] ?? 0);
            $partsTotal = floatval($data['partsTotal'] ?? 0);
            $unitPrice = floatval($data['unitPrice'] ?? 0);
            $date = $data['date'] ?? date('Y-m-d');
            
            if (empty($vehiclePlate)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Vehicle/Plate No. is required']);
                exit();
            }
            
            // Get next job order number
            $stmt = $pdo->query("SELECT MAX(job_order_no) as max_no FROM sales");
            $result = $stmt->fetch();
            $jobOrderNo = ($result['max_no'] ?? 0) + 1;
            
            $srpTotal = $laborTotal + $partsTotal;
            $profit = $srpTotal - $unitPrice;
            
            $stmt = $pdo->prepare("INSERT INTO sales (job_order_no, date, vehicle_plate, labor_total, parts_total, unit_price, srp_total, profit, confirmed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$jobOrderNo, $date, $vehiclePlate, $laborTotal, $partsTotal, $unitPrice, $srpTotal, $profit]);
            
            $id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
            $stmt->execute([$id]);
            $sale = $stmt->fetch();
            
            echo json_encode(['success' => true, 'message' => 'Sale added successfully', 'data' => $sale]);
            break;
            
        case 'PUT':
            // Update sale
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id'] ?? 0);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid sale ID']);
                exit();
            }
            
            // Check if confirmed
            $stmt = $pdo->prepare("SELECT confirmed FROM sales WHERE id = ?");
            $stmt->execute([$id]);
            $sale = $stmt->fetch();
            
            if ($sale && $sale['confirmed']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Confirmed sales cannot be edited']);
                exit();
            }
            
            $vehiclePlate = trim($data['vehiclePlate'] ?? '');
            $laborTotal = floatval($data['laborTotal'] ?? 0);
            $partsTotal = floatval($data['partsTotal'] ?? 0);
            $unitPrice = floatval($data['unitPrice'] ?? 0);
            
            $srpTotal = $laborTotal + $partsTotal;
            $profit = $srpTotal - $unitPrice;
            
            $stmt = $pdo->prepare("UPDATE sales SET vehicle_plate = ?, labor_total = ?, parts_total = ?, unit_price = ?, srp_total = ?, profit = ? WHERE id = ?");
            $stmt->execute([$vehiclePlate, $laborTotal, $partsTotal, $unitPrice, $srpTotal, $profit, $id]);
            
            $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
            $stmt->execute([$id]);
            $sale = $stmt->fetch();
            
            echo json_encode(['success' => true, 'message' => 'Sale updated successfully', 'data' => $sale]);
            break;
            
        case 'PATCH':
            // Confirm sale
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id'] ?? 0);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid sale ID']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE sales SET confirmed = 1 WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Sale confirmed successfully']);
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

