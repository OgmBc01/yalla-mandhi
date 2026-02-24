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

// Fetch item performance data
$items_query = "SELECT 
    oi.item_name_snapshot as item_name,
    COUNT(DISTINCT oi.order_id) as order_count,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.total_price) as total_revenue,
    AVG(oi.unit_price_snapshot) as avg_price,
    SUM(CASE WHEN o.order_type = 'dine_in' THEN oi.quantity ELSE 0 END) as dine_in_qty,
    SUM(CASE WHEN o.order_type = 'pickup' THEN oi.quantity ELSE 0 END) as pickup_qty,
    SUM(CASE WHEN o.order_type = 'delivery' THEN oi.quantity ELSE 0 END) as delivery_qty,
    MAX(oi.quantity) as max_qty_in_single_order
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND o.order_status IN ('completed', 'closed')
    GROUP BY oi.item_name_snapshot
    ORDER BY total_quantity DESC";

$stmt = $connection->prepare($items_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$items_result = $stmt->get_result();

// Calculate totals
$total_items_sold = 0;
$total_revenue = 0;
$unique_items = 0;

$items_data = [];
while ($row = $items_result->fetch_assoc()) {
    $items_data[] = $row;
    $total_items_sold += $row['total_quantity'];
    $total_revenue += $row['total_revenue'];
    $unique_items++;
}
$stmt->close();

// Get category breakdown
$category_query = "SELECT 
    COALESCE(mc.name, 'Uncategorized') as category_name,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.total_price) as total_revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
    LEFT JOIN menu_categories mc ON mi.category_id = mc.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND o.order_status IN ('completed', 'closed')
    GROUP BY mc.id
    ORDER BY total_revenue DESC";

$stmt = $connection->prepare($category_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$category_result = $stmt->get_result();
$stmt->close();
?>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="page-title">Menu Items Performance Report</h2>
        <!-- Date Range Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <input type="hidden" name="source" value="items">
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">From Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">To Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>" required>
                    </div>
                    
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter-circle me-2"></i>Generate Report
                        </button>
                        <a href="includes/export_report.php?source=items&export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success" target="_blank">
                            <i class="bi bi-file-earmark-excel"></i> CSV
                        </a>
                        <a href="includes/export_report.php?source=items&export=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-danger" target="_blank">
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
                        <h6 class="text-white-50">Unique Items Sold</h6>
                        <h3 class="text-white mb-0"><?php echo $unique_items; ?></h3>
                        <small>different menu items</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Quantity</h6>
                        <h3 class="text-white mb-0"><?php echo $total_items_sold; ?></h3>
                        <small>items sold</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Revenue</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($total_revenue, 2); ?> AED</h3>
                        <small>from items</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Avg Price</h6>
                        <h3 class="text-white mb-0"><?php echo $total_items_sold > 0 ? number_format($total_revenue / $total_items_sold, 2) : 0; ?> AED</h3>
                        <small>per item</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Sales by Category</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-report">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th class="text-center">Quantity Sold</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">% of Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $category_result->data_seek(0);
                                    while ($cat = $category_result->fetch_assoc()): 
                                    $percentage = $total_revenue > 0 ? round($cat['total_revenue'] / $total_revenue * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                                        <td class="text-center"><?php echo $cat['total_quantity']; ?></td>
                                        <td class="text-end text-success"><?php echo number_format($cat['total_revenue'], 2); ?> AED</td>
                                        <td class="text-end">
                                            <div class="progress" style="height: 5px; width: 100px; display: inline-block;">
                                                <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                            <small class="ms-2"><?php echo $percentage; ?>%</small>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Performance Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Item Performance Details</h5>
                    <div>
                        <input type="text" class="form-control form-control-sm" id="itemSearch" placeholder="Search items..." style="width: 250px;">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-report" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th class="text-center">Orders</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Avg Price</th>
                                <th class="text-center">% of Sales</th>
                                <th class="text-center">Dine In</th>
                                <th class="text-center">Pickup</th>
                                <th class="text-center">Delivery</th>
                                <th class="text-center">Max Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items_data)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block text-muted mb-3"></i>
                                    <h5>No item sales data found</h5>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php 
                                $rank = 1;
                                foreach ($items_data as $item): 
                                $percentage = $total_revenue > 0 ? round($item['total_revenue'] / $total_revenue * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                        <?php if ($rank <= 3): ?>
                                            <span class="badge bg-<?php echo $rank == 1 ? 'warning' : ($rank == 2 ? 'secondary' : 'bronze'); ?> ms-2">#<?php echo $rank; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo $item['order_count']; ?></td>
                                    <td class="text-center fw-bold"><?php echo $item['total_quantity']; ?></td>
                                    <td class="text-end text-success"><?php echo number_format($item['total_revenue'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($item['avg_price'], 2); ?></td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 5px; width: 60px; display: inline-block;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <small class="ms-1"><?php echo $percentage; ?>%</small>
                                    </td>
                                    <td class="text-center"><?php echo $item['dine_in_qty']; ?></td>
                                    <td class="text-center"><?php echo $item['pickup_qty']; ?></td>
                                    <td class="text-center"><?php echo $item['delivery_qty']; ?></td>
                                    <td class="text-center"><?php echo $item['max_qty_in_single_order']; ?></td>
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
    </div>
</div>

<style>
.badge.bronze {
    background-color: #cd7f32;
    color: white;
}
.table-report th {
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 2px solid #c41e3a;
}
.progress {
    vertical-align: middle;
}
</style>

<script>
// Item search functionality
document.getElementById('itemSearch')?.addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('#itemsTable tbody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(value) > -1 ? '' : 'none';
    });
});
</script>