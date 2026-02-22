<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: text/html; charset=utf-8');

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit;
}

$report_type = $_GET['report_type'] ?? 'dashboard';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Validate dates
if (!strtotime($start_date) || !strtotime($end_date)) {
    echo '<div class="alert alert-danger">Invalid date range</div>';
    exit;
}

// Include the appropriate report file based on type
$report_file = __DIR__ . '/../report_' . str_replace('_', '', $report_type) . '.php';

// Map tab IDs to actual file names
$report_map = [
    'dashboard' => 'report_dashboard.php',
    'daily' => 'report_daily_sales.php',
    'monthly' => 'report_monthly.php',
    'items' => 'report_items.php',
    'payment' => 'report_payment_methods.php',
    'vendor' => 'report_vendor.php',
    'staff' => 'report_staff.php',
    'tax' => 'report_tax.php'
];

$file_name = $report_map[$report_type] ?? 'report_dashboard.php';
$report_path = __DIR__ . '/../' . $file_name;

if (file_exists($report_path)) {
    // Pass the date parameters to the included file
    $_GET['start_date'] = $start_date;
    $_GET['end_date'] = $end_date;
    include $report_path;
} else {
    echo '<div class="alert alert-warning">Report not found: ' . htmlspecialchars($file_name) . '</div>';
}
?>