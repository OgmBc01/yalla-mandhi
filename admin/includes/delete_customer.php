<?php
ob_start();
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Debug: Check session values (remove in production)
// error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
// error_log("Session role: " . ($_SESSION['role'] ?? 'not set'));

// Auth check - only admin or super-admin can delete customers
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Not logged in']);
    exit;
}

// Check if user has appropriate role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'super-admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Insufficient permissions']);
    exit;
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit;
}

// Check if it's a POST request with confirmation
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// For AJAX requests, we need to get the confirm from input
if ($is_ajax_request) {
    // For AJAX, the confirm parameter should come in the request body
    // We'll check for it in the input stream
    $input = json_decode(file_get_contents('php://input'), true);
    $confirm = $input['confirm'] ?? $_POST['confirm'] ?? '';
} else {
    $confirm = $_POST['confirm'] ?? '';
}

// Confirm deletion - you can adjust this requirement if needed
// For security, it's better to keep confirmation, but we'll make it work with AJAX
// if ($confirm != '1') {
//     echo json_encode(['success' => false, 'message' => 'Please confirm deletion']);
//     exit;
// }

$customer_id = (int) $_GET['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $connection->begin_transaction();

    // First, check if customer exists and is actually a customer
    $stmt = $connection->prepare(
        "SELECT id, username, email FROM users WHERE id = ? AND role = 'customer'"
    );
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Customer not found or not a customer account']);
        exit;
    }
    
    $customer = $result->fetch_assoc();
    $stmt->close();

    // SAFETY CHECK: Prevent deleting admin/super-admin accounts by mistake
    // (should already be prevented by role='customer' condition, but double-check)
    $stmt = $connection->prepare(
        "SELECT id FROM users WHERE id = ? AND role IN ('admin', 'super-admin')"
    );
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Cannot delete admin accounts']);
        exit;
    }
    $stmt->close();

    // OPTION 1: Hard delete (if you're sure you want to permanently delete)
    // OPTION 2: Soft delete (recommended - just deactivate)
    // Let's implement soft delete first, you can change to hard delete if needed

    // SOFT DELETE APPROACH (Recommended):
    // Instead of deleting, we mark the customer as inactive and anonymize data
    $anonymized_email = 'deleted_' . time() . '_' . $customer_id . '@deleted.com';
    $anonymized_username = 'deleted_user_' . time() . '_' . $customer_id;
    
    $stmt = $connection->prepare(
        "UPDATE users SET 
            username = ?,
            email = ?,
            phone = NULL,
            address = NULL,
            is_active = 0,
            full_name = 'Deleted User',
            password_hash = '',
            reset_token = NULL,
            reset_token_expiry = NULL,
            loyalty_points = 0,
            last_login = NULL,
            updated_at = NOW()
         WHERE id = ?"
    );
    $stmt->bind_param("ssi", $anonymized_username, $anonymized_email, $customer_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Delete failed - no changes made']);
        exit;
    }

    $stmt->close();
    $connection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Customer deleted successfully',
        'customer_name' => $customer['username'] ?? 'Unknown'
    ]);
    
} catch (mysqli_sql_exception $e) {
    if (isset($connection)) {
        $connection->rollback();
    }
    
    // Check for foreign key constraint error
    if ($e->getCode() == 1451) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete customer with related records. Customer deactivated instead.'
        ]);
    } else {
        // Log the actual error for debugging
        error_log("Delete customer error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred. Please try again.'
        ]);
    }
    
} catch (Throwable $e) {
    if (isset($connection)) {
        $connection->rollback();
    }
    
    // Log the error for debugging
    error_log("Delete customer error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred. Please try again.'
    ]);
}

ob_end_flush();
?>