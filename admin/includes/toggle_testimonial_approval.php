<?php
ob_start();
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auth check - employees can only approve, admins can both approve/unapprove
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check permissions based on role
$user_role = $_SESSION['role'] ?? '';
if ($user_role === 'employee') {
    // Employees can only approve (status = 1), not unapprove
    if (!isset($_POST['status']) || $_POST['status'] != '1') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Insufficient permissions']);
        exit;
    }
} elseif (!in_array($user_role, ['admin', 'super-admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate inputs
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || 
    !isset($_POST['status']) || !in_array($_POST['status'], ['0', '1'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$testimonial_id = (int) $_POST['id'];
$new_status = (int) $_POST['status'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $stmt = $connection->prepare(
        "UPDATE testimonials SET is_approved = ? WHERE id = ?"
    );
    $stmt->bind_param("ii", $new_status, $testimonial_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Testimonial not found or already has this status']);
    } else {
        $status_text = $new_status ? 'approved' : 'unapproved';
        echo json_encode([
            'success' => true,
            'message' => 'Testimonial ' . $status_text . ' successfully'
        ]);
    }
    
    $stmt->close();
    
} catch (Throwable $e) {
    error_log("Toggle testimonial approval error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.'
    ]);
}

ob_end_flush();
?>