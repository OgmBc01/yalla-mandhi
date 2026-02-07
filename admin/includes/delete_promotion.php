<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'employee'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid promotion ID']);
    exit;
}

$promotion_id = (int) $_GET['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Begin transaction
    if (method_exists($connection, 'begin_transaction')) {
        $connection->begin_transaction();
    }

    // First, check if promotion exists and get image info
    $stmt = $connection->prepare(
        "SELECT id, title, image_url FROM promotions WHERE id = ?"
    );
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $connection->error);
    }
    
    $stmt->bind_param("i", $promotion_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        if (method_exists($connection, 'rollback')) {
            $connection->rollback();
        }
        echo json_encode(['success' => false, 'message' => 'Promotion not found']);
        exit;
    }
    
    $promotion = $result->fetch_assoc();
    $stmt->close();

    // Delete the promotion
    $stmt = $connection->prepare(
        "DELETE FROM promotions WHERE id = ?"
    );
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $connection->error);
    }
    
    $stmt->bind_param("i", $promotion_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        if (method_exists($connection, 'rollback')) {
            $connection->rollback();
        }
        echo json_encode(['success' => false, 'message' => 'Delete failed - promotion not found']);
        exit;
    }

    $stmt->close();

    // Delete the image file if exists
    if (!empty($promotion['image_url'])) {
        $image_path = __DIR__ . '/../../uploads/promotions/' . $promotion['image_url'];
        
        // Check if file exists before trying to delete
        if (file_exists($image_path) && is_file($image_path)) {
            if (!unlink($image_path)) {
                // Log error but don't stop the process
                error_log("Failed to delete promotion image: " . $image_path);
            }
        }
    }

    // Commit transaction
    if (method_exists($connection, 'commit')) {
        $connection->commit();
    }

    // Output and exit to prevent further catch blocks from running
    echo json_encode([
        'success' => true,
        'message' => 'Promotion deleted successfully',
        'promotion_title' => $promotion['title']
    ]);
    exit;
    
} catch (mysqli_sql_exception $e) {
    // Rollback on database error
    if (isset($connection) && method_exists($connection, 'rollback')) {
        $connection->rollback();
    }
    
    error_log("Delete promotion SQL error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred.'
    ]);
    
} catch (Throwable $e) {
    // Rollback on any other error
    if (isset($connection) && method_exists($connection, 'rollback')) {
        $connection->rollback();
    }
    
    error_log("Delete promotion error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.'
    ]);
}

// Close connection
$connection->close();
?>