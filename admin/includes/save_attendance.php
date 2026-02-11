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
$required_fields = ['shift_id', 'attendance_date', 'status', 'check_in_time'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
        exit;
    }
}

$shift_id = (int)$_POST['shift_id'];
$attendance_date = trim($_POST['attendance_date']);
$status = trim($_POST['status']);
$check_in_time = trim($_POST['check_in_time']);
$notes = trim($_POST['notes'] ?? '');

// Get shift details to calculate late minutes
$stmt = $connection->prepare(
    "SELECT s.start_time, s.employee_id 
     FROM shifts s 
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

// Calculate late minutes
$late_minutes = 0;
if ($status === 'present' || $status === 'late') {
    $scheduled_start = new DateTime($attendance_date . ' ' . $shift['start_time']);
    $actual_start = new DateTime($check_in_time);
    
    if ($actual_start > $scheduled_start) {
        $diff = $scheduled_start->diff($actual_start);
        $late_minutes = ($diff->h * 60) + $diff->i;
        
        // If late more than 30 minutes, auto-mark as late
        if ($status === 'present' && $late_minutes > 30) {
            $status = 'late';
        }
    }
}

// Check if attendance already exists
$check_stmt = $connection->prepare(
    "SELECT id FROM attendance WHERE shift_id = ? AND attendance_date = ?"
);
$check_stmt->bind_param("is", $shift_id, $attendance_date);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing attendance
    $attendance = $check_result->fetch_assoc();
    $update_stmt = $connection->prepare(
        "UPDATE attendance SET 
            status = ?, 
            check_in_time = ?, 
            late_minutes = ?,
            notes = ?,
            updated_at = NOW()
         WHERE id = ?"
    );
    $update_stmt->bind_param("ssisi", $status, $check_in_time, $late_minutes, $notes, $attendance['id']);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Attendance updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update attendance'
        ]);
    }
    $update_stmt->close();
} else {
    // Insert new attendance
    $insert_stmt = $connection->prepare(
        "INSERT INTO attendance (shift_id, employee_id, attendance_date, status, 
                               check_in_time, late_minutes, notes) 
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $insert_stmt->bind_param("iisssis", 
        $shift_id, $shift['employee_id'], $attendance_date, $status,
        $check_in_time, $late_minutes, $notes
    );
    
    if ($insert_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Attendance marked successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to mark attendance'
        ]);
    }
    $insert_stmt->close();
}

$check_stmt->close();
?>