<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_role = $_SESSION['role'] ?? '';

// Get filters
$filter_status = $_GET['status'] ?? '';
$filter_type = $_GET['type'] ?? '';
$filter_delivery_source = $_GET['delivery_source'] ?? '';
$filter_date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$filter_date_to = $_GET['date_to'] ?? date('Y-m-d');
$filter_search = $_GET['search'] ?? '';

// Build query
$query = "SELECT o.*, 
                 u1.full_name as punched_by_name,
                 u2.full_name as closed_by_name,
                 COUNT(oi.id) as item_count
          FROM orders o
          LEFT JOIN users u1 ON o.punched_by_admin_id = u1.id
          LEFT JOIN users u2 ON o.closed_by_admin_id = u2.id
          LEFT JOIN order_items oi ON o.id = oi.order_id
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($filter_status)) {
    $query .= " AND o.order_status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_type)) {
    $query .= " AND o.order_type = ?";
    $params[] = $filter_type;
    $types .= "s";
}

if (!empty($filter_delivery_source)) {
    $query .= " AND o.delivery_source = ?";
    $params[] = $filter_delivery_source;
    $types .= "s";
}

if (!empty($filter_date_from) && !empty($filter_date_to)) {
    $query .= " AND DATE(o.created_at) BETWEEN ? AND ?";
    $params[] = $filter_date_from;
    $params[] = $filter_date_to;
    $types .= "ss";
}

if (!empty($filter_search)) {
    $query .= " AND (o.order_number LIKE ? OR o.customer_name_snapshot LIKE ? OR o.customer_phone_snapshot LIKE ?)";
    $search_term = "%$filter_search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

// Prepare and execute
$stmt = $connection->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN order_status = 'in_preparation' THEN 1 ELSE 0 END) as preparing,
    SUM(CASE WHEN order_status = 'ready' THEN 1 ELSE 0 END) as ready,
    SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN payment_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid,
    SUM(total_amount) as total_sales
    FROM orders
    WHERE DATE(created_at) = CURDATE()";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();

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

$payment_status_badges = [
    'unpaid' => 'danger',
    'paid' => 'success',
    'vendor_settled' => 'info',
    'refunded' => 'warning'
];
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Order Management</h1>
            <div>
                <a href="orders.php?source=pos_view" class="btn btn-primary me-2">
                    <i class="bi bi-cart-plus"></i> POS Terminal
                </a>
                <a href="orders.php?source=add_order" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> New Order
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Today's Orders</h6>
                                <h2 class="mb-0"><?php echo $stats['total_orders'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-cart-check display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Pending</h6>
                                <h2 class="mb-0"><?php echo $stats['pending'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-clock-history display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Completed</h6>
                                <h2 class="mb-0"><?php echo $stats['completed'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-check-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Today's Sales</h6>
                                <h2 class="mb-0"><?php echo number_format($stats['total_sales'] ?? 0, 2); ?> AED</h2>
                            </div>
                            <i class="bi bi-cash-stack display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="orders.php" class="row g-3">
                    <input type="hidden" name="source" value="order_list">
                    
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="draft" <?php echo $filter_status == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $filter_status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="in_preparation" <?php echo $filter_status == 'in_preparation' ? 'selected' : ''; ?>>In Preparation</option>
                            <option value="ready" <?php echo $filter_status == 'ready' ? 'selected' : ''; ?>>Ready</option>
                            <option value="out_for_delivery" <?php echo $filter_status == 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                            <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Order Type</label>
                        <select class="form-select" name="type">
                            <option value="">All Types</option>
                            <option value="dine_in" <?php echo $filter_type == 'dine_in' ? 'selected' : ''; ?>>Dine In</option>
                            <option value="pickup" <?php echo $filter_type == 'pickup' ? 'selected' : ''; ?>>Pickup</option>
                            <option value="delivery" <?php echo $filter_type == 'delivery' ? 'selected' : ''; ?>>Delivery</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Delivery Source</label>
                        <select class="form-select" name="delivery_source">
                            <option value="">All Sources</option>
                            <option value="internal" <?php echo $filter_delivery_source == 'internal' ? 'selected' : ''; ?>>Internal</option>
                            <option value="noon" <?php echo $filter_delivery_source == 'noon' ? 'selected' : ''; ?>>Noon</option>
                            <option value="deliveroo" <?php echo $filter_delivery_source == 'deliveroo' ? 'selected' : ''; ?>>Deliveroo</option>
                            <option value="keeta" <?php echo $filter_delivery_source == 'keeta' ? 'selected' : ''; ?>>Keeta</option>
                            <option value="smile" <?php echo $filter_delivery_source == 'smile' ? 'selected' : ''; ?>>Smile</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" name="date_from" value="<?php echo $filter_date_from; ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" name="date_to" value="<?php echo $filter_date_to; ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" placeholder="Order #, Customer" value="<?php echo htmlspecialchars($filter_search); ?>">
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter"></i> Apply Filters
                        </button>
                        <a href="orders.php?source=order_list" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Orders List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="ordersTable">
                        <thead>
                            <tr class="table-dark">
                                <th>Order #</th>
                                <th>Date/Time</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Punched By</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($order = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo $order['order_number']; ?></strong>
                                        <?php if ($order['delivery_source'] != 'internal'): ?>
                                            <span class="badge bg-copper"><?php echo $order['delivery_source']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($order['customer_name_snapshot'] ?? 'Guest'); ?></div>
                                        <small class="text-muted"><?php echo $order['customer_phone_snapshot']; ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $type_icon = [
                                            'dine_in' => 'bi-shop',
                                            'pickup' => 'bi-bag',
                                            'delivery' => 'bi-truck'
                                        ];
                                        ?>
                                        <i class="bi <?php echo $type_icon[$order['order_type']] ?? 'bi-question'; ?>"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $order['order_type'])); ?>
                                        <?php if ($order['table_number']): ?>
                                            <br><small>Table: <?php echo $order['table_number']; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo $order['item_count']; ?></td>
                                    <td><strong><?php echo number_format($order['total_amount'], 2); ?> AED</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_badges[$order['order_status']] ?? 'secondary'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $payment_status_badges[$order['payment_status']] ?? 'secondary'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['payment_status'])); ?>
                                        </span>
                                        <br>
                                        <small><?php echo $order['payment_method']; ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($order['punched_by_name'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="orders.php?source=view_order&id=<?php echo $order['id']; ?>" 
                                               class="btn btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($order['order_status'] == 'draft' || $order['order_status'] == 'pending'): ?>
                                            <a href="orders.php?source=edit_order&id=<?php echo $order['id']; ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="printReceipt(<?php echo $order['id']; ?>, 'counter')"
                                                    title="Print Receipt">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                            <?php if (in_array($current_user_role, ['admin', 'super-admin'])): ?>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="showDeleteConfirm(<?php echo $order['id']; ?>, '<?php echo $order['order_number']; ?>')"
                                                    title="Delete" <?php echo !in_array($order['order_status'], ['draft', 'cancelled']) ? 'disabled' : ''; ?>>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            <h5>No orders found</h5>
                                            <p>Try adjusting your filters or create a new order</p>
                                            <a href="orders.php?source=pos_view" class="btn btn-primary mt-2">
                                                <i class="bi bi-cart-plus"></i> Create New Order
                                            </a>
                                        </div>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete order <strong id="deleteOrderNumber"></strong>?
                <div class="alert alert-danger mt-2">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Order</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteOrderId = null;
let deleteModal;

$(document).ready(function() {
    deleteModal = new bootstrap.Modal(document.getElementById('deleteOrderModal'));
    
    // Initialize DataTable
    $('#ordersTable').DataTable({
        pageLength: 25,
        order: [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: [9] }],
        responsive: true,
        language: {
            search: "Search orders:",
            lengthMenu: "Show _MENU_ orders per page"
        }
    });
    
    $('#confirmDeleteBtn').click(function() {
        deleteOrder();
    });
});

function showDeleteConfirm(orderId, orderNumber) {
    deleteOrderId = orderId;
    document.getElementById('deleteOrderNumber').textContent = orderNumber;
    deleteModal.show();
}

function deleteOrder() {
    if (!deleteOrderId) return;
    
    $('#confirmDeleteBtn').html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
    $('#confirmDeleteBtn').prop('disabled', true);
    
    $.ajax({
        url: 'includes/delete_order.php?id=' + deleteOrderId,
        method: 'POST',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
                $('#confirmDeleteBtn').html('Delete Order');
                $('#confirmDeleteBtn').prop('disabled', false);
                deleteModal.hide();
            }
        },
        error: function() {
            alert('Server error occurred');
            $('#confirmDeleteBtn').html('Delete Order');
            $('#confirmDeleteBtn').prop('disabled', false);
            deleteModal.hide();
        }
    });
}

function printReceipt(orderId, type) {
    window.open('orders.php?source=print_receipt&id=' + orderId + '&type=' + type, '_blank', 'width=400,height=600');
}
</script>