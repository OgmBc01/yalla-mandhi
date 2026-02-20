<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_GET['id'];
$current_user_role = $_SESSION['role'] ?? '';
$current_user_id = $_SESSION['user_id'];

// Fetch order details
$query = "SELECT o.*, 
                 u1.full_name as punched_by_name,
                 u2.full_name as closed_by_name,
                 u3.full_name as last_updated_by_name,
                 b.name as branch_name
          FROM orders o
          LEFT JOIN users u1 ON o.punched_by_admin_id = u1.id
          LEFT JOIN users u2 ON o.closed_by_admin_id = u2.id
          LEFT JOIN users u3 ON o.last_updated_by = u3.id
          LEFT JOIN branches b ON o.branch_id = b.id
          WHERE o.id = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: orders.php");
    exit();
}

$order = $result->fetch_assoc();
$stmt->close();

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $connection->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Fetch audit log for this order
$audit_query = "SELECT al.*, u.full_name 
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.entity_type = 'order' AND al.entity_id = ?
                ORDER BY al.created_at DESC
                LIMIT 10";
$audit_stmt = $connection->prepare($audit_query);
$audit_stmt->bind_param("i", $order_id);
$audit_stmt->execute();
$audit_result = $audit_stmt->get_result();

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

// Available status transitions
$status_transitions = [
    'draft' => ['pending', 'cancelled'],
    'pending' => ['confirmed', 'cancelled'],
    'confirmed' => ['in_preparation', 'cancelled'],
    'in_preparation' => ['ready', 'cancelled'],
    'ready' => ['out_for_delivery', 'completed'],
    'out_for_delivery' => ['completed', 'cancelled'],
    'completed' => ['closed'],
    'cancelled' => [],
    'refunded' => [],
    'closed' => []
];
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">
                Order Details - #<?php echo $order['order_number']; ?>
                <?php if ($order['delivery_source'] != 'internal'): ?>
                    <span class="badge bg-copper ms-2"><?php echo ucfirst($order['delivery_source']); ?></span>
                <?php endif; ?>
            </h1>
            <div>
                <a href="orders.php?source=order_list" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                <?php if ($order['order_status'] != 'closed'): ?>
                    <a href="orders.php?source=edit_order&id=<?php echo $order_id; ?>" class="btn btn-warning me-2">
                        <i class="bi bi-pencil"></i> Edit Order
                    </a>
                <?php endif; ?>
                <button class="btn btn-success" onclick="printReceipt('counter')">
                    <i class="bi bi-printer"></i> Print Receipt
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Order Summary -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="120">Order Number:</th>
                                        <td><strong>#<?php echo $order['order_number']; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Invoice Number:</th>
                                        <td><?php echo $order['invoice_number'] ?? 'Not generated'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Order Type:</th>
                                        <td>
                                            <?php
                                            $type_icons = [
                                                'dine_in' => 'bi-shop',
                                                'pickup' => 'bi-bag',
                                                'delivery' => 'bi-truck'
                                            ];
                                            ?>
                                            <i class="bi <?php echo $type_icons[$order['order_type']] ?? ''; ?>"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_type'])); ?>
                                            <?php if ($order['order_type'] == 'dine_in' && $order['table_number']): ?>
                                                - Table <?php echo $order['table_number']; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge bg-<?php echo $status_badges[$order['order_status']] ?? 'secondary'; ?> fs-6">
                                                <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Payment:</th>
                                        <td>
                                            <span class="badge bg-<?php echo $payment_status_badges[$order['payment_status']] ?? 'secondary'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $order['payment_status'])); ?>
                                            </span>
                                            - <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'] ?? 'N/A')); ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="120">Created:</th>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Punched By:</th>
                                        <td><?php echo htmlspecialchars($order['punched_by_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <?php if ($order['closed_at']): ?>
                                    <tr>
                                        <th>Closed:</th>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($order['closed_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Closed By:</th>
                                        <td><?php echo htmlspecialchars($order['closed_by_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($order['last_updated_by_name']): ?>
                                    <tr>
                                        <th>Last Updated By:</th>
                                        <td><?php echo htmlspecialchars($order['last_updated_by_name']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-basket me-2"></i>Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $subtotal = 0;
                                    while ($item = $items_result->fetch_assoc()): 
                                        $subtotal += $item['total_price'];
                                    ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($item['item_name_snapshot']); ?>
                                            <?php if (!empty($item['special_instructions'])): ?>
                                                <br><small class="text-muted">Note: <?php echo htmlspecialchars($item['special_instructions']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end"><?php echo number_format($item['unit_price_snapshot'], 2); ?> AED</td>
                                        <td class="text-end"><?php echo number_format($item['total_price'], 2); ?> AED</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th colspan="3" class="text-end">Subtotal:</th>
                                        <th class="text-end"><?php echo number_format($subtotal, 2); ?> AED</th>
                                    </tr>

                                    <?php if ($order['discount_amount'] > 0): ?>
                                    <tr>
                                        <th colspan="3" class="text-end">Discount:</th>
                                        <th class="text-end">-<?php echo number_format($order['discount_amount'], 2); ?> AED</th>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th colspan="3" class="text-end">Grand Total:</th>
                                        <th class="text-end"><?php echo number_format($order['total_amount'], 2); ?> AED</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name_snapshot'] ?? 'N/A'); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone_snapshot'] ?? 'N/A'); ?></p>
                            </div>
                            <?php if ($order['order_type'] == 'delivery'): ?>
                            <div class="col-md-6">
                                <p><strong>Delivery Address:</strong><br>
                                <?php echo nl2br(htmlspecialchars($order['delivery_address_snapshot'] ?? 'N/A')); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Status Update -->
                <?php if ($order['order_status'] != 'closed' && in_array($current_user_role, ['admin', 'super-admin', 'manager'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Update Status</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($status_transitions[$order['order_status']])): ?>
                        <div class="d-grid gap-2">
                            <?php foreach ($status_transitions[$order['order_status']] as $next_status): ?>
                            <button class="btn btn-outline-<?php echo $status_badges[$next_status] ?? 'primary'; ?>"
                                    onclick="updateOrderStatus('<?php echo $next_status; ?>')">
                                Mark as <?php echo ucfirst(str_replace('_', ' ', $next_status)); ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted mb-0">No further status updates available</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payment Actions -->
                <?php if ($order['payment_status'] == 'unpaid' && in_array($current_user_role, ['admin', 'super-admin', 'cashier'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cash me-2"></i>Payment Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" onclick="markAsPaid()">
                                <i class="bi bi-check-circle"></i> Mark as Paid
                            </button>
                            <?php if ($order['order_type'] == 'delivery' && $order['delivery_source'] != 'internal'): ?>
                            <button class="btn btn-info" onclick="markAsVendorSettled()">
                                <i class="bi bi-truck"></i> Mark as Vendor Settled
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Close Order -->
                <?php if ($order['order_status'] == 'completed' && !$order['closed_at'] && in_array($current_user_role, ['admin', 'super-admin'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-lock me-2"></i>Close Order</h5>
                    </div>
                    <div class="card-body">
                        <p>Closing this order will generate an invoice number and lock it from further edits.</p>
                        <button class="btn btn-dark w-100" onclick="closeOrder()">
                            <i class="bi bi-check2-all"></i> Close Order
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Reprint Options -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-printer me-2"></i>Reprint</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary" onclick="printReceipt('kitchen')">
                                <i class="bi bi-egg-fried"></i> Kitchen Receipt
                            </button>
                            <button class="btn btn-outline-primary" onclick="printReceipt('counter')">
                                <i class="bi bi-receipt"></i> Counter Receipt
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Audit Log -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if ($audit_result && $audit_result->num_rows > 0): ?>
                                <?php while ($log = $audit_result->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <small>
                                        <strong><?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?></strong>
                                        <?php echo ucfirst($log['action_type']); ?> order
                                        <br>
                                        <span class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                                        </span>
                                    </small>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted text-center">
                                    No activity logs found
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to update the status?</p>
                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea class="form-control" id="status_notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusBtn">Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
let statusModal;
let currentNewStatus = '';

$(document).ready(function() {
    statusModal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
});

function updateOrderStatus(newStatus) {
    currentNewStatus = newStatus;
    statusModal.show();
}

$('#confirmStatusBtn').click(function() {
    const btn = $(this);
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
    btn.prop('disabled', true);
    
    $.ajax({
        url: 'includes/ajax/update_order_status.php',
        method: 'POST',
        data: {
            order_id: <?php echo $order_id; ?>,
            status: currentNewStatus,
            notes: $('#status_notes').val()
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
                btn.html('Update Status');
                btn.prop('disabled', false);
                statusModal.hide();
            }
        },
        error: function() {
            alert('Server error occurred');
            btn.html('Update Status');
            btn.prop('disabled', false);
            statusModal.hide();
        }
    });
});

function markAsPaid() {
    if (!confirm('Mark this order as paid?')) return;
    
    $.ajax({
        url: 'includes/ajax/update_payment.php',
        method: 'POST',
        data: {
            order_id: <?php echo $order_id; ?>,
            payment_status: 'paid'
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
}

function markAsVendorSettled() {
    if (!confirm('Mark this order as vendor settled?')) return;
    
    $.ajax({
        url: 'includes/ajax/update_payment.php',
        method: 'POST',
        data: {
            order_id: <?php echo $order_id; ?>,
            payment_status: 'vendor_settled'
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
}

function closeOrder() {
    if (!confirm('Close this order? This action cannot be undone.')) return;
    
    $.ajax({
        url: 'includes/ajax/close_order.php',
        method: 'POST',
        data: {
            order_id: <?php echo $order_id; ?>
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
}

function printReceipt(type) {
    window.open('orders.php?source=print_receipt&id=<?php echo $order_id; ?>&type=' + type, 
                '_blank', 'width=400,height=600');
}
</script>

<?php
$items_stmt->close();
$audit_stmt->close();
?>