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

$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : 'all';
$category_name = 'All Items';

// Base query
$query = "SELECT mi.id, mi.name, mi.price, mi.image_url, mi.is_available,
                 mi.available_quantity, mi.is_daily_limited, mi.track_inventory,
                 mc.name as category_name
          FROM menu_items mi
          LEFT JOIN menu_categories mc ON mi.category_id = mc.id
          WHERE mi.is_available = 1";

if ($category_id !== 'all' && is_numeric($category_id)) {
    $query .= " AND mi.category_id = ?";
    // Get category name
    $cat_stmt = $connection->prepare("SELECT name FROM menu_categories WHERE id = ?");
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    if ($cat_row = $cat_result->fetch_assoc()) {
        $category_name = $cat_row['name'];
    }
    $cat_stmt->close();
}

$query .= " ORDER BY mi.name";

$items = [];

if ($category_id !== 'all' && is_numeric($category_id)) {
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
} else {
    $result = $connection->query($query);
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'items' => $items,
    'category_name' => $category_name,
    'count' => count($items)
]);