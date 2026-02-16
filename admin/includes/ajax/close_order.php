<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check (only admin and super-admin can close orders)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$connection->begin_transaction();

try {
    // Check if order can be closed
    $stmt = $connection->prepare(
        "SELECT order_status, closed_at, invoice_number FROM orders WHERE id = ?"
    );
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if ($order['closed_at']) {
        throw new Exception("Order already closed");
    }
    
    if ($order['order_status'] != 'completed') {
        throw new Exception("Only completed orders can be closed");
    }
    
    // Generate invoice number
    $date_prefix = date('Ymd');
    $invoice_number = 'INV-' . $date_prefix . '-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
    
    // Close the order
    $update = $connection->prepare(
        "UPDATE orders SET 
            order_status = 'closed', 
            closed_at = NOW(), 
            closed_by_admin_id = ?,
            invoice_number = ?,
            last_updated_by = ?,
            updated_at = NOW() 
         WHERE id = ?"
    );
    $update->bind_param("isii", $_SESSION['user_id'], $invoice_number, $_SESSION['user_id'], $order_id);
    $update->execute();
    $update->close();
    
    // Log the closure
    logAudit($connection, $_SESSION['user_id'], 'close', 'order', $order_id, 
             json_encode($order), json_encode(['status' => 'closed', 'invoice' => $invoice_number]));
    
    $connection->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order closed successfully',
        'invoice_number' => $invoice_number
    ]);
    
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