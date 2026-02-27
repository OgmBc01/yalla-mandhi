<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
    header("Location: login.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // Check if category is in use
    $check_stmt = $connection->prepare("SELECT COUNT(*) as count FROM expenses WHERE category_id = ?");
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $in_use = $check_result->fetch_assoc()['count'] > 0;
    $check_stmt->close();
    
    if ($in_use) {
        // Soft delete - just deactivate
        $stmt = $connection->prepare("UPDATE expense_categories SET is_active = 0 WHERE id = ?");
        $message = "Category deactivated (it is in use by expenses)";
    } else {
        // Hard delete
        $stmt = $connection->prepare("DELETE FROM expense_categories WHERE id = ?");
        $message = "Category deleted successfully";
    }
    
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<script>alert('$message'); window.location.href='expenses.php?source=expense_categories';</script>";
    }
    $stmt->close();
}

// Fetch all categories
$query = "SELECT c.*, 
                 (SELECT COUNT(*) FROM expenses WHERE category_id = c.id) as expense_count,
                 (SELECT SUM(total_amount) FROM expenses WHERE category_id = c.id) as total_spent
          FROM expense_categories c
          ORDER BY c.is_active DESC, c.name ASC";
$result = $connection->query($query);
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-tags me-2"></i>Expense Categories</h1>
            <div>
                <a href="expenses.php?source=add_category" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Category
                </a>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Manage Categories</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th class="text-center">Expenses</th>
                                <th class="text-end">Total Spent</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($cat = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($cat['description'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo $cat['expense_count']; ?></td>
                                    <td class="text-end"><?php echo number_format($cat['total_spent'] ?? 0, 2); ?> AED</td>
                                    <td>
                                        <?php if ($cat['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="expenses.php?source=edit_category&id=<?php echo $cat['id']; ?>" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($cat['expense_count'] == 0 || !$cat['is_active']): ?>
                                            <a href="?source=expense_categories&delete=<?php echo $cat['id']; ?>" 
                                               class="btn btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this category?')"
                                               title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            <h5>No categories found</h5>
                                            <p>Create your first expense category to organize expenses.</p>
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