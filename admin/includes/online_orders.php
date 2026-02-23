<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Records per page
$offset = ($page - 1) * $limit;

// Filter variables
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$filter_vendor = isset($_GET['vendor']) ? $_GET['vendor'] : '';
$filter_order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';

// Build the WHERE clause for filtering
$where_conditions = ["o.delivery_source IN ('noon', 'deliveroo', 'keeta', 'smile')"];
$params = [];
$types = "";

if (!empty($filter_date_from) && !empty($filter_date_to)) {
    $where_conditions[] = "DATE(o.created_at) BETWEEN ? AND ?";
    $params[] = $filter_date_from;
    $params[] = $filter_date_to;
    $types .= "ss";
} elseif (!empty($filter_date_from)) {
    $where_conditions[] = "DATE(o.created_at) >= ?";
    $params[] = $filter_date_from;
    $types .= "s";
} elseif (!empty($filter_date_to)) {
    $where_conditions[] = "DATE(o.created_at) <= ?";
    $params[] = $filter_date_to;
    $types .= "s";
}

if (!empty($filter_vendor)) {
    $where_conditions[] = "o.delivery_source = ?";
    $params[] = $filter_vendor;
    $types .= "s";
}

if (!empty($filter_order_number)) {
    $where_conditions[] = "o.order_number LIKE ?";
    $params[] = "%$filter_order_number%";
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total records for pagination
$count_query = "SELECT COUNT(DISTINCT o.id) as total 
                FROM orders o
                WHERE $where_clause";
$count_stmt = $connection->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$count_stmt->close();

// Get online orders with pagination
$query = "SELECT o.*, 
                 COUNT(oi.id) as item_count
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          WHERE $where_clause
          GROUP BY o.id
          ORDER BY o.created_at DESC
          LIMIT ? OFFSET ?";

$stmt = $connection->prepare($query);
if (!empty($params)) {
    // Add limit and offset parameters
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Vendor stats with date filter (if any)
$vendor_stats_query = "SELECT 
    delivery_source,
    COUNT(*) as count,
    SUM(total_amount) as total 
    FROM orders 
    WHERE delivery_source IN ('noon', 'deliveroo', 'keeta', 'smile')";

// Apply same date filters to stats if they exist
if (!empty($filter_date_from) && !empty($filter_date_to)) {
    $vendor_stats_query .= " AND DATE(created_at) BETWEEN '$filter_date_from' AND '$filter_date_to'";
} elseif (!empty($filter_date_from)) {
    $vendor_stats_query .= " AND DATE(created_at) >= '$filter_date_from'";
} elseif (!empty($filter_date_to)) {
    $vendor_stats_query .= " AND DATE(created_at) <= '$filter_date_to'";
}

$vendor_stats_query .= " GROUP BY delivery_source";
$vendor_stats_result = $connection->query($vendor_stats_query);

$vendor_stats = [
    'noon' => ['count' => 0, 'total' => 0],
    'deliveroo' => ['count' => 0, 'total' => 0],
    'keeta' => ['count' => 0, 'total' => 0],
    'smile' => ['count' => 0, 'total' => 0]
];

while ($row = $vendor_stats_result->fetch_assoc()) {
    $vendor_stats[$row['delivery_source']] = [
        'count' => $row['count'],
        'total' => $row['total']
    ];
}

// Status badges mapping
$status_badges = [
    'draft' => 'secondary',
    'pending' => 'warning',
    'confirmed' => 'info',
    'in_preparation' => 'primary',
    'ready' => 'success',
    'out_for_delivery' => 'warning',
    'completed' => 'success',
    'cancelled' => 'danger',
    'refunded' => 'danger',
    'closed' => 'dark'
];

$vendor_colors = [
    'noon' => 'warning',
    'deliveroo' => 'info',
    'keeta' => 'danger',
    'smile' => 'success'
];

$vendor_icons = [
    'noon' => 'bi-brightness-alt-high',
    'deliveroo' => 'bi-bicycle',
    'keeta' => 'bi-lightning',
    'smile' => 'bi-emoji-smile'
];
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-globe"></i> Online Orders</h1>
            <div>
                <button class="btn btn-primary" onclick="refreshOrders()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Orders</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="online_orders">
                    
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Vendor</label>
                        <select class="form-select" name="vendor">
                            <option value="">All Vendors</option>
                            <option value="noon" <?php echo $filter_vendor == 'noon' ? 'selected' : ''; ?>>Noon</option>
                            <option value="deliveroo" <?php echo $filter_vendor == 'deliveroo' ? 'selected' : ''; ?>>Deliveroo</option>
                            <option value="keeta" <?php echo $filter_vendor == 'keeta' ? 'selected' : ''; ?>>Keeta</option>
                            <option value="smile" <?php echo $filter_vendor == 'smile' ? 'selected' : ''; ?>>Smile</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Order Number</label>
                        <input type="text" class="form-control" name="order_number" placeholder="e.g., ORD123" value="<?php echo htmlspecialchars($filter_order_number); ?>">
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Apply Filters
                        </button>
                        <a href="orders.php?source=online_orders" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Vendor Stats Cards -->
        <div class="row mb-4">
            <?php foreach ($vendor_stats as $vendor => $stats): ?>
            <div class="col-md-3">
                <div class="card bg-<?php echo $vendor_colors[$vendor]; ?> text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title"><?php echo ucfirst($vendor); ?></h6>
                                <h3 class="mb-0"><?php echo $stats['count']; ?> orders</h3>
                                <small><?php echo number_format($stats['total'] ?? 0, 2); ?> AED</small>
                            </div>
                            <i class="bi <?php echo $vendor_icons[$vendor]; ?> display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Orders List -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    Recent Online Orders 
                    <span class="badge bg-secondary ms-2"><?php echo $total_records; ?> total</span>
                </h5>
                <span class="text-muted">Showing <?php echo min($offset + 1, $total_records); ?> - <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Vendor</th>
                                <th>Date/Time</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($order = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $vendor_colors[$order['delivery_source']]; ?> p-2">
                                            <i class="bi <?php echo $vendor_icons[$order['delivery_source']]; ?> me-1"></i>
                                            <?php echo ucfirst($order['delivery_source']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                        <br><small class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['customer_name_snapshot'] ?? 'N/A'); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?php echo $order['item_count']; ?></span>
                                    </td>
                                    <td><strong class="text-success"><?php echo number_format($order['total_amount'], 2); ?> AED</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_badges[$order['order_status']] ?? 'secondary'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="orders.php?source=view_order&id=<?php echo $order['id']; ?>" 
                                               class="btn btn-outline-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($order['order_status'] == 'completed' || $order['order_status'] == 'closed'): ?>
                                            <a href="orders.php?source=print_receipt&id=<?php echo $order['id']; ?>&type=counter" 
                                               class="btn btn-outline-success" title="Print Receipt" target="_blank">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-1 d-block mb-3" style="opacity: 0.5;"></i>
                                            <h5>No online orders found</h5>
                                            <p class="mb-0">Try adjusting your filters or check back later</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Orders pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous page -->
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?source=online_orders&page=<?php echo $page - 1; ?>&date_from=<?php echo urlencode($filter_date_from); ?>&date_to=<?php echo urlencode($filter_date_to); ?>&vendor=<?php echo urlencode($filter_vendor); ?>&order_number=<?php echo urlencode($filter_order_number); ?>" tabindex="-1">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        </li>
                        
                        <!-- Page numbers -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?source=online_orders&page=1&date_from=' . urlencode($filter_date_from) . '&date_to=' . urlencode($filter_date_to) . '&vendor=' . urlencode($filter_vendor) . '&order_number=' . urlencode($filter_order_number) . '">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?source=online_orders&page=<?php echo $i; ?>&date_from=<?php echo urlencode($filter_date_from); ?>&date_to=<?php echo urlencode($filter_date_to); ?>&vendor=<?php echo urlencode($filter_vendor); ?>&order_number=<?php echo urlencode($filter_order_number); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?source=online_orders&page=' . $total_pages . '&date_from=' . urlencode($filter_date_from) . '&date_to=' . urlencode($filter_date_to) . '&vendor=' . urlencode($filter_vendor) . '&order_number=' . urlencode($filter_order_number) . '">' . $total_pages . '</a></li>';
                        }
                        ?>
                        
                        <!-- Next page -->
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?source=online_orders&page=<?php echo $page + 1; ?>&date_from=<?php echo urlencode($filter_date_from); ?>&date_to=<?php echo urlencode($filter_date_to); ?>&vendor=<?php echo urlencode($filter_vendor); ?>&order_number=<?php echo urlencode($filter_order_number); ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Results per page info -->
                <div class="text-center text-muted small mt-2">
                    Showing page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $limit; ?> orders per page)
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.page-title {
    font-size: 2rem;
    font-weight: 800;
    color: #c41e3a;
    letter-spacing: 1px;
    margin-bottom: 0.5em;
    text-shadow: 0 2px 8px rgba(196,30,58,0.08);
}

/* Filter card styling */
.card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.card-header {
    background: #f8f9fa;
    border-bottom: 2px solid #c41e3a;
    border-radius: 12px 12px 0 0 !important;
    padding: 1rem 1.5rem;
}

.card-header h5 {
    color: #2c3e50;
    font-weight: 600;
}

/* Table improvements */
.table {
    margin-bottom: 0;
}

.table thead th {
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 2px solid #c41e3a;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}

/* Badge styling */
.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
    border-radius: 6px;
}

/* Pagination styling */
.pagination {
    margin-bottom: 0;
}

.page-link {
    color: #c41e3a;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
}

.page-link:hover {
    color: #a01830;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #c41e3a;
    border-color: #c41e3a;
    color: white;
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}

/* Stats cards */
.card.bg-warning { background: linear-gradient(135deg, #fbb034 0%, #ffdd00 100%) !important; }
.card.bg-info { background: linear-gradient(135deg, #00c3e3 0%, #2f80ed 100%) !important; }
.card.bg-danger { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important; }
.card.bg-success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%) !important; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
    }
    .btn-group .btn {
        border-radius: 4px !important;
        margin: 2px 0;
    }
}
</style>

<script>
function refreshOrders() {
    location.reload();
}

// Optional: Add AJAX search without page reload
$(document).ready(function() {
    // You can add AJAX functionality here if needed
    $('#filterForm').on('submit', function(e) {
        // For now, let the form submit normally
        // Can be enhanced with AJAX later
    });
});
</script>