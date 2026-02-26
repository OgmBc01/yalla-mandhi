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

// Get item ID if provided
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
$item = null;

if ($item_id > 0) {
    $stmt = $connection->prepare("SELECT * FROM inventory_items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = (int)$_POST['item_id'];
    $type = $_POST['type'] ?? '';
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unit_cost = floatval($_POST['unit_cost'] ?? 0);
    $reference = trim($_POST['reference'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validation
    if ($item_id <= 0) {
        $errors[] = "Invalid item";
    }
    
    if (empty($type)) {
        $errors[] = "Adjustment type is required";
    }
    
    if ($quantity <= 0) {
        $errors[] = "Quantity must be greater than zero";
    }
    
    if (empty($errors)) {
        $connection->begin_transaction();
        
        try {
            // Get current item quantity
            $stmt = $connection->prepare("SELECT quantity FROM inventory_items WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_item = $result->fetch_assoc();
            $current_qty = $current_item['quantity'];
            $stmt->close();
            
            // Calculate new quantity based on type
            $new_qty = $current_qty;
            $transaction_qty = $quantity;
            
            switch ($type) {
                case 'purchase':
                case 'return':
                    $new_qty = $current_qty + $quantity;
                    break;
                case 'usage':
                case 'damage':
                    $new_qty = $current_qty - $quantity;
                    $transaction_qty = -$quantity;
                    break;
                case 'adjustment':
                    // For manual adjustment, quantity is the new total
                    $new_qty = $quantity;
                    $transaction_qty = $new_qty - $current_qty;
                    break;
            }
            
            if ($new_qty < 0) {
                throw new Exception("Cannot reduce stock below zero");
            }
            
            // Insert transaction record
            $trans_stmt = $connection->prepare(
                "INSERT INTO inventory_transactions (
                    inventory_item_id, transaction_type, quantity, unit_cost,
                    previous_quantity, new_quantity, reference_id, notes, performed_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $user_id = $_SESSION['user_id'];
            $trans_stmt->bind_param(
                "isddddsi",
                $item_id, $type, $transaction_qty, $unit_cost,
                $current_qty, $new_qty, $reference, $notes, $user_id
            );
            $trans_stmt->execute();
            $trans_stmt->close();
            
            $connection->commit();
            $success_message = "Stock adjustment completed successfully!";
            
        } catch (Exception $e) {
            $connection->rollback();
            $errors[] = "Failed to adjust stock: " . $e->getMessage();
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-plus-slash-minus me-2"></i>Adjust Stock</h1>
            <a href="inventory.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Stock Adjustment Form</h5>
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
                        
                        <form method="POST" action="" id="adjustmentForm">
                            <div class="mb-3">
                                <label for="item_id" class="form-label">Select Item *</label>
                                <select class="form-select" id="item_id" name="item_id" required>
                                    <option value="">-- Select Item --</option>
                                    <?php
                                    $items_query = "SELECT id, item_name, sku, quantity, unit_of_measure FROM inventory_items WHERE is_active = 1 ORDER BY item_name";
                                    $items_result = $connection->query($items_query);
                                    while ($inv_item = $items_result->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $inv_item['id']; ?>" 
                                            data-qty="<?php echo $inv_item['quantity']; ?>"
                                            data-unit="<?php echo $inv_item['unit_of_measure']; ?>"
                                            <?php echo $item_id == $inv_item['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($inv_item['item_name']); ?> 
                                        (Current: <?php echo $inv_item['quantity']; ?> <?php echo $inv_item['unit_of_measure']; ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type" class="form-label">Adjustment Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="purchase">Purchase (Add Stock)</option>
                                    <option value="usage">Usage (Remove Stock)</option>
                                    <option value="adjustment">Manual Adjustment (Set Exact Quantity)</option>
                                    <option value="damage">Damage/Loss</option>
                                    <option value="return">Return to Supplier</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           step="0.01" min="0.01" required>
                                    <span class="input-group-text" id="unit_display">units</span>
                                </div>
                                <div class="form-text" id="quantity_help"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="unit_cost" class="form-label">Unit Cost (AED)</label>
                                <input type="number" class="form-control" id="unit_cost" name="unit_cost" 
                                       step="0.01" min="0">
                                <div class="form-text">Required for purchase transactions</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reference" class="form-label">Reference</label>
                                <input type="text" class="form-control" id="reference" name="reference" 
                                       placeholder="PO #, Invoice #, etc.">
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                            
                            <div class="alert alert-info" id="preview" style="display: none;">
                                <strong>Preview:</strong> 
                                <span id="preview_text"></span>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Apply Adjustment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Adjustment Guidelines</h5>
                    </div>
                    <div class="card-body">
                        <h6>Purchase</h6>
                        <p>Use when receiving new stock from supplier. Increases inventory.</p>
                        
                        <h6>Usage</h6>
                        <p>Use when items are consumed in operations. Decreases inventory.</p>
                        
                        <h6>Manual Adjustment</h6>
                        <p>Use to set exact quantity (for inventory counts). Enter the NEW total quantity.</p>
                        
                        <h6>Damage/Loss</h6>
                        <p>Use for spoiled, damaged, or lost items. Decreases inventory.</p>
                        
                        <h6>Return to Supplier</h6>
                        <p>Use when sending items back to supplier. Decreases inventory.</p>
                        
                        <hr>
                        
                        <p class="text-muted mb-0">
                            <i class="bi bi-shield-check"></i> All adjustments are logged and cannot be undone. 
                            Please verify quantities before submitting.
                        </p>
                    </div>
                </div>
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
                <h4 class="my-3">Adjustment Complete!</h4>
                <p><?php echo $success_message; ?></p>
            </div>
            <div class="modal-footer">
                <a href="inventory.php" class="btn btn-primary">View Inventory</a>
                <a href="inventory.php?source=adjust_stock" class="btn btn-success">Make Another Adjustment</a>
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
    // Update unit display when item changes
    $('#item_id').change(function() {
        let selected = $(this).find('option:selected');
        let unit = selected.data('unit') || 'units';
        let currentQty = selected.data('qty') || 0;
        
        $('#unit_display').text(unit);
        updatePreview();
    });
    
    // Update preview when type or quantity changes
    $('#type, #quantity').change(function() {
        updatePreview();
    });
    
    $('#type, #quantity').on('input', function() {
        updatePreview();
    });
    
    // Show/hide unit cost requirement based on type
    $('#type').change(function() {
        let type = $(this).val();
        if (type === 'purchase') {
            $('#unit_cost').prop('required', true);
            $('#unit_cost').closest('.mb-3').find('.form-text').text('Required for purchase');
        } else {
            $('#unit_cost').prop('required', false);
            $('#unit_cost').closest('.mb-3').find('.form-text').text('Optional for this transaction type');
        }
    });
});

function updatePreview() {
    let itemId = $('#item_id').val();
    if (!itemId) {
        $('#preview').hide();
        return;
    }
    
    let selected = $('#item_id').find('option:selected');
    let currentQty = selected.data('qty') || 0;
    let type = $('#type').val();
    let qty = parseFloat($('#quantity').val()) || 0;
    let unit = $('#unit_display').text();
    
    if (!type || qty <= 0) {
        $('#preview').hide();
        return;
    }
    
    let newQty = currentQty;
    let operation = '';
    
    switch (type) {
        case 'purchase':
            newQty = currentQty + qty;
            operation = '+';
            break;
        case 'usage':
        case 'damage':
        case 'return':
            newQty = currentQty - qty;
            operation = '-';
            break;
        case 'adjustment':
            newQty = qty;
            operation = '=';
            break;
    }
    
    let previewText = `Current: ${currentQty} ${unit} → `;
    if (operation === '+') previewText += `+${qty} `;
    else if (operation === '-') previewText += `-${qty} `;
    else previewText += `set to ${qty} `;
    
    previewText += `→ New: ${newQty.toFixed(2)} ${unit}`;
    
    $('#preview_text').text(previewText);
    $('#preview').show();
}
</script>