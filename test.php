I am still facing difficulties with submitting completed order into the orders table.

So now I want to tweak the logic.

Since I am able to submit drafted orders to the pos_order_drafts table, I just want to take the record/details  of the particular order in focus (which are already existing in pos_order_drafts) and add the payment method, the employee who closed it, time and date, discount, and other financial info which are not the data from the pos_order_drafts table, and collectively submit everything to the orders table.

This will help us skip the step of capturing the entire order data all over again, and simplify the submission process. As you already can see how I have been facing the type string errors above, and could not resolve that.

Now the save and close button shud be changed to choose payment method;
            <button class="btn btn-success btn-lg fw-bold" id="btnSaveCloseOrder" style="font-size:1.15rem; border-radius:12px; box-shadow:0 2px 8px rgba(39,174,96,0.08); min-width:240px;">
                <i class="bi bi-check2-circle me-2"></i>Save & Close Order
            </button>

 And Save and Close orders modal Shud become choose payment method modal and, should not contain the order details, and shud only serve as a way to confirm the which payment method used. 

Once payment method is confirmed and captured by the system, choose payment method shud be changed to save and close, where all the details get sent to the orders table.

pos_order_drafts table structure:


Now help me restructure the code to match the requirements I have described, and also make it aligned with the new database structure to support the new requirement.

Then give me the complete and updated page structures and database syntax to execute the queies/correct the db tables where necessary



------------------------------------------------------------------

Secondly I want to also make it possible for the system to prepare a sales invoice receipt that can be 
sent to the kitchen printer or printed at the payment counter (for restaurant record or on customer 
request) via the small printer machine like (Terminal Machine) with below details; - Printer Model: 
IRP-200D / POS-80C - Paper Width 800mm - Receipt: 72mm x 297mm - Connection with system: USB





Now let go back to the printing issue, since the automatic printing usinf QZ Printer is still not working, I want to swicth to the manual process where user needs to select the printer using the windows.print() or however you want to implement this

I just simply need the receipt printing to be something similar to what happens when we click the 'Export PDF' button in the repot pages; e.g.; #file:report_monthly.php , then there shud be a button that opens the preview page, that allows to print to a printer or save to a destination.

See current report_monthly.php;
<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'employee'])) {
    header("Location: login.php");
    exit();
}

// Get date range from URL or set defaults (full year by default)
$start_date = $_GET['start_date'] ?? date('Y-01-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Fetch monthly summary
$monthly_query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    DATE_FORMAT(created_at, '%M %Y') as month_name,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    SUM(CASE WHEN order_type = 'dine_in' THEN total_amount ELSE 0 END) as dine_in_revenue,
    SUM(CASE WHEN order_type = 'pickup' THEN total_amount ELSE 0 END) as pickup_revenue,
    SUM(CASE WHEN order_type = 'delivery' THEN total_amount ELSE 0 END) as delivery_revenue,
    SUM(CASE WHEN delivery_source != 'internal' AND delivery_source IS NOT NULL THEN total_amount ELSE 0 END) as vendor_revenue,
    SUM(discount_amount) as total_discounts,
    SUM(tax_amount) as total_tax,
    AVG(total_amount) as avg_order_value,
    COUNT(DISTINCT customer_id) as unique_customers,
    SUM(item_count) as total_items
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND order_status IN ('completed', 'closed')
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%M %Y')
    ORDER BY month DESC";

$stmt = $connection->prepare($monthly_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$monthly_result = $stmt->get_result();

// Calculate yearly totals
$yearly_totals = [
    'revenue' => 0,
    'orders' => 0,
    'tax' => 0,
    'discounts' => 0
];

$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[] = $row;
    $yearly_totals['revenue'] += $row['total_revenue'];
    $yearly_totals['orders'] += $row['total_orders'];
    $yearly_totals['tax'] += $row['total_tax'];
    $yearly_totals['discounts'] += $row['total_discounts'];
}
$stmt->close();

// Get current month vs previous month comparison
$current_month = date('Y-m');
$previous_month = date('Y-m', strtotime('-1 month'));

$comparison_query = "SELECT 
    SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = ? THEN total_amount ELSE 0 END) as current_revenue,
    SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = ? THEN total_amount ELSE 0 END) as previous_revenue,
    COUNT(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = ? THEN 1 END) as current_orders,
    COUNT(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = ? THEN 1 END) as previous_orders
    FROM orders 
    WHERE order_status IN ('completed', 'closed')
    AND (DATE_FORMAT(created_at, '%Y-%m') = ? OR DATE_FORMAT(created_at, '%Y-%m') = ?)";

$stmt = $connection->prepare($comparison_query);
$stmt->bind_param("ssssss", $current_month, $previous_month, $current_month, $previous_month, $current_month, $previous_month);
$stmt->execute();
$comparison = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calculate growth percentages
$revenue_growth = $comparison['previous_revenue'] > 0 
    ? round(($comparison['current_revenue'] - $comparison['previous_revenue']) / $comparison['previous_revenue'] * 100, 1)
    : 0;
$orders_growth = $comparison['previous_orders'] > 0
    ? round(($comparison['current_orders'] - $comparison['previous_orders']) / $comparison['previous_orders'] * 100, 1)
    : 0;
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Date Range Filter -->
        <h2 class="page-title">Monthly Sales Report</h2>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <input type="hidden" name="source" value="monthly">
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Year</label>
                        <select class="form-select" name="year" onchange="this.form.submit()">
                            <?php 
                            $current_year = date('Y');
                            $selected_year = $_GET['year'] ?? $current_year;
                            for ($year = $current_year; $year >= $current_year - 3; $year--): 
                            ?>
                            <option value="<?php echo $year; ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">From Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">To Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter-circle me-2"></i>Apply Filter
                        </button>
                        <a href="includes/export_report.php?source=monthly&export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success me-2" target="_blank">
                            <i class="bi bi-file-earmark-excel"></i> Export CSV
                        </a>
                        <a href="includes/export_report.php?source=monthly&export=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-danger" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Comparison Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Current Month vs Previous Month</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center p-3 <?php echo $revenue_growth >= 0 ? 'bg-success-light' : 'bg-danger-light'; ?> rounded">
                                    <small class="text-muted">Revenue</small>
                                    <h4 class="<?php echo $revenue_growth >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ($revenue_growth >= 0 ? '+' : '') . $revenue_growth; ?>%
                                    </h4>
                                    <small class="text-muted">
                                        <?php echo number_format($comparison['current_revenue'] ?? 0, 0); ?> AED vs <?php echo number_format($comparison['previous_revenue'] ?? 0, 0); ?> AED
                                    </small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 <?php echo $orders_growth >= 0 ? 'bg-success-light' : 'bg-danger-light'; ?> rounded">
                                    <small class="text-muted">Orders</small>
                                    <h4 class="<?php echo $orders_growth >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ($orders_growth >= 0 ? '+' : '') . $orders_growth; ?>%
                                    </h4>
                                    <small class="text-muted">
                                        <?php echo $comparison['current_orders'] ?? 0; ?> vs <?php echo $comparison['previous_orders'] ?? 0; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Year to Date Summary</h6>
                        <div class="row">
                            <div class="col-4 text-center">
                                <small class="text-muted">Revenue</small>
                                <h5 class="text-primary"><?php echo number_format($yearly_totals['revenue'], 0); ?> AED</h5>
                            </div>
                            <div class="col-4 text-center">
                                <small class="text-muted">Orders</small>
                                <h5 class="text-success"><?php echo $yearly_totals['orders']; ?></h5>
                            </div>
                            <div class="col-4 text-center">
                                <small class="text-muted">Avg Order</small>
                                <h5 class="text-info">
                                    <?php echo $yearly_totals['orders'] > 0 ? number_format($yearly_totals['revenue'] / $yearly_totals['orders'], 2) : 0; ?> AED
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Data Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar-month me-2"></i>Monthly Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-report">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Dine In</th>
                                <th class="text-end">Pickup</th>
                                <th class="text-end">Delivery</th>
                                <th class="text-end">Vendor</th>
                                <th class="text-end">Discounts</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Avg Order</th>
                                <th class="text-center">Customers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($monthly_data)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block text-muted mb-3"></i>
                                    <h5>No monthly data found</h5>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($monthly_data as $month): ?>
                                <tr>
                                    <td><strong><?php echo $month['month_name']; ?></strong></td>
                                    <td class="text-center"><?php echo $month['total_orders']; ?></td>
                                    <td class="text-end fw-bold text-success"><?php echo number_format($month['total_revenue'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['dine_in_revenue'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['pickup_revenue'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['delivery_revenue'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['vendor_revenue'], 2); ?></td>
                                    <td class="text-end text-danger"><?php echo number_format($month['total_discounts'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['total_tax'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['avg_order_value'], 2); ?></td>
                                    <td class="text-center"><?php echo $month['unique_customers']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-success-light {
    background-color: rgba(40, 167, 69, 0.1);
}
.bg-danger-light {
    background-color: rgba(220, 53, 69, 0.1);
}
.table-report th {
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 2px solid #c41e3a;
}
</style>


export_report.php;
<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get database connection
require_once __DIR__ . '/../../includes/database.php';
$connection = getDBConnection();

// Auth check
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

// Get parameters from URL
$report_type = $_GET['source'] ?? $_GET['report_type'] ?? '';
$format = $_GET['export'] ?? $_GET['format'] ?? 'csv';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

if (!$report_type) {
    die('Report type not specified');
}

// Remove any prefixes
$report_type = str_replace(['-tab', 'source='], '', $report_type);

// Map report type to query and title - FIXED GROUP BY for all queries
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
            GROUP BY DATE(created_at), DAYNAME(created_at)
            ORDER BY `Date` DESC"
    ],
    'monthly' => [
        'title' => 'Monthly Summary Report',
        'filename' => 'monthly_summary',
        'query' => "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as 'Month_Key',
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
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%M %Y')
            ORDER BY `Month_Key` DESC"
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
            ORDER BY `Quantity Sold` DESC"
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
            ORDER BY `Total Amount` DESC"
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
            ORDER BY `Gross Sales` DESC"
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
            GROUP BY u.id, u.full_name
            HAVING Orders > 0
            ORDER BY `Total Sales` DESC"
    ],
    'tax' => [
        'title' => 'Tax Report',
        'filename' => 'tax_report',
        'query' => "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as 'Month_Key',
            DATE_FORMAT(created_at, '%M %Y') as 'Month',
            COUNT(*) as 'Transactions',
            SUM(subtotal) as 'Subtotal',
            SUM(discount_amount) as 'Discounts',
            SUM(subtotal - discount_amount) as 'Taxable Amount',
            SUM(tax_amount) as 'Tax Collected'
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND order_status IN ('completed', 'closed')
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%M %Y')
            ORDER BY `Month_Key` DESC"
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
    // Remove Month_Key from headers if it exists (internal use only)
    if (isset($firstRow['Month_Key'])) {
        unset($firstRow['Month_Key']);
    }
    $headers = array_keys($firstRow);
    $data[] = $firstRow;
    
    // Get remaining rows
    while ($row = $result->fetch_assoc()) {
        // Remove Month_Key from row if it exists
        if (isset($row['Month_Key'])) {
            unset($row['Month_Key']);
        }
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

// Ensure no output before headers for CSV
if ($format === 'csv') {
    while (ob_get_level()) {
        ob_end_clean();
    }
}

function exportCSV($title, $headers, $data, $start_date, $end_date, $filename) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
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
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
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

Now, see my current recipt printing setup;
pos_order.php;
<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../includes/database.php';
$connection = getDBConnection();

// Fetch categories
$categories = $connection->query("
    SELECT id, name 
    FROM menu_categories 
    WHERE is_active = 1 
    ORDER BY sort_order ASC, name ASC
");

// Get first category ID for initial load
$firstCat = null;
if ($categories && $categories->num_rows > 0) {
    $categories->data_seek(0);
    $firstRow = $categories->fetch_assoc();
    $firstCat = $firstRow['id'];
    $categories->data_seek(0); // Reset pointer
}
?>

<div class="main-content">
<div class="container-fluid">

<!-- ================= HEADER ================= -->

<!-- Page Title -->
<div class="mt-3 mb-2">
    <h1 style="font-size:2rem; font-weight:800; color:#c41e3a; letter-spacing:1px; margin-bottom:0.2em; text-shadow:0 2px 8px rgba(196,30,58,0.08);">Punch Orders</h1>
</div>

<!-- Active Orders Card with Breakdown (compact, above row) -->
<div id="activeOrdersCard" class="card shadow-sm mb-2" style="max-width:100%; border-radius:12px; border:1px solid #f39c12; background:linear-gradient(135deg,#fffbe6,#fff);">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div style="font-size:1.05rem; color:#c41e3a; font-weight:600;">Active Orders: <span id="activeOrdersCount" style="font-size:1.5rem; font-weight:700; color:#f39c12;">0</span></div>
            <div id="ordersTypeBreakdown" class="d-flex flex-wrap gap-2"></div>
        </div>
    </div>
</div>

<!-- Main Row: New Order Button and Tabs -->
<div class="d-flex align-items-center mb-3" style="gap:18px;">
    <button class="btn btn-theme-gradient" id="btnNewOrder" style="background: linear-gradient(135deg, #c41e3a 0%, #f39c12 100%); color: #fff; border-radius: 14px; border: none; font-size: 1.1rem; font-weight: 600; box-shadow: 0 4px 16px rgba(196,30,58,0.12); padding: 12px 24px;">
        <i class="bi bi-plus-circle display-6 me-2"></i> Punch New Order
    </button>
    <div id="ordersTabsContainer" class="orders-tabs-card" style="background:#fff;border-radius:12px;padding:12px 8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);overflow-x:auto;white-space:nowrap;scroll-behavior:smooth;flex:1;">
        <div id="ordersTabs" class="d-flex flex-row gap-2"></div>
    </div>
</div>

<!-- ================= POS CONTAINER ================= -->

<div class="pos-container">

    <!-- 1️⃣ CATEGORY PANEL -->
    <div class="category-panel">
        <?php $catIndex = 0; while($cat = $categories->fetch_assoc()): ?>
            <div class="category-item<?= $catIndex === 0 ? ' active' : '' ?>" data-category="<?= $cat['id'] ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </div>
            <?php $catIndex++; ?>
        <?php endwhile; ?>
        <div class="d-flex flex-column align-items-center my-3">
            <a href="categories.php?source=add_category" class="btn btn-outline-success btn-sm mb-2 w-100" style="border-radius:8px; font-weight:600;"><i class="bi bi-plus-circle me-1"></i> Add Category</a>
        </div>
    </div>

    <!-- 2️⃣ MENU PANEL -->
    <div class="menu-panel">
        <div id="menuItems" class="row g-2"></div>
        <div class="d-flex flex-column align-items-center my-3">
            <a href="menu_items.php?source=add_item" class="btn btn-outline-primary btn-sm w-100" style="border-radius:8px; font-weight:600;"><i class="bi bi-plus-circle me-1"></i> Add Menu Item</a>
        </div>
    </div>

    <!-- 3️⃣ ORDER PANEL -->
    <div class="order-panel">
        <table class="table table-bordered mb-2">
            <thead>
                <tr>
                    <th>Item</th>
                    <th width="70">Qty</th>
                    <th width="100">Price</th>
                    <th width="100">Total</th>
                    <th width="40"></th>
                </tr>
            </thead>
            <tbody id="orderItemsBody">
                <tr class="empty-row">
                    <td colspan="5" class="text-center text-muted">
                        Create or select an order to begin
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- ================= ENHANCED FINANCIAL SUMMARY ================= -->
        <div class="financial-summary mt-3">
            <!-- Subtotal -->
            <div class="summary-row d-flex justify-content-between align-items-center py-2">
                <span class="summary-label">
                    <i class="bi bi-calculator me-2" style="color: #c41e3a;"></i>Subtotal:
                </span>
                <span class="summary-value fw-bold" id="summarySubtotal">0.00 AED</span>
            </div>
            
            <!-- Discount Row with Edit -->
            <div class="summary-row d-flex justify-content-between align-items-center py-2 bg-light rounded px-2">
                <div class="d-flex align-items-center">
                    <i class="bi bi-tag-fill me-2" style="color: #f39c12;"></i>
                    <span class="summary-label me-2">Discount:</span>
                    <div class="input-group input-group-sm" style="width: 110px;">
                        <input type="number" class="form-control form-control-sm" id="discountAmount" value="0" min="0" step="0.01" style="border-color: #f39c12;">
                        <span class="input-group-text bg-white" style="border-color: #f39c12;">AED</span>
                    </div>
                </div>
                <span class="summary-value text-warning fw-bold" id="summaryDiscount">-0.00 AED</span>
            </div>
            
            <!-- Discount Type Toggle -->
            <div class="d-flex justify-content-end mb-2">
                <div class="btn-group btn-group-sm" role="group" id="discountTypeGroup">
                    <button type="button" class="btn btn-outline-warning active" data-discount-type="fixed">Fixed</button>
                    <button type="button" class="btn btn-outline-warning" data-discount-type="percentage">%</button>
                </div>
            </div>
            
            <!-- Tax -->
            <div class="summary-row d-flex justify-content-between align-items-center py-2">
                <span class="summary-label">
                    <i class="bi bi-percent me-2" style="color: #3498db;"></i>Tax (0%):
                </span>
                <span class="summary-value" id="summaryTax">0.00 AED</span>
            </div>
            
            <!-- Delivery Fee (conditional) -->
            <div class="summary-row d-flex justify-content-between align-items-center py-2" id="deliveryFeeRow" style="display: none;">
                    <span class="summary-label">
                        <i class="bi bi-truck me-2" style="color: #2ecc71;"></i>Delivery Fee:
                    </span>
                    <div class="input-group input-group-sm" style="width: 110px;">
                        <input type="number" class="form-control form-control-sm" id="deliveryFeeInput" value="0" min="0" step="0.01" style="border-color: #2ecc71; text-align: right; font-weight: 600;">
                        <span class="input-group-text bg-white" style="border-color: #2ecc71;">AED</span>
                    </div>
                    <span class="summary-value ms-2" id="summaryDeliveryFee">0.00 AED</span>
                </div>
            
            <!-- Divider -->
            <div class="dropdown-divider my-2"></div>
            
            <!-- Net Total -->
            <div class="summary-row d-flex justify-content-between align-items-center py-3" style="background: linear-gradient(135deg, #c41e3a 0%, #f39c12 100%); border-radius: 8px; padding: 10px 15px !important; margin-top: 5px;">
                <span class="summary-label text-white fw-bold fs-5">
                    <i class="bi bi-cash-stack me-2"></i>NET TOTAL:
                </span>
                <span class="summary-value text-white fw-bold fs-4" id="orderTotal">0.00 AED</span>
            </div>
            
            <!-- Quick Discount Presets -->
            <div class="discount-presets mt-2">
                <small class="text-muted me-2">Quick discounts:</small>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary discount-preset" data-preset="5">5%</button>
                    <button class="btn btn-outline-secondary discount-preset" data-preset="10">10%</button>
                    <button class="btn btn-outline-secondary discount-preset" data-preset="15">15%</button>
                    <button class="btn btn-outline-secondary discount-preset" data-preset="20">20%</button>
                    <button class="btn btn-outline-secondary discount-preset" data-preset="25">25%</button>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-warning w-50" id="btnSendKitchen" disabled>
                <i class="bi bi-send me-2"></i> Send to Kitchen
            </button>
            <button class="btn btn-primary w-50" id="btnPrint" disabled>
                <i class="bi bi-printer me-2"></i> Print Receipt
            </button>
        </div>
        
        <!-- Additional Action Buttons Row -->
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-outline-secondary btn-sm flex-fill" id="btnHoldOrder">
                <i class="bi bi-pause-circle me-1"></i> Hold
            </button>
            <button class="btn btn-outline-info btn-sm flex-fill" id="btnAddNote">
                <i class="bi bi-chat-dots me-1"></i> Add Note
            </button>
            <button class="btn btn-outline-danger btn-sm flex-fill" id="btnCancelOrder">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </button>
        </div>

        <!-- Payment/Close Button - Will be toggled between states -->
        <div class="d-flex mt-3 justify-content-center">
            <button class="btn btn-success btn-lg fw-bold" id="btnPaymentAction" style="font-size:1.15rem; border-radius:12px; box-shadow:0 2px 8px rgba(39,174,96,0.08); min-width:240px;">
                <i class="bi bi-credit-card me-2"></i>Choose Payment Method
            </button>
        </div>
    </div> <!-- Close order-panel -->
</div> <!-- Close pos-container -->
</div> <!-- Close container-fluid -->
</div> <!-- Close main-content -->

<!-- ================= MODALS ================= -->

<!-- Payment Method Modal (Simplified) -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card-2-front display-6 text-white"></i>
                    <h5 class="modal-title mb-0 text-white">Select Payment Method</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body reservation-modal-body">
                <!-- Order Summary (Brief) -->
                <div class="alert alert-info mb-3">
                    <small>
                        <strong>Closing order:</strong> <span id="modalOrderNumber"></span> - 
                        <span id="modalCustomerName"></span><br>
                        <strong>Total:</strong> <span id="modalTotal" class="fw-bold text-success"></span>
                    </small>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold fs-5 mb-3">Select Payment Method</label>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="payment-method-card" data-method="cash" style="background: linear-gradient(135deg, #f39c12 0%, #f7b733 100%); color: #fff; cursor:pointer; border-radius:12px; padding:20px; text-align:center; transition:all 0.3s;">
                                <i class="bi bi-cash-coin display-5 mb-2"></i>
                                <h6 class="mb-0">Cash</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method-card" data-method="card" style="background: linear-gradient(135deg, #3498db 0%, #6dd5fa 100%); color: #fff; cursor:pointer; border-radius:12px; padding:20px; text-align:center; transition:all 0.3s;">
                                <i class="bi bi-credit-card-2-front display-5 mb-2"></i>
                                <h6 class="mb-0">POS Card</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method-card" data-method="credit" style="background: linear-gradient(135deg, #c41e3a 0%, #f39c12 100%); color: #fff; cursor:pointer; border-radius:12px; padding:20px; text-align:center; transition:all 0.3s;">
                                <i class="bi bi-credit-card display-5 mb-2"></i>
                                <h6 class="mb-0">Credit Card</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method-card" data-method="debit" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: #fff; cursor:pointer; border-radius:12px; padding:20px; text-align:center; transition:all 0.3s;">
                                <i class="bi bi-credit-card-2-back display-5 mb-2"></i>
                                <h6 class="mb-0">Debit Card</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reference (Optional) -->
                <div class="mb-3" id="referenceField" style="display: none;">
                    <label class="form-label">Reference/Transaction ID (Optional)</label>
                    <input type="text" class="form-control" id="paymentReference" placeholder="e.g., Transaction ID">
                </div>

                <input type="hidden" id="selectedPaymentMethod">
            </div>
            <div class="modal-footer reservation-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" id="confirmPaymentMethod" disabled>
                    <i class="bi bi-check2-circle me-2"></i>Confirm Payment Method
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="orderSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check2-circle me-2"></i>
                    Order Saved Successfully
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3" id="successOrderNumber">Order #</h4>
                <p class="text-muted">The order has been closed and saved to the database.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- New Order Modal -->
<div class="modal fade" id="initOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle display-6 text-theme"></i>
                    <h5 class="modal-title mb-0">Create New Order</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body reservation-modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Order Type</label>
                    <div class="d-flex gap-2 order-type-cards">
                        <div class="order-type-card" data-type="dine_in" style="background: linear-gradient(135deg, #3498db 0%, #6dd5fa 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:18px; text-align:center;">
                            <i class="bi bi-shop display-5 mb-2"></i><br>Dine In
                        </div>
                        <div class="order-type-card" data-type="pickup" style="background: linear-gradient(135deg, #f39c12 0%, #f7b733 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:18px; text-align:center;">
                            <i class="bi bi-bag display-5 mb-2"></i><br>Pickup
                        </div>
                        <div class="order-type-card" data-type="delivery" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:18px; text-align:center;">
                            <i class="bi bi-truck display-5 mb-2"></i><br>Delivery
                        </div>
                    </div>
                </div>
                <div id="deliveryOptions" class="d-none mb-3">
                    <label class="form-label fw-bold">Delivery Source</label>
                    <div class="d-flex gap-2 delivery-source-cards">
                        <div class="delivery-source-card" data-source="internal" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-shop display-6 mb-1"></i><br>Restaurant
                        </div>
                        <div class="delivery-source-card" data-source="noon" style="background: linear-gradient(135deg, #fbb034 0%, #ffdd00 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-sun display-6 mb-1"></i><br>Noon
                        </div>
                        <div class="delivery-source-card" data-source="keeta" style="background: linear-gradient(135deg, #e74c3c 0%, #e67e22 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-bicycle display-6 mb-1"></i><br>Keeta
                        </div>
                        <div class="delivery-source-card" data-source="deliveroo" style="background: linear-gradient(135deg, #00c3e3 0%, #2f80ed 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-bag-check display-6 mb-1"></i><br>Deliveroo
                        </div>
                        <div class="delivery-source-card" data-source="smile" style="background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-emoji-smile display-6 mb-1"></i><br>Smile
                        </div>
                    </div>
                </div>
                <input type="hidden" id="orderTypeSelect" value="">
                <input type="hidden" id="deliverySource" value="internal">
                <div id="dineInFields" class="d-none mb-2">
                    <label class="form-label fw-bold">Select Table</label>
                    <div id="tableSelector" class="d-flex flex-wrap gap-2 mb-2"></div>
                    <input type="number" id="numCustomers" class="form-control mb-2" placeholder="Number of Customers" min="1">
                </div>
                <input type="text" id="customerName" class="form-control mb-2" placeholder="Customer Name">
                <input type="text" id="customerPhone" class="form-control mb-2" placeholder="Phone">
                <input type="text" id="customerAddress" class="form-control mb-2 d-none" placeholder="Address">
            </div>
            <div class="modal-footer reservation-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-theme" id="confirmCreateOrder">Create Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Soft Delete Confirmation Modal -->
<div class="modal fade delete-confirm-modal" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Delete Order
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="warning-icon">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <h4 class="mb-3">Are you sure?</h4>
                <p class="text-muted mb-4">This order will be moved to trash. You can restore it later from the recovery panel.</p>
                
                <div class="order-details" id="deleteOrderDetails">
                    <!-- Will be filled dynamically -->
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="permanentDeleteCheck">
                    <label class="form-check-label" for="permanentDeleteCheck">
                        <strong class="text-danger">Permanently delete</strong> <span class="text-muted">(cannot be undone)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-2"></i> Delete Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recovery Modal (Trash Bin) -->
<div class="modal fade recovery-modal" id="recoveryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #6c757d, #495057); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-trash3-fill me-2"></i>
                    Deleted Orders Recovery
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="deletedOrdersList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-secondary"></div>
                        <p class="mt-2">Loading deleted orders...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="emptyTrashBtn" style="display: none;">
                    <i class="bi bi-trash3 me-2"></i> Empty Trash
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Undo Toast -->
<div id="undoToast" class="undo-toast" style="display: none;">
    <i class="bi bi-check-circle-fill me-2"></i>
    <span id="undoMessage">Order deleted</span>
    <button class="undo-btn" id="undoDeleteBtn">UNDO</button>
</div>

<!-- Trash Bin Button -->
<div class="trash-bin-panel">
    <button class="trash-bin-btn" id="trashBinBtn" title="View deleted orders">
        <i class="bi bi-trash3-fill"></i>
    </button>
</div>

<style>
.pos-container{
    display:flex;
    height:calc(100vh - 180px);
    background:#fff;
    border-radius:8px;
    overflow:hidden;
}
.category-panel{
    width:220px;
    background:#2c3e50;
    color:#fff;
    overflow-y:auto;
}
.category-item{
    padding:12px;
    cursor:pointer;
    transition: all 0.3s;
}
.category-item:hover{
    background:#34495e;
}
.category-item.active{
    background:#c41e3a;
    font-weight:500;
}
.menu-panel{
    width:320px;
    overflow-y:auto;
    padding:10px;
    border-left:1px solid #ddd;
}
.menu-item{
    border:1px solid #ddd;
    padding:10px;
    cursor:pointer;
    border-radius:6px;
    background:#fafafa;
    transition: all 0.2s;
}
.menu-item:hover{
    background:#f0f0f0;
    border-color:#c41e3a;
}
.order-panel{
    flex:1;
    padding:10px;
    display:flex;
    flex-direction:column;
    border-left:1px solid #ddd;
    min-height:0;
    overflow-y:auto;
    max-height:calc(100vh - 220px);
}
.order-tab{
    padding:6px 12px;
    background:#eee;
    border-radius:6px;
    cursor:pointer;
    transition:box-shadow 0.2s,border 0.2s;
    white-space:normal;
    font-size:0.98rem;
    line-height:1.2;
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    min-width:180px;
    max-width:220px;
    overflow:hidden;
    text-overflow:ellipsis;
}
.order-tab.active{
    background:#fff;
    border:2px solid #c41e3a!important;
    box-shadow:0 4px 12px rgba(196,30,58,0.12);
}
.orders-tabs-card{
    flex:1;
    overflow-x:auto;
    white-space:nowrap;
    scrollbar-width:thin;
    scrollbar-color:#c41e3a #eee;
    scroll-behavior:smooth;
}
.orders-tabs-card::-webkit-scrollbar{
    height:8px;
    background:#eee;
    border-radius:8px;
}
.orders-tabs-card::-webkit-scrollbar-thumb{
    background:#c41e3a;
    border-radius:8px;
}
.financial-summary {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 1px solid #e0e0e0;
}
.summary-row {
    transition: all 0.2s ease;
}
.summary-row:hover {
    background-color: #f8f9fa;
    border-radius: 6px;
}
.summary-label {
    font-size: 0.95rem;
    color: #2c3e50;
}
.summary-value {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}
#discountAmount {
    text-align: right;
    font-weight: 600;
}
#discountAmount:focus {
    border-color: #c41e3a;
    box-shadow: 0 0 0 0.2rem rgba(196,30,58,0.25);
}
.discount-presets {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
}
.discount-presets .btn-group {
    flex-wrap: wrap;
}
.discount-presets .btn-outline-secondary {
    border-color: #e0e0e0;
    color: #2c3e50;
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
}
.discount-presets .btn-outline-secondary:hover {
    background: #f39c12;
    border-color: #f39c12;
    color: #fff;
}
#discountTypeGroup .btn-outline-warning {
    border-color: #f39c12;
    color: #f39c12;
    font-size: 0.8rem;
    padding: 0.2rem 0.8rem;
}
#discountTypeGroup .btn-outline-warning.active {
    background: #f39c12;
    border-color: #f39c12;
    color: #fff;
}
.payment-method-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}
.payment-method-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.payment-method-card.selected {
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #27ae60;
    transform: scale(1.02);
}
.delete-order-tab-btn:hover {
    background: #dc3545 !important;
    color: #fff !important;
}
.undo-toast {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #28a745;
    color: white;
    padding: 12px 24px;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1100;
    display: flex;
    align-items: center;
    gap: 15px;
    animation: slideUp 0.3s ease;
}
.undo-toast .undo-btn {
    background: white;
    color: #28a745;
    border: none;
    border-radius: 4px;
    padding: 5px 15px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}
.undo-toast .undo-btn:hover {
    background: #f8f9fa;
    transform: scale(1.05);
}
@keyframes slideUp {
    from {
        bottom: -100px;
        opacity: 0;
    }
    to {
        bottom: 20px;
        opacity: 1;
    }
}
.trash-bin-panel {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1050;
}
.trash-bin-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.trash-bin-btn:hover {
    transform: scale(1.1);
    background: linear-gradient(135deg, #dc3545, #c82333);
}
.trash-bin-btn.has-items {
    background: linear-gradient(135deg, #dc3545, #c82333);
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}
</style>

<!-- Add Bootstrap and jQuery scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let orders = [];
let activeOrderId = null;
let isLoading = true;
let savedScrollPosition = 0;

// Financial variables
let discountAmount = 0;
let discountType = 'fixed';
let deliveryFee = 0;
const TAX_RATE = 0.0;

// Soft delete variables
let deletedOrders = [];
let lastDeletedOrder = null;
let undoTimeout = null;

// Payment variables
let selectedPaymentMethod = null;
let paymentReference = '';

// --- DRAFT ORDER PERSISTENCE FUNCTIONS ---
function saveDraftOrders() {
    localStorage.setItem('pos_orders', JSON.stringify(orders));
    
    orders.forEach(order => {
        $.ajax({
            url: 'includes/pos_order_drafts.php',
            method: 'POST',
            data: { 
                action: 'save', 
                order: JSON.stringify(order) 
            },
            success: function(response) {},
            error: function(xhr) {
                console.error('Failed to save order:', order.id, xhr.responseText);
            }
        });
    });
}

function loadDraftOrdersFromLocal() {
    let local = localStorage.getItem('pos_orders');
    if(local) {
        try {
            return JSON.parse(local) || [];
        } catch(e) { 
            console.error('Error parsing localStorage:', e);
            return []; 
        }
    }
    return [];
}

function loadDraftOrdersFromDB(callback) {
    $.ajax({
        url: 'includes/pos_order_drafts.php',
        method: 'GET',
        data: {action: 'load'},
        dataType: 'json',
        success: function(data) {
            callback(Array.isArray(data) ? data : []);
        },
        error: function(xhr) {
            console.error('Failed to load from DB:', xhr.responseText);
            callback([]);
        }
    });
}

function mergeOrders(local, db) {
    let map = {};
    
    db.forEach(o => {
        if (o && o.id) {
            map[o.id] = o;
        }
    });
    
    local.forEach(o => {
        if (!o || !o.id) return;
        
        if (!map[o.id]) {
            map[o.id] = o;
        } else {
            if (o.items && o.items.length > 0) {
                if (!map[o.id].items || map[o.id].items.length === 0) {
                    map[o.id].items = o.items;
                } else if (o.items.length > map[o.id].items.length) {
                    map[o.id].items = o.items;
                }
            }
        }
    });
    
    return Object.values(map);
}

// --- SCROLL POSITION FUNCTIONS ---
function saveScrollPosition() {
    let container = document.querySelector('.orders-tabs-card');
    if (container) {
        savedScrollPosition = container.scrollLeft;
    }
}

function restoreScrollPosition() {
    let container = document.querySelector('.orders-tabs-card');
    if (container) {
        container.scrollLeft = savedScrollPosition;
    }
}

function scrollActiveTabIntoView() {
    setTimeout(() => {
        let container = document.querySelector('.orders-tabs-card');
        let activeTab = document.querySelector('.order-tab.active');
        
        if (container && activeTab) {
            let containerRect = container.getBoundingClientRect();
            let tabRect = activeTab.getBoundingClientRect();
            
            let tabLeft = tabRect.left - containerRect.left + container.scrollLeft;
            let tabRight = tabLeft + tabRect.width;
            
            if (tabLeft < container.scrollLeft) {
                container.scrollLeft = tabLeft - 20;
            } else if (tabRight > container.scrollLeft + container.clientWidth) {
                container.scrollLeft = tabRight - container.clientWidth + 20;
            }
        }
    }, 50);
}

// --- FINANCIAL CALCULATION FUNCTIONS ---
function calculateFinancials() {
    let order = orders.find(o => o.id === activeOrderId);
    if (!order) return { subtotal: 0, discount: 0, tax: 0, deliveryFee: 0, total: 0 };
    
    let subtotal = 0;
    if (order.items && order.items.length > 0) {
        subtotal = order.items.reduce((sum, item) => {
            return sum + (item.qty * item.price);
        }, 0);
    }
    
    let discount = 0;
    if (discountType === 'fixed') {
        discount = Math.min(discountAmount, subtotal);
    } else {
        discount = (subtotal * discountAmount) / 100;
        discount = Math.min(discount, subtotal);
    }
    
    let deliveryFee = 0;
    if (order.type === 'delivery') {
        $('#deliveryFeeRow').show();
        // Use the value from the input, default to 0
        let inputVal = parseFloat($('#deliveryFeeInput').val());
        deliveryFee = isNaN(inputVal) ? 0 : inputVal;
    } else {
        $('#deliveryFeeRow').hide();
    }
    
    let taxableAmount = subtotal - discount;
    let tax = taxableAmount * TAX_RATE;
    let total = taxableAmount + tax + deliveryFee;
    
    return {
        subtotal: subtotal,
        discount: discount,
        tax: tax,
        deliveryFee: deliveryFee,
        total: total
    };
}

function calculateOrderTotal(order) {
    if (!order || !order.items) return 0;
    return order.items.reduce((sum, item) => {
        return sum + (item.qty * item.price);
    }, 0);
}

// --- RENDERING FUNCTIONS ---
function renderTabs() {
    saveScrollPosition();
    
    let html = '';
    let visibleOrders = orders.filter(order => order && !order.is_deleted);
    
    // Update active orders count card
    $('#activeOrdersCount').text(visibleOrders.length);
    
    // Breakdown by type
    let breakdown = {
        dine_in: 0,
        pickup: 0,
        delivery_internal: 0,
        delivery_noon: 0,
        delivery_keeta: 0,
        delivery_deliveroo: 0,
        delivery_smile: 0
    };
    
    visibleOrders.forEach(order => {
        if (order.type === 'dine_in') breakdown.dine_in++;
        else if (order.type === 'pickup') breakdown.pickup++;
        else if (order.type === 'delivery') {
            let src = order.delivery_source || 'internal';
            if (src === 'internal') breakdown.delivery_internal++;
            else if (src === 'noon') breakdown.delivery_noon++;
            else if (src === 'keeta') breakdown.delivery_keeta++;
            else if (src === 'deliveroo') breakdown.delivery_deliveroo++;
            else if (src === 'smile') breakdown.delivery_smile++;
        }
    });
    
    let breakdownHtml = '';
    breakdownHtml += `<span class="badge me-1" style="background:#3498db;"><i class="bi bi-shop"></i> Dine: ${breakdown.dine_in}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#f39c12;"><i class="bi bi-bag"></i> Pickup: ${breakdown.pickup}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#27ae60;"><i class="bi bi-truck"></i> Del: ${breakdown.delivery_internal}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#fbb034;"><i class="bi bi-sun"></i> Noon: ${breakdown.delivery_noon}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#e74c3c;"><i class="bi bi-bicycle"></i> Keeta: ${breakdown.delivery_keeta}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#00c3e3;"><i class="bi bi-bag-check"></i> Roo: ${breakdown.delivery_deliveroo}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#f1c40f;"><i class="bi bi-emoji-smile"></i> Smile: ${breakdown.delivery_smile}</span>`;
    $('#ordersTypeBreakdown').html(breakdownHtml);
    
    visibleOrders.forEach(order => {
        let active = order.id === activeOrderId ? 'active' : '';
        let typeColor = '';
        let typeIcon = '';
        let deliveryBadge = '';
        
        if(order.type === 'dine_in'){
            typeColor = 'background:linear-gradient(135deg,#3498db,#6dd5fa);color:#fff;';
            typeIcon = '<i class="bi bi-shop me-1"></i>';
        } else if(order.type === 'pickup'){
            typeColor = 'background:linear-gradient(135deg,#f39c12,#f7b733);color:#fff;';
            typeIcon = '<i class="bi bi-bag me-1"></i>';
        } else if(order.type === 'delivery'){
            typeColor = 'background:linear-gradient(135deg,#2ecc71,#27ae60);color:#fff;';
            typeIcon = '<i class="bi bi-truck me-1"></i>';
            let src = order.delivery_source || 'internal';
            let srcMap = {
                internal: {label:'Restaurant',color:'#2ecc71',icon:'<i class="bi bi-shop"></i>'},
                noon: {label:'Noon',color:'#fbb034',icon:'<i class="bi bi-sun"></i>'},
                keeta: {label:'Keeta',color:'#e74c3c',icon:'<i class="bi bi-bicycle"></i>'},
                deliveroo: {label:'Deliveroo',color:'#00c3e3',icon:'<i class="bi bi-bag-check"></i>'},
                smile: {label:'Smile',color:'#f1c40f',icon:'<i class="bi bi-emoji-smile"></i>'}
            };
            if(srcMap[src]){
                deliveryBadge = `<span class="badge ms-1" style="background:${srcMap[src].color};color:#fff;font-size:0.8em;vertical-align:middle;">${srcMap[src].icon} ${srcMap[src].label}</span>`;
            }
        }
        
        let customerName = order.customer && order.customer.name ? order.customer.name : 'Guest';
        
        html += `
            <div class="order-tab ${active}" 
                 style="${typeColor}margin-right:8px;min-width:180px;display:inline-block;cursor:pointer;border-radius:8px;padding:8px 16px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid ${active ? "#c41e3a" : "transparent"};position:relative;" 
                 onclick="switchOrder('${order.id}')">
                <button class="delete-order-tab-btn" onclick="event.stopPropagation(); showDeleteModal('${order.id}')" title="Delete order" style="position:absolute; top:6px; right:6px; width:22px; height:22px; border-radius:50%; background:rgba(255,255,255,0.9); border:1px solid #dc3545; color:#dc3545; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:10; font-size:13px; padding:0; box-shadow:0 2px 4px rgba(0,0,0,0.1);"><i class="bi bi-x"></i></button>
                <div style="font-weight:600;">
                    ${typeIcon}${order.type.toUpperCase()} - ${customerName} ${deliveryBadge}
                </div>
                <div style="font-size:0.85rem;opacity:0.9;">
                    Items: ${order.items ? order.items.length : 0}
                </div>
            </div>
        `;
    });
    
    if (visibleOrders.length === 0) {
        html = '<div class="text-muted p-2">No active orders. Click "Punch New Order" to start.</div>';
    }
    
    $('#ordersTabs').html(html);
    restoreScrollPosition();
    scrollActiveTabIntoView();
}

function renderOrder() {
    if (isLoading) return;
    
    let order = orders.find(o => o.id === activeOrderId);
    if (!order) {
        $('#orderItemsBody').html('<tr><td colspan="5" class="text-center text-muted">Select an order to begin</td></tr>');
        $('#summarySubtotal').text('0.00 AED');
        $('#summaryDiscount').text('-0.00 AED');
        $('#summaryTax').text('0.00 AED');
        $('#summaryDeliveryFee').text('0.00 AED');
        $('#orderTotal').text('0.00 AED');
        $('#btnSendKitchen, #btnPrint').prop('disabled', true);
        return;
    }

    if (!order.items) order.items = [];

    let body = $('#orderItemsBody');
    body.html('');

    order.items.forEach((item, i) => {
        if (!item.name) item.name = 'Unknown Item';
        if (!item.price) item.price = 0;
        if (!item.qty) item.qty = 1;
        
        let line = item.qty * item.price;
        
        body.append(`
            <tr>
                <td style="font-size:1.15rem; font-weight:700; color:#222;">${item.name}</td>
                <td>
                    <div class="input-group input-group-sm justify-content-center">
                        <button class="btn btn-qty-minus btn-sm" data-index="${i}" style="background:linear-gradient(135deg,#e74c3c,#f39c12);color:#fff;border:none;width:32px;">-</button>
                        <span class="form-control text-center border-0" style="width:40px;background:transparent;">${item.qty}</span>
                        <button class="btn btn-qty-plus btn-sm" data-index="${i}" style="background:linear-gradient(135deg,#27ae60,#2ecc71);color:#fff;border:none;width:32px;">+</button>
                    </div>
                </td>
                <td>${item.price.toFixed(2)}</td>
                <td>${line.toFixed(2)}</td>
                <td><button class="btn btn-sm btn-danger" onclick="removeItem(${i})">×</button></td>
            </tr>
        `);
    });

    if (order.items.length === 0) {
        body.html(`
            <tr>
                <td colspan="5">
                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                        <div style="font-size:2.2rem; color:#f39c12; margin-bottom:0.5em;"><i class="bi bi-emoji-neutral"></i></div>
                        <div class="card shadow-sm p-3 mb-2" style="border-radius:12px; background:linear-gradient(135deg,#fffbe6,#fff); border:1px solid #f39c12; max-width:340px;">
                            <div style="font-size:1.1rem; color:#c41e3a; font-weight:600;">No items added</div>
                            <div style="font-size:0.98rem; color:#495057;">Click on menu items to add them to the order.</div>
                        </div>
                    </div>
                </td>
            </tr>
        `);
    }

    let finances = calculateFinancials();
    $('#summarySubtotal').text(finances.subtotal.toFixed(2) + ' AED');
    $('#summaryDiscount').text('-' + finances.discount.toFixed(2) + ' AED');
    $('#summaryTax').text(finances.tax.toFixed(2) + ' AED');
    $('#summaryDeliveryFee').text(finances.deliveryFee.toFixed(2) + ' AED');
    $('#orderTotal').text(finances.total.toFixed(2) + ' AED');
    // Set delivery fee input value if delivery
    if (order.type === 'delivery') {
        $('#deliveryFeeInput').val(order.delivery_fee !== undefined ? order.delivery_fee : 0);
    }
    $('#btnSendKitchen, #btnPrint').prop('disabled', order.items.length === 0);
    saveDraftOrders();
// Delivery fee input handler
$(document).on('input', '#deliveryFeeInput', function() {
    let order = orders.find(o => o.id === activeOrderId);
    if (order && order.type === 'delivery') {
        let val = parseFloat($(this).val());
        order.delivery_fee = isNaN(val) ? 0 : val;
        renderOrder();
    }
});
}

function ordersChanged() {
    renderTabs();
    renderOrder();
    saveDraftOrders();
}

function switchOrder(id) {
    activeOrderId = id;
    discountAmount = 0;
    discountType = 'fixed';
    $('#discountAmount').val(0);
    $('#discountTypeGroup .btn[data-discount-type="fixed"]').click();
    
    // Reset payment button when switching orders
    resetPaymentButton();
    renderTabs();
    renderOrder();
}

function resetPaymentButton() {
    selectedPaymentMethod = null;
    paymentReference = '';
    $('#btnPaymentAction')
        .removeClass('btn-primary')
        .addClass('btn-success')
        .html('<i class="bi bi-credit-card me-2"></i>Choose Payment Method')
        .prop('disabled', false);
}

function removeItem(i) {
    // Allow both admin and super-admin to remove/cancel order items
    <?php if (!in_array($_SESSION['role'], ['admin', 'super-admin'])): ?>
        showNotification('Only Admin or Super Admin can remove items.', 'danger');
        return;
    <?php endif; ?>
    let order = orders.find(o => o.id === activeOrderId);
    if (order && order.items) {
        order.items.splice(i, 1);
        ordersChanged();
    }
}

function loadMenu(category) {
    $.get('includes/get_menu_items.php', {category_id: category}, function(data) {
        $('#menuItems').html(data);
    }).fail(function() {
        $('#menuItems').html('<div class="alert alert-danger">Failed to load menu items</div>');
    });
}

// --- TABLE SELECTOR FUNCTIONS ---
function renderTableSelector() {
    const tables = Array.from({length: 15}, (_, i) => ({ id: 'T'+(i+1), label: 'Table ' + (i+1), type: 'table' }));
    const halls = [
        { id: 'HALL', label: 'Hall', type: 'hall' },
        { id: 'FAMILY', label: 'Family Hall', type: 'family' }
    ];
    
    let occupied = new Set();
    orders.forEach(o => {
        if(o.type === 'dine_in' && o.table_number && o.items && o.items.length > 0) {
            occupied.add(o.table_number);
        }
    });
    
    let html = '';
    tables.concat(halls).forEach(t => {
        let isOccupied = occupied.has(t.id);
        html += `<button type="button" class="btn btn-outline-${isOccupied ? 'secondary' : 'danger'} table-btn mb-1" data-table="${t.id}" style="min-width:90px;${isOccupied?'opacity:0.5;pointer-events:none;':''}">${t.label}</button>`;
    });
    
    $('#tableSelector').html(html);
    $('#tableSelector .table-btn').removeClass('active');
    $('#tableNumber').val('');
}

// --- SOFT DELETE FUNCTIONS ---
function softDeleteOrder(orderId, permanent = false) {
    let order = orders.find(o => o.id === orderId);
    if (!order) return;
    
    if (permanent) {
        $.ajax({
            url: 'includes/pos_order_drafts.php',
            method: 'POST',
            data: { action: 'hard_delete', id: orderId },
            success: function(response) {
                if (response.success) {
                    let index = orders.findIndex(o => o.id === orderId);
                    if (index !== -1) {
                        orders.splice(index, 1);
                    }
                    if (activeOrderId === orderId) {
                        activeOrderId = orders.length > 0 ? orders[0].id : null;
                    }
                    ordersChanged();
                    showNotification('Order permanently deleted', 'danger');
                } else {
                    alert('Error: ' + (response.error || 'Failed to delete order'));
                }
            },
            error: function(xhr) {
                console.error('Failed to delete order:', xhr.responseText);
                alert('Server error occurred');
            }
        });
    } else {
        $.ajax({
            url: 'includes/pos_order_drafts.php',
            method: 'POST',
            data: { action: 'soft_delete', id: orderId },
            success: function(response) {
                if (response.success) {
                    lastDeletedOrder = {...order};
                    let idx = orders.findIndex(o => o.id === orderId);
                    if (idx !== -1) {
                        orders.splice(idx, 1);
                    }
                    if (activeOrderId === orderId) {
                        activeOrderId = orders.length > 0 ? orders[0].id : null;
                    }
                    ordersChanged();
                    showUndoToast(order);
                    updateTrashBinIndicator();
                } else {
                    alert('Error: ' + (response.error || 'Failed to delete order'));
                }
            },
            error: function(xhr) {
                console.error('Failed to delete order:', xhr.responseText);
                alert('Server error occurred');
            }
        });
    }
}

function restoreOrder(orderId) {
    $.ajax({
        url: 'includes/pos_order_drafts.php',
        method: 'POST',
        data: { 
            action: 'restore', 
            id: orderId 
        },
        success: function(response) {
            if (response.success) {
                loadDraftOrdersFromDB(function(dbOrders) {
                    let localOrders = loadDraftOrdersFromLocal();
                    orders = mergeOrders(localOrders, dbOrders);
                    activeOrderId = orderId;
                    ordersChanged();
                    showNotification('Order restored successfully', 'success');
                    updateTrashBinIndicator();
                });
            } else {
                alert('Error: ' + (response.error || 'Failed to restore order'));
            }
        },
        error: function(xhr) {
            console.error('Failed to restore order:', xhr.responseText);
            alert('Server error occurred');
        }
    });
}

function loadDeletedOrders() {
    $.ajax({
        url: 'includes/pos_order_drafts.php',
        method: 'GET',
        data: { action: 'load_deleted' },
        dataType: 'json',
        success: function(data) {
            deletedOrders = Array.isArray(data) ? data : [];
            displayDeletedOrders();
            updateTrashBinIndicator();
        },
        error: function(xhr) {
            console.error('Failed to load deleted orders:', xhr.responseText);
            $('#deletedOrdersList').html('<div class="alert alert-danger">Failed to load deleted orders</div>');
        }
    });
}

function displayDeletedOrders() {
    let html = '';
    
    if (deletedOrders.length === 0) {
        html = '<div class="text-center py-4"><i class="bi bi-trash3 display-1 text-muted mb-3"></i><h5 class="text-muted">Trash is empty</h5><p class="text-muted">No deleted orders found</p></div>';
        $('#emptyTrashBtn').hide();
    } else {
        html = '<div class="list-group">';
        deletedOrders.forEach(order => {
            let customerName = order.customer && order.customer.name ? order.customer.name : 'Guest';
            let itemCount = order.items ? order.items.length : 0;
            let deletedTime = order.deleted_at ? new Date(order.deleted_at).toLocaleString() : 'Unknown';
            
            html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <span class="badge bg-secondary me-2">#${order.id.substr(-6)}</span>
                                ${order.type.toUpperCase()} - ${customerName}
                            </h6>
                            <small class="text-muted">
                                <i class="bi bi-box me-1"></i>${itemCount} items |
                                <i class="bi bi-clock me-1"></i>Deleted: ${deletedTime}
                            </small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-success restore-btn" data-order-id="${order.id}">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                            <button class="btn btn-sm btn-danger hard-delete-btn" data-order-id="${order.id}">
                                <i class="bi bi-trash3"></i> Permanent
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        $('#emptyTrashBtn').show();
    }
    
    $('#deletedOrdersList').html(html);
    
    $('.restore-btn').click(function() {
        let orderId = $(this).data('order-id');
        restoreOrder(orderId);
        $('#recoveryModal').modal('hide');
    });
    
    $('.hard-delete-btn').click(function() {
        let orderId = $(this).data('order-id');
        if (confirm('Permanently delete this order? This cannot be undone!')) {
            softDeleteOrder(orderId, true);
            loadDeletedOrders();
        }
    });
}

function updateTrashBinIndicator() {
    if (deletedOrders.length > 0) {
        $('#trashBinBtn').addClass('has-items');
    } else {
        $('#trashBinBtn').removeClass('has-items');
    }
}

function showUndoToast(order) {
    if (undoTimeout) {
        clearTimeout(undoTimeout);
    }
    
    let customerName = order.customer && order.customer.name ? order.customer.name : 'Guest';
    $('#undoMessage').text(`Order for ${customerName} deleted`);
    $('#undoToast').fadeIn(300);
    
    undoTimeout = setTimeout(() => {
        $('#undoToast').fadeOut(300);
        lastDeletedOrder = null;
    }, 5000);
}

function showNotification(message, type = 'success') {
    let toast = $(`
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
            <div class="toast align-items-center text-bg-${type} border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    setTimeout(() => toast.fadeOut(300, function() { $(this).remove(); }), 3000);
}

window.showDeleteModal = function(orderId) {
    // Only allow super-admin to delete/cancel order
    <?php if ($_SESSION['role'] !== 'super-admin'): ?>
        showNotification('Only Super Admin can delete/cancel an order.', 'danger');
        return;
    <?php endif; ?>
    let order = orders.find(o => o.id === orderId);
    if (!order) return;
    let customerName = order.customer && order.customer.name ? order.customer.name : 'Guest';
    let itemCount = order.items ? order.items.length : 0;
    $('#deleteOrderDetails').html(`
        <strong>Order #${orderId.substr(-6)}</strong><br>
        Type: ${order.type.toUpperCase()}<br>
        Customer: ${customerName}<br>
        Items: ${itemCount}<br>
        Total: ${calculateOrderTotal(order).toFixed(2)} AED
    `);
    $('#confirmDeleteBtn').data('order-id', orderId);
    $('#deleteOrderModal').modal('show');
};

// --- PAYMENT & CLOSE ORDER FUNCTIONS ---
function openPaymentModal() {
    if (!activeOrderId) {
        alert('No active order selected');
        return;
    }
    
    let order = orders.find(o => o.id === activeOrderId);
    if (!order || order.items.length === 0) {
        alert('Cannot close an empty order');
        return;
    }
    
    // Calculate finances
    let finances = calculateFinancials();
    
    // Fill modal summary
    $('#modalOrderNumber').text(order.id.substr(-8));
    $('#modalCustomerName').text(order.customer ? order.customer.name : 'Guest');
    $('#modalTotal').text(finances.total.toFixed(2) + ' AED');
    
    // Reset modal fields
    $('.payment-method-card').removeClass('selected border border-3 border-success shadow');
    $('#selectedPaymentMethod').val('');
    $('#referenceField').hide();
    $('#paymentReference').val('');
    $('#confirmPaymentMethod').prop('disabled', true);
    
    // Show modal
    $('#paymentMethodModal').modal('show');
}

function closeOrderAndSave() {
    if (!selectedPaymentMethod) {
        alert('Please select a payment method first');
        return;
    }
    
    let order = orders.find(o => o.id === activeOrderId);
    if (!order) return;
    
    let finances = calculateFinancials();
    paymentReference = $('#paymentReference').val();
    
    // Disable button
    let btn = $('#btnPaymentAction');
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
    btn.prop('disabled', true);
    
    // Prepare data - only send what's needed
    // Map 'debit' to 'online' for DB
    let paymentMethodToSend = selectedPaymentMethod === 'debit' ? 'online' : selectedPaymentMethod;
    let saveData = {
        order_id: activeOrderId,
        payment_method: paymentMethodToSend,
        payment_reference: paymentReference,
        discount_amount: discountAmount,
        discount_type: discountType
    };
    
    console.log('Sending data:', saveData); // Debug log
    
    $.ajax({
        url: 'includes/ajax/close_order_from_draft.php',
        method: 'POST',
        data: JSON.stringify(saveData),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            console.log('Success response:', response); // Debug log
            if (response.success) {
                // Remove order from active orders
                orders = orders.filter(o => o.id !== activeOrderId);
                
                // Set active order to next available
                if (orders.length > 0) {
                    activeOrderId = orders[0].id;
                } else {
                    activeOrderId = null;
                }
                
                // Update UI
                ordersChanged();
                
                // Show success modal
                $('#successOrderNumber').text('Order #' + response.order_number);
                let successModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));
                successModal.show();
                
                // Reset payment button
                resetPaymentButton();
                
                // Clear from localStorage
                let localOrders = JSON.parse(localStorage.getItem('pos_orders') || '[]');
                localOrders = localOrders.filter(o => o.id !== activeOrderId);
                localStorage.setItem('pos_orders', JSON.stringify(localOrders));
                
            } else {
                alert('Error: ' + response.message);
                btn.html('<i class="bi bi-check2-circle me-2"></i>Save & Close Order');
                btn.prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Response Text:', xhr.responseText);
            let errorMsg = 'Server error occurred';
            try {
                const resp = JSON.parse(xhr.responseText);
                if (resp && resp.message) errorMsg = resp.message;
            } catch (e) {
                if (xhr.responseText) errorMsg = xhr.responseText;
            }
            alert('Error: ' + errorMsg);
            btn.html('<i class="bi bi-check2-circle me-2"></i>Save & Close Order');
            btn.prop('disabled', false);
        }
    });
}

// --- INITIALIZATION ---
$(document).ready(function() {
    $('.category-item:first').addClass('active');
    
    loadDraftOrdersFromDB(function(dbOrders) {
        let localOrders = loadDraftOrdersFromLocal();
        orders = mergeOrders(localOrders, dbOrders);
        
        if (orders.length > 0) {
            activeOrderId = orders[0].id;
        }
        
        isLoading = false;
        renderTabs();
        renderOrder();
        
        let firstCat = <?= json_encode($firstCat) ?>;
        if (firstCat) loadMenu(firstCat);
    });

    // Category click handler
    $(document).on('click', '.category-item', function() {
        $('.category-item').removeClass('active');
        $(this).addClass('active');
        loadMenu($(this).data('category'));
    });

    // Menu item click handler
    $(document).on('click', '.menu-item', function() {
        if (!activeOrderId) {
            alert('Please create or select an order first');
            return;
        }
        
        let order = orders.find(o => o.id === activeOrderId);
        if (!order) return;

        let id = $(this).data('id');
        let name = $(this).data('name');
        let price = parseFloat($(this).data('price'));

        let existing = order.items.find(item => item.id === id);
        if (existing) {
            existing.qty += 1;
        } else {
            order.items.push({id, name, price, qty: 1});
        }
        ordersChanged();
    });

    // Quantity buttons
    $(document).on('click', '.btn-qty-plus', function() {
        let order = orders.find(o => o.id === activeOrderId);
        let idx = $(this).data('index');
        if (order && order.items && order.items[idx]) {
            order.items[idx].qty += 1;
            ordersChanged();
        }
    });

    $(document).on('click', '.btn-qty-minus', function() {
        let order = orders.find(o => o.id === activeOrderId);
        let idx = $(this).data('index');
        if (order && order.items && order.items[idx]) {
            if (order.items[idx].qty > 1) {
                order.items[idx].qty -= 1;
            } else {
                order.items.splice(idx, 1);
            }
            ordersChanged();
        }
    });

    // New order button
    $('#btnNewOrder').click(function() {
        $('#orderTypeSelect').val("");
        $('#deliverySource').val("internal");
        $('#customerName').val("");
        $('#customerPhone').val("");
        $('#customerAddress').val("");
        $('.order-type-card').removeClass('border border-3 border-primary shadow');
        $('.delivery-source-card').removeClass('border border-3 border-warning shadow');
        $('#deliveryOptions').addClass('d-none');
        $('#customerAddress').addClass('d-none');
        $('#dineInFields').addClass('d-none');
        $('#initOrderModal').modal('show');
    });

    // Order type selection
    $(document).on('click', '.order-type-card', function() {
        $('.order-type-card').removeClass('border border-3 border-primary shadow');
        $(this).addClass('border border-3 border-primary shadow');
        let type = $(this).data('type');
        $('#orderTypeSelect').val(type);
        
        if(type === 'delivery'){
            $('#deliveryOptions').removeClass('d-none');
            $('#customerAddress').removeClass('d-none');
            $('#dineInFields').addClass('d-none');
        } else if(type === 'dine_in'){
            $('#deliveryOptions').addClass('d-none');
            $('#customerAddress').addClass('d-none');
            $('#dineInFields').removeClass('d-none');
            renderTableSelector();
        } else {
            $('#deliveryOptions').addClass('d-none');
            $('#customerAddress').addClass('d-none');
            $('#dineInFields').addClass('d-none');
        }
    });

    // Table selection
    $(document).on('click', '#tableSelector .table-btn', function() {
        $('#tableSelector .table-btn').removeClass('active');
        $(this).addClass('active');
        let table = $(this).data('table');
        if($('#tableNumber').length === 0) {
            $('<input type="hidden" id="tableNumber">').appendTo('#dineInFields');
        }
        $('#tableNumber').val(table);
    });

    // Delivery source selection
    $(document).on('click', '.delivery-source-card', function() {
        $('.delivery-source-card').removeClass('border border-3 border-warning shadow');
        $(this).addClass('border border-3 border-warning shadow');
        let source = $(this).data('source');
        $('#deliverySource').val(source);
    });

    // Confirm create order
    $('#confirmCreateOrder').off('click').on('click', function(){
        let type = $('#orderTypeSelect').val();
        if(!type) return alert('Select type');
        
            let order = {
                id: 'ORD' + Date.now(),
                type: type,
                delivery_source: $('#deliverySource').val(),
                customer: {
                    name: $('#customerName').val() || 'Guest',
                    phone: $('#customerPhone').val() || '',
                    address: $('#customerAddress').val() || ''
                },
                table_number: type === 'dine_in' ? $('#tableNumber').val() : null,
                num_customers: type === 'dine_in' ? $('#numCustomers').val() : null,
                items: [],
                order_status: 'pending'
            };
        
        orders.push(order);
        activeOrderId = order.id;
        ordersChanged();
        $('#initOrderModal').modal('hide');
    });



    // --- QZ Tray Integration for Printing Receipts ---
    // Improved: Wait for QZ Tray script to load before printing
    let qzTrayLoaded = false;
    let qzTrayLoading = false;
    function ensureQZTrayLoaded(callback) {
        if (window.qz) {
            qzTrayLoaded = true;
            callback();
            return;
        }
        if (qzTrayLoading) {
            setTimeout(() => ensureQZTrayLoaded(callback), 200);
            return;
        }
        qzTrayLoading = true;
        var qzScript = document.createElement('script');
        qzScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/qz-tray/2.1.0/qz-tray.js';
        qzScript.onload = function() {
            qzTrayLoaded = true;
            callback();
        };
        qzScript.onerror = function() {
            // Try loading from local QZ Tray websocket server as fallback
            var localScript = document.createElement('script');
            localScript.src = 'https://localhost:8181/qz-tray.js';
            localScript.onload = function() {
                qzTrayLoaded = true;
                callback();
            };
            localScript.onerror = function() {
                alert('Failed to load QZ Tray script from both CDN and local server.\nPlease ensure QZ Tray is running and accessible.');
            };
            document.head.appendChild(localScript);
        };
        document.head.appendChild(qzScript);
    }

    function printReceiptQZ(orderId, type) {
        ensureQZTrayLoaded(function() {
            const printerName = type === 'kitchen' ? 'XP-80C' : 'POS-80C';
            const url = `includes/print_receipt.php?id=${orderId}&type=${type}`;
            fetch(url)
                .then(res => res.text())
                .then(data => {
                    if (!window.qz) { alert('QZ Tray not available!'); return; }
                    qz.websocket.connect().then(() => qz.printers.find(printerName))
                    .then(printer => {
                        var config = qz.configs.create(printer, { encoding: 'UTF-8' });
                        var printData = [{ type: 'raw', format: 'plain', data: data }];
                        return qz.print(config, printData);
                    })
                    .catch(e => alert('QZ Print Error: ' + e));
                });
        });
    }

    // Print Receipt (Counter)
    $('#btnPrint').off('click').on('click', function() {
        if (!activeOrderId) return;
        let order = orders.find(o => o.id === activeOrderId);
        if (!order || order.items.length === 0) {
            alert('No items to print');
            return;
        }
        printReceiptQZ(order.id, 'counter');
    });

    // Send to Kitchen (Kitchen Printer)
    $('#btnSendKitchen').off('click').on('click', function() {
        if (!activeOrderId) return;
        let order = orders.find(o => o.id === activeOrderId);
        if (!order || order.items.length === 0) {
            alert('No items to send to kitchen');
            return;
        }
        printReceiptQZ(order.id, 'kitchen');
        order.order_status = 'in_preparation';
        ordersChanged();
        alert('Order sent to kitchen!');
    });

    // Discount handlers
    $('#discountAmount').on('input', function() {
        let val = parseFloat($(this).val()) || 0;
        if (val < 0) val = 0;
        discountAmount = val;
        renderOrder();
    });

    $('#discountTypeGroup .btn').click(function() {
        $('#discountTypeGroup .btn').removeClass('active');
        $(this).addClass('active');
        discountType = $(this).data('discount-type');
        
        if (discountType === 'fixed') {
            $('#discountAmount').attr('placeholder', 'Amount');
        } else {
            $('#discountAmount').attr('placeholder', 'Percentage');
        }
        
        renderOrder();
    });

    $('.discount-preset').click(function() {
        let preset = $(this).data('preset');
        $('#discountTypeGroup .btn[data-discount-type="percentage"]').click();
        $('#discountAmount').val(preset);
        discountAmount = preset;
        renderOrder();
    });

    // Hold order button
    $('#btnHoldOrder').click(function() {
        if (!activeOrderId) return;
        
        let order = orders.find(o => o.id === activeOrderId);
        if (order) {
            order.status = 'on_hold';
            ordersChanged();
            alert('Order placed on hold');
        }
    });

    // Add note button
    $('#btnAddNote').click(function() {
        if (!activeOrderId) return;
        
        let note = prompt('Enter order note:');
        if (note !== null) {
            let order = orders.find(o => o.id === activeOrderId);
            if (order) {
                if (!order.notes) order.notes = [];
                order.notes.push({
                    text: note,
                    timestamp: new Date().toISOString()
                });
                saveDraftOrders();
                alert('Note added');
            }
        }
    });

    // Cancel order button
    $('#btnCancelOrder').click(function() {
        if (!activeOrderId) return;
        showDeleteModal(activeOrderId);
    });

    // Main payment/close button - handles both states
    $('#btnPaymentAction').click(function() {
        if (!selectedPaymentMethod) {
            // No payment method selected yet - show payment modal
            openPaymentModal();
        } else {
            // Payment method already selected - close and save order
                let order = orders.find(o => o.id === activeOrderId);
                if (order) {
                    order.order_status = 'completed';
                }
                closeOrderAndSave();
        }
    });

    // Payment method selection in modal
    $(document).on('click', '.payment-method-card', function() {
        $('.payment-method-card').removeClass('selected border border-3 border-success shadow');
        $(this).addClass('selected border border-3 border-success shadow');
        selectedPaymentMethod = $(this).data('method');
        $('#selectedPaymentMethod').val(selectedPaymentMethod);
        
        // Show reference field for card payments
        if (selectedPaymentMethod === 'card' || selectedPaymentMethod === 'credit' || selectedPaymentMethod === 'debit') {
            $('#referenceField').show();
        } else {
            $('#referenceField').hide();
        }
        
        // Enable confirm button
        $('#confirmPaymentMethod').prop('disabled', false);
    });

    // Confirm payment method button
    $('#confirmPaymentMethod').click(function() {
        // Close modal
        $('#paymentMethodModal').modal('hide');

        // Update main button to "Save & Close Order"
        $('#btnPaymentAction')
            .removeClass('btn-success')
            .addClass('btn-primary')
            .html('<i class="bi bi-check2-circle me-2"></i>Save & Close Order');

        // For all payment methods, immediately close and save order
        setTimeout(function() {
            closeOrderAndSave();
        }, 300); // slight delay to allow modal to close smoothly
    });

    // Reset modal when hidden
    $('#paymentMethodModal').on('hidden.bs.modal', function() {
        // Don't reset selectedPaymentMethod if we're just closing after selection
        // Only reset if modal is closed without selection
        if (!$('#selectedPaymentMethod').val()) {
            // Modal was closed without selecting - do nothing
        }
    });

    // Delete confirmation
    $('#confirmDeleteBtn').click(function() {
        let orderId = $(this).data('order-id');
        let permanent = $('#permanentDeleteCheck').is(':checked');
        
        softDeleteOrder(orderId, permanent);
        $('#deleteOrderModal').modal('hide');
        $('#permanentDeleteCheck').prop('checked', false);
        
        // Reset payment button if the deleted order was active
        if (orderId === activeOrderId) {
            resetPaymentButton();
        }
    });

    // Undo delete
    $('#undoDeleteBtn').click(function() {
        if (lastDeletedOrder) {
            restoreOrder(lastDeletedOrder.id);
            $('#undoToast').fadeOut(300);
            lastDeletedOrder = null;
            if (undoTimeout) {
                clearTimeout(undoTimeout);
            }
        }
    });

    // Trash bin button
    $('#trashBinBtn').click(function() {
        loadDeletedOrders();
        $('#recoveryModal').modal('show');
    });

    // Empty trash button
    $('#emptyTrashBtn').click(function() {
        if (deletedOrders.length === 0) return;
        
        if (confirm(`Permanently delete ${deletedOrders.length} orders? This cannot be undone!`)) {
            alert('Bulk delete functionality - implement based on your needs');
        }
    });

    // Modal reset
    $('#deleteOrderModal').on('hidden.bs.modal', function() {
        $('#permanentDeleteCheck').prop('checked', false);
    });

    // Scroll position save
    $('.orders-tabs-card').on('scroll', function() {
        savedScrollPosition = this.scrollLeft;
    });
});

// Make functions globally available
window.removeItem = removeItem;
window.switchOrder = switchOrder;
window.showDeleteModal = showDeleteModal;
window.calculateOrderTotal = calculateOrderTotal;
window.resetPaymentButton = resetPaymentButton;
</script>

print_receipt,php;
<?php
require_once __DIR__ . '/../../includes/database.php';

// Check if user has permission
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

// Add a check to ensure this file is accessed directly, not included by another script
if (basename(__FILE__) != basename($_SERVER['SCRIPT_FILENAME'])) {
    // If included, do nothing (prevent output and header)
    return;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$receipt_type = isset($_GET['type']) ? $_GET['type'] : 'counter'; // kitchen or counter

if (!$order_id) {
    die("Invalid order");
}

// Fetch order details
$query = "SELECT o.*, 
                 u1.full_name as punched_by_name,
                 b.name as branch_name
          FROM orders o
          LEFT JOIN users u1 ON o.punched_by_admin_id = u1.id
          LEFT JOIN branches b ON o.branch_id = b.id
          WHERE o.id = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found");
}

$order = $result->fetch_assoc();
$stmt->close();

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $connection->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Log the print
$log_stmt = $connection->prepare(
    "INSERT INTO printer_logs (order_id, receipt_type, printed_by, is_reprint) 
     VALUES (?, ?, ?, ?)"
);
$is_reprint = isset($_GET['reprint']) ? 1 : 0;
$log_stmt->bind_param("isii", $order_id, $receipt_type, $_SESSION['user_id'], $is_reprint);
$log_stmt->execute();
$log_stmt->close();

// Set content type for thermal printer (text/plain for ESC/POS)
header('Content-Type: text/html; charset=utf-8');

// Restaurant info
$restaurant_name = "YALLA AL MANDI";
$restaurant_phone = "+966 XX XXX XXXX";
$restaurant_vat = "VAT: 123456789";
$address = "Restaurant Address Line";

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - <?php echo $restaurant_name; ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #fff; color: #222; }
        .receipt-container { max-width: 400px; margin: 30px auto; background: #fff; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 24px; }
        .receipt-title { text-align: center; font-size: 1.3rem; font-weight: bold; margin-bottom: 8px; }
        .receipt-section { margin-bottom: 12px; }
        .receipt-divider { border-top: 1px dashed #aaa; margin: 8px 0; }
        .receipt-items th, .receipt-items td { font-size: 0.95rem; }
        .receipt-items th { text-align: left; }
        .receipt-items td { padding: 2px 0; }
        .receipt-summary { font-weight: bold; }
        .receipt-footer { text-align: center; margin-top: 18px; font-size: 0.95rem; color: #555; }
        @media print { .print-btn { display: none !important; } }
    </style>
</head>
<body>
<div class="receipt-container">
    <div class="receipt-title">
        <?php echo $restaurant_name; ?>
    </div>
    <div class="receipt-section">
        <?php if ($receipt_type == 'kitchen'): ?>
            <div style="text-align:center;font-weight:bold;">KITCHEN COPY</div>
            <div class="receipt-divider"></div>
            <div>Order #: <?php echo $order['order_number']; ?></div>
            <div>Date: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
            <div>Type: <?php echo strtoupper(str_replace('_', ' ', $order['order_type'])); ?></div>
            <?php if ($order['order_type'] == 'dine_in' && $order['table_number']): ?>
                <div>Table: <?php echo $order['table_number']; ?></div>
            <?php endif; ?>
            <?php if ($order['order_type'] == 'delivery' && $order['delivery_source'] == 'internal'): ?>
                <div>Delivery to:<br><?php echo nl2br(htmlspecialchars($order['delivery_address_snapshot'] ?? '')); ?></div>
            <?php endif; ?>
            <?php if ($order['delivery_source'] != 'internal'): ?>
                <div>Vendor: <?php echo strtoupper($order['delivery_source']); ?></div>
            <?php endif; ?>
            <div class="receipt-divider"></div>
            <div><strong>ITEMS:</strong></div>
            <table class="receipt-items" width="100%">
                <tbody>
                <?php $items_result->data_seek(0); while ($item = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $item['quantity']; ?>x</td>
                        <td><?php echo htmlspecialchars($item['item_name_snapshot']); ?></td>
                    </tr>
                    <?php if (!empty($item['special_instructions'])): ?>
                        <tr><td></td><td style="font-size:0.9em;color:#888;">* <?php echo htmlspecialchars($item['special_instructions']); ?></td></tr>
                    <?php endif; ?>
                <?php endwhile; ?>
                </tbody>
            </table>
            <div class="receipt-divider"></div>
            <div style="text-align:center;font-weight:bold;">KITCHEN COPY</div>
        <?php else: ?>
            <div>Invoice: <?php echo $order['invoice_number'] ?? 'INV-' . str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></div>
            <div>Order #: <?php echo $order['order_number']; ?></div>
            <div>Date: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
            <div>Cashier: <?php echo $order['punched_by_name'] ?? 'N/A'; ?></div>
            <div class="receipt-divider"></div>
            <div>Customer: <?php echo $order['customer_name_snapshot'] ?? 'Guest'; ?></div>
            <?php if ($order['customer_phone_snapshot']): ?>
                <div>Phone: <?php echo $order['customer_phone_snapshot']; ?></div>
            <?php endif; ?>
            <?php if ($order['order_type'] == 'delivery' && $order['delivery_source'] == 'internal' && $order['delivery_address_snapshot']): ?>
                <div>Address:<br><?php echo nl2br(htmlspecialchars($order['delivery_address_snapshot'])); ?></div>
            <?php endif; ?>
            <div class="receipt-divider"></div>
            <table class="receipt-items" width="100%">
                <thead>
                    <tr><th>Item</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Price</th></tr>
                </thead>
                <tbody>
                <?php $items_result->data_seek(0); while ($item = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name_snapshot']); ?></td>
                        <td style="text-align:right;"><?php echo $item['quantity']; ?></td>
                        <td style="text-align:right;"><?php echo number_format($item['unit_price_snapshot'], 2); ?></td>
                    </tr>
                    <?php if (!empty($item['special_instructions'])): ?>
                        <tr><td colspan="3" style="font-size:0.9em;color:#888;">* <?php echo htmlspecialchars($item['special_instructions']); ?></td></tr>
                    <?php endif; ?>
                <?php endwhile; ?>
                </tbody>
            </table>
            <div class="receipt-divider"></div>
            <div class="receipt-summary">Subtotal: <?php echo number_format($order['subtotal'], 2); ?> AED</div>
            <?php if ($order['discount_amount'] > 0): ?>
                <div class="receipt-summary">Discount: -<?php echo number_format($order['discount_amount'], 2); ?> AED</div>
            <?php endif; ?>
            <div class="receipt-summary">TOTAL: <?php echo number_format($order['total_amount'], 2); ?> AED</div>
            <div class="receipt-divider"></div>
            <div>Payment: <?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?></div>
            <div>Status: <?php echo strtoupper(str_replace('_', ' ', $order['payment_status'])); ?></div>
            <?php if ($order['payment_reference']): ?>
                <div>Ref: <?php echo $order['payment_reference']; ?></div>
            <?php endif; ?>
            <div class="receipt-divider"></div>
            <div class="receipt-footer">
                THANK YOU FOR YOUR ORDER<br>
                <?php echo $restaurant_phone; ?><br>
                <?php echo $restaurant_vat; ?><br>
            </div>
        <?php endif; ?>
    </div>
    <button class="btn btn-primary print-btn" onclick="window.print()" style="width:100%;margin-top:18px;">Print</button>
</div>
</body>
</html>

// Add line feeds for paper cutting
echo "\n\n\n\n";

$items_stmt->close();

// Add auto-print for browser and QZ Tray integration for direct USB printing
if (php_sapi_name() !== 'cli') {
    echo "\n<script type=\"text/javascript\">\n";
    echo "// Auto-print for browser\n";
    echo "if (typeof window !== 'undefined' && window.print) { window.print(); }\n";
    echo "\n// QZ Tray integration for direct USB printing (IRP-200D / POS-80C)\n";
    echo "// Uncomment and configure the following if QZ Tray is installed on the POS system\n";
    echo "/*\n";
    echo "// QZ Tray sample\n";
    echo "function printWithQZ() {\n";
    echo "    if (!window.qz) { alert('QZ Tray is not available!'); return; }\n";
    echo "    qz.websocket.connect().then(function() {\n";
    echo "        return qz.printers.find('POS-80C'); // Use your printer name\n";
    echo "    }).then(function(printer) {\n";
    echo "        var config = qz.configs.create(printer, { encoding: 'UTF-8', copies: 1, \n";
    echo "            size: { width: 72, height: 297, units: 'mm' } });\n";
    echo "        var data = [\n";
    echo "            { type: 'raw', format: 'plain', data: document.body.innerText }\n";
    echo "        ];\n";
    echo "        return qz.print(config, data);\n";
    echo "    }).catch(function(e) { alert('QZ Print Error: ' + e); });\n";
    echo "}\n";
    echo "// To use: call printWithQZ() from a button or on page load\n";
    echo "*/\n";
    echo "</script>\n";
}

Update the receipt printing functionality to follow the same setup as the report_monthly.php, and give me the final and complete code

Is this possible?







Notice: session_start(): Ignoring session_start() because a session is already active in /var/www/html/admin/includes/print_receipt.php on line 2

Fatal error: Uncaught mysqli_sql_exception: Data truncated for column 'receipt_type' at row 1 in /var/www/html/admin/includes/print_receipt.php:94 Stack trace: #0 /var/www/html/admin/includes/print_receipt.php(94): mysqli_stmt->execute() #1 /var/www/html/admin/orders.php(53): include('/var/www/html/a...') #2 {main} thrown in /var/www/html/admin/includes/print_receipt.php on line 94