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
            // Get all job orders, by job_order_no, or by id
            $id = $_GET['id'] ?? null;
            $jobOrderNo = $_GET['job_order_no'] ?? null;
            
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM job_orders WHERE id = ?");
                $stmt->execute([$id]);
            } elseif ($jobOrderNo) {
                $stmt = $pdo->prepare("SELECT * FROM job_orders WHERE job_order_no = ? ORDER BY id DESC");
                $stmt->execute([$jobOrderNo]);
            } else {
                $stmt = $pdo->query("SELECT * FROM job_orders ORDER BY job_order_no DESC, id DESC");
            }
            
            $orders = $stmt->fetchAll();
            // Decode JSON fields
            foreach ($orders as &$order) {
                $order['labor_data'] = json_decode($order['labor_data'], true) ?? [];
                $order['parts_data'] = json_decode($order['parts_data'], true) ?? [];
            }
            
            echo json_encode(['success' => true, 'data' => $orders]);
            break;
            
        case 'POST':
            // Create new job order
            $data = json_decode(file_get_contents('php://input'), true);
            
            $jobOrderNo = intval($data['job_order_no'] ?? 0);
            $type = $data['type'] ?? 'Private';
            $customerName = trim($data['customer_name'] ?? '');
            $address = trim($data['address'] ?? '');
            $contactNo = trim($data['contact_no'] ?? '');
            $model = trim($data['model'] ?? '');
            $plateNo = trim($data['plate_no'] ?? '');
            $motorChasis = trim($data['motor_chasis'] ?? '');
            $timeIn = trim($data['time_in'] ?? '');
            $date = $data['date'] ?? date('Y-m-d');
            $vehicleColor = trim($data['vehicle_color'] ?? '');
            $fuelLevel = trim($data['fuel_level'] ?? '');
            $engineNumber = trim($data['engine_number'] ?? '');
            $laborData = json_encode($data['labor'] ?? []);
            $partsData = json_encode($data['parts'] ?? []);
            $totalAmount = floatval($data['total_amount'] ?? 0);
            
            if ($jobOrderNo <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Job order number is required']);
                exit();
            }
            
            $stmt = $pdo->prepare("INSERT INTO job_orders (job_order_no, type, customer_name, address, contact_no, model, plate_no, motor_chasis, time_in, date, vehicle_color, fuel_level, engine_number, labor_data, parts_data, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$jobOrderNo, $type, $customerName, $address, $contactNo, $model, $plateNo, $motorChasis, $timeIn, $date, $vehicleColor, $fuelLevel, $engineNumber, $laborData, $partsData, $totalAmount]);
            
            $id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM job_orders WHERE id = ?");
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            $order['labor_data'] = json_decode($order['labor_data'], true) ?? [];
            $order['parts_data'] = json_decode($order['parts_data'], true) ?? [];
            
            echo json_encode(['success' => true, 'message' => 'Job order saved successfully', 'data' => $order]);
            break;
            
        case 'PUT':
            // Update job order
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id'] ?? 0);
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid job order ID']);
                exit();
            }
            
            $jobOrderNo = intval($data['job_order_no'] ?? 0);
            $type = $data['type'] ?? 'Private';
            $customerName = trim($data['customer_name'] ?? '');
            $address = trim($data['address'] ?? '');
            $contactNo = trim($data['contact_no'] ?? '');
            $model = trim($data['model'] ?? '');
            $plateNo = trim($data['plate_no'] ?? '');
            $motorChasis = trim($data['motor_chasis'] ?? '');
            $timeIn = trim($data['time_in'] ?? '');
            $date = $data['date'] ?? date('Y-m-d');
            $vehicleColor = trim($data['vehicle_color'] ?? '');
            $fuelLevel = trim($data['fuel_level'] ?? '');
            $engineNumber = trim($data['engine_number'] ?? '');
            $laborData = json_encode($data['labor'] ?? []);
            $partsData = json_encode($data['parts'] ?? []);
            $totalAmount = floatval($data['total_amount'] ?? 0);
            
            $stmt = $pdo->prepare("UPDATE job_orders SET job_order_no = ?, type = ?, customer_name = ?, address = ?, contact_no = ?, model = ?, plate_no = ?, motor_chasis = ?, time_in = ?, date = ?, vehicle_color = ?, fuel_level = ?, engine_number = ?, labor_data = ?, parts_data = ?, total_amount = ? WHERE id = ?");
            $stmt->execute([$jobOrderNo, $type, $customerName, $address, $contactNo, $model, $plateNo, $motorChasis, $timeIn, $date, $vehicleColor, $fuelLevel, $engineNumber, $laborData, $partsData, $totalAmount, $id]);
            
            $stmt = $pdo->prepare("SELECT * FROM job_orders WHERE id = ?");
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            $order['labor_data'] = json_decode($order['labor_data'], true) ?? [];
            $order['parts_data'] = json_decode($order['parts_data'], true) ?? [];
            
            echo json_encode(['success' => true, 'message' => 'Job order updated successfully', 'data' => $order]);
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

