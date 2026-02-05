<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

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

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate input
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID']);
    exit;
}

if (!isset($_POST['status']) || !in_array($_POST['status'], ['pending', 'confirmed', 'cancelled', 'completed'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$reservation_id = (int)$_POST['id'];
$status = mysqli_real_escape_string($connection, $_POST['status']);

// Update reservation status
$query = "UPDATE reservations SET status = '$status', updated_at = NOW() WHERE id = $reservation_id";
$result = mysqli_query($connection, $query);

if ($result && mysqli_affected_rows($connection) > 0) {
    echo json_encode(['success' => true, 'message' => 'Reservation status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update reservation status']);
}
ob_end_flush();
?>