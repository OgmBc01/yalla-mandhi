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
    // Fetch branch details
    $stmt = $connection->prepare(
        "SELECT * FROM branches WHERE id = ?"
    );
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Branch not found']);
        exit;
    }
    $branch = $result->fetch_assoc();
    $stmt->close();

    // Return branch details for modal
    echo json_encode([
        'success' => true,
        'branch' => [
            'id' => $branch['id'],
            'name' => $branch['name'],
            'address' => $branch['address'],
            'phone' => $branch['phone'],
            'email' => $branch['email'],
            'opening_hours' => $branch['opening_hours'],
            'is_active' => $branch['is_active']
        ]
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