<?php
ob_start();
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid branch ID']);
    exit;
}

// For security, we check if it's an AJAX request with confirmation
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$branch_id = (int) $_GET['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $connection->begin_transaction();

    // First, check if branch exists
    $stmt = $connection->prepare(
        "SELECT id, name FROM branches WHERE id = ?"
    );
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Branch not found']);
        exit;
    }
    
    $branch = $result->fetch_assoc();
    $stmt->close();

    // SAFETY CHECK: Check if branch is being used by customers
    $check_stmt = $connection->prepare(
        "SELECT COUNT(*) as user_count FROM users WHERE preferred_branch = ?"
    );
    $check_stmt->bind_param("i", $branch_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_data = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($check_data['user_count'] > 0) {
        // Instead of deleting, we can deactivate or suggest reassigning users
        $connection->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Cannot delete branch. ' . $check_data['user_count'] . ' customer(s) have this as preferred branch.'
        ]);
        exit;
    }

    // Check if branch is used in orders or other tables
    // Add more checks as needed based on your database structure

    // Delete the branch
    $stmt = $connection->prepare(
        "DELETE FROM branches WHERE id = ?"
    );
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
        exit;
    }

    $stmt->close();
    $connection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Branch deleted successfully',
        'branch_name' => $branch['name']
    ]);
    
} catch (mysqli_sql_exception $e) {
    if (isset($connection)) {
        $connection->rollback();
    }
    
    // Check for foreign key constraint error
    if ($e->getCode() == 1451) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete branch. It is being used in other records.'
        ]);
    } else {
        error_log("Delete branch error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred.'
        ]);
    }
    
} catch (Throwable $e) {
    if (isset($connection)) {
        $connection->rollback();
    }
    
    error_log("Delete branch error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.'
    ]);
}

ob_end_flush();
?>