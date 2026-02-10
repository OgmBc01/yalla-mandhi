<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate ID from POST or GET
$shift_id = null;
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $shift_id = (int)$_POST['id'];
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $shift_id = (int)$_GET['id'];
}
if (!$shift_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid shift ID']);
    exit;
}

// Delete shift
$stmt = $connection->prepare("DELETE FROM shifts WHERE id = ?");
$stmt->bind_param("i", $shift_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Shift deleted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete shift'
    ]);
}

$stmt->close();
?>