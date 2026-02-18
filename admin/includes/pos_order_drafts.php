<?php
// Handles saving, loading, and listing draft POS orders (for AJAX)
session_start();
require_once __DIR__ . '/../../includes/database.php';
$connection = getDBConnection();

// Set header for JSON responses
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'save') {
    // Save or update a draft order
    $orderJson = $_POST['order'] ?? '';
    if (!$orderJson) {
        echo json_encode(['error' => 'No order data provided']);
        exit;
    }
    
    $order = json_decode($orderJson, true);
    if (!$order || !isset($order['id'])) {
        echo json_encode(['error' => 'Invalid order data']);
        exit;
    }
    
    $id = $connection->real_escape_string($order['id']);
    $user_id = $_SESSION['user_id'] ?? 0;
    $data = $connection->real_escape_string($orderJson);
    $now = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO pos_order_drafts (id, user_id, data, updated_at) 
            VALUES ('$id', $user_id, '$data', '$now') 
            ON DUPLICATE KEY UPDATE data='$data', updated_at='$now'";
    
    if ($connection->query($sql)) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        echo json_encode(['error' => 'Database error: ' . $connection->error]);
    }
    exit;
}

if ($action === 'load') {
    // Load all draft orders (admin: all, others: own)
    $user_id = $_SESSION['user_id'] ?? 0;
    $role = $_SESSION['role'] ?? '';
    
    $sql = "SELECT data FROM pos_order_drafts";
    if ($role !== 'admin' && $role !== 'super-admin') {
        $sql .= " WHERE user_id = $user_id";
    }
    
    $result = $connection->query($sql);
    $orders = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $order = json_decode($row['data'], true);
            if ($order) {
                $orders[] = $order;
            }
        }
    }
    
    echo json_encode($orders);
    exit;
}

if ($action === 'delete') {
    $id = $connection->real_escape_string($_POST['id'] ?? '');
    if ($id) {
        $connection->query("DELETE FROM pos_order_drafts WHERE id='$id'");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No ID provided']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);