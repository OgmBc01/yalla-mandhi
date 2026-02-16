<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'cashier'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
$payment_reference = isset($_POST['payment_reference']) ? $_POST['payment_reference'] : '';

if (!$order_id || !$payment_status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$connection->begin_transaction();

try {
    // Get current payment status
    $stmt = $connection->prepare("SELECT payment_status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $old_status = $order['payment_status'];
    $stmt->close();
    
    // Update payment status
    $update = $connection->prepare(
        "UPDATE orders SET payment_status = ?, payment_reference = ?, last_updated_by = ?, updated_at = NOW() 
         WHERE id = ?"
    );
    $update->bind_param("ssii", $payment_status, $payment_reference, $_SESSION['user_id'], $order_id);
    $update->execute();
    $update->close();
    
    // Log the change
    logAudit($connection, $_SESSION['user_id'], 'payment_update', 'order', $order_id, 
             $old_status, $payment_status);
    
    $connection->commit();
    
    echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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