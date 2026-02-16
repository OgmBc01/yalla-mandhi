<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    echo "Invalid order";
    exit;
}

// Fetch order details
$query = "SELECT o.*, 
                 u1.full_name as punched_by_name,
                 u2.full_name as closed_by_name,
                 b.name as branch_name
          FROM orders o
          LEFT JOIN users u1 ON o.punched_by_admin_id = u1.id
          LEFT JOIN users u2 ON o.closed_by_admin_id = u2.id
          LEFT JOIN branches b ON o.branch_id = b.id
          WHERE o.id = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $connection->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

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

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <h5>Order #<?php echo $order['order_number']; ?></h5>
            <table class="table table-sm">
                <tr>
                    <th>Status:</th>
                    <td>
                        <span class="badge bg-<?php echo $status_badges[$order['order_status']] ?? 'secondary'; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Type:</th>
                    <td><?php echo ucfirst(str_replace('_', ' ', $order['order_type'])); ?></td>
                </tr>
                <tr>
                    <th>Created:</th>
                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                </tr>
                <tr>
                    <th>Customer:</th>
                    <td><?php echo htmlspecialchars($order['customer_name_snapshot'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo htmlspecialchars($order['customer_phone_snapshot'] ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h5>Items</h5>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name_snapshot']); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-end"><?php echo number_format($item['total_price'], 2); ?> SAR</td>
                    </tr>
                    <?php if (!empty($item['special_instructions'])): ?>
                    <tr>
                        <td colspan="3" class="text-muted small">
                            <i class="bi bi-chat"></i> <?php echo htmlspecialchars($item['special_instructions']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">Total:</th>
                        <th class="text-end"><?php echo number_format($order['total_amount'], 2); ?> SAR</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-12">
            <p><strong>Payment:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'] ?? 'N/A')); ?> - 
                <span class="badge bg-<?php echo $payment_status_badges[$order['payment_status']] ?? 'secondary'; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $order['payment_status'])); ?>
                </span>
            </p>
        </div>
    </div>
</div>

<?php
$items_stmt->close();
?>