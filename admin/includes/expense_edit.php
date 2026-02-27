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

$errors = [];
$success_message = '';

// Get expense ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: expenses.php");
    exit();
}

$expense_id = (int)$_GET['id'];

// Fetch expense details
$query = "SELECT e.*, ec.name as category_name 
          FROM expenses e
          LEFT JOIN expense_categories ec ON e.category_id = ec.id
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

// Fetch categories for dropdown
$categories = [];
$cat_query = "SELECT id, name FROM expense_categories WHERE is_active = 1 ORDER BY name";
$cat_result = $connection->query($cat_query);
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $expense_date = $_POST['expense_date'] ?? $expense['expense_date'];
    $description = trim($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $tax_amount = floatval($_POST['tax_amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $payment_status = $_POST['payment_status'] ?? 'pending';
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $receipt_number = trim($_POST['receipt_number'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $recurring_frequency = $_POST['recurring_frequency'] ?? null;
    $recurring_end_date = !empty($_POST['recurring_end_date']) ? $_POST['recurring_end_date'] : null;
    
    // Validation
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    if ($amount <= 0) {
        $errors[] = "Amount must be greater than zero";
    }
    
    if (empty($errors)) {
        // Update expense
        $stmt = $connection->prepare(
            "UPDATE expenses SET 
                category_id = ?, expense_date = ?, description = ?, 
                amount = ?, tax_amount = ?, payment_method = ?, 
                payment_status = ?, supplier_name = ?, receipt_number = ?, 
                notes = ?, is_recurring = ?, recurring_frequency = ?, 
                recurring_end_date = ?
             WHERE id = ?"
        );
        
        $stmt->bind_param(
            "issddsdssssssi",
            $category_id, $expense_date, $description,
            $amount, $tax_amount, $payment_method, $payment_status,
            $supplier_name, $receipt_number, $notes, $is_recurring,
            $recurring_frequency, $recurring_end_date, $expense_id
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Handle file upload if receipt attached
            if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] == 0) {
                handleReceiptUpload($connection, $expense_id, $_FILES['receipt_file'], $_SESSION['user_id']);
            }
            
            $success_message = "Expense updated successfully!";
            
            // Refresh expense data
            $stmt = $connection->prepare($query);
            $stmt->bind_param("i", $expense_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $expense = $result->fetch_assoc();
            $stmt->close();
        } else {
            $errors[] = "Failed to update expense: " . $connection->error;
        }
    }
}

function handleReceiptUpload($connection, $expense_id, $file, $user_id) {
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    $upload_dir = __DIR__ . '/../../uploads/expenses/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_name = time() . '_' . uniqid() . '.' . $file_extension;
    $file_path = 'uploads/expenses/' . $file_name;
    $target_file = $upload_dir . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $stmt = $connection->prepare(
            "INSERT INTO expense_attachments (expense_id, file_name, file_path, file_size, uploaded_by) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("issii", $expense_id, $file['name'], $file_path, $file['size'], $user_id);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    
    return false;
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-pencil-square me-2"></i>Edit Expense</h1>
            <div>
                <a href="expenses.php?source=view_expense&id=<?php echo $expense_id; ?>" class="btn btn-info me-2">
                    <i class="bi bi-eye"></i> View
                </a>
                <a href="expenses.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Edit: <?php echo htmlspecialchars($expense['expense_number']); ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Expense Number</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($expense['expense_number']); ?>" readonly disabled>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="expense_date" class="form-label">Expense Date *</label>
                                <input type="date" class="form-control" id="expense_date" name="expense_date" 
                                       value="<?php echo $expense['expense_date']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $expense['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="2" required><?php echo htmlspecialchars($expense['description']); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (AED) *</label>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       value="<?php echo $expense['amount']; ?>" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tax_amount" class="form-label">Tax Amount (AED)</label>
                                <input type="number" class="form-control" id="tax_amount" name="tax_amount" 
                                       value="<?php echo $expense['tax_amount']; ?>" step="0.01" min="0">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="total_display" readonly value="<?php echo $expense['total_amount']; ?>">
                                    <span class="input-group-text">AED</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="cash" <?php echo $expense['payment_method'] == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                    <option value="card" <?php echo $expense['payment_method'] == 'card' ? 'selected' : ''; ?>>Card</option>
                                    <option value="bank_transfer" <?php echo $expense['payment_method'] == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                    <option value="cheque" <?php echo $expense['payment_method'] == 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="payment_status" class="form-label">Payment Status</label>
                                <select class="form-select" id="payment_status" name="payment_status">
                                    <option value="paid" <?php echo $expense['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="pending" <?php echo $expense['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="partial" <?php echo $expense['payment_status'] == 'partial' ? 'selected' : ''; ?>>Partial</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="supplier_name" class="form-label">Supplier/Vendor</label>
                                <input type="text" class="form-control" id="supplier_name" name="supplier_name" 
                                       value="<?php echo htmlspecialchars($expense['supplier_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="receipt_number" class="form-label">Receipt/Invoice Number</label>
                                <input type="text" class="form-control" id="receipt_number" name="receipt_number" 
                                       value="<?php echo htmlspecialchars($expense['receipt_number'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="receipt_file" class="form-label">Attach Additional Receipt</label>
                                <input type="file" class="form-control" id="receipt_file" name="receipt_file" 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($expense['notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring" value="1"
                                           <?php echo $expense['is_recurring'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_recurring">
                                        This is a recurring expense
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 recurring-fields" style="<?php echo $expense['is_recurring'] ? '' : 'display: none;'; ?>">
                            <div class="mb-3">
                                <label for="recurring_frequency" class="form-label">Frequency</label>
                                <select class="form-select" id="recurring_frequency" name="recurring_frequency">
                                    <option value="monthly" <?php echo $expense['recurring_frequency'] == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="quarterly" <?php echo $expense['recurring_frequency'] == 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                                    <option value="yearly" <?php echo $expense['recurring_frequency'] == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4 recurring-fields" style="<?php echo $expense['is_recurring'] ? '' : 'display: none;'; ?>">
                            <div class="mb-3">
                                <label for="recurring_end_date" class="form-label">End Date (Optional)</label>
                                <input type="date" class="form-control" id="recurring_end_date" name="recurring_end_date" 
                                       value="<?php echo $expense['recurring_end_date'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($attachments)): ?>
                    <div class="mt-4">
                        <h6>Current Attachments</h6>
                        <div class="list-group">
                            <?php foreach ($attachments as $att): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark"></i>
                                    <a href="../../<?php echo $att['file_path']; ?>" target="_blank">
                                        <?php echo htmlspecialchars($att['file_name']); ?>
                                    </a>
                                    <small class="text-muted ms-2">(<?php echo round($att['file_size'] / 1024, 1); ?> KB)</small>
                                </div>
                                <small><?php echo date('d/m/Y', strtotime($att['uploaded_at'])); ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="expenses.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Update Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h4 class="my-3">Expense Updated Successfully!</h4>
                <p><?php echo $success_message; ?></p>
            </div>
            <div class="modal-footer">
                <a href="expenses.php?source=view_expense&id=<?php echo $expense_id; ?>" class="btn btn-primary">View Expense</a>
                <a href="expenses.php" class="btn btn-success">Back to List</a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($success_message)): ?>
<script>
    window.addEventListener('load', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Calculate total
    function calculateTotal() {
        let amount = parseFloat($('#amount').val()) || 0;
        let tax = parseFloat($('#tax_amount').val()) || 0;
        let total = amount + tax;
        $('#total_display').val(total.toFixed(2));
    }
    
    $('#amount, #tax_amount').on('input', calculateTotal);
    
    // Show/hide recurring fields
    $('#is_recurring').change(function() {
        if ($(this).is(':checked')) {
            $('.recurring-fields').slideDown();
        } else {
            $('.recurring-fields').slideUp();
        }
    });
});
</script>