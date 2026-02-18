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

$order_id = $data['order_id'] ?? '';
$payment_method = $data['payment_method'] ?? '';
$payment_reference = $data['payment_reference'] ?? '';
$amount_paid = floatval($data['amount_paid'] ?? 0);
$discount_amount = floatval($data['discount_amount'] ?? 0);
$discount_type = $data['discount_type'] ?? 'fixed';
$user_id = $_SESSION['user_id'];

if (!$order_id || !$payment_method) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$connection->begin_transaction();

try {
    // Generate order number and invoice number
    $date_prefix = date('Ymd');
    $order_number = generateOrderNumber($connection, $date_prefix);
    $invoice_number = 'INV-' . $date_prefix . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Get order data from drafts
    $stmt = $connection->prepare("SELECT data FROM pos_order_drafts WHERE id = ?");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $draft = $result->fetch_assoc();
    $stmt->close();
    
    if (!$draft) {
        throw new Exception("Order draft not found");
    }
    
    $order_data = json_decode($draft['data'], true);
    
    // Calculate financials
    $subtotal = 0;
    foreach ($order_data['items'] as $item) {
        $subtotal += $item['qty'] * $item['price'];
    }
    
    // Apply discount
    if ($discount_type === 'percentage') {
        $discount = ($subtotal * $discount_amount) / 100;
    } else {
        $discount = min($discount_amount, $subtotal);
    }
    
    $taxable_amount = $subtotal - $discount;
    $tax = $taxable_amount * 0.15; // 15% tax
    $delivery_fee = ($order_data['type'] === 'delivery') ? 10 : 0;
    $total = $taxable_amount + $tax + $delivery_fee;
    
    // Handle customer (find or create)
    $customer_id = null;
    if (!empty($order_data['customer']['phone'])) {
        $customer_id = findOrCreateCustomer($connection, $order_data['customer']);
    }
    
    // Insert into orders table
    $order_sql = "INSERT INTO orders (
        order_number, invoice_number, customer_id, customer_name, customer_phone, customer_address,
        order_type, delivery_source, table_number, subtotal, discount_amount, tax_amount, delivery_fee, total_amount, item_count,
        order_status, payment_method, payment_status, payment_reference,
        punched_by_admin_id, closed_by_admin_id, closed_at, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";
    
    $stmt = $connection->prepare($order_sql);
    
    $customer_name = $order_data['customer']['name'] ?? 'Guest';
    $customer_phone = $order_data['customer']['phone'] ?? '';
    $customer_address = $order_data['customer']['address'] ?? '';
    $invoice_number = $invoice_number ?? '';
    $delivery_source = $order_data['delivery_source'] ?? 'internal';
    $table_number = $order_data['table_number'] ?? '';
    $subtotal = $subtotal ?? 0.00;
    $discount = $discount ?? 0.00;
    $tax = $tax ?? 0.00;
    $delivery_fee = $delivery_fee ?? 0.00;
    $item_count = count($order_data['items'] ?? []);
    $valid_order_types = ['delivery', 'pickup', 'dine_in'];
    $order_type = isset($order_data['type']) && in_array(strval($order_data['type']), $valid_order_types) ? strval($order_data['type']) : 'dine_in';
    $valid_order_statuses = ['draft','pending','confirmed','in_preparation','ready','out_for_delivery','completed','cancelled','refunded','closed'];
    $order_status = 'closed';
    $payment_reference = $payment_reference ?? '';
    $punched_by_admin_id = $user_id;
    $closed_by_admin_id = $user_id;
    
    $stmt->bind_param(
        "ssssissssssddddissssii",
        $order_number,
        $invoice_number,
        $customer_id,
        $customer_name,
        $customer_phone,
        $customer_address,
        $order_type,
        $delivery_source,
        $table_number,
        $subtotal,
        $discount,
        $tax,
        $delivery_fee,
        $total,
        $item_count,
        $order_status,
        $payment_method,
        $payment_status,
        $payment_reference,
        $punched_by_admin_id,
        $closed_by_admin_id
    );
        $payment_status = 'paid';
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order: " . $stmt->error);
    }
    
    $new_order_id = $stmt->insert_id;
    $stmt->close();
    
    // Insert order items
    $item_sql = "INSERT INTO order_items (
        order_id, menu_item_id, item_name_snapshot,
        quantity, unit_price_snapshot, total_price,
        special_instructions
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $item_stmt = $connection->prepare($item_sql);
    
    foreach ($order_data['items'] as $item) {
        $total_price = $item['qty'] * $item['price'];
        $instructions = $item['instructions'] ?? '';
        $menu_item_id = isset($item['id']) && is_numeric($item['id']) ? $item['id'] : null;
        
        $item_stmt->bind_param(
            "iisidds",
            $new_order_id, $menu_item_id, $item['name'],
            $item['qty'], $item['price'], $total_price,
            $instructions
        );
        
        if (!$item_stmt->execute()) {
            throw new Exception("Failed to add order item: " . $item_stmt->error);
        }
        
        // Update inventory if tracking
        if ($menu_item_id) {
            updateInventoryForItem($connection, $menu_item_id, $item['qty']);
        }
    }
    
    $item_stmt->close();
    
    // Delete the draft (soft delete)
    $delete_draft = $connection->prepare("DELETE FROM pos_order_drafts WHERE id = ?");
    $delete_draft->bind_param("s", $order_id);
    $delete_draft->execute();
    $delete_draft->close();
    
    // Create audit log
    logAudit($connection, $user_id, 'create', 'order', $new_order_id, null, json_encode([
        'order_number' => $order_number,
        'invoice_number' => $invoice_number,
        'total' => $total,
        'payment_method' => $payment_method
    ]));
    
    $connection->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $new_order_id,
        'order_number' => $order_number,
        'invoice_number' => $invoice_number,
        'total' => $total,
        'message' => 'Order saved successfully'
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    // Output detailed error for debugging
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
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
    if (empty($customer_data['phone'])) return null;
    
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
    $password_hash = password_hash(uniqid(), PASSWORD_DEFAULT);
    $full_name = $customer_data['name'] ?? 'Guest Customer';
    $phone = $customer_data['phone'];
    $address = $customer_data['address'] ?? '';
    $email = $customer_data['email'] ?? ("auto_" . uniqid() . "@noemail.local");
    
    $insert = $connection->prepare(
        "INSERT INTO users (username, full_name, phone, address, email, role, password_hash, is_active, created_at) 
         VALUES (?, ?, ?, ?, ?, 'customer', ?, 1, NOW())"
    );
    $insert->bind_param("ssssss", $username, $full_name, $phone, $address, $email, $password_hash);
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
        
        // If auto_unavailable is true and quantity becomes 0 or less, mark as unavailable
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