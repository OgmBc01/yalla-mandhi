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

$connection->begin_transaction();

try {
    // Generate order number
    $date_prefix = date('Ymd');
    $order_number = generateOrderNumber($connection, $date_prefix);
    
    // Handle customer
    $customer_id = null;
    if (!empty($data['customer']['phone'])) {
        $customer_id = findOrCreateCustomer($connection, $data['customer']);
    }
    
    // Calculate totals
    $subtotal = $data['subtotal'] ?? 0;
    $tax = $data['tax'] ?? 0;
    $delivery_fee = $data['delivery_fee'] ?? 0;
    $discount = $data['discount'] ?? 0;
    $total = $data['total'] ?? ($subtotal + $tax + $delivery_fee - $discount);
    
    // Insert order
    $order_sql = "INSERT INTO orders (
        order_number, order_type, delivery_source, customer_id,
        customer_name_snapshot, customer_phone_snapshot, delivery_address_snapshot,
        table_number, subtotal, tax_amount, delivery_fee, discount_amount,
        total_amount, payment_method, payment_status, order_status,
        punched_by_admin_id, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = $connection->prepare($order_sql);
    
    $order_type = $data['type'];
    $delivery_source = $data['delivery_source'] ?? 'internal';
    $customer_name_snapshot = $data['customer']['name'] ?? 'Guest';
    $customer_phone_snapshot = $data['customer']['phone'] ?? '';
    $delivery_address_snapshot = $data['customer']['address'] ?? '';
    $table_number = $data['table_number'] ?? null;
    $payment_method = $data['payment_method'] ?? 'cash';
    $payment_status = $payment_method === 'vendor_debit' ? 'vendor_settled' : 'pending';
    $order_status = $data['type'] === 'delivery' ? 'pending' : 'confirmed';
    $punched_by_admin_id = $_SESSION['user_id'];
    
    $stmt->bind_param(
        "sssissssddddddsssi",
        $order_number, $order_type, $delivery_source, $customer_id,
        $customer_name_snapshot, $customer_phone_snapshot, $delivery_address_snapshot,
        $table_number, $subtotal, $tax, $delivery_fee, $discount,
        $total, $payment_method, $payment_status, $order_status,
        $punched_by_admin_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order: " . $stmt->error);
    }
    
    $order_id = $stmt->insert_id;
    $stmt->close();
    
    // Insert order items
    $item_sql = "INSERT INTO order_items (
        order_id, menu_item_id, item_name_snapshot,
        quantity, unit_price_snapshot, total_price,
        special_instructions
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $item_stmt = $connection->prepare($item_sql);
    
    foreach ($data['items'] as $item) {
        $total_price = $item['price'] * $item['quantity'];
        $instructions = $item['instructions'] ?? '';
        
        $item_stmt->bind_param(
            "iisidds",
            $order_id, $item['id'], $item['name'],
            $item['quantity'], $item['price'], $total_price,
            $instructions
        );
        
        if (!$item_stmt->execute()) {
            throw new Exception("Failed to add order item: " . $item_stmt->error);
        }
        
        // Update inventory if tracking
        updateInventoryForItem($connection, $item['id'], $item['quantity']);
    }
    
    $item_stmt->close();
    
    // Create audit log
    logAudit($connection, $_SESSION['user_id'], 'create', 'order', $order_id, null, json_encode($data));
    
    $connection->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'order_number' => $order_number,
        'print_kitchen' => in_array($order_status, ['confirmed', 'in_preparation']),
        'message' => 'Order placed successfully'
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function generateOrderNumber($connection, $prefix) {
    $query = "SELECT COUNT(*) as count FROM orders WHERE order_number LIKE ? AND DATE(created_at) = CURDATE()";
    $stmt = $connection->prepare($query);
    $search = $prefix . '%';
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1;
    $stmt->close();
    
    return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function findOrCreateCustomer($connection, $customer_data) {
    // Check if customer exists by phone
    $stmt = $connection->prepare("SELECT id FROM users WHERE phone = ? AND role = 'customer'");
    $stmt->bind_param("s", $customer_data['phone']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['id'];
    }
    
    $stmt->close();
    
    // Create new customer
    $username = 'cust_' . uniqid();
    $password_hash = password_hash(uniqid(), PASSWORD_DEFAULT); // Random password, they can reset later
    
    $insert = $connection->prepare(
        "INSERT INTO users (username, full_name, phone, address, role, password_hash, is_active, created_at) 
         VALUES (?, ?, ?, ?, 'customer', ?, 1, NOW())"
    );
    $insert->bind_param("sssss", $username, $customer_data['name'], $customer_data['phone'], 
                        $customer_data['address'], $password_hash);
    $insert->execute();
    $new_id = $insert->insert_id;
    $insert->close();
    
    return $new_id;
}

function updateInventoryForItem($connection, $menu_item_id, $quantity) {
    // Check if menu item tracks inventory
    $stmt = $connection->prepare("SELECT track_inventory, available_quantity FROM menu_items WHERE id = ?");
    $stmt->bind_param("i", $menu_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
    
    if ($item && $item['track_inventory']) {
        $new_quantity = $item['available_quantity'] - $quantity;
        $update = $connection->prepare("UPDATE menu_items SET available_quantity = ? WHERE id = ?");
        $update->bind_param("ii", $new_quantity, $menu_item_id);
        $update->execute();
        $update->close();
        
        // If auto_unavailable is true and quantity becomes 0, mark as unavailable
        if ($new_quantity <= 0) {
            $auto = $connection->prepare("UPDATE menu_items SET is_available = 0 WHERE id = ? AND auto_unavailable = 1");
            $auto->bind_param("i", $menu_item_id);
            $auto->execute();
            $auto->close();
        }
    }
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