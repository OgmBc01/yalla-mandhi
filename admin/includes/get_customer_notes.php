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

// Validate customer ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit;
}

$customer_id = (int)$_GET['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// First, create customer_notes table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS customer_notes (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    customer_id INT(11) NOT NULL,
    note TEXT NOT NULL,
    added_by VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
)";

$connection->query($create_table);

// Fetch notes
$stmt = $connection->prepare(
    "SELECT * FROM customer_notes WHERE customer_id = ? ORDER BY created_at DESC"
);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$notes = [];
while ($note = $result->fetch_assoc()) {
    $notes[] = $note;
}

echo json_encode([
    'success' => true,
    'notes' => $notes
]);

$stmt->close();
?>