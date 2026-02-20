<?php
session_start();
require_once "../includes/database.php";

// Check if user has permission
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$receipt_type = isset($_GET['type']) ? $_GET['type'] : 'counter'; // kitchen or counter

if (!$order_id) {
    die("Invalid order");
}

// Fetch order details
$query = "SELECT o.*, 
                 u1.full_name as punched_by_name,
                 b.name as branch_name
          FROM orders o
          LEFT JOIN users u1 ON o.punched_by_admin_id = u1.id
          LEFT JOIN branches b ON o.branch_id = b.id
          WHERE o.id = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found");
}

$order = $result->fetch_assoc();
$stmt->close();

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $connection->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Log the print
$log_stmt = $connection->prepare(
    "INSERT INTO printer_logs (order_id, receipt_type, printed_by, is_reprint) 
     VALUES (?, ?, ?, ?)"
);
$is_reprint = isset($_GET['reprint']) ? 1 : 0;
$log_stmt->bind_param("isii", $order_id, $receipt_type, $_SESSION['user_id'], $is_reprint);
$log_stmt->execute();
$log_stmt->close();

// Set content type for thermal printer (text/plain for ESC/POS)
header('Content-Type: text/plain; charset=utf-8');

// Restaurant info
$restaurant_name = "YALLA AL MANDI";
$restaurant_phone = "+966 XX XXX XXXX";
$restaurant_vat = "VAT: 123456789";
$address = "Restaurant Address Line";

// Generate receipt content based on type
if ($receipt_type == 'kitchen') {
    // KITCHEN RECEIPT - No prices, no totals
    echo str_repeat("=", 42) . "\n";
    echo "         KITCHEN COPY\n";
    echo str_repeat("=", 42) . "\n";
    echo "$restaurant_name\n";
    echo "FOR KITCHEN USE ONLY\n";
    echo str_repeat("-", 42) . "\n";
    
    echo "Order #: " . $order['order_number'] . "\n";
    echo "Date: " . date('d/m/Y H:i', strtotime($order['created_at'])) . "\n";
    echo "Type: " . strtoupper(str_replace('_', ' ', $order['order_type'])) . "\n";
    
    if ($order['order_type'] == 'dine_in' && $order['table_number']) {
        echo "Table: " . $order['table_number'] . "\n";
    }
    
    if ($order['order_type'] == 'delivery' && $order['delivery_source'] == 'internal') {
        echo "Delivery to:\n" . wordwrap($order['delivery_address_snapshot'] ?? '', 40, "\n") . "\n";
    }
    
    if ($order['delivery_source'] != 'internal') {
        echo "Vendor: " . strtoupper($order['delivery_source']) . "\n";
    }
    
    echo str_repeat("-", 42) . "\n";
    echo "ITEMS:\n";
    
    $items_result->data_seek(0);
    while ($item = $items_result->fetch_assoc()) {
        echo sprintf("%dx %s\n", $item['quantity'], $item['item_name_snapshot']);
        if (!empty($item['special_instructions'])) {
            echo "  * " . $item['special_instructions'] . "\n";
        }
    }
    
    echo str_repeat("=", 42) . "\n";
    echo "         KITCHEN COPY\n";
    echo str_repeat("=", 42) . "\n";
    
} else {
    // COUNTER / CUSTOMER RECEIPT
    echo str_repeat("=", 42) . "\n";
    echo "        " . $restaurant_name . "\n";
    echo str_repeat("=", 42) . "\n";
    
    // Invoice info
    $invoice_number = $order['invoice_number'] ?? 'INV-' . str_pad($order['id'], 8, '0', STR_PAD_LEFT);
    echo "Invoice: " . $invoice_number . "\n";
    echo "Order #: " . $order['order_number'] . "\n";
    echo "Date: " . date('d/m/Y H:i', strtotime($order['created_at'])) . "\n";
    echo "Cashier: " . ($order['punched_by_name'] ?? 'N/A') . "\n";
    echo str_repeat("-", 42) . "\n";
    
    // Customer info
    echo "Customer: " . ($order['customer_name_snapshot'] ?? 'Guest') . "\n";
    if ($order['customer_phone_snapshot']) {
        echo "Phone: " . $order['customer_phone_snapshot'] . "\n";
    }
    
    if ($order['order_type'] == 'delivery' && $order['delivery_source'] == 'internal' && $order['delivery_address_snapshot']) {
        echo "Address:\n" . wordwrap($order['delivery_address_snapshot'], 40, "\n") . "\n";
    }
    
    echo str_repeat("-", 42) . "\n";
    
    // Items
    echo "ITEM                 QTY  PRICE\n";
    echo str_repeat("-", 42) . "\n";
    
    $items_result->data_seek(0);
    while ($item = $items_result->fetch_assoc()) {
        $name = substr($item['item_name_snapshot'], 0, 20);
        echo sprintf("%-20s %3d %7.2f\n", 
            $name, 
            $item['quantity'], 
            $item['unit_price_snapshot']
        );
        if (!empty($item['special_instructions'])) {
            echo "  * " . substr($item['special_instructions'], 0, 35) . "\n";
        }
    }
    
    echo str_repeat("-", 42) . "\n";
    
    // Totals
    echo sprintf("%-30s %10.2f\n", "Subtotal:", $order['subtotal']);


    if ($order['discount_amount'] > 0) {
        echo sprintf("%-30s %10.2f\n", "Discount:", -$order['discount_amount']);
    }
    echo str_repeat("=", 42) . "\n";
    echo sprintf("%-30s %10.2f\n", "TOTAL:", $order['total_amount']);
    echo str_repeat("=", 42) . "\n";
    
    // Payment info
    echo "Payment: " . strtoupper(str_replace('_', ' ', $order['payment_method'])) . "\n";
    echo "Status: " . strtoupper(str_replace('_', ' ', $order['payment_status'])) . "\n";
    
    if ($order['payment_reference']) {
        echo "Ref: " . $order['payment_reference'] . "\n";
    }
    
    echo str_repeat("=", 42) . "\n";
    echo "      THANK YOU FOR YOUR ORDER\n";
    echo "          " . $restaurant_phone . "\n";
    echo "          " . $restaurant_vat . "\n";
    echo str_repeat("=", 42) . "\n";
}

// Add line feeds for paper cutting
echo "\n\n\n\n";

$items_stmt->close();
?>