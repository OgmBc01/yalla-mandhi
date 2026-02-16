<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';
$notes = isset($_POST['notes']) ? $_POST['notes'] : '';

if (!$order_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$connection->begin_transaction();

try {
    // Get current order status
    $stmt = $connection->prepare("SELECT order_status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $old_status = $order['order_status'];
    $stmt->close();
    
    // Update order status
    $update = $connection->prepare(
        "UPDATE orders SET order_status = ?, last_updated_by = ?, updated_at = NOW() 
         WHERE id = ?"
    );
    $update->bind_param("sii", $new_status, $_SESSION['user_id'], $order_id);
    $update->execute();
    $update->close();
    
    // Log the change
    logAudit($connection, $_SESSION['user_id'], 'status_change', 'order', $order_id, 
             $old_status, $new_status);
    
    // If status changed to confirmed, print kitchen receipt
    if ($new_status == 'confirmed') {
        // Trigger kitchen print (you might want to queue this)
        // We'll just log it for now
        $print_log = $connection->prepare(
            "INSERT INTO printer_logs (order_id, receipt_type, printed_by) VALUES (?, 'kitchen', ?)"
        );
        $print_log->bind_param("ii", $order_id, $_SESSION['user_id']);
        $print_log->execute();
        $print_log->close();
    }
    
    $connection->commit();
    
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    
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