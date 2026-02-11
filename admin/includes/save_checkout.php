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

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate inputs
if (!isset($_POST['attendance_id']) || !is_numeric($_POST['attendance_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid attendance ID']);
    exit;
}

$attendance_id = (int)$_POST['attendance_id'];
$check_out_time = trim($_POST['check_out_time'] ?? '');
$overtime_minutes = intval($_POST['overtime_minutes'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

if (empty($check_out_time)) {
    echo json_encode(['success' => false, 'message' => 'Check out time is required']);
    exit;
}

// Update attendance with check out time
$update_stmt = $connection->prepare(
    "UPDATE attendance SET 
        check_out_time = ?, 
        overtime_minutes = ?,
        notes = CONCAT(IFNULL(notes, ''), ' | ', ?),
        updated_at = NOW()
     WHERE id = ?"
);

// Preserve existing notes
$existing_notes = $notes ? "Check out: " . $notes : "Checked out";

$update_stmt->bind_param("sisi", $check_out_time, $overtime_minutes, $existing_notes, $attendance_id);

if ($update_stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Check out recorded successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to record check out'
    ]);
}

$update_stmt->close();
?>