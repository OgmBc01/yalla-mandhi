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

// Get item ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: inventory.php");
    exit();
}

$item_id = (int)$_GET['id'];

// Fetch item details with created by user info
$query = "SELECT i.*, 
           u1.full_name as created_by_name
       FROM inventory_items i
       LEFT JOIN users u1 ON i.created_by = u1.id
       WHERE i.id = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: inventory.php");
    exit();
}

$item = $result->fetch_assoc();
$stmt->close();

// Fetch recent transactions
$trans_query = "SELECT t.*, u.full_name as performed_by_name
                FROM inventory_transactions t
                LEFT JOIN users u ON t.performed_by = u.id
                WHERE t.inventory_item_id = ?
                ORDER BY t.created_at DESC
                LIMIT 20";
$trans_stmt = $connection->prepare($trans_query);
$trans_stmt->bind_param("i", $item_id);
$trans_stmt->execute();
$trans_result = $trans_stmt->get_result();

// Calculate stock status
$stock_status = 'Good';
$status_color = 'success';
if ($item['quantity_available'] <= 0) {
    $stock_status = 'Out of Stock';
    $status_color = 'danger';
} elseif ($item['quantity_available'] <= $item['reorder_level']) {
    $stock_status = 'Low Stock';
    $status_color = 'warning';
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-box-seam me-2"></i>Item Details</h1>
            <div>
                <a href="inventory.php?source=edit_item&id=<?php echo $item_id; ?>" class="btn btn-warning me-2">
                    <i class="bi bi-pencil"></i> Edit Item
                </a>
                <a href="inventory.php?source=stock_history&id=<?php echo $item_id; ?>" class="btn btn-info me-2">
                    <i class="bi bi-clock-history"></i> Full History
                </a>
                <a href="inventory.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- Item Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-<?php echo $status_color; ?> text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Current Stock</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($item['quantity_available'], 2); ?> <?php echo $item['unit_of_measure']; ?></h3>
                        <small>Status: <?php echo $stock_status; ?></small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Unit Cost</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($item['cost_per_unit'], 2); ?> AED</h3>
                        <small>Per <?php echo $item['unit_of_measure']; ?></small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Value</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($item['quantity_available'] * $item['cost_per_unit'], 2); ?> AED</h3>
                        <small>Current stock value</small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Reorder Level</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($item['reorder_level'], 2); ?> <?php echo $item['unit_of_measure']; ?></h3>
                        <small>Minimum before reorder</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Details -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Item Name:</th>
                                <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                            </tr>
                            <tr>
                                <th>SKU:</th>
                                <td><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td><?php echo htmlspecialchars($item['category'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td><?php echo nl2br(htmlspecialchars($item['description'] ?? 'N/A')); ?></td>
                            </tr>
                            <tr>
                                <th>Unit of Measure:</th>
                                <td><?php echo htmlspecialchars($item['unit_of_measure']); ?></td>
                            </tr>
                            <tr>
                                <th>Location:</th>
                                <td><?php echo htmlspecialchars($item['location'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <?php if ($item['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Supplier Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Supplier:</th>
                                <td><?php echo htmlspecialchars($item['supplier'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Contact:</th>
                                <td><?php echo htmlspecialchars($item['supplier_contact'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>System Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Created By:</th>
                                <td><?php echo htmlspecialchars($item['created_by_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Recent Stock Movements</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Type</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Previous</th>
                                <th class="text-end">New</th>
                                <th>Reference</th>
                                <th>Performed By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($trans_result && $trans_result->num_rows > 0): ?>
                                <?php while ($trans = $trans_result->fetch_assoc()): 
                                    $type_class = '';
                                    switch ($trans['transaction_type']) {
                                        case 'purchase':
                                        case 'return':
                                            $type_class = 'text-success';
                                            break;
                                        case 'usage':
                                        case 'damage':
                                            $type_class = 'text-danger';
                                            break;
                                        default:
                                            $type_class = 'text-warning';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($trans['created_at'])); ?></td>
                                    <td class="<?php echo $type_class; ?> fw-bold">
                                        <?php echo ucfirst($trans['transaction_type']); ?>
                                    </td>
                                    <td class="text-end fw-bold">
                                        <?php echo ($trans['transaction_type'] == 'purchase' || $trans['transaction_type'] == 'return') ? '+' : '-'; ?>
                                        <?php echo number_format(abs($trans['quantity']), 2); ?>
                                    </td>
                                    <td class="text-end"><?php echo number_format($trans['previous_quantity'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($trans['new_quantity'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($trans['reference_id'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($trans['performed_by_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($trans['notes'] ?? ''); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-3">
                                        <div class="text-muted">
                                            <i class="bi bi-arrow-left-right"></i> No transactions found
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