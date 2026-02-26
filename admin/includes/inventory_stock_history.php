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
$limit = 50;
$offset = ($page - 1) * $limit;

// Filters
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
$type = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Build query
$query = "SELECT t.*, i.item_name, i.unit_of_measure, u.full_name as performed_by_name
          FROM inventory_transactions t
          JOIN inventory_items i ON t.inventory_item_id = i.id
          LEFT JOIN users u ON t.performed_by = u.id
          WHERE DATE(t.created_at) BETWEEN ? AND ?";
$params = [$date_from, $date_to];
$types = "ss";

if ($item_id > 0) {
    $query .= " AND t.inventory_item_id = ?";
    $params[] = $item_id;
    $types .= "i";
}

if (!empty($type)) {
    $query .= " AND t.transaction_type = ?";
    $params[] = $type;
    $types .= "s";
}

// Get total count for pagination
        $count_query = "SELECT COUNT(*) as total FROM inventory_transactions t JOIN inventory_items i ON t.inventory_item_id = i.id LEFT JOIN users u ON t.performed_by = u.id WHERE DATE(t.created_at) BETWEEN ? AND ?";
        if ($item_id > 0) {
            $count_query .= " AND t.inventory_item_id = ?";
        }
        if (!empty($type)) {
            $count_query .= " AND t.transaction_type = ?";
        }
$count_stmt = $connection->prepare($count_query);
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$count_stmt->close();

// Add pagination to main query
$query .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $connection->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get items for filter dropdown
$items_query = "SELECT id, item_name FROM inventory_items WHERE is_active = 1 ORDER BY item_name";
$items_result = $connection->query($items_query);

// Get transaction types for summary
$summary_query = "SELECT 
    transaction_type,
    COUNT(*) as count,
    SUM(CASE WHEN transaction_type IN ('purchase','return') THEN quantity_added WHEN transaction_type IN ('usage','damage') THEN quantity_used ELSE 0 END) as total_quantity
    FROM inventory_transactions t
    WHERE DATE(t.created_at) BETWEEN ? AND ?
    GROUP BY transaction_type";
$summary_stmt = $connection->prepare($summary_query);
$summary_stmt->bind_param("ss", $date_from, $date_to);
$summary_stmt->execute();
$summary_result = $summary_stmt->get_result();
$summary_stmt->close();
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-clock-history me-2"></i>Stock Movement History</h1>
            <a href="inventory.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <?php 
            $summary_data = [];
            while ($row = $summary_result->fetch_assoc()) {
                $summary_data[$row['transaction_type']] = $row;
            }
            ?>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body p-3">
                        <h6 class="text-white-50">Purchases</h6>
                        <h4 class="text-white mb-0"><?php echo $summary_data['purchase']['count'] ?? 0; ?></h4>
                        <small><?php echo number_format($summary_data['purchase']['total_quantity'] ?? 0, 2); ?> units</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body p-3">
                        <h6 class="text-white-50">Usage</h6>
                        <h4 class="text-white mb-0"><?php echo $summary_data['usage']['count'] ?? 0; ?></h4>
                        <small><?php echo number_format(abs($summary_data['usage']['total_quantity'] ?? 0), 2); ?> units</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-white">
                    <div class="card-body p-3">
                        <h6 class="text-white-50">Adjustments</h6>
                        <h4 class="text-white mb-0"><?php echo $summary_data['adjustment']['count'] ?? 0; ?></h4>
                        <small><?php echo number_format(abs($summary_data['adjustment']['total_quantity'] ?? 0), 2); ?> units</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white">
                    <div class="card-body p-3">
                        <h6 class="text-white-50">Returns</h6>
                        <h4 class="text-white mb-0"><?php echo $summary_data['return']['count'] ?? 0; ?></h4>
                        <small><?php echo number_format($summary_data['return']['total_quantity'] ?? 0, 2); ?> units</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-secondary text-white">
                    <div class="card-body p-3">
                        <h6 class="text-white-50">Damage</h6>
                        <h4 class="text-white mb-0"><?php echo $summary_data['damage']['count'] ?? 0; ?></h4>
                        <small><?php echo number_format(abs($summary_data['damage']['total_quantity'] ?? 0), 2); ?> units</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body p-3">
                        <h6 class="text-white-50">Total</h6>
                        <h4 class="text-white mb-0"><?php echo $total_records; ?></h4>
                        <small>transactions</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="stock_history">
                    
                    <div class="col-md-3">
                        <label class="form-label">Item</label>
                        <select class="form-select" name="item_id">
                            <option value="">All Items</option>
                            <?php while ($item = $items_result->fetch_assoc()): ?>
                            <option value="<?php echo $item['id']; ?>" <?php echo $item_id == $item['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($item['item_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Transaction Type</label>
                        <select class="form-select" name="type">
                            <option value="">All Types</option>
                            <option value="purchase" <?php echo $type == 'purchase' ? 'selected' : ''; ?>>Purchase</option>
                            <option value="usage" <?php echo $type == 'usage' ? 'selected' : ''; ?>>Usage</option>
                            <option value="adjustment" <?php echo $type == 'adjustment' ? 'selected' : ''; ?>>Adjustment</option>
                            <option value="return" <?php echo $type == 'return' ? 'selected' : ''; ?>>Return</option>
                            <option value="damage" <?php echo $type == 'damage' ? 'selected' : ''; ?>>Damage</option>
                            <option value="transfer" <?php echo $type == 'transfer' ? 'selected' : ''; ?>>Transfer</option>
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
                        <a href="inventory.php?source=stock_history" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Stock Movements</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Item</th>
                                <th>Type</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Total Cost</th>
                                <th class="text-end">Previous</th>
                                <th class="text-end">New</th>
                                <th>Reference</th>
                                <th>Performed By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($trans = $result->fetch_assoc()): 
                                    $type_class = '';
                                    $quantity_prefix = '';
                                    switch ($trans['transaction_type']) {
                                        case 'purchase':
                                        case 'return':
                                            $type_class = 'text-success';
                                            $quantity_prefix = '+';
                                            break;
                                        case 'usage':
                                        case 'damage':
                                            $type_class = 'text-danger';
                                            $quantity_prefix = '-';
                                            break;
                                        default:
                                            $type_class = 'text-warning';
                                            $quantity_prefix = '±';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($trans['created_at'])); ?></td>
                                    <td>
                                        <a href="inventory.php?source=view_item&id=<?php echo $trans['inventory_item_id']; ?>">
                                            <?php echo htmlspecialchars($trans['item_name']); ?>
                                        </a>
                                    </td>
                                    <td class="<?php echo $type_class; ?> fw-bold">
                                        <?php echo ucfirst($trans['transaction_type']); ?>
                                    </td>
                                    <td class="text-end <?php echo $type_class; ?> fw-bold">
                                        <?php echo $quantity_prefix . number_format(abs($trans['quantity']), 2); ?>
                                    </td>
                                    <td class="text-end"><?php echo number_format($trans['unit_cost'], 2); ?> AED</td>
                                    <td class="text-end"><?php echo number_format($trans['total_cost'], 2); ?> AED</td>
                                    <td class="text-end"><?php echo number_format($trans['previous_quantity'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($trans['new_quantity'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($trans['reference_id'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($trans['performed_by_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($trans['notes'] ?? ''); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            <h5>No transactions found</h5>
                                            <p>Try adjusting your filters or add some stock movements.</p>
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
                            <a class="page-link" href="?source=stock_history&page=<?php echo $page-1; ?>&item_id=<?php echo $item_id; ?>&type=<?php echo $type; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                Previous
                            </a>
                        </li>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($page+2, $total_pages); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?source=stock_history&page=<?php echo $i; ?>&item_id=<?php echo $item_id; ?>&type=<?php echo $type; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?source=stock_history&page=<?php echo $page+1; ?>&item_id=<?php echo $item_id; ?>&type=<?php echo $type; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
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