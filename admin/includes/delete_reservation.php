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

// Ensure $connection is set
if (!isset($connection) || !$connection) {
    if (function_exists('getDBConnection')) {
        $connection = getDBConnection();
    } else {
        require_once dirname(__DIR__, 2) . '/includes/database.php';
        $connection = getDBConnection();
    }
}

$reservation_id = (int)$_GET['id'];

// First, check if reservation exists
$check_query = "SELECT id, customer_name FROM reservations WHERE id = $reservation_id";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit;
}

// Delete the reservation
$query = "DELETE FROM reservations WHERE id = $reservation_id";
$result = mysqli_query($connection, $query);

if ($result && mysqli_affected_rows($connection) > 0) {
    echo json_encode(['success' => true, 'message' => 'Reservation deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete reservation. Please try again.']);
}

if ($check_result) {
    mysqli_free_result($check_result);
}
ob_end_flush();
?>