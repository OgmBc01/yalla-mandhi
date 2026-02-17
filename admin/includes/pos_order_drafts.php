<?php
// Handles saving, loading, and listing draft POS orders (for AJAX)
require_once __DIR__ . '/../../includes/database.php';
$connection = getDBConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'save') {
    // Save or update a draft order
    $order = json_decode($_POST['order'] ?? '', true);
    if (!$order || !isset($order['id'])) {
        http_response_code(400);
        echo 'Invalid order data';
        exit;
    }
    $id = $connection->real_escape_string($order['id']);
    $user_id = $_SESSION['user_id'] ?? 0;
    $data = $connection->real_escape_string(json_encode($order));
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO pos_order_drafts (id, user_id, data, updated_at) VALUES ('$id', $user_id, '$data', '$now') ON DUPLICATE KEY UPDATE data='$data', updated_at='$now'";
    $connection->query($sql);
    echo 'OK';
    exit;
}

if ($action === 'load') {
    // Load all draft orders (admin: all, others: own)
    $user_id = $_SESSION['user_id'] ?? 0;
    $is_admin = $_SESSION['role'] === 'admin';
    $where = $is_admin ? '' : "WHERE user_id = $user_id";
    $result = $connection->query("SELECT data FROM pos_order_drafts $where");
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = json_decode($row['data'], true);
    }
    header('Content-Type: application/json');
    echo json_encode($orders);
    exit;
}

if ($action === 'delete') {
    $id = $connection->real_escape_string($_POST['id'] ?? '');
    if ($id) {
        $connection->query("DELETE FROM pos_order_drafts WHERE id='$id'");
    }
    echo 'OK';
    exit;
}

echo 'Invalid action';
