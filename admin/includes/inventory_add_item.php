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

$errors = [];
$success_message = '';

// Fetch suppliers for dropdown
$suppliers = [];
$supplier_query = "SELECT id, supplier_name FROM suppliers WHERE is_active = 1 ORDER BY supplier_name";
$supplier_result = $connection->query($supplier_query);
if ($supplier_result) {
    while ($row = $supplier_result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate inputs
        $quantity_available = floatval($_POST['quantity_available'] ?? 0);
    $item_name = trim($_POST['item_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $unit_of_measure = trim($_POST['unit_of_measure'] ?? '');
    // Removed $quantity, use $quantity_available only
    $reorder_level = floatval($_POST['reorder_level'] ?? 0);
    $cost_per_unit = floatval($_POST['cost_per_unit'] ?? 0);
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $supplier_contact = trim($_POST['supplier_contact'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Use supplier_name if no supplier selected from dropdown
    $final_supplier = $supplier_name;
    if ($supplier_id) {
        // Get supplier name from database
        $supplier_stmt = $connection->prepare("SELECT supplier_name FROM suppliers WHERE id = ?");
        $supplier_stmt->bind_param("i", $supplier_id);
        $supplier_stmt->execute();
        $supplier_result = $supplier_stmt->get_result();
        if ($supplier_row = $supplier_result->fetch_assoc()) {
            $final_supplier = $supplier_row['supplier_name'];
        }
        $supplier_stmt->close();
    }
    
    // Validation
    if (empty($item_name)) {
        $errors[] = "Item name is required";
    }
    
    if (empty($unit_of_measure)) {
        $errors[] = "Unit of measure is required";
    }
    
    if (!empty($sku)) {
        // Check if SKU already exists
        $check_stmt = $connection->prepare("SELECT id FROM inventory_items WHERE sku = ?");
        $check_stmt->bind_param("s", $sku);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $errors[] = "SKU already exists. Please use a different SKU.";
        }
        $check_stmt->close();
    }
    
    if (empty($errors)) {
        // Insert into database
            $stmt = $connection->prepare(
                    "INSERT INTO inventory_items (
                    item_name, description, sku, category, unit_of_measure, 
                        quantity_available, reorder_level, cost_per_unit, supplier, 
                    location, is_active, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $user_id = $_SESSION['user_id'];
            $stmt->bind_param(
                    "sssssdddsisi",
                $item_name, $description, $sku, $category, $unit_of_measure,
                    $quantity_available, $reorder_level, $cost_per_unit, $final_supplier,
                $location, $is_active, $user_id
        );
        
        if ($stmt->execute()) {
            $new_item_id = $stmt->insert_id;
            
            // If initial quantity_available > 0, create a transaction record
            if ($quantity_available > 0) {
                $trans_stmt = $connection->prepare(
                    "INSERT INTO inventory_transactions (
                        inventory_item_id, transaction_type, quantity, unit_cost,
                        previous_quantity, new_quantity, notes, performed_by
                    ) VALUES (?, 'purchase', ?, ?, 0, ?, 'Initial stock', ?)"
                );
                $trans_stmt->bind_param("idddi", $new_item_id, $quantity_available, $cost_per_unit, $quantity_available, $user_id);
                $trans_stmt->execute();
                $trans_stmt->close();
            }
            
            $stmt->close();
            $success_message = "Inventory item added successfully!";
            
            // Clear form fields
            $_POST = [];
        } else {
            $errors[] = "Failed to add item: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-plus-circle me-2"></i>Add New Inventory Item</h1>
            <a href="inventory.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Item Information</h5>
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
                
                <form method="POST" action="" id="inventoryForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="item_name" class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="item_name" name="item_name" 
                                       value="<?php echo htmlspecialchars($_POST['item_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU (Stock Keeping Unit)</label>
                                <input type="text" class="form-control" id="sku" name="sku" 
                                       value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>">
                                <div class="form-text">Unique identifier for the item</div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" 
                                       value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>"
                                       placeholder="e.g., Meat, Produce, Dry Goods">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="unit_of_measure" class="form-label">Unit of Measure *</label>
                                <select class="form-select" id="unit_of_measure" name="unit_of_measure" required>
                                    <option value="">Select Unit</option>
                                    <option value="kg" <?php echo ($_POST['unit_of_measure'] ?? '') == 'kg' ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                    <option value="g" <?php echo ($_POST['unit_of_measure'] ?? '') == 'g' ? 'selected' : ''; ?>>Gram (g)</option>
                                    <option value="liter" <?php echo ($_POST['unit_of_measure'] ?? '') == 'liter' ? 'selected' : ''; ?>>Liter</option>
                                    <option value="ml" <?php echo ($_POST['unit_of_measure'] ?? '') == 'ml' ? 'selected' : ''; ?>>Milliliter (ml)</option>
                                    <option value="piece" <?php echo ($_POST['unit_of_measure'] ?? '') == 'piece' ? 'selected' : ''; ?>>Piece</option>
                                    <option value="box" <?php echo ($_POST['unit_of_measure'] ?? '') == 'box' ? 'selected' : ''; ?>>Box</option>
                                    <option value="case" <?php echo ($_POST['unit_of_measure'] ?? '') == 'case' ? 'selected' : ''; ?>>Case</option>
                                    <option value="dozen" <?php echo ($_POST['unit_of_measure'] ?? '') == 'dozen' ? 'selected' : ''; ?>>Dozen</option>
                                    <option value="other" <?php echo ($_POST['unit_of_measure'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="location" class="form-label">Storage Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                                       placeholder="e.g., Dry Store, Fridge 2, Freezer A">
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Stock & Pricing</h5>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Initial Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="<?php echo htmlspecialchars($_POST['quantity'] ?? 0); ?>" 
                                       step="0.01" min="0">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="reorder_level" class="form-label">Reorder Level</label>
                                <input type="number" class="form-control" id="reorder_level" name="reorder_level" 
                                       value="<?php echo htmlspecialchars($_POST['reorder_level'] ?? 0); ?>" 
                                       step="0.01" min="0">
                                <div class="form-text">Minimum quantity before reordering</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cost_per_unit" class="form-label">Cost per Unit (AED)</label>
                                <input type="number" class="form-control" id="cost_per_unit" name="cost_per_unit" 
                                       value="<?php echo htmlspecialchars($_POST['cost_per_unit'] ?? 0); ?>" 
                                       step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Supplier Information</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier_id" class="form-label">Select Existing Supplier</label>
                                <select class="form-select" id="supplier_id" name="supplier_id">
                                    <option value="">-- Select Supplier --</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['id']; ?>" <?php echo ($_POST['supplier_id'] ?? '') == $supplier['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <option value="new">+ Add New Supplier</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier_contact" class="form-label">Supplier Contact</label>
                                <input type="text" class="form-control" id="supplier_contact" name="supplier_contact" 
                                       value="<?php echo htmlspecialchars($_POST['supplier_contact'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-12" id="new_supplier_fields" style="display: none;">
                            <div class="mb-3">
                                <label for="supplier_name" class="form-label">New Supplier Name</label>
                                <input type="text" class="form-control" id="supplier_name" name="supplier_name" 
                                       value="<?php echo htmlspecialchars($_POST['supplier_name'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   <?php echo !isset($_POST['is_active']) || $_POST['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Active Item
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="inventory.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Add Item
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
                <h4 class="my-3">Item Added Successfully!</h4>
                <p><?php echo $success_message; ?></p>
            </div>
            <div class="modal-footer">
                <a href="inventory.php" class="btn btn-secondary">View Inventory</a>
                <a href="inventory.php?source=add_stock" class="btn btn-success">Add Another Item</a>
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
// Show/hide new supplier fields
$('#supplier_id').change(function() {
    if ($(this).val() === 'new') {
        $('#new_supplier_fields').slideDown();
    } else {
        $('#new_supplier_fields').slideUp();
    }
});

// Auto-generate SKU from item name
$('#item_name').on('blur', function() {
    if ($('#sku').val() === '') {
        let name = $(this).val();
        let sku = name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 8);
        let timestamp = Date.now().toString().slice(-4);
        $('#sku').val(sku + timestamp);
    }
});
</script>