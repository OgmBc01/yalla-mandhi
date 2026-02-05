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

// Validate item ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
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

$item_id = (int)$_GET['id'];

// Fetch item details with category information
$query = "SELECT mi.*, mc.name as category_name 
          FROM menu_items mi 
          LEFT JOIN menu_categories mc ON mi.category_id = mc.id 
          WHERE mi.id = $item_id";
$result = mysqli_query($connection, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $item = mysqli_fetch_assoc($result);
    // Ensure all fields exist
    $item = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $item);
    
    echo json_encode([
        'success' => true,
        'item' => $item
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Menu item not found'
    ]);
}

if ($result) {
    mysqli_free_result($result);
}
ob_end_flush();
?>