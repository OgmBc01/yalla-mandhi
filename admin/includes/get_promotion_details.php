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

// Validate promotion ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid promotion ID']);
    exit;
}

$promotion_id = (int)$_GET['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Fetch promotion details
$stmt = $connection->prepare(
    "SELECT * FROM promotions WHERE id = ?"
);
$stmt->bind_param("i", $promotion_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $promotion = $result->fetch_assoc();
    
    // Ensure all fields exist
    $promotion = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $promotion);
    
    echo json_encode([
        'success' => true,
        'promotion' => $promotion
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Promotion not found'
    ]);
}

if ($result) {
    mysqli_free_result($result);
}
$stmt->close();
?>