<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$expense_id = isset($_POST['expense_id']) ? (int)$_POST['expense_id'] : 0;

if (!$expense_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid expense ID']);
    exit;
}

// Get the recurring expense template
$stmt = $connection->prepare("SELECT * FROM expenses WHERE id = ? AND is_recurring = 1");
$stmt->bind_param("i", $expense_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Recurring expense not found']);
    exit;
}

$expense = $result->fetch_assoc();
$stmt->close();

// Calculate next due date based on frequency
$next_date = null;
switch ($expense['recurring_frequency']) {
    case 'monthly':
        $next_date = date('Y-m-d', strtotime($expense['expense_date'] . ' +1 month'));
        break;
    case 'quarterly':
        $next_date = date('Y-m-d', strtotime($expense['expense_date'] . ' +3 months'));
        break;
    case 'yearly':
        $next_date = date('Y-m-d', strtotime($expense['expense_date'] . ' +1 year'));
        break;
    default:
        $next_date = date('Y-m-d', strtotime($expense['expense_date'] . ' +1 month'));
        break;
}

// Check if we've reached the end date
if (!empty($expense['recurring_end_date']) && $next_date > $expense['recurring_end_date']) {
    echo json_encode(['success' => false, 'message' => 'Recurring period has ended']);
    exit;
}

// Create new expense (non-recurring copy)
$expense_number = $expense['expense_number'] . '-R' . date('YmdHis');
$today = date('Y-m-d');
$payment_status = 'pending';
$is_recurring = 0; // This is a generated copy, not recurring itself

$insert = $connection->prepare(
    "INSERT INTO expenses (
        expense_number, category_id, expense_date, description,
        amount, tax_amount, payment_method, payment_status,
        supplier_name, receipt_number, notes, is_recurring, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$insert->bind_param(
    "sisddsdsssssi", // 13 characters: string, int, string, string, double, double, string, string, string, string, string, int, int
    $expense_number,
    $expense['category_id'],
    $today,
    $expense['description'],
    $expense['amount'],
    $expense['tax_amount'],
    $expense['payment_method'],
    $payment_status,
    $expense['supplier_name'],
    $expense['receipt_number'],
    $expense['notes'],
    $is_recurring,
    $_SESSION['user_id']
);

if ($insert->execute()) {
    $new_id = $insert->insert_id;
    $insert->close();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Recurring expense generated successfully',
        'new_expense_id' => $new_id,
        'expense_number' => $expense_number
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to generate expense: ' . $connection->error]);
}
?>