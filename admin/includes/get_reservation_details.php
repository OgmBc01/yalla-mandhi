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

// Validate reservation ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID']);
    exit;
}

$reservation_id = (int)$_GET['id'];

// Fetch reservation details with branch information
$query = "SELECT r.*, b.name 
          FROM reservations r 
          LEFT JOIN branches b ON r.branch_id = b.id 
          WHERE r.id = $reservation_id";
$result = mysqli_query($connection, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $reservation = mysqli_fetch_assoc($result);
    // Ensure all fields exist
    $reservation = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $reservation);
    
    echo json_encode([
        'success' => true,
        'reservation' => $reservation
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Reservation not found'
    ]);
}

if ($result) {
    mysqli_free_result($result);
}
ob_end_flush();
?>