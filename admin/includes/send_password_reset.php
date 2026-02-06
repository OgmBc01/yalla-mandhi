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

// Validate ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit;
}

$customer_id = (int)$_POST['id'];
$connection = getDBConnection();

if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Fetch customer email
$stmt = $connection->prepare(
    "SELECT email FROM users WHERE id = ? AND role = 'customer'"
);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
    exit;
}

$customer = $result->fetch_assoc();
$email = $customer['email'];
$stmt->close();

// Generate reset token
$reset_token = bin2hex(random_bytes(32));
$reset_token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Store token in database
$stmt = $connection->prepare(
    "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?"
);
$stmt->bind_param("ssi", $reset_token, $reset_token_expiry, $customer_id);
$stmt->execute();
$stmt->close();

// In a real application, you would send an email here
// For now, we'll just return success with the reset link

$reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $reset_token;

echo json_encode([
    'success' => true,
    'message' => 'Password reset link generated',
    'reset_link' => $reset_link // Remove this in production, just for demo
]);

// Example email sending (commented out for demo):
/*
$to = $email;
$subject = "Password Reset Request";
$message = "Click the link to reset your password: " . $reset_link;
$headers = "From: noreply@yourdomain.com\r\n";
mail($to, $subject, $message, $headers);
*/
?>