<?php
require_once __DIR__ . '/../../includes/database.php';
$connection = getDBConnection();

$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : 'all';

$sql = "SELECT id, name, price FROM menu_items WHERE is_available = 1";
if ($category_id !== 'all') {
    $sql .= " AND category_id = '" . $connection->real_escape_string($category_id) . "'";
}
$sql .= " ORDER BY name ASC";

$result = $connection->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="col-6">';
        echo '<div class="menu-item" data-id="' . $row['id'] . '" data-name="' . htmlspecialchars($row['name']) . '" data-price="' . $row['price'] . '">';
        echo '<strong>' . htmlspecialchars($row['name']) . '</strong><br>';
        echo number_format($row['price'], 2) . ' AED';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<div class="col-12 text-center text-muted">No items found.</div>';
}
?>
