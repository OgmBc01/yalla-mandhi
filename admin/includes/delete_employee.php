<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Auth check - only super-admin can delete employees
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super-admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}


// Accept ID from POST or GET for flexibility
$employee_id = null;
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $employee_id = (int)$_POST['id'];
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $employee_id = (int)$_GET['id'];
}
if (!$employee_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
    exit;
}

// Prevent deleting yourself
if ($employee_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit;
}

// Check if employee exists
$check_stmt = $connection->prepare("SELECT role FROM users WHERE id = ?");
$check_stmt->bind_param("i", $employee_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit;
}

$employee = $check_result->fetch_assoc();
$check_stmt->close();

// Prevent deleting super-admin accounts (except by super-admin)
if ($employee['role'] === 'super-admin' && $_SESSION['role'] !== 'super-admin') {
    echo json_encode(['success' => false, 'message' => 'Cannot delete super-admin accounts']);
    exit;
}

// Delete employee
$stmt = $connection->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $employee_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Employee deleted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete employee'
    ]);
}

$stmt->close();
?>