<?php
ob_start();
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate inputs
if (!isset($_POST['customer_id']) || !is_numeric($_POST['customer_id']) || 
    !isset($_POST['note']) || empty(trim($_POST['note']))) {
    echo json_encode(['success' => false, 'message' => 'Invalid inputs']);
    exit;
}

$customer_id = (int)$_POST['customer_id'];
$note = trim($_POST['note']);
$added_by = $_SESSION['username'] ?? 'Admin';

$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Insert note
$stmt = $connection->prepare(
    "INSERT INTO customer_notes (customer_id, note, added_by) VALUES (?, ?, ?)"
);
$stmt->bind_param("iss", $customer_id, $note, $added_by);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Note added successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add note'
    ]);
}

$stmt->close();
?>