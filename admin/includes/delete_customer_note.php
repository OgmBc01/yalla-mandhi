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

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid note ID']);
    exit;
}

$note_id = (int)$_GET['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Delete note
$stmt = $connection->prepare(
    "DELETE FROM customer_notes WHERE id = ?"
);
$stmt->bind_param("i", $note_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Note deleted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete note'
    ]);
}

$stmt->close();
?>