<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate inputs
if (!isset($_GET['shift_id']) || !is_numeric($_GET['shift_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid shift ID']);
    exit;
}

$shift_id = (int)$_GET['shift_id'];
$status = $_GET['status'] ?? 'present';
$date = $_GET['date'] ?? date('Y-m-d');

// Fetch shift and employee details
$stmt = $connection->prepare(
    "SELECT s.*, u.full_name, u.position 
     FROM shifts s 
     JOIN users u ON s.employee_id = u.id 
     WHERE s.id = ?"
);
$stmt->bind_param("i", $shift_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Shift not found']);
    exit;
}

$shift = $result->fetch_assoc();
$stmt->close();

// Current datetime for check-in (rounded to nearest 5 minutes)
$current_time = date('Y-m-d\TH:i', round(time() / 300) * 300);

echo json_encode([
    'success' => true,
    'employee_name' => $shift['full_name'] . ' (' . $shift['position'] . ')',
    'current_time' => $current_time,
    'shift' => $shift
]);
?>