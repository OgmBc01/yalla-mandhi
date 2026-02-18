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
    
    // Check if order exists and is deleted
    $checkSql = "SELECT id, is_deleted FROM pos_order_drafts WHERE id = '$id'";
    $checkResult = $connection->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        $row = $checkResult->fetch_assoc();
        if ($row['is_deleted'] == 1) {
            // If it's deleted, restore it
            $sql = "UPDATE pos_order_drafts 
                    SET data='$data', user_id=$user_id, updated_at='$now', 
                        is_deleted=0, restored_at='$now', deleted_at=NULL 
                    WHERE id='$id'";
        } else {
            // Normal update
            $sql = "UPDATE pos_order_drafts 
                    SET data='$data', user_id=$user_id, updated_at='$now' 
                    WHERE id='$id'";
        }
    } else {
        // New insert
        $sql = "INSERT INTO pos_order_drafts (id, user_id, data, created_at, updated_at) 
                VALUES ('$id', $user_id, '$data', '$now', '$now')";
    }
    
    if ($connection->query($sql)) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        echo json_encode(['error' => 'Database error: ' . $connection->error]);
    }
    exit;
}

if ($action === 'load') {
    // Load all non-deleted draft orders
    $user_id = $_SESSION['user_id'] ?? 0;
    $role = $_SESSION['role'] ?? '';
    
    $sql = "SELECT data FROM pos_order_drafts WHERE is_deleted = 0";
    if ($role !== 'admin' && $role !== 'super-admin') {
        $sql .= " AND user_id = $user_id";
    }
    $sql .= " ORDER BY updated_at DESC";
    
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

if ($action === 'soft_delete') {
    // Soft delete an order
    $id = $connection->real_escape_string($_POST['id'] ?? '');
    $user_id = $_SESSION['user_id'] ?? 0;
    $now = date('Y-m-d H:i:s');
    
    if ($id) {
        $sql = "UPDATE pos_order_drafts 
                SET is_deleted = 1, deleted_at = '$now' 
                WHERE id = '$id'";
        
        if ($connection->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Order soft deleted']);
        } else {
            echo json_encode(['error' => 'Database error: ' . $connection->error]);
        }
    } else {
        echo json_encode(['error' => 'No ID provided']);
    }
    exit;
}

if ($action === 'restore') {
    // Restore a soft-deleted order
    $id = $connection->real_escape_string($_POST['id'] ?? '');
    $now = date('Y-m-d H:i:s');
    
    if ($id) {
        $sql = "UPDATE pos_order_drafts 
                SET is_deleted = 0, restored_at = '$now', deleted_at = NULL 
                WHERE id = '$id'";
        
        if ($connection->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Order restored']);
        } else {
            echo json_encode(['error' => 'Database error: ' . $connection->error]);
        }
    } else {
        echo json_encode(['error' => 'No ID provided']);
    }
    exit;
}

if ($action === 'load_deleted') {
    // Load deleted orders (for admin/recovery)
    $user_id = $_SESSION['user_id'] ?? 0;
    $role = $_SESSION['role'] ?? '';
    
    if ($role !== 'admin' && $role !== 'super-admin') {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $sql = "SELECT data, deleted_at FROM pos_order_drafts WHERE is_deleted = 1 ORDER BY deleted_at DESC";
    $result = $connection->query($sql);
    $orders = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $order = json_decode($row['data'], true);
            if ($order) {
                $order['deleted_at'] = $row['deleted_at'];
                $orders[] = $order;
            }
        }
    }
    
    echo json_encode($orders);
    exit;
}

if ($action === 'hard_delete') {
    // Permanently delete an order (admin only)
    $id = $connection->real_escape_string($_POST['id'] ?? '');
    $role = $_SESSION['role'] ?? '';
    
    if ($role !== 'admin' && $role !== 'super-admin') {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    if ($id) {
        $sql = "DELETE FROM pos_order_drafts WHERE id = '$id'";
        
        if ($connection->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Order permanently deleted']);
        } else {
            echo json_encode(['error' => 'Database error: ' . $connection->error]);
        }
    } else {
        echo json_encode(['error' => 'No ID provided']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);