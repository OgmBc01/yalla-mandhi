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

// Fetch tax summary
$tax_query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as tax_month,
    DATE_FORMAT(created_at, '%M %Y') as month_name,
    COUNT(*) as transaction_count,
    SUM(subtotal) as total_subtotal,
    SUM(discount_amount) as total_discounts,
    SUM(tax_amount) as total_tax,
    SUM(total_amount) as total_with_tax,
    SUM(CASE WHEN order_type = 'dine_in' THEN tax_amount ELSE 0 END) as dine_in_tax,
    SUM(CASE WHEN order_type = 'pickup' THEN tax_amount ELSE 0 END) as pickup_tax,
    SUM(CASE WHEN order_type = 'delivery' THEN tax_amount ELSE 0 END) as delivery_tax,
    AVG(tax_amount) as avg_tax_per_order
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND order_status IN ('completed', 'closed')
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%M %Y')
    ORDER BY tax_month DESC";

$stmt = $connection->prepare($tax_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$tax_result = $stmt->get_result();

// Calculate totals
$total_tax = 0;
$total_subject_to_tax = 0;
$tax_data = [];

while ($row = $tax_result->fetch_assoc()) {
    $tax_data[] = $row;
    $total_tax += $row['total_tax'];
    $total_subject_to_tax += ($row['total_subtotal'] - $row['total_discounts']);
}
$stmt->close();

// Get daily tax breakdown
$daily_tax_query = "SELECT 
    DATE(created_at) as sale_date,
    SUM(tax_amount) as daily_tax,
    COUNT(*) as order_count
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND order_status IN ('completed', 'closed')
    GROUP BY DATE(created_at)
    ORDER BY sale_date";

$stmt = $connection->prepare($daily_tax_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$daily_result = $stmt->get_result();

$daily_dates = [];
$daily_tax = [];
$daily_orders = [];

while ($row = $daily_result->fetch_assoc()) {
    $daily_dates[] = date('M d', strtotime($row['sale_date']));
    $daily_tax[] = $row['daily_tax'];
    $daily_orders[] = $row['order_count'];
}
$stmt->close();

// Tax rate (configurable)
$tax_rate = 15; // 15% VAT
?>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="page-title">Tax Report</h2>
        <!-- Date Range Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <input type="hidden" name="source" value="tax">
                    
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
                        <a href="includes/export_report.php?source=tax&export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success me-2">
                            <i class="bi bi-file-earmark-excel"></i> CSV
                        </a>
                        <a href="includes/export_report.php?source=tax&export=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tax Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Tax Collected</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($total_tax, 2); ?> AED</h3>
                        <small>VAT at <?php echo $tax_rate; ?>%</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Taxable Amount</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($total_subject_to_tax, 2); ?> AED</h3>
                        <small>subtotal after discounts</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Effective Tax Rate</h6>
                        <h3 class="text-white mb-0">
                            <?php echo $total_subject_to_tax > 0 ? round(($total_tax / $total_subject_to_tax) * 100, 2) : 0; ?>%
                        </h3>
                        <small>of taxable amount</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Tax Chart -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Daily Tax Collection</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyTaxChart" height="300"></canvas>
            </div>
        </div>

        <!-- Monthly Tax Breakdown -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar-month me-2"></i>Monthly Tax Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-report">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">Discounts</th>
                                <th class="text-end">Taxable Amount</th>
                                <th class="text-end">Tax Collected</th>
                                <th class="text-end">Avg Tax/Order</th>
                                <th class="text-end">Dine In Tax</th>
                                <th class="text-end">Pickup Tax</th>
                                <th class="text-end">Delivery Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tax_data)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block text-muted mb-3"></i>
                                    <h5>No tax data found</h5>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($tax_data as $month): ?>
                                <tr>
                                    <td><strong><?php echo $month['month_name']; ?></strong></td>
                                    <td class="text-center"><?php echo $month['transaction_count']; ?></td>
                                    <td class="text-end"><?php echo number_format($month['total_subtotal'], 2); ?></td>
                                    <td class="text-end text-danger"><?php echo number_format($month['total_discounts'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['total_subtotal'] - $month['total_discounts'], 2); ?></td>
                                    <td class="text-end fw-bold text-success"><?php echo number_format($month['total_tax'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['avg_tax_per_order'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['dine_in_tax'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['pickup_tax'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($month['delivery_tax'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <!-- Summary Row -->
                                <?php 
                                $total_months = count($tax_data);
                                $total_transactions = array_sum(array_column($tax_data, 'transaction_count'));
                                $total_subtotal = array_sum(array_column($tax_data, 'total_subtotal'));
                                $total_discounts = array_sum(array_column($tax_data, 'total_discounts'));
                                $total_tax_sum = array_sum(array_column($tax_data, 'total_tax'));
                                $total_dine_in_tax = array_sum(array_column($tax_data, 'dine_in_tax'));
                                $total_pickup_tax = array_sum(array_column($tax_data, 'pickup_tax'));
                                $total_delivery_tax = array_sum(array_column($tax_data, 'delivery_tax'));
                                ?>
                                <tr class="table-secondary fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-center"><?php echo $total_transactions; ?></td>
                                    <td class="text-end"><?php echo number_format($total_subtotal, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($total_discounts, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($total_subtotal - $total_discounts, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($total_tax_sum, 2); ?></td>
                                    <td class="text-end"><?php echo $total_transactions > 0 ? number_format($total_tax_sum / $total_transactions, 2) : 0; ?></td>
                                    <td class="text-end"><?php echo number_format($total_dine_in_tax, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($total_pickup_tax, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($total_delivery_tax, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tax by Order Type -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="chart-container">
                    <h5 class="mb-3"><i class="bi bi-pie-chart me-2"></i>Tax by Order Type</h5>
                    <canvas id="taxByTypeChart" height="250"></canvas>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="chart-container">
                    <h5 class="mb-3"><i class="bi bi-calculator me-2"></i>Tax Calculation Summary</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Total Sales (inc. tax)</th>
                            <td class="text-end"><?php echo number_format($total_subtotal - $total_discounts + $total_tax_sum, 2); ?> AED</td>
                        </tr>
                        <tr>
                            <th>Less: VAT (<?php echo $tax_rate; ?>%)</th>
                            <td class="text-end text-danger">- <?php echo number_format($total_tax_sum, 2); ?> AED</td>
                        </tr>
                        <tr class="table-success">
                            <th>Net Sales (excl. tax)</th>
                            <td class="text-end fw-bold"><?php echo number_format($total_subject_to_tax, 2); ?> AED</td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading">VAT Return Summary</h6>
                        <hr>
                        <p class="mb-1">Output VAT collected: <strong><?php echo number_format($total_tax_sum, 2); ?> AED</strong></p>
                        <p class="mb-0">Period: <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?></p>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="includes/export_report.php?source=tax&export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Export Tax Report (CSV)
                        </a>
                        <a href="includes/export_report.php?source=tax&export=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> Export Tax Report (PDF)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Tax Chart
    const ctx1 = document.getElementById('dailyTaxChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($daily_dates); ?>,
            datasets: [{
                label: 'Tax Collected (AED)',
                data: <?php echo json_encode($daily_tax); ?>,
                borderColor: '#c41e3a',
                backgroundColor: 'rgba(196,30,58,0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Orders',
                data: <?php echo json_encode($daily_orders); ?>,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52,152,219,0.1)',
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
                        text: 'Tax (AED)'
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

    // Tax by Order Type Chart
    const ctx2 = document.getElementById('taxByTypeChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Dine In', 'Pickup', 'Delivery'],
            datasets: [{
                data: [
                    <?php echo $total_dine_in_tax ?? 0; ?>,
                    <?php echo $total_pickup_tax ?? 0; ?>,
                    <?php echo $total_delivery_tax ?? 0; ?>
                ],
                backgroundColor: ['#3498db', '#f39c12', '#27ae60'],
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
</style>