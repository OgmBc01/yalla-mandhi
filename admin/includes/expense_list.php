<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filters
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT e.*, ec.name as category_name, u.full_name as created_by_name
          FROM expenses e
          LEFT JOIN expense_categories ec ON e.category_id = ec.id
          LEFT JOIN users u ON e.created_by = u.id
          WHERE DATE(e.expense_date) BETWEEN ? AND ?";
$params = [$date_from, $date_to];
$types = "ss";

if ($category_id > 0) {
    $query .= " AND e.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if (!empty($status)) {
    $query .= " AND e.payment_status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND (e.description LIKE ? OR e.supplier_name LIKE ? OR e.receipt_number LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

// Get total count for pagination
$count_query = str_replace("e.*, ec.name as category_name, u.full_name as created_by_name", "COUNT(*) as total", $query);
$count_stmt = $connection->prepare($count_query);
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
$count_stmt->close();

// Add pagination
$query .= " ORDER BY e.expense_date DESC, e.id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $connection->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter
$categories_query = "SELECT id, name FROM expense_categories WHERE is_active = 1 ORDER BY name";
$categories_result = $connection->query($categories_query);

// Get summary statistics
$stats_query = "SELECT 
    COUNT(*) as total_expenses,
    SUM(total_amount) as total_amount,
    SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_amount,
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_amount
    FROM expenses
    WHERE DATE(expense_date) BETWEEN ? AND ?";
$stats_stmt = $connection->prepare($stats_query);
$stats_stmt->bind_param("ss", $date_from, $date_to);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-receipt me-2"></i>Expenses Management</h1>
            <div>
                <a href="expenses.php?source=add_expense" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Add Expense
                </a>
                <a href="expenses.php?source=expense_categories" class="btn btn-info">
                    <i class="bi bi-tags"></i> Categories
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Expenses</h6>
                        <h3 class="text-white mb-0"><?php echo $stats['total_expenses'] ?? 0; ?></h3>
                        <small>Transactions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Amount</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($stats['total_amount'] ?? 0, 2); ?> AED</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Pending</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($stats['pending_amount'] ?? 0, 2); ?> AED</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Paid</h6>
                        <h3 class="text-white mb-0"><?php echo number_format($stats['paid_amount'] ?? 0, 2); ?> AED</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="list_expenses">
                    
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id">
                            <option value="0">All Categories</option>
                            <?php while ($cat = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="paid" <?php echo $status == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="partial" <?php echo $status == 'partial' ? 'selected' : ''; ?>>Partial</option>
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
                    
                    <div class="col-md-2">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" placeholder="Description, supplier..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-filter"></i> Apply
                        </button>
                        <a href="expenses.php?source=list_expenses" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Expenses List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Expense #</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($expense = $result->fetch_assoc()): 
                                    $status_colors = [
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'partial' => 'info'
                                    ];
                                    $status_color = $status_colors[$expense['payment_status']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($expense['expense_date'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($expense['expense_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($expense['description']); ?>
                                        <?php if ($expense['is_recurring']): ?>
                                        <span class="badge bg-info ms-1">Recurring</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($expense['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td><?php echo htmlspecialchars($expense['supplier_name'] ?? 'N/A'); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($expense['total_amount'], 2); ?> AED</td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_color; ?>">
                                            <?php echo ucfirst($expense['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($expense['created_by_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="expenses.php?source=view_expense&id=<?php echo $expense['id']; ?>" 
                                               class="btn btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="expenses.php?source=edit_expense&id=<?php echo $expense['id']; ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            <h5>No expenses found</h5>
                                            <p>Click "Add Expense" to record your first expense.</p>
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
                            <a class="page-link" href="?source=list_expenses&page=<?php echo $page-1; ?>&category_id=<?php echo $category_id; ?>&status=<?php echo $status; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&search=<?php echo urlencode($search); ?>">
                                Previous
                            </a>
                        </li>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($page+2, $total_pages); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?source=list_expenses&page=<?php echo $i; ?>&category_id=<?php echo $category_id; ?>&status=<?php echo $status; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?source=list_expenses&page=<?php echo $page+1; ?>&category_id=<?php echo $category_id; ?>&status=<?php echo $status; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&search=<?php echo urlencode($search); ?>">
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