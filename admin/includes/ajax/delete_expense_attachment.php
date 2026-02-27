<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$attachment_id = isset($_POST['attachment_id']) ? (int)$_POST['attachment_id'] : 0;

if (!$attachment_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid attachment ID']);
    exit;
}

// Get file path before deleting
$stmt = $connection->prepare("SELECT file_path FROM expense_attachments WHERE id = ?");
$stmt->bind_param("i", $attachment_id);
$stmt->execute();
$result = $stmt->get_result();
$attachment = $result->fetch_assoc();
$stmt->close();

if ($attachment) {
    // Delete file from server
    $file_path = __DIR__ . '/../../../' . $attachment['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Delete database record
    $stmt = $connection->prepare("DELETE FROM expense_attachments WHERE id = ?");
    $stmt->bind_param("i", $attachment_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Attachment deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete attachment']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Attachment not found']);
}
?>