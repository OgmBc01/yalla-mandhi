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

// Fetch staff performance data
$staff_query = "SELECT 
    u.id as user_id,
    u.full_name,
    u.username,
    COUNT(DISTINCT o.id) as orders_processed,
    SUM(o.total_amount) as total_sales,
    AVG(o.total_amount) as avg_order_value,
    COUNT(DISTINCT o.customer_id) as unique_customers,
    SUM(o.item_count) as items_sold,
    MIN(o.created_at) as first_order,
    MAX(o.created_at) as last_order,
    SUM(CASE WHEN o.order_type = 'dine_in' THEN 1 ELSE 0 END) as dine_in_orders,
    SUM(CASE WHEN o.order_type = 'pickup' THEN 1 ELSE 0 END) as pickup_orders,
    SUM(CASE WHEN o.order_type = 'delivery' THEN 1 ELSE 0 END) as delivery_orders,
    SUM(CASE WHEN o.payment_method = 'cash' THEN o.total_amount ELSE 0 END) as cash_sales,
    SUM(CASE WHEN o.payment_method = 'card' THEN o.total_amount ELSE 0 END) as card_sales,
    SUM(CASE WHEN o.payment_method = 'online' THEN o.total_amount ELSE 0 END) as online_sales
    FROM users u
    LEFT JOIN orders o ON (o.punched_by_admin_id = u.id OR o.closed_by_admin_id = u.id)
        AND DATE(o.created_at) BETWEEN ? AND ?
        AND o.order_status IN ('completed', 'closed')
    WHERE u.role IN ('admin', 'super-admin', 'manager', 'cashier')
    GROUP BY u.id
    HAVING orders_processed > 0
    ORDER BY total_sales DESC";

$stmt = $connection->prepare($staff_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$staff_result = $stmt->get_result();

// Calculate team totals
$team_totals = [
    'orders' => 0,
    'sales' => 0,
    'items' => 0,
    'customers' => 0
];

$staff_data = [];
while ($row = $staff_result->fetch_assoc()) {
    $staff_data[] = $row;
    $team_totals['orders'] += $row['orders_processed'];
    $team_totals['sales'] += $row['total_sales'];
    $team_totals['items'] += $row['items_sold'];
    $team_totals['customers'] += $row['unique_customers'];
}
$stmt->close();

// Calculate daily averages
$days_in_period = max(1, (strtotime($end_date) - strtotime($start_date)) / 86400 + 1);
$avg_daily_orders = round($team_totals['orders'] / $days_in_period, 1);
$avg_daily_sales = round($team_totals['sales'] / $days_in_period, 2);
?>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="page-title">Sales Staff Performance Report</h2>
        <!-- Date Range Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <input type="hidden" name="source" value="staff">
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">From Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">To Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>" required>
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 me-2">
                            <i class="bi bi-filter-circle me-2"></i>Generate Report
                        </button>
                        <a href="includes/export_report.php?source=staff&export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success me-2">
                            <i class="bi bi-file-earmark-excel"></i> CSV
                        </a>
                        <a href="includes/export_report.php?source=staff&export=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Team Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Team Members</h6>
                        <h3 class="text-white mb-0"><?php echo count($staff_data); ?></h3>
                        <small>active staff</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Orders</h6>
                        <h3 class="text-white mb-0"><?php echo $team_totals['orders']; ?></h3>
                        <small><?php echo $avg_daily_orders; ?> per day</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Sales</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($team_totals['sales'], 2); ?> AED</h3>
                        <small><?php echo number_format($avg_daily_sales, 2); ?> AED/day</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Items Sold</h6>
                        <h3 class="text-white mb-0"><?php echo $team_totals['items']; ?></h3>
                        <small><?php echo $team_totals['customers']; ?> customers</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Performance Chart -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Staff Sales Comparison</h5>
            </div>
            <div class="card-body">
                <canvas id="staffChart" height="300"></canvas>
            </div>
        </div>

        <!-- Staff Performance Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Individual Staff Performance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-report">
                        <thead>
                            <tr>
                                <th>Staff Member</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">Total Sales</th>
                                <th class="text-end">Avg Order</th>
                                <th class="text-center">Items Sold</th>
                                <th class="text-center">Customers</th>
                                <th class="text-center">Dine In</th>
                                <th class="text-center">Pickup</th>
                                <th class="text-center">Delivery</th>
                                <th class="text-end">Cash</th>
                                <th class="text-end">Card</th>
                                <th class="text-end">Online</th>
                                <th class="text-center">Share %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($staff_data)): ?>
                            <tr>
                                <td colspan="13" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block text-muted mb-3"></i>
                                    <h5>No staff performance data found</h5>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php 
                                $rank = 1;
                                foreach ($staff_data as $staff): 
                                $share = $team_totals['sales'] > 0 ? round($staff['total_sales'] / $team_totals['sales'] * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($staff['full_name'] ?: $staff['username']); ?></strong>
                                        <?php if ($rank == 1): ?>
                                            <span class="badge bg-warning ms-2">Top Performer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold"><?php echo $staff['orders_processed']; ?></td>
                                    <td class="text-end text-success"><?php echo number_format($staff['total_sales'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($staff['avg_order_value'], 2); ?></td>
                                    <td class="text-center"><?php echo $staff['items_sold']; ?></td>
                                    <td class="text-center"><?php echo $staff['unique_customers']; ?></td>
                                    <td class="text-center"><?php echo $staff['dine_in_orders']; ?></td>
                                    <td class="text-center"><?php echo $staff['pickup_orders']; ?></td>
                                    <td class="text-center"><?php echo $staff['delivery_orders']; ?></td>
                                    <td class="text-end"><?php echo number_format($staff['cash_sales'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($staff['card_sales'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($staff['online_sales'], 2); ?></td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 5px; width: 60px; display: inline-block;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $share; ?>%"></div>
                                        </div>
                                        <small class="ms-1"><?php echo $share; ?>%</small>
                                    </td>
                                </tr>
                                <?php 
                                $rank++;
                                endforeach; 
                                ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Staff Activity Summary -->
        <?php if (!empty($staff_data)): 
            // Aggregate totals for distribution charts
            $total_dine_in = array_sum(array_column($staff_data, 'dine_in_orders'));
            $total_pickup = array_sum(array_column($staff_data, 'pickup_orders'));
            $total_delivery = array_sum(array_column($staff_data, 'delivery_orders'));
            $total_cash = array_sum(array_column($staff_data, 'cash_sales'));
            $total_card = array_sum(array_column($staff_data, 'card_sales'));
            $total_online = array_sum(array_column($staff_data, 'online_sales'));
        ?>
        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <div class="chart-container">
                    <h5 class="mb-3"><i class="bi bi-pie-chart me-2"></i>Order Type Distribution</h5>
                    <canvas id="orderTypeDistChart" height="250"></canvas>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="chart-container">
                    <h5 class="mb-3"><i class="bi bi-credit-card me-2"></i>Payment Method Distribution</h5>
                    <canvas id="paymentDistChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Staff Sales Chart
    const staffLabels = <?php echo json_encode(array_column($staff_data, 'full_name') ?: []); ?>;
    const staffSales = <?php echo json_encode(array_column($staff_data, 'total_sales') ?: []); ?>;
    const staffOrders = <?php echo json_encode(array_column($staff_data, 'orders_processed') ?: []); ?>;

    if (staffLabels.length > 0) {
        const ctx1 = document.getElementById('staffChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: staffLabels,
                datasets: [{
                    label: 'Sales (AED)',
                    data: staffSales,
                    backgroundColor: '#c41e3a',
                    borderRadius: 5,
                    yAxisID: 'y'
                }, {
                    label: 'Orders',
                    data: staffOrders,
                    backgroundColor: '#f39c12',
                    borderRadius: 5,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Sales (AED)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Orders'
                        }
                    }
                }
            }
        });
    }

    <?php if (!empty($staff_data)): ?>
    // Order Type Distribution Chart
    const ctx2 = document.getElementById('orderTypeDistChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Dine In', 'Pickup', 'Delivery'],
            datasets: [{
                data: [<?php echo $total_dine_in; ?>, <?php echo $total_pickup; ?>, <?php echo $total_delivery; ?>],
                backgroundColor: ['#3498db', '#f39c12', '#27ae60'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Payment Distribution Chart
    const ctx3 = document.getElementById('paymentDistChart').getContext('2d');
    new Chart(ctx3, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Card', 'Online'],
            datasets: [{
                data: [<?php echo $total_cash; ?>, <?php echo $total_card; ?>, <?php echo $total_online; ?>],
                backgroundColor: ['#f39c12', '#3498db', '#9b59b6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<style>
.table-report th {
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 2px solid #c41e3a;
}
.chart-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    height: 100%;
}
.progress {
    vertical-align: middle;
}
</style>