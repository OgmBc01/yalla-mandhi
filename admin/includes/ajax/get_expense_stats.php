<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$period = $_GET['period'] ?? 'month'; // day, week, month, year
$date = $_GET['date'] ?? date('Y-m-d');

$stats = [];

switch ($period) {
    case 'day':
        $stats = getDailyStats($connection, $date);
        break;
    case 'week':
        $stats = getWeeklyStats($connection, $date);
        break;
    case 'month':
        $stats = getMonthlyStats($connection, $date);
        break;
    case 'year':
        $stats = getYearlyStats($connection, $date);
        break;
}

echo json_encode(['success' => true, 'stats' => $stats]);

function getDailyStats($connection, $date) {
    $query = "SELECT 
        COUNT(*) as total_expenses,
        SUM(total_amount) as total_amount,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_amount,
        SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_amount
        FROM expenses 
        WHERE DATE(expense_date) = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getWeeklyStats($connection, $date) {
    $start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
    $end = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
    
    $query = "SELECT 
        COUNT(*) as total_expenses,
        SUM(total_amount) as total_amount,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_amount,
        SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_amount
        FROM expenses 
        WHERE expense_date BETWEEN ? AND ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getMonthlyStats($connection, $date) {
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    
    $query = "SELECT 
        COUNT(*) as total_expenses,
        SUM(total_amount) as total_amount,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_amount,
        SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_amount
        FROM expenses 
        WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getYearlyStats($connection, $date) {
    $year = date('Y', strtotime($date));
    
    $query = "SELECT 
        COUNT(*) as total_expenses,
        SUM(total_amount) as total_amount,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_amount,
        SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_amount
        FROM expenses 
        WHERE YEAR(expense_date) = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>