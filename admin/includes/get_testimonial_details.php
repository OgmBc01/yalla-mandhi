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
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'employee'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate testimonial ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid testimonial ID']);
    exit;
}

$testimonial_id = (int)$_GET['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Fetch testimonial details
$stmt = $connection->prepare(
    "SELECT * FROM testimonials WHERE id = ?"
);
$stmt->bind_param("i", $testimonial_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $testimonial = $result->fetch_assoc();
    
    // Ensure all fields exist
    $testimonial = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $testimonial);
    
    echo json_encode([
        'success' => true,
        'testimonial' => $testimonial
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Testimonial not found'
    ]);
}

if ($result) {
    mysqli_free_result($result);
}
$stmt->close();

ob_end_flush();
?>