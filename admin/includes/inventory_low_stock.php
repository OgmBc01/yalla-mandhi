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

// Get filter parameters
$category = $_GET['category'] ?? '';
$supplier = $_GET['supplier'] ?? '';

// Build query for low stock items
$query = "SELECT i.*, 
                   COUNT(t.id) as transaction_count,
                   MAX(t.created_at) as last_movement
               FROM inventory_items i
               LEFT JOIN inventory_transactions t ON i.id = t.inventory_item_id
               WHERE i.is_active = 1 
               AND i.quantity_available <= i.reorder_level";
$params = [];
$types = "";

if (!empty($category)) {
    $query .= " AND i.category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($supplier)) {
    $query .= " AND i.supplier = ?";
    $params[] = $supplier;
    $types .= "s";
}

$query .= " GROUP BY i.id ORDER BY (i.quantity_available / i.reorder_level) ASC";

// Prepare and execute
$stmt = $connection->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM inventory_items WHERE category IS NOT NULL AND category != '' AND quantity_available <= reorder_level ORDER BY category";
$categories_result = $connection->query($categories_query);

// Get suppliers for filter
$suppliers_query = "SELECT DISTINCT supplier FROM inventory_items WHERE supplier IS NOT NULL AND supplier != '' AND quantity_available <= reorder_level ORDER BY supplier";
$suppliers_result = $connection->query($suppliers_query);

// Get summary statistics
$stats_query = "SELECT 
    COUNT(*) as low_stock_count,
    SUM(CASE WHEN quantity_available <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
    SUM(quantity_available) as total_quantity,
    SUM(quantity_available * cost_per_unit) as total_value
    FROM inventory_items 
    WHERE is_active = 1 AND quantity_available <= reorder_level";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alerts</h1>
            <a href="inventory.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
        </div>

        <!-- Alert Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Low Stock Items</h6>
                                <h2 class="text-white mb-0"><?php echo $stats['low_stock_count'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-exclamation-triangle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Out of Stock</h6>
                                <h2 class="text-white mb-0"><?php echo $stats['out_of_stock_count'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-x-circle display-4 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Stock Value at Risk</h6>
                                <h2 class="text-white mb-0"><?php echo number_format($stats['total_value'] ?? 0, 2); ?> AED</h2>
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
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="low_stock">
                    
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <?php if ($categories_result): ?>
                                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="supplier">
                            <option value="">All Suppliers</option>
                            <?php if ($suppliers_result): ?>
                                <?php while ($sup = $suppliers_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($sup['supplier']); ?>" <?php echo $supplier == $sup['supplier'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sup['supplier']); ?>
                                </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-filter"></i> Apply Filters
                        </button>
                        <a href="inventory.php?source=low_stock" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Low Stock Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Items Requiring Attention</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th class="text-end">Current Stock</th>
                                <th class="text-end">Reorder Level</th>
                                <th class="text-end">Deficit</th>
                                <th>Status</th>
                                <th>Supplier</th>
                                <th>Last Movement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($item = $result->fetch_assoc()): 
                                    $deficit = $item['reorder_level'] - $item['quantity_available'];
                                    $status_class = $item['quantity_available'] <= 0 ? 'danger' : 'warning';
                                    $status_text = $item['quantity_available'] <= 0 ? 'Out of Stock' : 'Low Stock';
                                    
                                    // Calculate urgency
                                    $urgency = $item['reorder_level'] > 0 ? ($item['quantity_available'] / $item['reorder_level']) * 100 : 100;
                                    $urgency_class = $urgency <= 25 ? 'danger' : ($urgency <= 50 ? 'warning' : 'info');
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                        <?php if (!empty($item['sku'])): ?>
                                        <br><small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['category'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['unit_of_measure']); ?></td>
                                    <td class="text-end fw-bold text-<?php echo $status_class; ?>">
                                        <?php echo number_format($item['quantity_available'], 2); ?>
                                    </td>
                                    <td class="text-end"><?php echo number_format($item['reorder_level'], 2); ?></td>
                                    <td class="text-end text-danger fw-bold">
                                        <?php echo number_format($deficit, 2); ?>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-<?php echo $urgency_class; ?>" 
                                                 style="width: <?php echo min(100, $urgency); ?>%;">
                                                <?php echo number_format($urgency, 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['supplier'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($item['last_movement']): ?>
                                            <?php echo date('d/m/Y', strtotime($item['last_movement'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="inventory.php?source=view_item&id=<?php echo $item['id']; ?>" 
                                               class="btn btn-outline-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="quickPurchase(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['item_name'])); ?>', <?php echo $deficit; ?>)"
                                                    title="Quick Purchase">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" 
                                                    onclick="adjustStock(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['item_name'])); ?>')"
                                                    title="Adjust Stock">
                                                <i class="bi bi-plus-slash-minus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                                            <h5>No Low Stock Items Found</h5>
                                            <p>All inventory items are at healthy levels. Great job!</p>
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

<!-- Quick Purchase Modal -->
<div class="modal fade" id="quickPurchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cart-plus me-2"></i>Quick Purchase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickPurchaseForm">
                    <input type="hidden" id="purchase_item_id" name="item_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <p class="form-control-plaintext fw-bold" id="purchase_item_name"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <p class="form-control-plaintext" id="purchase_current_qty"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Suggested Order Quantity</label>
                        <p class="form-control-plaintext" id="purchase_suggested_qty"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="purchase_quantity" class="form-label">Quantity to Order *</label>
                        <input type="number" class="form-control" id="purchase_quantity" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="purchase_unit_cost" class="form-label">Unit Cost (AED) *</label>
                        <input type="number" class="form-control" id="purchase_unit_cost" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="purchase_supplier" class="form-label">Supplier</label>
                        <input type="text" class="form-control" id="purchase_supplier">
                    </div>
                    
                    <div class="mb-3">
                        <label for="purchase_reference" class="form-label">Reference (PO #, Invoice #)</label>
                        <input type="text" class="form-control" id="purchase_reference">
                    </div>
                    
                    <div class="mb-3">
                        <label for="purchase_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="purchase_notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmPurchaseBtn">Record Purchase</button>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal (reuse from view_all) -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="adjustStockForm">
                    <input type="hidden" id="adjust_item_id" name="item_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <p class="form-control-plaintext fw-bold" id="adjust_item_name"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Quantity</label>
                        <p class="form-control-plaintext" id="adjust_current_qty"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select class="form-select" id="adjust_type" required>
                            <option value="purchase">Purchase (Add Stock)</option>
                            <option value="usage">Usage (Remove Stock)</option>
                            <option value="adjustment">Manual Adjustment</option>
                            <option value="damage">Damage/Loss</option>
                            <option value="return">Return to Supplier</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="adjust_quantity" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Unit Cost (for purchases)</label>
                        <input type="number" class="form-control" id="adjust_unit_cost" step="0.01" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference</label>
                        <input type="text" class="form-control" id="adjust_reference">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="adjust_notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAdjustBtn">Save Adjustment</button>
            </div>
        </div>
    </div>
</div>

<script>
let purchaseModal;
let adjustModal;

$(document).ready(function() {
    purchaseModal = new bootstrap.Modal(document.getElementById('quickPurchaseModal'));
    adjustModal = new bootstrap.Modal(document.getElementById('adjustStockModal'));
});

function quickPurchase(itemId, itemName, suggestedQty) {
    // Get current item details
    $.ajax({
        url: 'includes/ajax/get_inventory_item.php',
        method: 'GET',
        data: { id: itemId },
        success: function(response) {
            if (response.success) {
                $('#purchase_item_id').val(itemId);
                $('#purchase_item_name').text(itemName);
                $('#purchase_current_qty').text(response.item.quantity_available + ' ' + response.item.unit_of_measure);
                $('#purchase_suggested_qty').text(suggestedQty + ' ' + response.item.unit_of_measure);
                $('#purchase_quantity').val(suggestedQty);
                $('#purchase_unit_cost').val(response.item.cost_per_unit);
                $('#purchase_supplier').val(response.item.supplier || '');
                $('#purchase_reference').val('');
                $('#purchase_notes').val('');
                purchaseModal.show();
            }
        }
    });
}

function adjustStock(itemId, itemName) {
    $.ajax({
        url: 'includes/ajax/get_inventory_item.php',
        method: 'GET',
        data: { id: itemId },
        success: function(response) {
            if (response.success) {
                $('#adjust_item_id').val(itemId);
                $('#adjust_item_name').text(itemName);
                $('#adjust_current_qty').text(response.item.quantity_available + ' ' + response.item.unit_of_measure);
                $('#adjust_quantity').val('');
                $('#adjust_unit_cost').val(response.item.cost_per_unit);
                $('#adjust_reference').val('');
                $('#adjust_notes').val('');
                adjustModal.show();
            }
        }
    });
}

$('#confirmPurchaseBtn').click(function() {
    let formData = {
        item_id: $('#purchase_item_id').val(),
        type: 'purchase',
        quantity: $('#purchase_quantity').val(),
        unit_cost: $('#purchase_unit_cost').val(),
        reference: $('#purchase_reference').val(),
        notes: $('#purchase_notes').val(),
        supplier: $('#purchase_supplier').val()
    };
    
    if (!formData.quantity || formData.quantity <= 0) {
        alert('Please enter a valid quantity');
        return;
    }
    
    if (!formData.unit_cost || formData.unit_cost <= 0) {
        alert('Please enter a valid unit cost');
        return;
    }
    
    $.ajax({
        url: 'includes/ajax/adjust_inventory.php',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Server error occurred');
        }
    });
});

$('#confirmAdjustBtn').click(function() {
    let formData = {
        item_id: $('#adjust_item_id').val(),
        type: $('#adjust_type').val(),
        quantity: $('#adjust_quantity').val(),
        unit_cost: $('#adjust_unit_cost').val(),
        reference: $('#adjust_reference').val(),
        notes: $('#adjust_notes').val()
    };
    
    if (!formData.quantity || formData.quantity <= 0) {
        alert('Please enter a valid quantity');
        return;
    }
    
    $.ajax({
        url: 'includes/ajax/adjust_inventory.php',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Server error occurred');
        }
    });
});
</script>