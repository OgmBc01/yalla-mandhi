<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$expense_id = isset($_POST['expense_id']) ? (int)$_POST['expense_id'] : 0;
$status = $_POST['status'] ?? '';

if (!$expense_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Update status
$stmt = $connection->prepare("UPDATE expenses SET payment_status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $expense_id);

if ($stmt->execute()) {
    // If marking as paid, also update approved_by
    if ($status == 'paid') {
        $approve_stmt = $connection->prepare("UPDATE expenses SET approved_by = ? WHERE id = ?");
        $approve_stmt->bind_param("ii", $_SESSION['user_id'], $expense_id);
        $approve_stmt->execute();
        $approve_stmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}

$stmt->close();
?>