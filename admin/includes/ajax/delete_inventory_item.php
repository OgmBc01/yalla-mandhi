<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check (only admin and super-admin can delete items)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate input
$item_id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

// Check if item has any transactions
$check_stmt = $connection->prepare("SELECT COUNT(*) as count FROM inventory_transactions WHERE inventory_item_id = ?");
$check_stmt->bind_param("i", $item_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$transaction_count = $check_result->fetch_assoc()['count'];
$check_stmt->close();

if ($transaction_count > 0) {
    // Soft delete - just mark as inactive
    $stmt = $connection->prepare("UPDATE inventory_items SET is_active = 0 WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $action = "deactivated";
} else {
    // Hard delete - no transactions, safe to delete
    $stmt = $connection->prepare("DELETE FROM inventory_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $action = "deleted";
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "Item {$action} successfully",
        'action' => $action
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete item: ' . $connection->error
    ]);
}

$stmt->close();
?>