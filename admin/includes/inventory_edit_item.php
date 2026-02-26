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

// Get item ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: inventory.php");
    exit();
}

$item_id = (int)$_GET['id'];

// Fetch item details
$stmt = $connection->prepare("SELECT * FROM inventory_items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: inventory.php");
    exit();
}

$item = $result->fetch_assoc();
$stmt->close();

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
    $item_name = trim($_POST['item_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $unit_of_measure = trim($_POST['unit_of_measure'] ?? '');
    $reorder_level = floatval($_POST['reorder_level'] ?? 0);
    $cost_per_unit = floatval($_POST['cost_per_unit'] ?? 0);
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    $supplier_name = trim($_POST['supplier_name'] ?? '');
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
    
    if (!empty($sku) && $sku != $item['sku']) {
        // Check if SKU already exists (excluding current item)
        $check_stmt = $connection->prepare("SELECT id FROM inventory_items WHERE sku = ? AND id != ?");
        $check_stmt->bind_param("si", $sku, $item_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $errors[] = "SKU already exists. Please use a different SKU.";
        }
        $check_stmt->close();
    }
    
    if (empty($errors)) {
        // Update database
        $stmt = $connection->prepare(
            "UPDATE inventory_items SET 
                item_name = ?, description = ?, sku = ?, category = ?, 
                unit_of_measure = ?, reorder_level = ?, cost_per_unit = ?,
                supplier = ?, location = ?, is_active = ?
            WHERE id = ?"
        );
        
        $stmt->bind_param(
            "sssssddssii",
            $item_name, $description, $sku, $category, $unit_of_measure,
            $reorder_level, $cost_per_unit, $final_supplier,
            $location, $is_active, $item_id
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            $success_message = "Item updated successfully!";
            
            // Refresh item data
            $stmt = $connection->prepare("SELECT * FROM inventory_items WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();
            $stmt->close();
        } else {
            $errors[] = "Failed to update item: " . $connection->error;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-pencil-square me-2"></i>Edit Inventory Item</h1>
            <div>
                <a href="inventory.php?source=view_item&id=<?php echo $item_id; ?>" class="btn btn-info me-2">
                    <i class="bi bi-eye"></i> View Details
                </a>
                <a href="inventory.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Edit Item: <?php echo htmlspecialchars($item['item_name']); ?></h5>
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
                                       value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU (Stock Keeping Unit)</label>
                                <input type="text" class="form-control" id="sku" name="sku" 
                                       value="<?php echo htmlspecialchars($item['sku'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" 
                                       value="<?php echo htmlspecialchars($item['category'] ?? ''); ?>"
                                       placeholder="e.g., Meat, Produce, Dry Goods">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="unit_of_measure" class="form-label">Unit of Measure *</label>
                                <select class="form-select" id="unit_of_measure" name="unit_of_measure" required>
                                    <option value="">Select Unit</option>
                                    <option value="kg" <?php echo $item['unit_of_measure'] == 'kg' ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                    <option value="g" <?php echo $item['unit_of_measure'] == 'g' ? 'selected' : ''; ?>>Gram (g)</option>
                                    <option value="liter" <?php echo $item['unit_of_measure'] == 'liter' ? 'selected' : ''; ?>>Liter</option>
                                    <option value="ml" <?php echo $item['unit_of_measure'] == 'ml' ? 'selected' : ''; ?>>Milliliter (ml)</option>
                                    <option value="piece" <?php echo $item['unit_of_measure'] == 'piece' ? 'selected' : ''; ?>>Piece</option>
                                    <option value="box" <?php echo $item['unit_of_measure'] == 'box' ? 'selected' : ''; ?>>Box</option>
                                    <option value="case" <?php echo $item['unit_of_measure'] == 'case' ? 'selected' : ''; ?>>Case</option>
                                    <option value="dozen" <?php echo $item['unit_of_measure'] == 'dozen' ? 'selected' : ''; ?>>Dozen</option>
                                    <option value="other" <?php echo $item['unit_of_measure'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="location" class="form-label">Storage Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo htmlspecialchars($item['location'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Stock & Pricing</h5>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Current Quantity</label>
                                <p class="form-control-plaintext fw-bold">
                                    <?php echo number_format($item['quantity_available'], 2); ?> <?php echo $item['unit_of_measure']; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="reorder_level" class="form-label">Reorder Level</label>
                                <input type="number" class="form-control" id="reorder_level" name="reorder_level" 
                                       value="<?php echo $item['reorder_level']; ?>" step="0.01" min="0">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cost_per_unit" class="form-label">Cost per Unit (AED)</label>
                                <input type="number" class="form-control" id="cost_per_unit" name="cost_per_unit" 
                                       value="<?php echo $item['cost_per_unit']; ?>" step="0.01" min="0">
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
                                    <option value="<?php echo $supplier['id']; ?>" <?php echo $item['supplier'] == $supplier['supplier_name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <option value="new">+ Add New Supplier</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
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
                                   <?php echo $item['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Active Item
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="inventory.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Update Item
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
                <h4 class="my-3">Item Updated Successfully!</h4>
                <p><?php echo $success_message; ?></p>
            </div>
            <div class="modal-footer">
                <a href="inventory.php" class="btn btn-secondary">Back to Inventory</a>
                <a href="inventory.php?source=view_item&id=<?php echo $item_id; ?>" class="btn btn-success">View Details</a>
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
</script>