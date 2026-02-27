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

// Generate expense number
$expense_prefix = 'EXP-' . date('Ymd');
$expense_query = "SELECT COUNT(*) as count FROM expenses WHERE expense_number LIKE '$expense_prefix%'";
$expense_result = $connection->query($expense_query);
$expense_count = $expense_result->fetch_assoc()['count'] + 1;
$expense_number = $expense_prefix . '-' . str_pad($expense_count, 4, '0', STR_PAD_LEFT);

// Fetch categories for dropdown
$categories = [];
$cat_query = "SELECT id, name FROM expense_categories WHERE is_active = 1 ORDER BY name";
$cat_result = $connection->query($cat_query);
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');
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
    
    // Validate payment_method against ENUM values
    $valid_payment_methods = ['cash', 'card', 'online'];
    if (!in_array($payment_method, $valid_payment_methods)) {
        $payment_method = 'cash'; // Default to cash if invalid
    }
    
    // Validate payment_status against possible values
    $valid_statuses = ['paid', 'pending', 'partial'];
    if (!in_array($payment_status, $valid_statuses)) {
        $payment_status = 'pending'; // Default to pending if invalid
    }
    
    // Validation
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    if ($amount <= 0) {
        $errors[] = "Amount must be greater than zero";
    }
    
    if (empty($errors)) {

        $stmt = $connection->prepare(
            "INSERT INTO expenses (
                expense_number,
                category_id,
                expense_date,
                description,
                amount,
                tax_amount,
                payment_method,
                payment_status,
                supplier_name,
                receipt_number,
                notes,
                is_recurring,
                recurring_frequency,
                recurring_end_date,
                created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            die("Prepare failed: " . $connection->error);
        }

        $user_id = (int) $_SESSION['user_id'];

        // Proper NULL handling
        $category_id = $category_id ?: null;
        $recurring_frequency = $is_recurring ? $recurring_frequency : null;
        $recurring_end_date = $is_recurring ? $recurring_end_date : null;

        $stmt->bind_param(
            "sissddsssssissi",
            $expense_number,
            $category_id,
            $expense_date,
            $description,
            $amount,
            $tax_amount,
            $payment_method,
            $payment_status,
            $supplier_name,
            $receipt_number,
            $notes,
            $is_recurring,
            $recurring_frequency,
            $recurring_end_date,
            $user_id
        );

        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        $new_expense_id = $stmt->insert_id;
        $stmt->close();

        if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] == 0) {
            handleReceiptUpload($connection, $new_expense_id, $_FILES['receipt_file'], $user_id);
        }

        $success_message = "Expense added successfully! Expense #: $expense_number";
        $_POST = [];
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
            <h1 class="page-title"><i class="bi bi-plus-circle me-2"></i>Add New Expense</h1>
            <a href="expenses.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Expenses
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Expense Details</h5>
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
                
                <form method="POST" action="" enctype="multipart/form-data" id="expenseForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Expense Number</label>
                                <input type="text" class="form-control" value="<?php echo $expense_number; ?>" readonly disabled>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="expense_date" class="form-label">Expense Date *</label>
                                <input type="date" class="form-control" id="expense_date" name="expense_date" 
                                       value="<?php echo $_POST['expense_date'] ?? date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="2" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (AED) *</label>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       value="<?php echo $_POST['amount'] ?? ''; ?>" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tax_amount" class="form-label">Tax Amount (AED)</label>
                                <input type="number" class="form-control" id="tax_amount" name="tax_amount" 
                                       value="<?php echo $_POST['tax_amount'] ?? '0'; ?>" step="0.01" min="0">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Total Amount</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="total_display" readonly value="0.00">
                                    <span class="input-group-text">AED</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="cash" <?php echo ($_POST['payment_method'] ?? '') == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                    <option value="card" <?php echo ($_POST['payment_method'] ?? '') == 'card' ? 'selected' : ''; ?>>Card</option>
                                    <option value="online" <?php echo ($_POST['payment_method'] ?? '') == 'online' ? 'selected' : ''; ?>>Online</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="payment_status" class="form-label">Payment Status</label>
                                <select class="form-select" id="payment_status" name="payment_status">
                                    <option value="paid" <?php echo ($_POST['payment_status'] ?? '') == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="pending" <?php echo ($_POST['payment_status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="partial" <?php echo ($_POST['payment_status'] ?? '') == 'partial' ? 'selected' : ''; ?>>Partial</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="supplier_name" class="form-label">Supplier/Vendor</label>
                                <input type="text" class="form-control" id="supplier_name" name="supplier_name" 
                                       value="<?php echo htmlspecialchars($_POST['supplier_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="receipt_number" class="form-label">Receipt/Invoice Number</label>
                                <input type="text" class="form-control" id="receipt_number" name="receipt_number" 
                                       value="<?php echo htmlspecialchars($_POST['receipt_number'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="receipt_file" class="form-label">Attach Receipt</label>
                                <input type="file" class="form-control" id="receipt_file" name="receipt_file" 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring" value="1"
                                           <?php echo isset($_POST['is_recurring']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_recurring">
                                        This is a recurring expense
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 recurring-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="recurring_frequency" class="form-label">Frequency</label>
                                <select class="form-select" id="recurring_frequency" name="recurring_frequency">
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4 recurring-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="recurring_end_date" class="form-label">End Date (Optional)</label>
                                <input type="date" class="form-control" id="recurring_end_date" name="recurring_end_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="expenses.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Expense
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
                <h4 class="my-3">Expense Added Successfully!</h4>
                <p><?php echo $success_message; ?></p>
            </div>
            <div class="modal-footer">
                <a href="expenses.php" class="btn btn-primary">View All Expenses</a>
                <a href="expenses.php?source=add_expense" class="btn btn-success">Add Another</a>
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
    calculateTotal();
    
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