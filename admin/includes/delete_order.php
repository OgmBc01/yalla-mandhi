<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Auth check (only admin and super-admin can delete)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// Check if order can be deleted (only draft or cancelled)
$check = $connection->prepare("SELECT order_status FROM orders WHERE id = ?");
$check->bind_param("i", $order_id);
$check->execute();
$result = $check->get_result();
$order = $result->fetch_assoc();
$check->close();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

if (!in_array($order['order_status'], ['draft', 'cancelled'])) {
    echo json_encode(['success' => false, 'message' => 'Only draft or cancelled orders can be deleted']);
    exit;
}

$connection->begin_transaction();

try {
    // Log before deletion
    logAudit($connection, $_SESSION['user_id'], 'delete', 'order', $order_id, json_encode($order), null);
    
    // Delete order items first
    $delete_items = $connection->prepare("DELETE FROM order_items WHERE order_id = ?");
    $delete_items->bind_param("i", $order_id);
    $delete_items->execute();
    $delete_items->close();
    
    // Delete order
    $delete_order = $connection->prepare("DELETE FROM orders WHERE id = ?");
    $delete_order->bind_param("i", $order_id);
    $delete_order->execute();
    $delete_order->close();
    
    $connection->commit();
    
    echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to delete order: ' . $e->getMessage()]);
}

function logAudit($connection, $user_id, $action, $entity_type, $entity_id, $old_value, $new_value) {
    $stmt = $connection->prepare(
        "INSERT INTO audit_logs (user_id, action_type, entity_type, entity_id, old_value, new_value, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param("ississ", $user_id, $action, $entity_type, $entity_id, $old_value, $new_value);
    $stmt->execute();
    $stmt->close();
}