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
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || 
    !isset($_POST['status']) || !in_array($_POST['status'], ['0', '1'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$shift_id = (int)$_POST['id'];
$new_status = (int)$_POST['status'];

try {
    $stmt = $connection->prepare("UPDATE shifts SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $shift_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Shift not found or already has this status']);
    } else {
        $status_text = $new_status ? 'activated' : 'deactivated';
        echo json_encode([
            'success' => true,
            'message' => 'Shift ' . $status_text . ' successfully'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred'
    ]);
}
?>