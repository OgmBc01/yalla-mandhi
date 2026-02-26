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

// Search
$search = $_GET['search'] ?? '';

// Build query for suppliers
$query = "SELECT s.*
          FROM suppliers s
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (s.supplier_name LIKE ? OR s.contact_person LIKE ? OR s.email LIKE ? OR s.phone LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= "ssss";
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM suppliers s WHERE 1=1";
if (!empty($search)) {
    $count_query .= " AND (s.supplier_name LIKE ? OR s.contact_person LIKE ? OR s.email LIKE ? OR s.phone LIKE ?)";
}
$count_stmt = $connection->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$count_stmt->close();

// Add pagination to main query
$query .= " ORDER BY s.supplier_name ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $connection->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get item counts and values for each supplier (separate query)
$supplier_stats = [];
$stats_query = "SELECT 
                    supplier,
                    COUNT(*) as item_count,
                    SUM(quantity_available * unit_price) as total_value
                FROM inventory_items 
                WHERE supplier IS NOT NULL AND supplier != ''
                GROUP BY supplier";
$stats_result = $connection->query($stats_query);
while ($row = $stats_result->fetch_assoc()) {
    $supplier_stats[$row['supplier']] = [
        'item_count' => $row['item_count'],
        'total_value' => $row['total_value']
    ];
}

// Get summary statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM suppliers) as total_suppliers,
    (SELECT COUNT(DISTINCT supplier) FROM inventory_items WHERE supplier IS NOT NULL AND supplier != '') as active_suppliers,
    (SELECT COUNT(*) FROM inventory_items) as total_items,
    (SELECT SUM(quantity_available * unit_price) FROM inventory_items) as total_inventory_value";
$stats_result = $connection->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-truck me-2"></i>Supplier Management</h1>
            <div>
                <a href="inventory.php?source=add_supplier" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Supplier
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Suppliers</h6>
                        <h3 class="text-white mb-0"><?php echo $stats['total_suppliers'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Active Suppliers</h6>
                        <h3 class="text-white mb-0"><?php echo $stats['active_suppliers'] ?? 0; ?></h3>
                        <small>with inventory items</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Inventory Value</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($stats['total_inventory_value'] ?? 0, 2); ?> AED</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="suppliers">
                    
                    <div class="col-md-8">
                        <label class="form-label">Search Suppliers</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by name, contact person, email, or phone..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <a href="inventory.php?source=suppliers" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Suppliers List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Supplier Name</th>
                                <th>Contact Person</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th class="text-center">Items Supplied</th>
                                <th class="text-end">Inventory Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($supplier = $result->fetch_assoc()): 
                                    // Get stats for this supplier
                                    $item_count = 0;
                                    $total_value = 0;
                                    foreach ($supplier_stats as $supplier_name => $stats) {
                                        if (strtolower(trim($supplier_name)) == strtolower(trim($supplier['supplier_name']))) {
                                            $item_count = $stats['item_count'];
                                            $total_value = $stats['total_value'];
                                            break;
                                        }
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($supplier['supplier_name']); ?></strong>
                                        <?php if (!empty($supplier['tax_number'])): ?>
                                        <br><small class="text-muted">Tax: <?php echo htmlspecialchars($supplier['tax_number']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($supplier['contact_person'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if (!empty($supplier['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($supplier['email']); ?>">
                                            <?php echo htmlspecialchars($supplier['email']); ?>
                                        </a>
                                        <?php else: ?>
                                        N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($supplier['phone'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($supplier['phone']); ?>">
                                            <?php echo htmlspecialchars($supplier['phone']); ?>
                                        </a>
                                        <?php else: ?>
                                        N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $item_count; ?>
                                        <?php if ($item_count > 0): ?>
                                        <br><small class="text-success">items</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?php echo number_format($total_value, 2); ?> AED</td>
                                    <td>
                                        <?php if ($supplier['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="inventory.php?source=edit_supplier&id=<?php echo $supplier['id']; ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="inventory.php?source=add_purchase&supplier_id=<?php echo $supplier['id']; ?>" 
                                               class="btn btn-outline-success" title="Create Purchase Order">
                                                <i class="bi bi-cart-plus"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-info" 
                                                    onclick="viewSupplierItems('<?php echo htmlspecialchars(addslashes($supplier['supplier_name'])); ?>')"
                                                    title="View Items">
                                                <i class="bi bi-box-seam"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            <h5>No suppliers found</h5>
                                            <p>Add your first supplier to start tracking inventory.</p>
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
                            <a class="page-link" href="?source=suppliers&page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">
                                Previous
                            </a>
                        </li>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($page+2, $total_pages); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?source=suppliers&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?source=suppliers&page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">
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

<!-- View Supplier Items Modal -->
<div class="modal fade" id="supplierItemsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Items from Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="supplierItemsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading items...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewSupplierItems(supplierName) {
    $('#supplierItemsModal').modal('show');
    $('#supplierItemsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading items...</p></div>');
    
    $.ajax({
        url: 'includes/ajax/get_supplier_items.php',
        method: 'GET',
        data: { supplier_name: supplierName },
        success: function(response) {
            $('#supplierItemsContent').html(response);
        },
        error: function() {
            $('#supplierItemsContent').html('<div class="alert alert-danger">Failed to load items</div>');
        }
    });
}
</script>