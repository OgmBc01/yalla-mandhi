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

// Fetch summary statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN order_status IN ('completed', 'closed') THEN total_amount ELSE 0 END) as total_revenue,
    AVG(CASE WHEN order_status IN ('completed', 'closed') THEN total_amount ELSE NULL END) as avg_order_value,
    SUM(item_count) as total_items_sold,
    COUNT(DISTINCT customer_id) as unique_customers,
    SUM(CASE WHEN payment_method = 'cash' AND order_status IN ('completed', 'closed') THEN total_amount ELSE 0 END) as cash_sales,
    SUM(CASE WHEN payment_method = 'card' AND order_status IN ('completed', 'closed') THEN total_amount ELSE 0 END) as card_sales,
    SUM(CASE WHEN payment_method = 'online' AND order_status IN ('completed', 'closed') THEN total_amount ELSE 0 END) as online_sales
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND order_status IN ('completed', 'closed')";

$stmt = $connection->prepare($stats_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch sales by order type
$type_query = "SELECT 
    order_type,
    COUNT(*) as order_count,
    SUM(total_amount) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND order_status IN ('completed', 'closed')
    GROUP BY order_type";

$stmt = $connection->prepare($type_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$type_result = $stmt->get_result();
$order_types = [];
while ($row = $type_result->fetch_assoc()) {
    $order_types[$row['order_type']] = $row;
}
$stmt->close();

// Fetch daily trends for chart
$daily_query = "SELECT 
    DATE(created_at) as sale_date,
    COUNT(*) as order_count,
    SUM(total_amount) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND order_status IN ('completed', 'closed')
    GROUP BY DATE(created_at)
    ORDER BY sale_date";

$stmt = $connection->prepare($daily_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$daily_result = $stmt->get_result();
$daily_data = [];
while ($row = $daily_result->fetch_assoc()) {
    $daily_data[] = $row;
}
$stmt->close();

// Fetch top 5 items
$items_query = "SELECT 
    oi.item_name_snapshot as item_name,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.total_price) as total_revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND o.order_status IN ('completed', 'closed')
    GROUP BY oi.item_name_snapshot
    ORDER BY total_quantity DESC
    LIMIT 5";

$stmt = $connection->prepare($items_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$top_items = $stmt->get_result();
$stmt->close();
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Date Range Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <input type="hidden" name="source" value="dashboard">
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date Range</label>
                        <select class="form-select" id="dateRangePreset" name="preset">
                            <option value="today" <?php echo ($_GET['preset'] ?? '') == 'today' ? 'selected' : ''; ?>>Today</option>
                            <option value="yesterday" <?php echo ($_GET['preset'] ?? '') == 'yesterday' ? 'selected' : ''; ?>>Yesterday</option>
                            <option value="this_week" <?php echo ($_GET['preset'] ?? '') == 'this_week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="last_week" <?php echo ($_GET['preset'] ?? '') == 'last_week' ? 'selected' : ''; ?>>Last Week</option>
                            <option value="this_month" <?php echo ($_GET['preset'] ?? 'this_month') == 'this_month' ? 'selected' : ''; ?>>This Month</option>
                            <option value="last_month" <?php echo ($_GET['preset'] ?? '') == 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                            <option value="this_year" <?php echo ($_GET['preset'] ?? '') == 'this_year' ? 'selected' : ''; ?>>This Year</option>
                            <option value="custom" <?php echo ($_GET['preset'] ?? '') == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>" id="startDate">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>" id="endDate">
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter-circle me-2"></i>Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Revenue</h6>
                            <h2 class="text-white mb-0"><?php echo number_format($stats['total_revenue'] ?? 0, 2); ?> AED</h2>
                        </div>
                        <i class="bi bi-cash-stack display-4 opacity-50"></i>
                    </div>
                    <div class="mt-3 text-white-50">
                        <i class="bi bi-arrow-up me-1"></i>
                        <?php echo $stats['total_orders'] ?? 0; ?> orders
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Avg Order Value</h6>
                            <h2 class="text-white mb-0"><?php echo number_format($stats['avg_order_value'] ?? 0, 2); ?> AED</h2>
                        </div>
                        <i class="bi bi-graph-up-arrow display-4 opacity-50"></i>
                    </div>
                    <div class="mt-3 text-white-50">
                        <i class="bi bi-box me-1"></i>
                        <?php echo $stats['total_items_sold'] ?? 0; ?> items sold
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Unique Customers</h6>
                            <h2 class="text-white mb-0"><?php echo $stats['unique_customers'] ?? 0; ?></h2>
                        </div>
                        <i class="bi bi-people display-4 opacity-50"></i>
                    </div>
                    <div class="mt-3 text-white-50">
                        <i class="bi bi-repeat me-1"></i>
                        <?php echo round(($stats['unique_customers'] ?? 0) / max(1, ($stats['total_orders'] ?? 1)) * 100, 1); ?>% repeat rate
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Period</h6>
                            <h5 class="text-white mb-0"><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></h5>
                        </div>
                        <i class="bi bi-calendar-range display-4 opacity-50"></i>
                    </div>
                    <div class="mt-3 text-white-50">
                        <i class="bi bi-clock-history me-1"></i>
                        <?php echo $stats['total_orders'] ?? 0; ?> completed orders
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-xl-8 mb-4">
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Daily Sales Trend</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" onclick="chartView('daily')">Daily</button>
                            <button class="btn btn-outline-secondary" onclick="chartView('cumulative')">Cumulative</button>
                        </div>
                    </div>
                    <canvas id="salesTrendChart" height="300"></canvas>
                </div>
            </div>
            
            <div class="col-xl-4 mb-4">
                <div class="chart-container">
                    <h5 class="mb-3">Payment Methods</h5>
                    <canvas id="paymentChart" height="250"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm table-report">
                            <tr>
                                <td><span class="badge bg-warning">Cash</span></td>
                                <td class="text-end"><?php echo number_format($stats['cash_sales'] ?? 0, 2); ?> AED</td>
                                <td class="text-end"><?php echo round(($stats['cash_sales'] ?? 0) / max(1, ($stats['total_revenue'] ?? 1)) * 100, 1); ?>%</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">Card</span></td>
                                <td class="text-end"><?php echo number_format($stats['card_sales'] ?? 0, 2); ?> AED</td>
                                <td class="text-end"><?php echo round(($stats['card_sales'] ?? 0) / max(1, ($stats['total_revenue'] ?? 1)) * 100, 1); ?>%</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">Online</span></td>
                                <td class="text-end"><?php echo number_format($stats['online_sales'] ?? 0, 2); ?> AED</td>
                                <td class="text-end"><?php echo round(($stats['online_sales'] ?? 0) / max(1, ($stats['total_revenue'] ?? 1)) * 100, 1); ?>%</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Types and Top Items Row -->
        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="chart-container">
                    <h5 class="mb-3">Sales by Order Type</h5>
                    <canvas id="orderTypeChart" height="250"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm table-report">
                            <?php 
                            $type_colors = [
                                'dine_in' => 'primary',
                                'pickup' => 'warning',
                                'delivery' => 'success'
                            ];
                            foreach ($order_types as $type => $data): 
                            ?>
                            <tr>
                                <td><span class="badge bg-<?php echo $type_colors[$type] ?? 'secondary'; ?>"><?php echo ucfirst(str_replace('_', ' ', $type)); ?></span></td>
                                <td class="text-end"><?php echo $data['order_count']; ?> orders</td>
                                <td class="text-end"><?php echo number_format($data['revenue'], 2); ?> AED</td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 mb-4">
                <div class="chart-container">
                    <h5 class="mb-3">Top Selling Items</h5>
                    <canvas id="topItemsChart" height="250"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm table-report">
                            <?php while ($item = $top_items->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td class="text-end"><?php echo $item['total_quantity']; ?> sold</td>
                                <td class="text-end"><?php echo number_format($item['total_revenue'], 2); ?> AED</td>
                            </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Sales Trend Chart
    const dailyData = <?php echo json_encode($daily_data); ?>;
    const dates = dailyData.map(d => d.sale_date);
    const orders = dailyData.map(d => d.order_count);
    const revenue = dailyData.map(d => parseFloat(d.revenue));
    
    const ctx1 = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Revenue (AED)',
                data: revenue,
                borderColor: '#c41e3a',
                backgroundColor: 'rgba(196,30,58,0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Orders',
                data: orders,
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243,156,18,0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
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
                        text: 'Revenue (AED)'
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

    // Payment Chart
    const ctx2 = document.getElementById('paymentChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Card', 'Online'],
            datasets: [{
                data: [
                    <?php echo $stats['cash_sales'] ?? 0; ?>,
                    <?php echo $stats['card_sales'] ?? 0; ?>,
                    <?php echo $stats['online_sales'] ?? 0; ?>
                ],
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

    // Order Type Chart
    const ctx3 = document.getElementById('orderTypeChart').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: ['Dine In', 'Pickup', 'Delivery'],
            datasets: [{
                label: 'Revenue',
                data: [
                    <?php echo $order_types['dine_in']['revenue'] ?? 0; ?>,
                    <?php echo $order_types['pickup']['revenue'] ?? 0; ?>,
                    <?php echo $order_types['delivery']['revenue'] ?? 0; ?>
                ],
                backgroundColor: ['#3498db', '#f39c12', '#27ae60'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue (AED)'
                    }
                }
            }
        }
    });

    // Top Items Chart
    const topItemsData = <?php 
        $top_items->data_seek(0);
        $items = [];
        while ($item = $top_items->fetch_assoc()) {
            $items[] = $item;
        }
        echo json_encode($items); 
    ?>;
    
    if (topItemsData.length > 0) {
        const ctx4 = document.getElementById('topItemsChart').getContext('2d');
        new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: topItemsData.map(d => d.item_name.substring(0, 15) + (d.item_name.length > 15 ? '...' : '')),
                datasets: [{
                    label: 'Quantity Sold',
                    data: topItemsData.map(d => d.total_quantity),
                    backgroundColor: '#e74c3c',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});

function chartView(view) {
    // Toggle between daily and cumulative view
    // Implementation can be added based on requirements
    alert('Chart view: ' + view);
}
</script>

<style>
.stat-card {
    border-radius: 15px;
    padding: 20px;
    transition: transform 0.3s;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.stat-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card.success { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-card.warning { background: linear-gradient(135deg, #fdc830 0%, #f37335 100%); }
.stat-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

.chart-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    height: 100%;
}

.table-report {
    font-size: 0.9rem;
    margin-bottom: 0;
}

.table-report td {
    padding: 0.5rem;
}

.text-white-50 {
    color: rgba(255,255,255,0.7) !important;
}
</style>