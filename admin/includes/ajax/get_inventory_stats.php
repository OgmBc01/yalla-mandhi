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

// Get summary statistics
$stats = [];

// Total items and value
$query = "SELECT 
    COUNT(*) as total_items,
    SUM(quantity) as total_quantity,
    SUM(quantity * cost_per_unit) as total_value,
    SUM(CASE WHEN quantity <= reorder_level AND quantity > 0 THEN 1 ELSE 0 END) as low_stock,
    SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM inventory_items
    WHERE is_active = 1";

$result = $connection->query($query);
$stats['summary'] = $result->fetch_assoc();

// Items by category
$cat_query = "SELECT 
    category,
    COUNT(*) as item_count,
    SUM(quantity) as total_quantity,
    SUM(quantity * cost_per_unit) as total_value
    FROM inventory_items
    WHERE is_active = 1 AND category IS NOT NULL AND category != ''
    GROUP BY category
    ORDER BY total_value DESC";

$cat_result = $connection->query($cat_query);
$stats['by_category'] = [];
while ($row = $cat_result->fetch_assoc()) {
    $stats['by_category'][] = $row;
}

// Recent transactions
$trans_query = "SELECT 
    t.*,
    i.item_name,
    u.full_name as performed_by_name
    FROM inventory_transactions t
    JOIN inventory_items i ON t.inventory_item_id = i.id
    LEFT JOIN users u ON t.performed_by = u.id
    ORDER BY t.created_at DESC
    LIMIT 10";

$trans_result = $connection->query($trans_query);
$stats['recent_transactions'] = [];
while ($row = $trans_result->fetch_assoc()) {
    $stats['recent_transactions'][] = $row;
}

// Top items by value
$top_query = "SELECT 
    item_name,
    quantity,
    cost_per_unit,
    (quantity * cost_per_unit) as total_value
    FROM inventory_items
    WHERE is_active = 1
    ORDER BY total_value DESC
    LIMIT 10";

$top_result = $connection->query($top_query);
$stats['top_items'] = [];
while ($row = $top_result->fetch_assoc()) {
    $stats['top_items'][] = $row;
}

echo json_encode([
    'success' => true,
    'stats' => $stats
]);
?>