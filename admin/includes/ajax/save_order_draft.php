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

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// If order_id exists, update draft, otherwise create new draft
if (!empty($data['id'])) {
    $order_id = $data['id'];
    
    // Update existing draft
    $connection->begin_transaction();
    
    try {
        // Update order basic info
        $update_sql = "UPDATE orders SET 
            order_type = ?, delivery_source = ?, table_number = ?,
            customer_name_snapshot = ?, customer_phone_snapshot = ?, delivery_address_snapshot = ?,
            subtotal = ?, tax_amount = ?, delivery_fee = ?, discount_amount = ?, total_amount = ?,
            payment_method = ?, updated_at = NOW(), last_updated_by = ?
            WHERE id = ? AND order_status = 'draft'";
        
        $stmt = $connection->prepare($update_sql);
        $delivery_fee = isset($data['delivery_fee']) ? floatval($data['delivery_fee']) : 0;
        $tax_amount = 0;
        $stmt->bind_param(
            "ssssssdddddsii",
            $data['type'], $data['delivery_source'], $data['table_number'],
            $data['customer']['name'], $data['customer']['phone'], $data['customer']['address'],
            $data['subtotal'], $tax_amount, $delivery_fee, $data['discount'], $data['total'],
            $data['payment_method'], $_SESSION['user_id'], $order_id
        );
        $stmt->execute();
        $stmt->close();
        
        // Delete existing items
        $delete = $connection->prepare("DELETE FROM order_items WHERE order_id = ?");
        $delete->bind_param("i", $order_id);
        $delete->execute();
        $delete->close();
        
        // Insert current items
        $item_sql = "INSERT INTO order_items (order_id, menu_item_id, item_name_snapshot, quantity, unit_price_snapshot, total_price, special_instructions) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        $item_stmt = $connection->prepare($item_sql);
        
        foreach ($data['items'] as $item) {
            $total = $item['price'] * $item['quantity'];
            $item_stmt->bind_param(
                "iisidds",
                $order_id, $item['id'], $item['name'],
                $item['quantity'], $item['price'], $total,
                $item['instructions']
            );
            $item_stmt->execute();
        }
        
        $item_stmt->close();
        $connection->commit();
        
        echo json_encode(['success' => true, 'order_id' => $order_id]);
        
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
} else {
    // Create new draft
    $connection->begin_transaction();
    
    try {
        // Generate temp order number for draft
        $order_number = 'DRFT' . date('YmdHis');
        
        $insert_sql = "INSERT INTO orders (
            order_number, order_type, delivery_source, table_number,
            customer_name_snapshot, customer_phone_snapshot, delivery_address_snapshot,
            subtotal, tax_amount, delivery_fee, discount_amount, total_amount,
            payment_method, order_status, punched_by_admin_id, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, NOW(), NOW())";
        
        $stmt = $connection->prepare($insert_sql);
        $delivery_fee = isset($data['delivery_fee']) ? floatval($data['delivery_fee']) : 0;
        $tax_amount = 0;
        $stmt->bind_param(
            "sssssssddddddsi",
            $order_number, $data['type'], $data['delivery_source'], $data['table_number'],
            $data['customer']['name'], $data['customer']['phone'], $data['customer']['address'],
            $data['subtotal'], $tax_amount, $delivery_fee, $data['discount'], $data['total'],
            $data['payment_method'], $_SESSION['user_id']
        );
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
        
        // Insert items
        if (!empty($data['items'])) {
            $item_sql = "INSERT INTO order_items (order_id, menu_item_id, item_name_snapshot, quantity, unit_price_snapshot, total_price, special_instructions) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
            $item_stmt = $connection->prepare($item_sql);
            
            foreach ($data['items'] as $item) {
                $total = $item['price'] * $item['quantity'];
                $item_stmt->bind_param(
                    "iisidds",
                    $order_id, $item['id'], $item['name'],
                    $item['quantity'], $item['price'], $total,
                    $item['instructions']
                );
                $item_stmt->execute();
            }
            
            $item_stmt->close();
        }
        
        $connection->commit();
        
        echo json_encode(['success' => true, 'order_id' => $order_id]);
        
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}