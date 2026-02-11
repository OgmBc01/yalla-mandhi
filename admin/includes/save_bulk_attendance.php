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

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['date']) || !isset($data['shifts'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$date = $data['date'];
$shifts = $data['shifts'];
$marked_count = 0;

// Process each shift
foreach ($shifts as $shift_data) {
    if (!isset($shift_data['shift_id']) || !isset($shift_data['status'])) {
        continue;
    }
    
    $shift_id = (int)$shift_data['shift_id'];
    $status = $shift_data['status'];
    $check_in_time = $shift_data['check_in_time'] ?? $date . 'T09:00';
    $notes = $shift_data['notes'] ?? '';
    
    // Get shift details
    $stmt = $connection->prepare(
        "SELECT s.start_time, s.employee_id 
         FROM shifts s 
         WHERE s.id = ?"
    );
    $stmt->bind_param("i", $shift_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        continue;
    }
    
    $shift = $result->fetch_assoc();
    $stmt->close();
    
    // Calculate late minutes
    $late_minutes = 0;
    if ($status === 'present' || $status === 'late') {
        $scheduled_start = new DateTime($date . ' ' . $shift['start_time']);
        $actual_start = new DateTime($check_in_time);
        
        if ($actual_start > $scheduled_start) {
            $diff = $scheduled_start->diff($actual_start);
            $late_minutes = ($diff->h * 60) + $diff->i;
            
            if ($status === 'present' && $late_minutes > 30) {
                $status = 'late';
            }
        }
    }
    
    // Check if attendance already exists
    $check_stmt = $connection->prepare(
        "SELECT id FROM attendance WHERE shift_id = ? AND attendance_date = ?"
    );
    $check_stmt->bind_param("is", $shift_id, $date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing
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
        $update_stmt->execute();
        $update_stmt->close();
        $marked_count++;
    } else {
        // Insert new
        $insert_stmt = $connection->prepare(
            "INSERT INTO attendance (shift_id, employee_id, attendance_date, status, 
                                   check_in_time, late_minutes, notes) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $insert_stmt->bind_param("iisssis", 
            $shift_id, $shift['employee_id'], $date, $status,
            $check_in_time, $late_minutes, $notes
        );
        $insert_stmt->execute();
        $insert_stmt->close();
        $marked_count++;
    }
    
    $check_stmt->close();
}

echo json_encode([
    'success' => true,
    'message' => 'Bulk attendance saved successfully',
    'marked_count' => $marked_count
]);
?>