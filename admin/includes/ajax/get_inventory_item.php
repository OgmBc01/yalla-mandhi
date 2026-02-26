<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager', 'employee'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate input
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

// Fetch item details - using your actual column names
$query = "SELECT i.*, 
                 COUNT(t.id) as transaction_count,
                 MAX(t.created_at) as last_transaction
          FROM inventory_items i
          LEFT JOIN inventory_transactions t ON i.id = t.inventory_item_id
          WHERE i.id = ?
          GROUP BY i.id";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

$item = $result->fetch_assoc();
$stmt->close();

// Format response with proper column names
$response_item = [
    'id' => $item['id'],
    'item_name' => $item['item_name'],
    'sku' => $item['sku'] ?? '',
    'unit_of_measure' => $item['unit_of_measure'],
    'quantity_available' => floatval($item['quantity_available']), // Use actual column name
    'quantity' => floatval($item['quantity_available']), // Alias for backward compatibility
    'reorder_level' => floatval($item['reorder_level'] ?? 0),
    'cost_per_unit' => floatval($item['unit_price'] ?? 0), // Your table uses unit_price, not cost_per_unit
    'unit_price' => floatval($item['unit_price'] ?? 0),
    'supplier' => $item['supplier'] ?? '',
    'description' => $item['description'] ?? '',
    'category' => $item['category'] ?? '',
    'location' => $item['location'] ?? '',
    'is_active' => (bool)$item['is_active'],
    'transaction_count' => intval($item['transaction_count'] ?? 0),
    'last_transaction' => $item['last_transaction'] ?? null
];

echo json_encode([
    'success' => true,
    'item' => $response_item
]);
?>