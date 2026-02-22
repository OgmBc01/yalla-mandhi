<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$report_type = $_GET['report_type'] ?? $_POST['report_type'] ?? '';
$format = $_GET['format'] ?? $_POST['format'] ?? 'csv';
$start_date = $_GET['start_date'] ?? $_POST['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? $_POST['end_date'] ?? date('Y-m-d');

if (!$report_type) {
    die('Report type not specified');
}

// Remove any prefixes
$report_type = str_replace(['-tab', 'source='], '', $report_type);

// Map report type to query and title
$report_config = [
    'daily' => [
        'title' => 'Daily Sales Report',
        'filename' => 'daily_sales',
        'query' => "SELECT 
            DATE(created_at) as 'Date',
            DAYNAME(created_at) as 'Day',
            COUNT(*) as 'Orders',
            SUM(total_amount) as 'Revenue',
            SUM(CASE WHEN order_type = 'dine_in' THEN 1 ELSE 0 END) as 'Dine In Orders',
            SUM(CASE WHEN order_type = 'pickup' THEN 1 ELSE 0 END) as 'Pickup Orders',
            SUM(CASE WHEN order_type = 'delivery' THEN 1 ELSE 0 END) as 'Delivery Orders',
            SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END) as 'Cash',
            SUM(CASE WHEN payment_method = 'card' THEN total_amount ELSE 0 END) as 'Card',
            SUM(CASE WHEN payment_method = 'online' THEN total_amount ELSE 0 END) as 'Online',
            AVG(total_amount) as 'Avg Order Value'
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND order_status IN ('completed', 'closed')
            GROUP BY DATE(created_at)
            ORDER BY Date DESC"
    ],
    'monthly' => [
        'title' => 'Monthly Summary Report',
        'filename' => 'monthly_summary',
        'query' => "SELECT 
            DATE_FORMAT(created_at, '%M %Y') as 'Month',
            COUNT(*) as 'Orders',
            SUM(total_amount) as 'Revenue',
            SUM(tax_amount) as 'Tax',
            SUM(discount_amount) as 'Discounts',
            AVG(total_amount) as 'Avg Order',
            COUNT(DISTINCT customer_id) as 'Unique Customers'
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND order_status IN ('completed', 'closed')
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY DATE_FORMAT(created_at, '%Y-%m') DESC"
    ],
    'items' => [
        'title' => 'Item Performance Report',
        'filename' => 'item_performance',
        'query' => "SELECT 
            oi.item_name_snapshot as 'Item Name',
            COUNT(DISTINCT oi.order_id) as 'Order Count',
            SUM(oi.quantity) as 'Quantity Sold',
            SUM(oi.total_price) as 'Revenue',
            AVG(oi.unit_price_snapshot) as 'Avg Price'
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?
            AND o.order_status IN ('completed', 'closed')
            GROUP BY oi.item_name_snapshot
            ORDER BY 'Quantity Sold' DESC"
    ],
    'payment' => [
        'title' => 'Payment Method Report',
        'filename' => 'payment_methods',
        'query' => "SELECT 
            payment_method as 'Payment Method',
            COUNT(*) as 'Transactions',
            SUM(total_amount) as 'Total Amount',
            AVG(total_amount) as 'Average',
            COUNT(DISTINCT customer_id) as 'Unique Customers'
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND order_status IN ('completed', 'closed')
            AND payment_method IS NOT NULL
            GROUP BY payment_method
            ORDER BY 'Total Amount' DESC"
    ],
    'vendor' => [
        'title' => 'Vendor Reconciliation Report',
        'filename' => 'vendor_reconciliation',
        'query' => "SELECT 
            delivery_source as 'Vendor',
            COUNT(*) as 'Orders',
            SUM(total_amount) as 'Gross Sales',
            SUM(delivery_fee) as 'Delivery Fees',
            SUM(discount_amount) as 'Discounts',
            SUM(tax_amount) as 'Tax',
            AVG(total_amount) as 'Avg Order'
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND delivery_source IN ('noon', 'deliveroo', 'keeta', 'smile')
            AND order_status IN ('completed', 'closed')
            GROUP BY delivery_source
            ORDER BY 'Gross Sales' DESC"
    ],
    'staff' => [
        'title' => 'Staff Performance Report',
        'filename' => 'staff_performance',
        'query' => "SELECT 
            u.full_name as 'Staff Name',
            COUNT(DISTINCT o.id) as 'Orders',
            SUM(o.total_amount) as 'Total Sales',
            AVG(o.total_amount) as 'Avg Order',
            SUM(o.item_count) as 'Items Sold',
            COUNT(DISTINCT o.customer_id) as 'Customers'
            FROM users u
            LEFT JOIN orders o ON o.punched_by_admin_id = u.id
                AND DATE(o.created_at) BETWEEN ? AND ?
                AND o.order_status IN ('completed', 'closed')
            WHERE u.role IN ('admin', 'super-admin', 'manager', 'cashier')
            GROUP BY u.id
            HAVING Orders > 0
            ORDER BY 'Total Sales' DESC"
    ],
    'tax' => [
        'title' => 'Tax Report',
        'filename' => 'tax_report',
        'query' => "SELECT 
            DATE_FORMAT(created_at, '%M %Y') as 'Month',
            COUNT(*) as 'Transactions',
            SUM(subtotal) as 'Subtotal',
            SUM(discount_amount) as 'Discounts',
            SUM(subtotal - discount_amount) as 'Taxable Amount',
            SUM(tax_amount) as 'Tax Collected'
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND order_status IN ('completed', 'closed')
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY DATE_FORMAT(created_at, '%Y-%m') DESC"
    ]
];

if (!isset($report_config[$report_type])) {
    die('Invalid report type: ' . htmlspecialchars($report_type));
}

$config = $report_config[$report_type];
$title = $config['title'];
$filename = $config['filename'] . '_' . date('Ymd');
$query = $config['query'];

// Execute query
$stmt = $connection->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

// Fetch data
$data = [];
$headers = [];
if ($result && $result->num_rows > 0) {
    // Get column names from first row
    $firstRow = $result->fetch_assoc();
    $headers = array_keys($firstRow);
    $data[] = $firstRow;
    
    // Get remaining rows
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
$stmt->close();

// Handle export based on format
if ($format == 'csv') {
    exportCSV($title, $headers, $data, $start_date, $end_date, $filename);
} else {
    // Default to HTML printable version
    exportHTML($title, $headers, $data, $start_date, $end_date, $filename);
}

function exportCSV($title, $headers, $data, $start_date, $end_date, $filename) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Add title and date range
    fputcsv($output, [$title]);
    fputcsv($output, ['Period:', date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date))]);
    fputcsv($output, []); // Empty row
    
    // Add headers
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($data as $row) {
        $output_row = [];
        foreach ($headers as $header) {
            $value = $row[$header] ?? '';
            // Format numbers
            if (is_numeric($value) && !is_int($value)) {
                $value = number_format($value, 2);
            }
            $output_row[] = $value;
        }
        fputcsv($output, $output_row);
    }
    
    // Add footer
    fputcsv($output, []); // Empty row
    fputcsv($output, ['Generated on: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, ['Generated by: ' . ($_SESSION['username'] ?? 'Unknown')]);
    
    fclose($output);
    exit;
}

function exportHTML($title, $headers, $data, $start_date, $end_date, $filename) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                line-height: 1.6;
            }
            h1 {
                color: #c41e3a;
                border-bottom: 2px solid #c41e3a;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .report-info {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                font-size: 14px;
            }
            th {
                background: #c41e3a;
                color: white;
                padding: 10px;
                text-align: left;
            }
            td {
                padding: 8px;
                border-bottom: 1px solid #ddd;
            }
            tr:nth-child(even) {
                background: #f8f9fa;
            }
            .footer {
                margin-top: 30px;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 10px;
            }
            .print-btn {
                background: #c41e3a;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                margin-bottom: 20px;
            }
            .print-btn:hover {
                background: #a01830;
            }
            @media print {
                .print-btn {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
        
        <h1><?php echo htmlspecialchars($title); ?></h1>
        
        <div class="report-info">
            <strong>Period:</strong> <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?><br>
            <strong>Generated:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
            <strong>Generated by:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Unknown'); ?>
        </div>
        
        <?php if (empty($data)): ?>
            <p style="text-align: center; padding: 40px; background: #f8f9fa;">No data found for the selected period.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <?php foreach ($headers as $header): ?>
                            <th><?php echo htmlspecialchars(str_replace('_', ' ', $header)); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($headers as $header): ?>
                                <td>
                                    <?php 
                                    $value = $row[$header] ?? '';
                                    if (is_numeric($value) && !is_int($value)) {
                                        echo number_format($value, 2);
                                    } else {
                                        echo htmlspecialchars($value);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div class="footer">
            <p>This is a computer generated report. No signature is required.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>