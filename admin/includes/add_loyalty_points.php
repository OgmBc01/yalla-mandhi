<?php
ob_start();
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate inputs
if (!isset($_POST['customer_id']) || !is_numeric($_POST['customer_id']) || 
    !isset($_POST['points']) || !is_numeric($_POST['points'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid inputs']);
    exit;
}

$customer_id = (int)$_POST['customer_id'];
$points = (int)$_POST['points'];
$reason = $_POST['reason'] ?? '';

if ($points <= 0 || $points > 1000) {
    echo json_encode(['success' => false, 'message' => 'Points must be between 1 and 1000']);
    exit;
}

$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $connection->begin_transaction();
    
    // Update loyalty points
    $stmt = $connection->prepare(
        "UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ? AND role = 'customer'"
    );
    $stmt->bind_param("ii", $points, $customer_id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Customer not found');
    }
    $stmt->close();
    
    // Log the transaction (optional)
    $log_stmt = $connection->prepare(
        "INSERT INTO loyalty_logs (customer_id, points, reason, added_by) 
         VALUES (?, ?, ?, ?)"
    );
    $added_by = $_SESSION['username'] ?? 'Admin';
    $log_stmt->bind_param("iiss", $customer_id, $points, $reason, $added_by);
    $log_stmt->execute();
    $log_stmt->close();
    
    $connection->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Loyalty points added successfully'
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>