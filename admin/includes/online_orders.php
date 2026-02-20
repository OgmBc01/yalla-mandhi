<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get online orders (from external vendors)
$query = "SELECT o.*, 
                 COUNT(oi.id) as item_count
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          WHERE o.delivery_source IN ('noon', 'deliveroo', 'keeta', 'smile')
          GROUP BY o.id
          ORDER BY o.created_at DESC
          LIMIT 50";

$result = $connection->query($query);
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

        <!-- Vendor Stats -->
        <div class="row mb-4">
            <?php
            $vendor_stats = [
                'noon' => ['color' => 'warning', 'icon' => 'bi-brightness-alt-high'],
                'deliveroo' => ['color' => 'info', 'icon' => 'bi-bicycle'],
                'keeta' => ['color' => 'danger', 'icon' => 'bi-lightning'],
                'smile' => ['color' => 'success', 'icon' => 'bi-emoji-smile']
            ];
            
            foreach ($vendor_stats as $vendor => $style):
                $count_query = "SELECT COUNT(*) as count, SUM(total_amount) as total 
                               FROM orders 
                               WHERE delivery_source = ? AND DATE(created_at) = CURDATE()";
                $stmt = $connection->prepare($count_query);
                $stmt->bind_param("s", $vendor);
                $stmt->execute();
                $stats = $stmt->get_result()->fetch_assoc();
            ?>
            <div class="col-md-3">
                <div class="card bg-<?php echo $style['color']; ?> text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title"><?php echo ucfirst($vendor); ?></h6>
                                <h3 class="mb-0"><?php echo $stats['count'] ?? 0; ?> orders</h3>
                                <small><?php echo number_format($stats['total'] ?? 0, 2); ?> AED</small>
                            </div>
                            <i class="bi <?php echo $style['icon']; ?> display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Orders List -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Recent Online Orders</h5>
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
                                    <td>#<?php echo $order['order_number']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $vendor_stats[$order['delivery_source']]['color']; ?>">
                                            <?php echo ucfirst($order['delivery_source']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name_snapshot'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo $order['item_count']; ?></td>
                                    <td><strong><?php echo number_format($order['total_amount'], 2); ?> AED</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_badges[$order['order_status']] ?? 'secondary'; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orders.php?source=view_order&id=<?php echo $order['id']; ?>" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-globe display-4 d-block mb-2"></i>
                                            <h5>No online orders found</h5>
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

<script>
function refreshOrders() {
    location.reload();
}
</script>