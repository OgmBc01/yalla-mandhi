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

$errors = [];
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $tax_number = trim($_POST['tax_number'] ?? '');
    $payment_terms = trim($_POST['payment_terms'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($supplier_name)) {
        $errors[] = "Supplier name is required";
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($errors)) {
        $stmt = $connection->prepare(
            "INSERT INTO suppliers (supplier_name, contact_person, email, phone, address, tax_number, payment_terms, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param(
            "sssssssi",
            $supplier_name, $contact_person, $email, $phone, $address,
            $tax_number, $payment_terms, $is_active
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            $success_message = "Supplier added successfully!";
            $_POST = []; // Clear form
        } else {
            $errors[] = "Failed to add supplier: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-plus-circle me-2"></i>Add New Supplier</h1>
            <a href="inventory.php?source=suppliers" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Suppliers
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Supplier Information</h5>
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
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier_name" class="form-label">Supplier Name *</label>
                                <input type="text" class="form-control" id="supplier_name" name="supplier_name" 
                                       value="<?php echo htmlspecialchars($_POST['supplier_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_person" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                       value="<?php echo htmlspecialchars($_POST['contact_person'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tax_number" class="form-label">Tax Number / VAT ID</label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number" 
                                       value="<?php echo htmlspecialchars($_POST['tax_number'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_terms" class="form-label">Payment Terms</label>
                                <select class="form-select" id="payment_terms" name="payment_terms">
                                    <option value="">Select Terms</option>
                                    <option value="Due on receipt" <?php echo ($_POST['payment_terms'] ?? '') == 'Due on receipt' ? 'selected' : ''; ?>>Due on receipt</option>
                                    <option value="Net 7" <?php echo ($_POST['payment_terms'] ?? '') == 'Net 7' ? 'selected' : ''; ?>>Net 7</option>
                                    <option value="Net 15" <?php echo ($_POST['payment_terms'] ?? '') == 'Net 15' ? 'selected' : ''; ?>>Net 15</option>
                                    <option value="Net 30" <?php echo ($_POST['payment_terms'] ?? '') == 'Net 30' ? 'selected' : ''; ?>>Net 30</option>
                                    <option value="Net 60" <?php echo ($_POST['payment_terms'] ?? '') == 'Net 60' ? 'selected' : ''; ?>>Net 60</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           <?php echo !isset($_POST['is_active']) || $_POST['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Supplier
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="inventory.php?source=suppliers" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Add Supplier
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
                <h4 class="my-3">Supplier Added Successfully!</h4>
                <p><?php echo $success_message; ?></p>
            </div>
            <div class="modal-footer">
                <a href="inventory.php?source=suppliers" class="btn btn-primary">View All Suppliers</a>
                <a href="inventory.php?source=add_supplier" class="btn btn-success">Add Another Supplier</a>
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