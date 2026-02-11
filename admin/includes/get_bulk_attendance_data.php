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

// Validate date
$date = $_GET['date'] ?? date('Y-m-d');

// Fetch shifts for the date
$stmt = $connection->prepare(
    "SELECT s.id, u.full_name, u.position, a.status as attendance_status
     FROM shifts s 
     JOIN users u ON s.employee_id = u.id 
     LEFT JOIN attendance a ON s.id = a.shift_id AND a.attendance_date = s.shift_date
     WHERE s.shift_date = ? AND s.is_active = 1
     ORDER BY u.full_name"
);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$shifts = [];
$current_time = date('Y-m-d\TH:i', round(time() / 300) * 300);

while ($row = $result->fetch_assoc()) {
    $shifts[] = [
        'id' => $row['id'],
        'full_name' => $row['full_name'],
        'position' => $row['position'],
        'attendance_status' => $row['attendance_status'] ?? 'absent',
        'current_time' => $current_time
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'shifts' => $shifts,
    'date' => $date,
    'count' => count($shifts)
]);
?>