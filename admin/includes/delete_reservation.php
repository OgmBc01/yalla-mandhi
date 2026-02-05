<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set custom error handlers
set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($errno, $errstr) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        exit;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
$database_paths = [
    __DIR__ . '/../../includes/database.php',
    __DIR__ . '/../includes/database.php',
    'includes/database.php'
];

$connection = null;
foreach ($database_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        if (function_exists('getDBConnection')) {
            $connection = getDBConnection();
            if ($connection) break;
        }
    }
}

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate reservation ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID']);
    exit;
}

$reservation_id = (int)$_GET['id'];

// First, check if reservation exists
$check_query = "SELECT id, customer_name FROM reservations WHERE id = ?";
$check_stmt = $connection->prepare($check_query);
$check_stmt->bind_param("i", $reservation_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if (!$check_result || $check_result->num_rows === 0) {
    $check_stmt->close();
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit;
}

$reservation = $check_result->fetch_assoc();
$check_stmt->close();

// Delete the reservation
$delete_query = "DELETE FROM reservations WHERE id = ?";
$delete_stmt = $connection->prepare($delete_query);
$delete_stmt->bind_param("i", $reservation_id);

if ($delete_stmt->execute()) {
    $delete_stmt->close();
    echo json_encode([
        'success' => true, 
        'message' => "Reservation #{$reservation_id} for {$reservation['customer_name']} deleted successfully"
    ]);
} else {
    $error = $connection->error;
    $delete_stmt->close();
    echo json_encode(['success' => false, 'message' => 'Failed to delete reservation: ' . $error]);
}

ob_end_flush();
?>