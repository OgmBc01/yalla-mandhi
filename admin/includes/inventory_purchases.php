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

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filters
$status = $_GET['status'] ?? '';
$supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Build query for main data
$query = "SELECT po.*, s.supplier_name, u.full_name as created_by_name,
                 COUNT(poi.id) as item_count,
                 COALESCE(SUM(poi.quantity_received), 0) as items_received
          FROM purchase_orders po
          LEFT JOIN suppliers s ON po.supplier_id = s.id
          LEFT JOIN users u ON po.created_by = u.id
          LEFT JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
          WHERE DATE(po.order_date) BETWEEN ? AND ?";
$params = [$date_from, $date_to];
$types = "ss";

if (!empty($status)) {
    $query .= " AND po.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($supplier_id > 0) {
    $query .= " AND po.supplier_id = ?";
    $params[] = $supplier_id;
    $types .= "i";
}

$query .= " GROUP BY po.id, po.order_date, po.supplier_id, po.created_by, po.status, po.total_amount, po.expected_delivery, po.po_number, s.supplier_name, u.full_name";

// Get total count for pagination (separate simplified query)
$count_query = "SELECT COUNT(DISTINCT po.id) as total
                FROM purchase_orders po
                WHERE DATE(po.order_date) BETWEEN ? AND ?";
$count_params = [$date_from, $date_to];
$count_types = "ss";

if (!empty($status)) {
    $count_query .= " AND po.status = ?";
    $count_params[] = $status;
    $count_types .= "s";
}

if ($supplier_id > 0) {
    $count_query .= " AND po.supplier_id = ?";
    $count_params[] = $supplier_id;
    $count_types .= "i";
}

$count_stmt = $connection->prepare($count_query);
$count_stmt->bind_param($count_types, ...$count_params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$count_stmt->close();

// Add pagination to main query
$query .= " ORDER BY po.order_date DESC, po.id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $connection->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get suppliers for filter
$suppliers_query = "SELECT id, supplier_name FROM suppliers WHERE is_active = 1 ORDER BY supplier_name";
$suppliers_result = $connection->query($suppliers_query);

// Get summary statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'ordered' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) as partial,
    SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) as received,
    COALESCE(SUM(total_amount), 0) as total_value
    FROM purchase_orders
    WHERE DATE(order_date) BETWEEN ? AND ?";
$stats_params = [$date_from, $date_to];
$stats_types = "ss";

if (!empty($status)) {
    $stats_query .= " AND status = ?";
    $stats_params[] = $status;
    $stats_types .= "s";
}

if ($supplier_id > 0) {
    $stats_query .= " AND supplier_id = ?";
    $stats_params[] = $supplier_id;
    $stats_types .= "i";
}

$stats_stmt = $connection->prepare($stats_query);
$stats_stmt->bind_param($stats_types, ...$stats_params);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-truck me-2"></i>Purchase Orders</h1>
            <div>
                <a href="inventory.php?source=add_purchase" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> New Purchase Order
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Orders</h6>
                        <h3 class="text-white mb-0"><?php echo $stats['total_orders'] ?? 0; ?></h3>
                        <small>This period</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Pending</h6>
                        <h3 class="text-white mb-0"><?php echo $stats['pending'] ?? 0; ?></h3>
                        <small>Awaiting delivery</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Partial</h6>
                        <h3 class="text-white mb-0"><?php echo $stats['partial'] ?? 0; ?></h3>
                        <small>Partially received</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Value</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($stats['total_value'] ?? 0, 2); ?> AED</h3>
                        <small>All orders</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="view_purchases">
                    
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="draft" <?php echo $status == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="ordered" <?php echo $status == 'ordered' ? 'selected' : ''; ?>>Ordered</option>
                            <option value="partial" <?php echo $status == 'partial' ? 'selected' : ''; ?>>Partial</option>
                            <option value="received" <?php echo $status == 'received' ? 'selected' : ''; ?>>Received</option>
                            <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="supplier_id">
                            <option value="0">All Suppliers</option>
                            <?php 
                            if ($suppliers_result) {
                                $suppliers_result->data_seek(0);
                                while ($supplier = $suppliers_result->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $supplier['id']; ?>" <?php echo $supplier_id == $supplier['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                            </option>
                            <?php 
                                endwhile;
                            } 
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-filter"></i> Apply Filters
                        </button>
                        <a href="inventory.php?source=view_purchases" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Purchase Orders Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Purchase Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th class="text-center">Items</th>
                                <th class="text-end">Total</th>
                                <th>Status</th>
                                <th>Expected</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($po = $result->fetch_assoc()): 
                                    $status_colors = [
                                        'draft' => 'secondary',
                                        'ordered' => 'primary',
                                        'partial' => 'warning',
                                        'received' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $status_color = $status_colors[$po['status']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($po['po_number']); ?></strong>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($po['order_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($po['supplier_name'] ?? 'N/A'); ?></td>
                                    <td class="text-center">
                                        <?php echo $po['item_count']; ?>
                                        <?php if ($po['items_received'] > 0): ?>
                                        <br><small class="text-success">Received: <?php echo $po['items_received']; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?php echo number_format($po['total_amount'], 2); ?> AED</td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_color; ?>">
                                            <?php echo ucfirst($po['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($po['expected_delivery']): ?>
                                            <?php echo date('d/m/Y', strtotime($po['expected_delivery'])); ?>
                                            <?php if (strtotime($po['expected_delivery']) < time() && $po['status'] != 'received' && $po['status'] != 'cancelled'): ?>
                                            <br><small class="text-danger">Overdue</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($po['created_by_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="inventory.php?source=view_purchase&id=<?php echo $po['id']; ?>" 
                                               class="btn btn-outline-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($po['status'] == 'ordered' || $po['status'] == 'partial'): ?>
                                            <a href="inventory.php?source=receive_purchase&id=<?php echo $po['id']; ?>" 
                                               class="btn btn-outline-success" title="Receive Items">
                                                <i class="bi bi-box-seam"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            <h5>No purchase orders found</h5>
                                            <p>Create your first purchase order to start tracking inventory.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?source=view_purchases&page=<?php echo $page-1; ?>&status=<?php echo urlencode($status); ?>&supplier_id=<?php echo $supplier_id; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                Previous
                            </a>
                        </li>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($page+2, $total_pages); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?source=view_purchases&page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&supplier_id=<?php echo $supplier_id; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?source=view_purchases&page=<?php echo $page+1; ?>&status=<?php echo urlencode($status); ?>&supplier_id=<?php echo $supplier_id; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>