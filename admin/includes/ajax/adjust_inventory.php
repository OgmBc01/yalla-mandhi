<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check (only admin and super-admin can adjust inventory)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Only administrators can adjust inventory.']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Log received data for debugging
error_log("Adjust inventory data: " . print_r($data, true));

// Validate required fields
$item_id = isset($data['item_id']) ? (int)$data['item_id'] : 0;
$type = $data['type'] ?? '';
$quantity = isset($data['quantity']) ? floatval($data['quantity']) : 0;
$unit_cost = isset($data['unit_cost']) ? floatval($data['unit_cost']) : 0;
$reference = $data['reference'] ?? '';
$notes = $data['notes'] ?? '';

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

if (empty($type)) {
    echo json_encode(['success' => false, 'message' => 'Adjustment type is required']);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be greater than zero']);
    exit;
}

$connection->begin_transaction();

try {
    // Get current item details - using your actual column names
    $stmt = $connection->prepare("SELECT id, item_name, unit_of_measure, quantity_available, unit_price FROM inventory_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Item not found");
    }
    
    $item = $result->fetch_assoc();
    $current_qty = floatval($item['quantity_available']);
    $stmt->close();
    
    // Calculate new quantity based on type
    $new_qty = $current_qty;
    $transaction_qty = $quantity;
    
    switch ($type) {
        case 'purchase':
        case 'return':
            $new_qty = $current_qty + $quantity;
            break;
            
        case 'usage':
        case 'damage':
            $new_qty = $current_qty - $quantity;
            $transaction_qty = -$quantity;
            break;
            
        case 'adjustment':
            // For manual adjustment, quantity is the new total
            $new_qty = $quantity;
            $transaction_qty = $new_qty - $current_qty;
            break;
            
        default:
            throw new Exception("Invalid adjustment type");
    }
    
    if ($new_qty < 0) {
        throw new Exception("Cannot reduce stock below zero. Current stock: {$current_qty} {$item['unit_of_measure']}");
    }
    
    // Update inventory_items quantity
    $update_stmt = $connection->prepare("UPDATE inventory_items SET quantity_available = ? WHERE id = ?");
    $update_stmt->bind_param("di", $new_qty, $item_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update inventory: " . $update_stmt->error);
    }
    $update_stmt->close();
    
    // Insert transaction record
    $trans_stmt = $connection->prepare(
        "INSERT INTO inventory_transactions (
            inventory_item_id, transaction_type, quantity, unit_cost,
            previous_quantity, new_quantity, reference_id, notes, performed_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    $user_id = $_SESSION['user_id'];
    $trans_stmt->bind_param(
        "isddddsi",
        $item_id, $type, $transaction_qty, $unit_cost,
        $current_qty, $new_qty, $reference, $notes, $user_id
    );
    
    if (!$trans_stmt->execute()) {
        throw new Exception("Failed to record transaction: " . $trans_stmt->error);
    }
    
    $trans_id = $trans_stmt->insert_id;
    $trans_stmt->close();
    
    $connection->commit();
    
    // Prepare success message
    $action = '';
    switch ($type) {
        case 'purchase':
            $action = 'added to';
            break;
        case 'usage':
            $action = 'removed from';
            break;
        case 'adjustment':
            $action = 'adjusted to';
            break;
        case 'damage':
            $action = 'written off from';
            break;
        case 'return':
            $action = 'returned to';
            break;
    }
    
    $message = "Stock {$action} {$item['item_name']}. New quantity: {$new_qty} {$item['unit_of_measure']}";
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'transaction_id' => $trans_id,
        'new_quantity' => $new_qty,
        'previous_quantity' => $current_qty,
        'change' => $transaction_qty
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>