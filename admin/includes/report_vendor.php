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

// Vendor commission rates (configurable)
$vendor_commissions = [
    'noon' => 15,      // 15% commission
    'deliveroo' => 18,  // 18% commission
    'keeta' => 16,      // 16% commission
    'smile' => 14       // 14% commission
];

// Fetch vendor order summary
$vendor_query = "SELECT 
    delivery_source,
    COUNT(*) as order_count,
    SUM(total_amount) as gross_sales,
    SUM(delivery_fee) as total_delivery_fees,
    SUM(discount_amount) as total_discounts,
    SUM(tax_amount) as total_tax,
    AVG(total_amount) as avg_order_value,
    MIN(created_at) as first_order,
    MAX(created_at) as last_order
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND delivery_source IN ('noon', 'deliveroo', 'keeta', 'smile')
    AND order_status IN ('completed', 'closed')
    GROUP BY delivery_source
    ORDER BY gross_sales DESC";


$stmt_vendor = $connection->prepare($vendor_query);
$stmt_vendor->bind_param("ss", $start_date, $end_date);
$stmt_vendor->execute();
$vendor_result = $stmt_vendor->get_result();

// Fetch daily vendor trends
$daily_vendor_query = "SELECT 
    DATE(created_at) as sale_date,
    delivery_source,
    COUNT(*) as order_count,
    SUM(total_amount) as daily_total
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND delivery_source IN ('noon', 'deliveroo', 'keeta', 'smile')
    AND order_status IN ('completed', 'closed')
    GROUP BY DATE(created_at), delivery_source
    ORDER BY sale_date";

$stmt_daily = $connection->prepare($daily_vendor_query);
$stmt_daily->bind_param("ss", $start_date, $end_date);
$stmt_daily->execute();
$daily_result = $stmt_daily->get_result();

// Process data
$vendor_data = [];
$total_gross = 0;
$total_commission = 0;
$total_net = 0;

while ($row = $vendor_result->fetch_assoc()) {
    $vendor = $row['delivery_source'];
    $commission_rate = $vendor_commissions[$vendor] ?? 15;
    $commission_amount = $row['gross_sales'] * ($commission_rate / 100);
    $net_amount = $row['gross_sales'] - $commission_amount;
    
    $row['commission_rate'] = $commission_rate;
    $row['commission_amount'] = $commission_amount;
    $row['net_amount'] = $net_amount;
    
    $vendor_data[$vendor] = $row;
    
    $total_gross += $row['gross_sales'];
    $total_commission += $commission_amount;
    $total_net += $net_amount;
}
$stmt_vendor->close();

// Prepare chart data
$chart_dates = [];
$noon_data = [];
$deliveroo_data = [];
$keeta_data = [];
$smile_data = [];

while ($row = $daily_result->fetch_assoc()) {
    if (!in_array($row['sale_date'], $chart_dates)) {
        $chart_dates[] = $row['sale_date'];
    }
    
    switch ($row['delivery_source']) {
        case 'noon':
            $noon_data[$row['sale_date']] = $row['daily_total'];
            break;
        case 'deliveroo':
            $deliveroo_data[$row['sale_date']] = $row['daily_total'];
            break;
        case 'keeta':
            $keeta_data[$row['sale_date']] = $row['daily_total'];
            break;
        case 'smile':
            $smile_data[$row['sale_date']] = $row['daily_total'];
            break;
    }
}
$stmt_daily->close();

// Fill missing dates with zeros
$noon_chart = [];
$deliveroo_chart = [];
$keeta_chart = [];
$smile_chart = [];

foreach ($chart_dates as $date) {
    $noon_chart[] = $noon_data[$date] ?? 0;
    $deliveroo_chart[] = $deliveroo_data[$date] ?? 0;
    $keeta_chart[] = $keeta_data[$date] ?? 0;
    $smile_chart[] = $smile_data[$date] ?? 0;
}
?>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="page-title">Online Vendors Report</h2>
        <!-- Date Range Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <input type="hidden" name="source" value="vendor">
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
                            <a href="includes/export_report.php?source=vendor&export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success me-2">
                                <i class="bi bi-file-earmark-excel"></i> CSV
                            </a>
                            <a href="includes/export_report.php?source=vendor&export=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-danger">
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
                        <h6 class="text-white-50">Total Vendors</h6>
                        <h3 class="text-white mb-0"><?php echo count($vendor_data); ?></h3>
                        <small>active vendors</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Gross Sales</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($total_gross, 2); ?> AED</h3>
                        <small>before commission</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Commission</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($total_commission, 2); ?> AED</h3>
                        <small>vendor fees</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Net Receivable</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($total_net, 2); ?> AED</h3>
                        <small>after commission</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Performance Chart -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Daily Vendor Sales</h5>
            </div>
            <div class="card-body">
                <canvas id="vendorTrendChart" height="300"></canvas>
            </div>
        </div>

        <!-- Vendor Details Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Vendor Reconciliation Details</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-report">
                        <thead>
                            <tr>
                                <th>Vendor</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">Gross Sales</th>
                                <th class="text-end">Delivery Fees</th>
                                <th class="text-end">Discounts</th>
                                <th class="text-end">Tax</th>
                                <th class="text-center">Commission Rate</th>
                                <th class="text-end">Commission Amount</th>
                                <th class="text-end">Net Receivable</th>
                                <th class="text-end">Avg Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vendor_data)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block text-muted mb-3"></i>
                                    <h5>No vendor orders found</h5>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($vendor_data as $vendor => $data): 
                                    $vendor_colors = [
                                        'noon' => 'warning',
                                        'deliveroo' => 'info',
                                        'keeta' => 'danger',
                                        'smile' => 'success'
                                    ];
                                    $color = $vendor_colors[$vendor] ?? 'secondary';
                                    $icons = [
                                        'noon' => 'sun',
                                        'deliveroo' => 'bicycle',
                                        'keeta' => 'lightning',
                                        'smile' => 'emoji-smile'
                                    ];
                                    $icon = $icons[$vendor] ?? 'truck';
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?php echo $color; ?> p-2" style="font-size: 0.9rem;">
                                            <i class="bi bi-<?php echo $icon; ?> me-1"></i>
                                            <?php echo ucfirst($vendor); ?>
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold"><?php echo $data['order_count']; ?></td>
                                    <td class="text-end text-success"><?php echo number_format($data['gross_sales'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($data['total_delivery_fees'], 2); ?></td>
                                    <td class="text-end text-danger"><?php echo number_format($data['total_discounts'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($data['total_tax'], 2); ?></td>
                                    <td class="text-center"><?php echo $data['commission_rate']; ?>%</td>
                                    <td class="text-end text-warning"><?php echo number_format($data['commission_amount'], 2); ?></td>
                                    <td class="text-end fw-bold text-primary"><?php echo number_format($data['net_amount'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($data['avg_order_value'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <!-- Summary Row -->
                                <tr class="table-secondary fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-center"><?php echo array_sum(array_column($vendor_data, 'order_count')); ?></td>
                                    <td class="text-end"><?php echo number_format($total_gross, 2); ?></td>
                                    <td class="text-end"><?php echo number_format(array_sum(array_column($vendor_data, 'total_delivery_fees')), 2); ?></td>
                                    <td class="text-end"><?php echo number_format(array_sum(array_column($vendor_data, 'total_discounts')), 2); ?></td>
                                    <td class="text-end"><?php echo number_format(array_sum(array_column($vendor_data, 'total_tax')), 2); ?></td>
                                    <td class="text-center">-</td>
                                    <td class="text-end"><?php echo number_format($total_commission, 2); ?></td>
                                    <td class="text-end"><?php echo number_format($total_net, 2); ?></td>
                                    <td class="text-end">-</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vendor Cards -->
        <?php if (!empty($vendor_data)): ?>
        <div class="row mt-4">
            <?php foreach ($vendor_data as $vendor => $data): 
                $vendor_colors = [
                    'noon' => ['bg' => '#fbb034', 'icon' => 'sun'],
                    'deliveroo' => ['bg' => '#00c3e3', 'icon' => 'bicycle'],
                    'keeta' => ['bg' => '#e74c3c', 'icon' => 'lightning'],
                    'smile' => ['bg' => '#f1c40f', 'icon' => 'emoji-smile']
                ];
                $color = $vendor_colors[$vendor]['bg'] ?? '#6c757d';
                $icon = $vendor_colors[$vendor]['icon'] ?? 'truck';
            ?>
            <div class="col-md-3">
                <div class="card mb-3" style="border-top: 3px solid <?php echo $color; ?>;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="bi bi-<?php echo $icon; ?> me-2" style="color: <?php echo $color; ?>;"></i>
                                <?php echo ucfirst($vendor); ?>
                            </h5>
                            <span class="badge" style="background-color: <?php echo $color; ?>; color: white;">
                                <?php echo $data['order_count']; ?> orders
                            </span>
                        </div>
                        <table class="table table-sm">
                            <tr>
                                <td>Gross Sales:</td>
                                <td class="text-end"><?php echo number_format($data['gross_sales'], 2); ?> AED</td>
                            </tr>
                            <tr>
                                <td>Commission (<?php echo $data['commission_rate']; ?>%):</td>
                                <td class="text-end text-danger">-<?php echo number_format($data['commission_amount'], 2); ?> AED</td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Net:</td>
                                <td class="text-end text-success"><?php echo number_format($data['net_amount'], 2); ?> AED</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vendor Trend Chart
    const ctx = document.getElementById('vendorTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_map(function($date) {
                return date('M d', strtotime($date));
            }, $chart_dates)); ?>,
            datasets: [{
                label: 'Noon',
                data: <?php echo json_encode($noon_chart); ?>,
                borderColor: '#fbb034',
                backgroundColor: 'rgba(251,176,52,0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Deliveroo',
                data: <?php echo json_encode($deliveroo_chart); ?>,
                borderColor: '#00c3e3',
                backgroundColor: 'rgba(0,195,227,0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Keeta',
                data: <?php echo json_encode($keeta_chart); ?>,
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231,76,60,0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Smile',
                data: <?php echo json_encode($smile_chart); ?>,
                borderColor: '#f1c40f',
                backgroundColor: 'rgba(241,196,15,0.1)',
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
                        text: 'Sales (AED)'
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
</style>