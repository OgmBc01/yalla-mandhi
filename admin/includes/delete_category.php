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

require_once __DIR__ . '/../../includes/database.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate category ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

$category_id = (int)$_GET['id'];

// Get database connection
$connection = getDBConnection();
if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// First, check if category exists
$check_query = "SELECT id, name FROM menu_categories WHERE id = ?";
$check_stmt = $connection->prepare($check_query);
$check_stmt->bind_param("i", $category_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if (!$check_result || $check_result->num_rows === 0) {
    $check_stmt->close();
    echo json_encode(['success' => false, 'message' => 'Category not found']);
    exit;
}

$category = $check_result->fetch_assoc();
$check_stmt->close();

// Check if category has menu items
$items_query = "SELECT COUNT(*) as item_count FROM menu_items WHERE category_id = ?";
$items_stmt = $connection->prepare($items_query);
$items_stmt->bind_param("i", $category_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items_data = $items_result->fetch_assoc();
$item_count = $items_data['item_count'];
$items_stmt->close();

// If category has items, delete them first (with their images)
if ($item_count > 0) {
    // Get all items in this category with their images
    $items_query = "SELECT id, image_url FROM menu_items WHERE category_id = ?";
    $items_stmt = $connection->prepare($items_query);
    $items_stmt->bind_param("i", $category_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    while ($item = $items_result->fetch_assoc()) {
        // Delete item image if it exists
        if (!empty($item['image_url']) && $item['image_url'] !== 'null') {
            $image_path = __DIR__ . '/../../uploads/menu/' . $item['image_url'];
            if (file_exists($image_path)) {
                @unlink($image_path);
            }
        }
        
        // Delete the menu item
        $delete_item_query = "DELETE FROM menu_items WHERE id = ?";
        $delete_item_stmt = $connection->prepare($delete_item_query);
        $delete_item_stmt->bind_param("i", $item['id']);
        $delete_item_stmt->execute();
        $delete_item_stmt->close();
    }
    $items_stmt->close();
}

// Delete the category
$delete_query = "DELETE FROM menu_categories WHERE id = ?";
$delete_stmt = $connection->prepare($delete_query);
$delete_stmt->bind_param("i", $category_id);

if ($delete_stmt->execute()) {
    $delete_stmt->close();
    
    $message = $item_count > 0 
        ? "Category '{$category['name']}' and its $item_count item(s) deleted successfully!" 
        : "Category '{$category['name']}' deleted successfully!";
    
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    $error = $connection->error;
    $delete_stmt->close();
    echo json_encode(['success' => false, 'message' => 'Failed to delete category: ' . $error]);
}

ob_end_flush();
?>