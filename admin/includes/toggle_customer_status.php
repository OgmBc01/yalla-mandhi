<?php
ob_start();
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate inputs
if (!isset($_POST['id']) || !ctype_digit($_POST['id']) || 
    !isset($_POST['status']) || !in_array($_POST['status'], ['0', '1'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$customer_id = (int) $_POST['id'];
$new_status = (int) $_POST['status'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $stmt = $connection->prepare(
        "UPDATE users SET is_active = ? WHERE id = ? AND role = 'customer'"
    );
    $stmt->bind_param("ii", $new_status, $customer_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Customer not found or already has this status']);
    } else {
        $status_text = $new_status ? 'activated' : 'deactivated';
        echo json_encode([
            'success' => true,
            'message' => 'Customer ' . $status_text . ' successfully'
        ]);
    }
    
    $stmt->close();
    
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}
?>