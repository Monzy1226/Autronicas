<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../auth.php';

logoutUser();
echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>

