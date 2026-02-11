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
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$employees = isset($_GET['employees']) ? explode(',', $_GET['employees']) : [];
$include_weekends = isset($_GET['include_weekends']) ? 1 : 0;

if (empty($start_date) || empty($end_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date range']);
    exit;
}

// Calculate preview data
$start_timestamp = strtotime($start_date);
$end_timestamp = strtotime($end_date);
$days_count = ceil(($end_timestamp - $start_timestamp) / (60 * 60 * 24)) + 1;
$employee_count = count($employees);
$weekend_days = 0;

if (!$include_weekends) {
    $temp_date = $start_timestamp;
    while ($temp_date <= $end_timestamp) {
        $day = date('w', $temp_date);
        if ($day == 0 || $day == 6) {
            $weekend_days++;
        }
        $temp_date = strtotime('+1 day', $temp_date);
    }
    $days_count -= $weekend_days;
}

$total_shifts = $days_count * $employee_count;

// Get employee names for display
$employee_names = [];
if (!empty($employees)) {
    $placeholders = str_repeat('?,', count($employees) - 1) . '?';
    $types = str_repeat('i', count($employees));
    
    $stmt = $connection->prepare("SELECT id, full_name FROM users WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$employees);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $employee_names[] = $row['full_name'];
    }
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'preview' => [
        'start_date' => date('M d, Y', $start_timestamp),
        'end_date' => date('M d, Y', $end_timestamp),
        'days_count' => $days_count,
        'employee_count' => $employee_count,
        'weekend_days' => $weekend_days,
        'total_shifts' => $total_shifts,
        'employees' => $employee_names
    ]
]);
?>