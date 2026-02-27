<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager', 'accountant'])) {
    header("Location: login.php");
    exit();
}

// Process recurring expenses generation
if (isset($_POST['generate_recurring'])) {
    generateRecurringExpenses($connection);
}

// Fetch all recurring expenses
$query = "SELECT e.*, ec.name as category_name,
                 DATEDIFF(e.recurring_end_date, CURDATE()) as days_remaining,
                 CASE 
                     WHEN e.recurring_frequency = 'monthly' THEN DATE_ADD(e.expense_date, INTERVAL 1 MONTH)
                     WHEN e.recurring_frequency = 'quarterly' THEN DATE_ADD(e.expense_date, INTERVAL 3 MONTH)
                     WHEN e.recurring_frequency = 'yearly' THEN DATE_ADD(e.expense_date, INTERVAL 1 YEAR)
                 END as next_due_date
          FROM expenses e
          LEFT JOIN expense_categories ec ON e.category_id = ec.id
          WHERE e.is_recurring = 1 
          AND (e.recurring_end_date IS NULL OR e.recurring_end_date >= CURDATE())
          ORDER BY next_due_date ASC";

$result = $connection->query($query);

function generateRecurringExpenses($connection) {
    $today = date('Y-m-d');
    $generated = 0;
    
    // Find recurring expenses that are due
    $query = "SELECT e.* FROM expenses e
              WHERE e.is_recurring = 1 
              AND (e.recurring_end_date IS NULL OR e.recurring_end_date >= ?)
              AND NOT EXISTS (
                  SELECT 1 FROM expenses e2 
                  WHERE e2.expense_number LIKE CONCAT(LEFT(e.expense_number, 15), '%')
                  AND DATE(e2.expense_date) = ?
              )";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss", $today, $today);
    $stmt->execute();
    $recurring = $stmt->get_result();
    
    while ($expense = $recurring->fetch_assoc()) {
        // Calculate next due date based on frequency
        $next_date = null;
        switch ($expense['recurring_frequency']) {
            case 'monthly':
                $next_date = date('Y-m-d', strtotime($expense['expense_date'] . ' +1 month'));
                break;
            case 'quarterly':
                $next_date = date('Y-m-d', strtotime($expense['expense_date'] . ' +3 months'));
                break;
            case 'yearly':
                $next_date = date('Y-m-d', strtotime($expense['expense_date'] . ' +1 year'));
                break;
        }
        
        if ($next_date && $next_date <= $today) {
            // Create new expense
            $expense_number = $expense['expense_number'] . '-R' . date('Ymd');
            
            $insert = $connection->prepare(
                "INSERT INTO expenses (
                    expense_number, category_id, expense_date, description,
                    amount, tax_amount, payment_method, payment_status,
                    supplier_name, notes, is_recurring, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)"
            );
            
            $insert->bind_param(
                "sisddsdsssi",
                $expense_number, $expense['category_id'], $today, $expense['description'],
                $expense['amount'], $expense['tax_amount'], $expense['payment_method'], 'pending',
                $expense['supplier_name'], $expense['notes'], $expense['created_by']
            );
            
            if ($insert->execute()) {
                $generated++;
            }
            $insert->close();
        }
    }
    
    if ($generated > 0) {
        echo "<script>alert('$generated recurring expenses generated for today!');</script>";
    } else {
        echo "<script>alert('No recurring expenses due today.');</script>";
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-arrow-repeat me-2"></i>Recurring Expenses</h1>
            <div>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="generate_recurring" class="btn btn-warning me-2">
                        <i class="bi bi-lightning"></i> Generate Due Expenses
                    </button>
                </form>
                <a href="expenses.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Expenses
                </a>
            </div>
        </div>

        <!-- Info Card -->
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            Recurring expenses are automatically generated based on their frequency. 
            Click "Generate Due Expenses" to create expenses that are due today.
        </div>

        <!-- Recurring Expenses Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Active Recurring Expenses</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Expense #</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Frequency</th>
                                <th class="text-end">Amount</th>
                                <th>Start Date</th>
                                <th>Next Due</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($expense = $result->fetch_assoc()): 
                                    $today = strtotime(date('Y-m-d'));
                                    $next_due = strtotime($expense['next_due_date']);
                                    $days_diff = round(($next_due - $today) / 86400);
                                    
                                    $due_class = '';
                                    if ($days_diff <= 0) {
                                        $due_class = 'table-danger';
                                    } elseif ($days_diff <= 7) {
                                        $due_class = 'table-warning';
                                    }
                                ?>
                                <tr class="<?php echo $due_class; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($expense['expense_number']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['category_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst($expense['recurring_frequency']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold"><?php echo number_format($expense['total_amount'], 2); ?> AED</td>
                                    <td><?php echo date('d/m/Y', strtotime($expense['expense_date'])); ?></td>
                                    <td>
                                        <strong><?php echo date('d/m/Y', strtotime($expense['next_due_date'])); ?></strong>
                                        <?php if ($days_diff <= 0): ?>
                                            <span class="badge bg-danger ms-1">Due Today</span>
                                        <?php elseif ($days_diff <= 7): ?>
                                            <span class="badge bg-warning ms-1">In <?php echo $days_diff; ?> days</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($expense['recurring_end_date']): ?>
                                            <?php echo date('d/m/Y', strtotime($expense['recurring_end_date'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No end date</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $days_remaining = $expense['days_remaining'];
                                        if ($days_remaining === null) {
                                            echo '<span class="badge bg-success">Ongoing</span>';
                                        } elseif ($days_remaining > 0) {
                                            echo '<span class="badge bg-info">' . $days_remaining . ' days left</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Expired</span>';
                                        }
                                        ?>
                                    </td>
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
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="generateNow(<?php echo $expense['id']; ?>)"
                                                    title="Generate Now">
                                                <i class="bi bi-lightning"></i>
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
                                            <h5>No recurring expenses found</h5>
                                            <p>Create an expense and mark it as recurring to see it here.</p>
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
function generateNow(expenseId) {
    if (confirm('Generate this recurring expense now?')) {
        $.ajax({
            url: 'includes/ajax/generate_recurring_expense.php',
            method: 'POST',
            data: { expense_id: expenseId },
            success: function(response) {
                if (response.success) {
                    alert('Expense generated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>