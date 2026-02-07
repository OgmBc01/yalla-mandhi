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

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'employee'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate CSRF token if you have one
if (isset($_POST['csrf_token']) && $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

// Validate inputs
$promotion_id = $_POST['id'] ?? '';
$new_status = $_POST['status'] ?? '';

if (empty($promotion_id) || !is_numeric($promotion_id) || 
    !in_array($new_status, ['0', '1'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$promotion_id = (int) $promotion_id;
$new_status = (int) $new_status;

$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Prepare the update statement
    $stmt = $connection->prepare("UPDATE promotions SET is_active = ?, updated_at = NOW() WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $connection->error);
    }
    
    $stmt->bind_param("ii", $new_status, $promotion_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Promotion not found or status unchanged',
            'affected_rows' => $stmt->affected_rows
        ]);
    } else {
        $status_text = $new_status ? 'activated' : 'deactivated';
        echo json_encode([
            'success' => true,
            'message' => 'Promotion ' . $status_text . ' successfully',
            'new_status' => $new_status
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Toggle promotion status error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'debug' => (isset($_SESSION['role']) && $_SESSION['role'] === 'super-admin') ? $e->getMessage() : ''
    ]);
}

$connection->close();
?>