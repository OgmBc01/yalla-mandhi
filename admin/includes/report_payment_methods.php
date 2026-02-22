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

// Fetch payment method summary
$payment_query = "SELECT 
    payment_method,
    COUNT(*) as transaction_count,
    SUM(total_amount) as total_amount,
    AVG(total_amount) as avg_amount,
    COUNT(DISTINCT customer_id) as unique_customers,
    SUM(CASE WHEN order_type = 'dine_in' THEN total_amount ELSE 0 END) as dine_in_amount,
    SUM(CASE WHEN order_type = 'pickup' THEN total_amount ELSE 0 END) as pickup_amount,
    SUM(CASE WHEN order_type = 'delivery' THEN total_amount ELSE 0 END) as delivery_amount,
    MIN(total_amount) as min_amount,
    MAX(total_amount) as max_amount
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND order_status IN ('completed', 'closed')
    AND payment_method IS NOT NULL
    GROUP BY payment_method
    ORDER BY total_amount DESC";


$stmt_payment = $connection->prepare($payment_query);
$stmt_payment->bind_param("ss", $start_date, $end_date);
$stmt_payment->execute();
$payment_result = $stmt_payment->get_result();

// Get daily payment trends
$trend_query = "SELECT 
    DATE(created_at) as sale_date,
    SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END) as cash_amount,
    SUM(CASE WHEN payment_method = 'card' THEN total_amount ELSE 0 END) as card_amount,
    SUM(CASE WHEN payment_method = 'online' THEN total_amount ELSE 0 END) as online_amount,
    COUNT(CASE WHEN payment_method = 'cash' THEN 1 END) as cash_count,
    COUNT(CASE WHEN payment_method = 'card' THEN 1 END) as card_count,
    COUNT(CASE WHEN payment_method = 'online' THEN 1 END) as online_count
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND order_status IN ('completed', 'closed')
    GROUP BY DATE(created_at)
    ORDER BY sale_date";

$stmt_trend = $connection->prepare($trend_query);
$stmt_trend->bind_param("ss", $start_date, $end_date);
$stmt_trend->execute();
$trend_result = $stmt_trend->get_result();

// Calculate totals
$payment_data = [];
$total_transactions = 0;
$total_amount = 0;
$payment_methods = ['cash' => 0, 'card' => 0, 'online' => 0];

while ($row = $payment_result->fetch_assoc()) {
    $payment_data[$row['payment_method']] = $row;
    $total_transactions += $row['transaction_count'];
    $total_amount += $row['total_amount'];
    $payment_methods[$row['payment_method']] = $row['total_amount'];
}
$stmt_payment->close();

// Prepare trend data for charts
$trend_dates = [];
$cash_trend = [];
$card_trend = [];
$online_trend = [];

while ($row = $trend_result->fetch_assoc()) {
    $trend_dates[] = date('M d', strtotime($row['sale_date']));
    $cash_trend[] = $row['cash_amount'];
    $card_trend[] = $row['card_amount'];
    $online_trend[] = $row['online_amount'];
}
$stmt_trend->close();

// Color mapping
$method_colors = [
    'cash' => ['bg' => '#f39c12', 'light' => 'rgba(243,156,18,0.1)'],
    'card' => ['bg' => '#3498db', 'light' => 'rgba(52,152,219,0.1)'],
    'online' => ['bg' => '#9b59b6', 'light' => 'rgba(155,89,182,0.1)']
];
?>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="page-title">Payment Method Report</h2>
        <!-- Date Range Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <input type="hidden" name="source" value="payment">
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">From Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">To Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter-circle me-2"></i>Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card" style="border-left: 4px solid #f39c12;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Cash Payments</h6>
                                <h3 class="mb-0"><?php echo number_format($payment_methods['cash'] ?? 0, 2); ?> AED</h3>
                                <small class="text-muted">
                                    <?php echo $payment_data['cash']['transaction_count'] ?? 0; ?> transactions
                                </small>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-cash-coin fs-1 text-warning"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo $total_amount > 0 ? round(($payment_methods['cash'] ?? 0) / $total_amount * 100, 1) : 0; ?>%"></div>
                            </div>
                            <small class="text-muted">
                                <?php echo $total_amount > 0 ? round(($payment_methods['cash'] ?? 0) / $total_amount * 100, 1) : 0; ?>% of total
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card" style="border-left: 4px solid #3498db;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Card Payments</h6>
                                <h3 class="mb-0"><?php echo number_format($payment_methods['card'] ?? 0, 2); ?> AED</h3>
                                <small class="text-muted">
                                    <?php echo $payment_data['card']['transaction_count'] ?? 0; ?> transactions
                                </small>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-credit-card fs-1 text-primary"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: <?php echo $total_amount > 0 ? round(($payment_methods['card'] ?? 0) / $total_amount * 100, 1) : 0; ?>%"></div>
                            </div>
                            <small class="text-muted">
                                <?php echo $total_amount > 0 ? round(($payment_methods['card'] ?? 0) / $total_amount * 100, 1) : 0; ?>% of total
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card" style="border-left: 4px solid #9b59b6;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Online Payments</h6>
                                <h3 class="mb-0"><?php echo number_format($payment_methods['online'] ?? 0, 2); ?> AED</h3>
                                <small class="text-muted">
                                    <?php echo $payment_data['online']['transaction_count'] ?? 0; ?> transactions
                                </small>
                            </div>
                            <div class="bg-purple bg-opacity-10 p-3 rounded">
                                <i class="bi bi-wifi fs-1 text-purple"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" style="background-color: #9b59b6; width: <?php echo $total_amount > 0 ? round(($payment_methods['online'] ?? 0) / $total_amount * 100, 1) : 0; ?>%"></div>
                            </div>
                            <small class="text-muted">
                                <?php echo $total_amount > 0 ? round(($payment_methods['online'] ?? 0) / $total_amount * 100, 1) : 0; ?>% of total
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="chart-container">
                    <h5 class="mb-3"><i class="bi bi-pie-chart me-2"></i>Payment Method Distribution</h5>
                    <canvas id="paymentPieChart" height="250"></canvas>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="chart-container">
                    <h5 class="mb-3"><i class="bi bi-graph-up me-2"></i>Daily Payment Trends</h5>
                    <canvas id="paymentTrendChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Payment Tables -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Payment Method Details</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-report">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end">Average</th>
                                <th class="text-end">Minimum</th>
                                <th class="text-end">Maximum</th>
                                <th class="text-center">Unique Customers</th>
                                <th class="text-end">Dine In</th>
                                <th class="text-end">Pickup</th>
                                <th class="text-end">Delivery</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_data as $method => $data): ?>
                            <tr>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $method_colors[$method]['bg']; ?>; color: white; padding: 8px 12px;">
                                        <i class="bi bi-<?php echo $method == 'cash' ? 'cash-coin' : ($method == 'card' ? 'credit-card' : 'wifi'); ?> me-1"></i>
                                        <?php echo ucfirst($method); ?>
                                    </span>
                                </td>
                                <td class="text-center"><?php echo $data['transaction_count']; ?></td>
                                <td class="text-end fw-bold text-success"><?php echo number_format($data['total_amount'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($data['avg_amount'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($data['min_amount'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($data['max_amount'], 2); ?></td>
                                <td class="text-center"><?php echo $data['unique_customers']; ?></td>
                                <td class="text-end"><?php echo number_format($data['dine_in_amount'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($data['pickup_amount'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($data['delivery_amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($payment_data)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block text-muted mb-3"></i>
                                    <h5>No payment data found</h5>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment Distribution Pie Chart
    const ctx1 = document.getElementById('paymentPieChart').getContext('2d');
    new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Cash', 'Card', 'Online'],
            datasets: [{
                data: [
                    <?php echo $payment_methods['cash'] ?? 0; ?>,
                    <?php echo $payment_methods['card'] ?? 0; ?>,
                    <?php echo $payment_methods['online'] ?? 0; ?>
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value.toFixed(2) + ' AED (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Payment Trend Chart
    const ctx2 = document.getElementById('paymentTrendChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trend_dates); ?>,
            datasets: [{
                label: 'Cash',
                data: <?php echo json_encode($cash_trend); ?>,
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243,156,18,0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Card',
                data: <?php echo json_encode($card_trend); ?>,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52,152,219,0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Online',
                data: <?php echo json_encode($online_trend); ?>,
                borderColor: '#9b59b6',
                backgroundColor: 'rgba(155,89,182,0.1)',
                tension: 0.4,
                fill: true
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
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (AED)'
                    }
                }
            }
        }
    });
});
</script>

<style>
.text-purple {
    color: #9b59b6;
}
.bg-purple {
    background-color: #9b59b6;
}
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
</style>