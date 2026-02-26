<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager', 'employee'])) {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit;
}

// Validate input
$supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;

if (!$supplier_id) {
    echo '<div class="alert alert-warning">Invalid supplier ID</div>';
    exit;
}

// Get supplier info
$supplier_query = "SELECT supplier_name FROM suppliers WHERE id = ?";
$supplier_stmt = $connection->prepare($supplier_query);
$supplier_stmt->bind_param("i", $supplier_id);
$supplier_stmt->execute();
$supplier_result = $supplier_stmt->get_result();
$supplier = $supplier_result->fetch_assoc();
$supplier_stmt->close();

if (!$supplier) {
    echo '<div class="alert alert-warning">Supplier not found</div>';
    exit;
}

// Get items from this supplier
$items_query = "SELECT i.*, 
                       COUNT(t.id) as transaction_count,
                       MAX(t.created_at) as last_received
                FROM inventory_items i
                LEFT JOIN inventory_transactions t ON i.id = t.inventory_item_id
                WHERE i.supplier = ? AND i.is_active = 1
                GROUP BY i.id
                ORDER BY i.item_name";

$items_stmt = $connection->prepare($items_query);
$items_stmt->bind_param("s", $supplier['supplier_name']);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<h5 class="mb-3">Items from <?php echo htmlspecialchars($supplier['supplier_name']); ?></h5>

<?php if ($items_result->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th class="text-end">Current Stock</th>
                    <th class="text-end">Cost/Unit</th>
                    <th class="text-end">Total Value</th>
                    <th>Last Received</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items_result->fetch_assoc()): 
                    $total_value = $item['quantity'] * $item['cost_per_unit'];
                ?>
                <tr>
                    <td>
                        <a href="inventory.php?source=view_item&id=<?php echo $item['id']; ?>" target="_blank">
                            <?php echo htmlspecialchars($item['item_name']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($item['category'] ?? 'N/A'); ?></td>
                    <td class="text-end">
                        <?php echo number_format($item['quantity'], 2); ?> <?php echo $item['unit_of_measure']; ?>
                    </td>
                    <td class="text-end"><?php echo number_format($item['cost_per_unit'], 2); ?> AED</td>
                    <td class="text-end"><?php echo number_format($total_value, 2); ?> AED</td>
                    <td>
                        <?php if ($item['last_received']): ?>
                            <?php echo date('d/m/Y', strtotime($item['last_received'])); ?>
                        <?php else: ?>
                            <span class="text-muted">Never</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No items found from this supplier.
    </div>
<?php endif; ?>

<?php
$items_stmt->close();
?>