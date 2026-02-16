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

$statuses = ['pending', 'in_preparation', 'ready'];

$query = "SELECT o.id, o.order_number, o.order_type, o.table_number, 
                 o.created_at, o.order_status,
                 o.customer_name_snapshot
          FROM orders o
          WHERE o.order_status IN ('pending', 'in_preparation', 'ready')
          ORDER BY FIELD(o.order_status, 'pending', 'in_preparation', 'ready'), 
                   o.created_at ASC";

$result = $connection->query($query);
$orders = [];

while ($order = $result->fetch_assoc()) {
    // Get items for this order
    $items_query = "SELECT item_name_snapshot, quantity, special_instructions 
                    FROM order_items WHERE order_id = ?";
    $stmt = $connection->prepare($items_query);
    $stmt->bind_param("i", $order['id']);
    $stmt->execute();
    $items_result = $stmt->get_result();
    
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    $stmt->close();
    
    $order['items'] = $items;
    $orders[] = $order;
}

echo json_encode([
    'success' => true,
    'orders' => $orders
]);