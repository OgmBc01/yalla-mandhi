<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Validate employee ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
    exit;
}

$employee_id = (int)$_GET['id'];

// Fetch employee details
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ? AND role IN ('employee', 'admin', 'super-admin')");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    $stmt->close();
    
    // Remove sensitive data
    unset($employee['password_hash']);
    unset($employee['reset_token']);
    unset($employee['reset_token_expiry']);
    
    echo json_encode([
        'success' => true,
        'employee' => $employee
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Employee not found'
    ]);
}
?>