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