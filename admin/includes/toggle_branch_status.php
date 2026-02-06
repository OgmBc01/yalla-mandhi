<?php
ob_start();
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate inputs
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || 
    !isset($_POST['status']) || !in_array($_POST['status'], ['0', '1'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$branch_id = (int) $_POST['id'];
$new_status = (int) $_POST['status'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $stmt = $connection->prepare(
        "UPDATE branches SET is_active = ? WHERE id = ?"
    );
    $stmt->bind_param("ii", $new_status, $branch_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Branch not found or already has this status']);
    } else {
        $status_text = $new_status ? 'activated' : 'deactivated';
        echo json_encode([
            'success' => true,
            'message' => 'Branch ' . $status_text . ' successfully'
        ]);
    }
    
    $stmt->close();
    
} catch (Throwable $e) {
    error_log("Toggle branch status error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.'
    ]);
}

ob_end_flush();
?>