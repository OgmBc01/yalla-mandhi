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

// Validate customer ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit;
}

$customer_id = (int)$_GET['id'];

// Fetch customer details
$query = "SELECT * FROM users WHERE id = $customer_id AND role = 'customer'";
$result = mysqli_query($connection, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $customer = mysqli_fetch_assoc($result);
    
    // Ensure all fields exist
    $customer = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $customer);
    
    // Remove sensitive data
    unset($customer['password_hash']);
    unset($customer['reset_token']);
    unset($customer['reset_token_expiry']);
    
    echo json_encode([
        'success' => true,
        'customer' => $customer
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Customer not found'
    ]);
}

if ($result) {
    mysqli_free_result($result);
}
ob_end_flush();
?>