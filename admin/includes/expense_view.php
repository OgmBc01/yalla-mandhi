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

// Get expense ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: expenses.php");
    exit();
}

$expense_id = (int)$_GET['id'];

// Fetch expense details with related info
$query = "SELECT e.*, 
                 ec.name as category_name,
                 u1.full_name as created_by_name,
                 u2.full_name as approved_by_name
          FROM expenses e
          LEFT JOIN expense_categories ec ON e.category_id = ec.id
          LEFT JOIN users u1 ON e.created_by = u1.id
          LEFT JOIN users u2 ON e.approved_by = u2.id
          WHERE e.id = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $expense_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: expenses.php");
    exit();
}

$expense = $result->fetch_assoc();
$stmt->close();

// Fetch attachments
$attachments = [];
$att_query = "SELECT * FROM expense_attachments WHERE expense_id = ? ORDER BY uploaded_at DESC";
$att_stmt = $connection->prepare($att_query);
$att_stmt->bind_param("i", $expense_id);
$att_stmt->execute();
$att_result = $att_stmt->get_result();
while ($row = $att_result->fetch_assoc()) {
    $attachments[] = $row;
}
$att_stmt->close();

// Status colors
$status_colors = [
    'paid' => 'success',
    'pending' => 'warning',
    'partial' => 'info'
];
$status_color = $status_colors[$expense['payment_status']] ?? 'secondary';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-receipt me-2"></i>Expense Details</h1>
            <div>
                <a href="expenses.php?source=edit_expense&id=<?php echo $expense_id; ?>" class="btn btn-warning me-2">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="expenses.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- Status Banner -->
        <div class="alert alert-<?php echo $status_color; ?> mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>Status:</strong> 
                    <span class="badge bg-<?php echo $status_color; ?> fs-6">
                        <?php echo strtoupper($expense['payment_status']); ?>
                    </span>
                </div>
                <div>
                    <strong>Total Amount:</strong> 
                    <span class="fs-4 fw-bold"><?php echo number_format($expense['total_amount'], 2); ?> AED</span>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Details -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Expense Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Expense Number</th>
                                <td><strong><?php echo htmlspecialchars($expense['expense_number']); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td><?php echo date('d F Y', strtotime($expense['expense_date'])); ?></td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td>
                                    <?php if ($expense['category_name']): ?>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($expense['category_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Uncategorized</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td><?php echo nl2br(htmlspecialchars($expense['description'])); ?></td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td><?php echo number_format($expense['amount'], 2); ?> AED</td>
                            </tr>
                            <?php if ($expense['tax_amount'] > 0): ?>
                            <tr>
                                <th>Tax Amount</th>
                                <td><?php echo number_format($expense['tax_amount'], 2); ?> AED</td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-active">
                                <th>Total Amount</th>
                                <td><strong class="text-success fs-5"><?php echo number_format($expense['total_amount'], 2); ?> AED</strong></td>
                            </tr>
                            <tr>
                                <th>Payment Method</th>
                                <td>
                                    <?php 
                                    $method_icons = [
                                        'cash' => 'cash-coin',
                                        'card' => 'credit-card',
                                        'bank_transfer' => 'bank',
                                        'cheque' => 'file-text'
                                    ];
                                    $icon = $method_icons[$expense['payment_method']] ?? 'cash-coin';
                                    ?>
                                    <i class="bi bi-<?php echo $icon; ?> me-2"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $expense['payment_method'])); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Status</th>
                                <td>
                                    <span class="badge bg-<?php echo $status_color; ?>">
                                        <?php echo ucfirst($expense['payment_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php if ($expense['supplier_name']): ?>
                            <tr>
                                <th>Supplier/Vendor</th>
                                <td><?php echo htmlspecialchars($expense['supplier_name']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($expense['receipt_number']): ?>
                            <tr>
                                <th>Receipt/Invoice #</th>
                                <td><?php echo htmlspecialchars($expense['receipt_number']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($expense['notes']): ?>
                            <tr>
                                <th>Notes</th>
                                <td><?php echo nl2br(htmlspecialchars($expense['notes'])); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($expense['is_recurring']): ?>
                            <tr>
                                <th>Recurring</th>
                                <td>
                                    <span class="badge bg-info">Yes</span>
                                    <?php if ($expense['recurring_frequency']): ?>
                                        - <?php echo ucfirst($expense['recurring_frequency']); ?>
                                    <?php endif; ?>
                                    <?php if ($expense['recurring_end_date']): ?>
                                        <br><small>Ends: <?php echo date('d/m/Y', strtotime($expense['recurring_end_date'])); ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Attachments -->
                <?php if (!empty($attachments)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-paperclip me-2"></i>Attachments</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($attachments as $att): ?>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center p-2 border rounded">
                                    <i class="bi bi-file-earmark-text fs-2 me-3 text-primary"></i>
                                    <div class="flex-grow-1">
                                        <a href="../../<?php echo $att['file_path']; ?>" target="_blank" class="text-decoration-none">
                                            <?php echo htmlspecialchars($att['file_name']); ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo round($att['file_size'] / 1024, 1); ?> KB • 
                                            <?php echo date('d/m/Y', strtotime($att['uploaded_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-4">
                <!-- Audit Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Audit Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Created By:</th>
                                <td><?php echo htmlspecialchars($expense['created_by_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($expense['created_at'])); ?></td>
                            </tr>
                            <?php if ($expense['approved_by_name']): ?>
                            <tr>
                                <th>Approved By:</th>
                                <td><?php echo htmlspecialchars($expense['approved_by_name']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Last Updated:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($expense['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($expense['payment_status'] != 'paid'): ?>
                            <button class="btn btn-success" onclick="markAsPaid(<?php echo $expense_id; ?>)">
                                <i class="bi bi-check-circle"></i> Mark as Paid
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Print Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsPaid(expenseId) {
    if (confirm('Mark this expense as paid?')) {
        $.ajax({
            url: 'includes/ajax/update_expense_status.php',
            method: 'POST',
            data: { 
                expense_id: expenseId,
                status: 'paid'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>