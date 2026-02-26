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
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$supplier = $_GET['supplier'] ?? '';
$stock_status = $_GET['stock_status'] ?? '';

// Build query
$query = "SELECT i.*, 
                 COUNT(t.id) as transaction_count,
                 MAX(t.created_at) as last_movement
          FROM inventory_items i
          LEFT JOIN inventory_transactions t ON i.id = t.inventory_item_id
          WHERE i.is_active = 1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (i.item_name LIKE ? OR i.sku LIKE ? OR i.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

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

if (!empty($stock_status)) {
    switch ($stock_status) {
        case 'low':
            $query .= " AND i.quantity_available <= i.reorder_level AND i.quantity_available > 0";
            break;
        case 'out':
            $query .= " AND i.quantity_available <= 0";
            break;
        case 'overstock':
            $query .= " AND i.quantity_available > i.reorder_level * 3";
            break;
    }
}

$query .= " GROUP BY i.id ORDER BY i.item_name ASC";

// Prepare and execute
$stmt = $connection->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM inventory_items WHERE category IS NOT NULL AND category != '' ORDER BY category";
$categories_result = $connection->query($categories_query);

// Get suppliers for filter
$suppliers_query = "SELECT DISTINCT supplier FROM inventory_items WHERE supplier IS NOT NULL AND supplier != '' ORDER BY supplier";
$suppliers_result = $connection->query($suppliers_query);

// Get summary statistics
        $stats_query = "SELECT 
            COUNT(*) as total_items,
            SUM(quantity_available) as total_quantity,
            SUM(quantity_available * cost_per_unit) as total_value,
            SUM(CASE WHEN quantity_available <= reorder_level AND quantity_available > 0 THEN 1 ELSE 0 END) as low_stock_count,
            SUM(CASE WHEN quantity_available <= 0 THEN 1 ELSE 0 END) as out_of_stock_count
            FROM inventory_items WHERE is_active = 1";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-boxes me-2"></i>Inventory Management</h1>
            <div>
                <a href="inventory.php?source=add_stock" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Add New Item
                </a>
                <a href="inventory.php?source=add_purchase" class="btn btn-success">
                    <i class="bi bi-cart-plus"></i> New Purchase
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="inventory-stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Items</h6>
                            <h2 class="text-white mb-0"><?php echo $stats['total_items'] ?? 0; ?></h2>
                        </div>
                        <i class="bi bi-box-seam display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="inventory-stats-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Value</h6>
                            <h2 class="text-white mb-0"><?php echo number_format($stats['total_value'] ?? 0, 2); ?> AED</h2>
                        </div>
                        <i class="bi bi-cash-stack display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="inventory-stats-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Low Stock</h6>
                            <h2 class="text-white mb-0"><?php echo $stats['low_stock_count'] ?? 0; ?></h2>
                        </div>
                        <i class="bi bi-exclamation-triangle display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="inventory-stats-card danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Out of Stock</h6>
                            <h2 class="text-white mb-0"><?php echo $stats['out_of_stock_count'] ?? 0; ?></h2>
                        </div>
                        <i class="bi bi-x-circle display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="view_inventory">
                    
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" placeholder="Item name, SKU..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="col-md-2">
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
                    
                    <div class="col-md-2">
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
                    
                    <div class="col-md-2">
                        <label class="form-label">Stock Status</label>
                        <select class="form-select" name="stock_status">
                            <option value="">All</option>
                            <option value="low" <?php echo $stock_status == 'low' ? 'selected' : ''; ?>>Low Stock</option>
                            <option value="out" <?php echo $stock_status == 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                            <option value="overstock" <?php echo $stock_status == 'overstock' ? 'selected' : ''; ?>>Overstock</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-filter"></i> Apply Filters
                        </button>
                        <a href="inventory.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Inventory Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-report" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Cost/Unit</th>
                                <th class="text-end">Total Value</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($item = $result->fetch_assoc()): 
                                    // Determine stock level status
                                    $stock_status_class = 'stock-level-high';
                                    $status_text = 'Good';
                                    
                                    if ($item['quantity_available'] <= 0) {
                                        $stock_status_class = 'stock-level-out';
                                        $status_text = 'Out of Stock';
                                    } elseif ($item['quantity_available'] <= $item['reorder_level']) {
                                        $stock_status_class = 'stock-level-low';
                                        $status_text = 'Low Stock';
                                    } elseif ($item['quantity_available'] <= $item['reorder_level'] * 2) {
                                        $stock_status_class = 'stock-level-medium';
                                        $status_text = 'Medium';
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                        <?php if (!empty($item['description'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($item['description'], 0, 50)) . (strlen($item['description']) > 50 ? '...' : ''); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['category'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['unit_of_measure']); ?></td>
                                    <td class="text-end fw-bold">
                                        <?php echo number_format($item['quantity_available'], 2); ?>
                                    </td>
                                    <td class="text-end"><?php echo number_format($item['cost_per_unit'], 2); ?> AED</td>
                                    <td class="text-end text-success">
                                        <?php echo number_format($item['quantity_available'] * $item['cost_per_unit'], 2); ?> AED
                                    </td>
                                    <td><?php echo htmlspecialchars($item['supplier'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="stock-level-indicator <?php echo $stock_status_class; ?>"></span>
                                        <?php echo $status_text; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="inventory.php?source=view_item&id=<?php echo $item['id']; ?>" 
                                               class="btn btn-outline-info" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="inventory.php?source=edit_item&id=<?php echo $item['id']; ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="inventory.php?source=stock_history&id=<?php echo $item['id']; ?>" 
                                               class="btn btn-outline-secondary" title="History">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="adjustStock(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['item_name']); ?>')"
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
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            <h5>No inventory items found</h5>
                                            <p>Click "Add New Item" to create your first inventory item.</p>
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

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
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
                        <label class="form-label">Reference (Order #, PO #, etc.)</label>
                        <input type="text" class="form-control" id="adjust_reference" placeholder="Optional">
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
let adjustModal;

$(document).ready(function() {
    adjustModal = new bootstrap.Modal(document.getElementById('adjustStockModal'));
    
    // Initialize DataTable
    $('#inventoryTable').DataTable({
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: [9] }],
        responsive: true,
        language: {
            search: "Search inventory:",
            lengthMenu: "Show _MENU_ items per page"
        }
    });
});

function adjustStock(itemId, itemName) {
    $.ajax({
        url: 'includes/ajax/get_inventory_item.php',
        method: 'GET',
        data: { id: itemId },
        success: function(response) {
            if (response.success) {
                $('#adjust_item_id').val(itemId);
                $('#adjust_item_name').text(itemName);
                // Use quantity_available from the response
                $('#adjust_current_qty').text(response.item.quantity_available + ' ' + response.item.unit_of_measure);
                $('#adjust_quantity').val('');
                $('#adjust_unit_cost').val(response.item.unit_price || 0);
                $('#adjust_reference').val('');
                $('#adjust_notes').val('');
                adjustModal.show();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('Failed to load item details');
        }
    });
}

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