<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || 
    !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
    header("Location: login.php");
    exit();
}

$period = $_GET['period'] ?? 'monthly';
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

if ($period == 'daily') {
    $profit_data = getDailyProfit($connection, $date_from, $date_to);
    $chart_type = 'Daily';
} elseif ($period == 'yearly') {
    $profit_data = getYearlyProfit($connection, $year);
    $chart_type = 'Yearly';
} else {
    $profit_data = getMonthlyProfit($connection, $year);
    $chart_type = 'Monthly';
}

/* ============================================================
   DAILY PROFIT (Strict Mode Safe)
============================================================ */
function getDailyProfit($connection, $date_from, $date_to) {

    $query = "
        SELECT 
            d.date_key,
            COALESCE(o.total_sales,0) as total_sales,
            COALESCE(e.total_expenses,0) as total_expenses,
            COALESCE(c.cogs,0) as cost_of_goods
        FROM (
            SELECT DISTINCT DATE(created_at) as date_key
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ?
        ) d
        LEFT JOIN (
            SELECT DATE(created_at) as date_key,
                   SUM(total_amount) as total_sales
            FROM orders
            WHERE order_status IN ('completed','closed')
            GROUP BY DATE(created_at)
        ) o ON d.date_key = o.date_key
        LEFT JOIN (
            SELECT DATE(expense_date) as date_key,
                   SUM(total_amount) as total_expenses
            FROM expenses
            GROUP BY DATE(expense_date)
        ) e ON d.date_key = e.date_key
        LEFT JOIN (
            SELECT DATE(created_at) as date_key,
                   SUM(quantity_used * unit_cost) as cogs
            FROM inventory_transactions
            WHERE transaction_type='usage'
            GROUP BY DATE(created_at)
        ) c ON d.date_key = c.date_key
        ORDER BY d.date_key DESC
    ";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss", $date_from, $date_to);
    $stmt->execute();
    $result = $stmt->get_result();

    return processProfitResult($result, 'date_key');
}

/* ============================================================
   MONTHLY PROFIT (STRICT MODE SAFE – NO CORRELATED SUBQUERIES)
============================================================ */
function getMonthlyProfit($connection, $year) {

    $query = "
        SELECT 
            m.year_val,
            m.month_val,
            DATE_FORMAT(STR_TO_DATE(CONCAT(m.year_val,'-',m.month_val,'-01'), '%Y-%m-%d'), '%M %Y') as month_name,
            COALESCE(o.total_sales,0) as total_sales,
            COALESCE(e.total_expenses,0) as total_expenses,
            COALESCE(c.cogs,0) as cost_of_goods
        FROM (
            SELECT DISTINCT YEAR(created_at) as year_val,
                            MONTH(created_at) as month_val
            FROM orders
            WHERE YEAR(created_at)=?
        ) m
        LEFT JOIN (
            SELECT YEAR(created_at) as year_val,
                   MONTH(created_at) as month_val,
                   SUM(total_amount) as total_sales
            FROM orders
            WHERE order_status IN ('completed','closed')
            GROUP BY YEAR(created_at), MONTH(created_at)
        ) o ON m.year_val=o.year_val AND m.month_val=o.month_val
        LEFT JOIN (
            SELECT YEAR(expense_date) as year_val,
                   MONTH(expense_date) as month_val,
                   SUM(total_amount) as total_expenses
            FROM expenses
            GROUP BY YEAR(expense_date), MONTH(expense_date)
        ) e ON m.year_val=e.year_val AND m.month_val=e.month_val
        LEFT JOIN (
            SELECT YEAR(created_at) as year_val,
                   MONTH(created_at) as month_val,
                   SUM(quantity_used * unit_cost) as cogs
            FROM inventory_transactions
            WHERE transaction_type='usage'
            GROUP BY YEAR(created_at), MONTH(created_at)
        ) c ON m.year_val=c.year_val AND m.month_val=c.month_val
        ORDER BY m.year_val DESC, m.month_val DESC
    ";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();

    return processProfitResult($result, 'month_name');
}

/* ============================================================
   YEARLY PROFIT
============================================================ */
function getYearlyProfit($connection, $year) {

    $query = "
        SELECT 
            COALESCE(o.total_sales,0) as total_sales,
            COALESCE(e.total_expenses,0) as total_expenses,
            COALESCE(c.cogs,0) as cost_of_goods
        FROM
        (SELECT SUM(total_amount) as total_sales
         FROM orders
         WHERE order_status IN ('completed','closed')
         AND YEAR(created_at)=?) o
        CROSS JOIN
        (SELECT SUM(total_amount) as total_expenses
         FROM expenses
         WHERE YEAR(expense_date)=?) e
        CROSS JOIN
        (SELECT SUM(quantity_used*unit_cost) as cogs
         FROM inventory_transactions
         WHERE transaction_type='usage'
         AND YEAR(created_at)=?) c
    ";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("iii", $year,$year,$year);
    $stmt->execute();
    $result = $stmt->get_result();

    return processProfitResult($result);
}

/* ============================================================
   COMMON RESULT PROCESSOR
============================================================ */
function processProfitResult($result, $period_key=null) {

    $data=[];
    $total_sales=0;
    $total_expenses=0;
    $total_cogs=0;
    $total_profit=0;

    while($row=$result->fetch_assoc()){

        $row['gross_profit']=$row['total_sales']-$row['total_expenses'];
        $row['net_profit']=$row['gross_profit']-$row['cost_of_goods'];

        $data[]=$row;

        $total_sales+=$row['total_sales'];
        $total_expenses+=$row['total_expenses'];
        $total_cogs+=$row['cost_of_goods'];
        $total_profit+=$row['net_profit'];
    }

    return [
        'data'=>$data,
        'totals'=>[
            'sales'=>$total_sales,
            'expenses'=>$total_expenses,
            'cogs'=>$total_cogs,
            'profit'=>$total_profit
        ]
    ];
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-pie-chart-fill me-2"></i>Profit & Loss Report</h1>
            <div>
                <a href="expenses.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Expenses
                </a>
            </div>
        </div>

        <!-- Period Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="profit_report">
                    
                    <div class="col-md-2">
                        <label class="form-label">Period</label>
                        <select class="form-select" name="period" id="periodSelect">
                            <option value="monthly" <?php echo $period == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            <option value="daily" <?php echo $period == 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="yearly" <?php echo $period == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2" id="yearField">
                        <label class="form-label">Year</label>
                        <select class="form-select" name="year">
                            <?php for ($y = date('Y'); $y >= date('Y')-3; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2" id="monthField" style="display: none;">
                        <label class="form-label">Month</label>
                        <select class="form-select" name="month">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo $month == $m ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3" id="dateRangeField" style="display: none;">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label">From</label>
                                <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">To</label>
                                <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Sales</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($profit_data['totals']['sales'], 2); ?> AED</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Expenses</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($profit_data['totals']['expenses'], 2); ?> AED</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Cost of Goods</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($profit_data['totals']['cogs'], 2); ?> AED</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card <?php echo $profit_data['totals']['profit'] >= 0 ? 'bg-success' : 'bg-danger'; ?> text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Net Profit</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($profit_data['totals']['profit'], 2); ?> AED</h3>
                        <small>Margin: <?php 
                            $margin = $profit_data['totals']['sales'] > 0 
                                ? ($profit_data['totals']['profit'] / $profit_data['totals']['sales']) * 100 
                                : 0;
                            echo number_format($margin, 1); ?>%
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit Chart -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i><?php echo $chart_type; ?> Profit Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="profitChart" height="300"></canvas>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Profit Details</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th class="text-end">Sales (AED)</th>
                                <th class="text-end">Expenses (AED)</th>
                                <th class="text-end">Gross Profit</th>
                                <th class="text-end">COGS (AED)</th>
                                <th class="text-end">Net Profit</th>
                                <th class="text-end">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($profit_data['data'] as $row): 
                                $gross = $row['gross_profit'];
                                $net = $row['net_profit'];
                                $margin = $row['total_sales'] > 0 ? ($net / $row['total_sales']) * 100 : 0;
                            ?>
                            <tr>
                                <td>
                                    <?php if ($period == 'daily'): ?>
                                        <?php echo date('d M Y', strtotime($row['date'])); ?>
                                    <?php elseif ($period == 'yearly'): ?>
                                        <?php echo $year; ?> (Full Year)
                                    <?php else: ?>
                                        <?php echo $row['month_name'] ?? $row['month_key']; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?php echo number_format($row['total_sales'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($row['total_expenses'], 2); ?></td>
                                <td class="text-end <?php echo $gross >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($gross, 2); ?>
                                </td>
                                <td class="text-end"><?php echo number_format($row['cost_of_goods'], 2); ?></td>
                                <td class="text-end fw-bold <?php echo $net >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($net, 2); ?>
                                </td>
                                <td class="text-end">
                                    <?php echo number_format($margin, 1); ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th>TOTAL</th>
                                <th class="text-end"><?php echo number_format($profit_data['totals']['sales'], 2); ?></th>
                                <th class="text-end"><?php echo number_format($profit_data['totals']['expenses'], 2); ?></th>
                                <th class="text-end"><?php echo number_format($profit_data['totals']['sales'] - $profit_data['totals']['expenses'], 2); ?></th>
                                <th class="text-end"><?php echo number_format($profit_data['totals']['cogs'], 2); ?></th>
                                <th class="text-end"><?php echo number_format($profit_data['totals']['profit'], 2); ?></th>
                                <th class="text-end"><?php 
                                    $total_margin = $profit_data['totals']['sales'] > 0 
                                        ? ($profit_data['totals']['profit'] / $profit_data['totals']['sales']) * 100 
                                        : 0;
                                    echo number_format($total_margin, 1); ?>%
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    // Toggle fields based on period
    $('#periodSelect').change(function() {
        let period = $(this).val();
        $('#yearField, #monthField, #dateRangeField').hide();
        
        if (period == 'daily') {
            $('#dateRangeField').show();
        } else if (period == 'monthly') {
            $('#yearField').show();
            $('#monthField').show();
        } else if (period == 'yearly') {
            $('#yearField').show();
        }
    }).trigger('change');
    
    // Initialize chart
    const ctx = document.getElementById('profitChart').getContext('2d');
    const chartData = <?php 
        $labels = [];
        $sales = [];
        $expenses = [];
        $profits = [];
        
        foreach ($profit_data['data'] as $row) {
            if ($period == 'daily') {
                $labels[] = date('d M', strtotime($row['date']));
            } elseif ($period == 'yearly') {
                $labels[] = $year;
            } else {
                $labels[] = $row['month_name'] ?? $row['month_key'];
            }
            $sales[] = (float)$row['total_sales'];
            $expenses[] = (float)$row['total_expenses'];
            $profits[] = (float)$row['net_profit'];
        }
        echo json_encode([
            'labels' => $labels,
            'sales' => $sales,
            'expenses' => $expenses,
            'profits' => $profits
        ]);
    ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Sales',
                data: chartData.sales,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52,152,219,0.1)',
                tension: 0.4
            }, {
                label: 'Expenses',
                data: chartData.expenses,
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231,76,60,0.1)',
                tension: 0.4
            }, {
                label: 'Net Profit',
                data: chartData.profits,
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39,174,96,0.1)',
                tension: 0.4,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return value.toFixed(2) + ' AED';
                        }
                    }
                }
            }
        }
    });
});
</script>