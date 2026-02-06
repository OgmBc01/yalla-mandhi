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
    echo json_encode(['success' => false, 'message' => 'Invalid testimonial ID']);
    exit;
}

$testimonial_id = (int) $_GET['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $connection->begin_transaction();

    // First, check if testimonial exists
    $stmt = $connection->prepare(
        "SELECT id, customer_name, customer_image FROM testimonials WHERE id = ?"
    );
    $stmt->bind_param("i", $testimonial_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Testimonial not found']);
        exit;
    }
    
    $testimonial = $result->fetch_assoc();
    $stmt->close();

    // Delete the testimonial
    $stmt = $connection->prepare(
        "DELETE FROM testimonials WHERE id = ?"
    );
    $stmt->bind_param("i", $testimonial_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        $connection->rollback();
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
        exit;
    }

    $stmt->close();

    // Delete the image file if exists
    if (!empty($testimonial['customer_image'])) {
        $image_path = __DIR__ . '/../../uploads/testimonials/' . $testimonial['customer_image'];
        if (file_exists($image_path)) {
            @unlink($image_path);
        }
    }

    $connection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Testimonial deleted successfully',
        'customer_name' => $testimonial['customer_name']
    ]);
    
} catch (mysqli_sql_exception $e) {
    if (isset($connection)) {
        $connection->rollback();
    }
    
    error_log("Delete testimonial error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred.'
    ]);
    
} catch (Throwable $e) {
    if (isset($connection)) {
        $connection->rollback();
    }
    
    error_log("Delete testimonial error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.'
    ]);
}

ob_end_flush();
?>