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

// Get date range from URL or set defaults
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Fetch daily sales data
$daily_query = "SELECT 
    DATE(created_at) as sale_date,
    DAYNAME(created_at) as day_name,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    SUM(CASE WHEN order_type = 'dine_in' THEN 1 ELSE 0 END) as dine_in_orders,
    SUM(CASE WHEN order_type = 'dine_in' THEN total_amount ELSE 0 END) as dine_in_revenue,
    SUM(CASE WHEN order_type = 'pickup' THEN 1 ELSE 0 END) as pickup_orders,
    SUM(CASE WHEN order_type = 'pickup' THEN total_amount ELSE 0 END) as pickup_revenue,
    SUM(CASE WHEN order_type = 'delivery' THEN 1 ELSE 0 END) as delivery_orders,
    SUM(CASE WHEN order_type = 'delivery' THEN total_amount ELSE 0 END) as delivery_revenue,
    SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END) as cash_revenue,
    SUM(CASE WHEN payment_method = 'card' THEN total_amount ELSE 0 END) as card_revenue,
    SUM(CASE WHEN payment_method = 'online' THEN total_amount ELSE 0 END) as online_revenue,
    AVG(total_amount) as avg_order_value,
    SUM(item_count) as total_items
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND order_status IN ('completed', 'closed')
    GROUP BY DATE(created_at), DAYNAME(created_at)
    ORDER BY sale_date DESC";

$stmt = $connection->prepare($daily_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$daily_result = $stmt->get_result();

// Calculate totals
$total_orders = 0;
$total_revenue = 0;
$total_cash = 0;
$total_card = 0;
$total_online = 0;

$daily_data = [];
while ($row = $daily_result->fetch_assoc()) {
    $daily_data[] = $row;
    $total_orders += $row['total_orders'];
    $total_revenue += $row['total_revenue'];
    $total_cash += $row['cash_revenue'];
    $total_card += $row['card_revenue'];
    $total_online += $row['online_revenue'];
}

// Calculate averages
$avg_daily_orders = count($daily_data) > 0 ? round($total_orders / count($daily_data), 1) : 0;
$avg_daily_revenue = count($daily_data) > 0 ? round($total_revenue / count($daily_data), 2) : 0;
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Date Range Filter -->
        <h2 class="page-title">Daily Sales Report</h2>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <input type="hidden" name="source" value="daily">
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">From Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">To Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>" required>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter-circle me-2"></i>Apply
                        </button>
                    </div>
                    
                    <div class="col-md-4 text-end">
                        <a href="reports.php?source=daily&export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success me-2">
                            <i class="bi bi-file-earmark-excel"></i> CSV
                        </a>
                        <a href="reports.php?source=daily&export=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Orders</h6>
                        <h3 class="text-white mb-0"><?php echo $total_orders; ?></h3>
                        <small>Avg: <?php echo $avg_daily_orders; ?>/day</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Revenue</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($total_revenue, 2); ?> AED</h3>
                        <small>Avg: <?php echo number_format($avg_daily_revenue, 2); ?> AED/day</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Avg Order Value</h6>
                        <h3 class="text-white mb-0"><?php echo $total_orders > 0 ? number_format($total_revenue / $total_orders, 2) : '0.00'; ?> AED</h3>
                        <small>Per transaction</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Days in Period</h6>
                        <h3 class="text-white mb-0"><?php echo count($daily_data); ?></h3>
                        <small><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Sales Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Daily Sales Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-report">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-center">Dine In</th>
                                <th class="text-center">Pickup</th>
                                <th class="text-center">Delivery</th>
                                <th class="text-end">Cash</th>
                                <th class="text-end">Card</th>
                                <th class="text-end">Online</th>
                                <th class="text-end">Avg Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daily_data)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block text-muted mb-3"></i>
                                    <h5>No data found for selected period</h5>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($daily_data as $day): ?>
                                <tr>
                                    <td><strong><?php echo date('M d, Y', strtotime($day['sale_date'])); ?></strong></td>
                                    <td><?php echo $day['day_name']; ?></td>
                                    <td class="text-center fw-bold"><?php echo $day['total_orders']; ?></td>
                                    <td class="text-end fw-bold text-success"><?php echo number_format($day['total_revenue'], 2); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo $day['dine_in_orders']; ?></span>
                                        <small class="text-muted d-block"><?php echo number_format($day['dine_in_revenue'], 0); ?> AED</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning"><?php echo $day['pickup_orders']; ?></span>
                                        <small class="text-muted d-block"><?php echo number_format($day['pickup_revenue'], 0); ?> AED</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?php echo $day['delivery_orders']; ?></span>
                                        <small class="text-muted d-block"><?php echo number_format($day['delivery_revenue'], 0); ?> AED</small>
                                    </td>
                                    <td class="text-end"><?php echo number_format($day['cash_revenue'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($day['card_revenue'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($day['online_revenue'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($day['avg_order_value'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <!-- Summary Row -->
                                <tr class="table-secondary fw-bold">
                                    <td colspan="2">TOTAL</td>
                                    <td class="text-center"><?php echo $total_orders; ?></td>
                                    <td class="text-end text-success"><?php echo number_format($total_revenue, 2); ?></td>
                                    <td class="text-center"><?php echo array_sum(array_column($daily_data, 'dine_in_orders')); ?></td>
                                    <td class="text-center"><?php echo array_sum(array_column($daily_data, 'pickup_orders')); ?></td>
                                    <td class="text-center"><?php echo array_sum(array_column($daily_data, 'delivery_orders')); ?></td>
                                    <td class="text-end"><?php echo number_format($total_cash, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($total_card, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($total_online, 2); ?></td>
                                    <td class="text-end">-</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-report th {
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 2px solid #c41e3a;
}
.table-report td {
    vertical-align: middle;
}
.badge {
    font-size: 0.8rem;
    padding: 0.35em 0.65em;
}
</style>