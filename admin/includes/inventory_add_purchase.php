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

// Fetch suppliers for dropdown
$suppliers = [];
$supplier_query = "SELECT id, supplier_name FROM suppliers WHERE is_active = 1 ORDER BY supplier_name";
$supplier_result = $connection->query($supplier_query);
if ($supplier_result) {
    while ($row = $supplier_result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

// Fetch inventory items for dropdown and store in array for JavaScript
$items_list = [];
$items_query = "SELECT id, item_name, sku, unit_of_measure, cost_per_unit FROM inventory_items WHERE is_active = 1 ORDER BY item_name";
$items_result = $connection->query($items_query);
if ($items_result) {
    while ($row = $items_result->fetch_assoc()) {
        $items_list[] = $row;
    }
}

// Generate PO number
$po_prefix = 'PO-' . date('Ymd');
$po_query = "SELECT COUNT(*) as count FROM purchase_orders WHERE po_number LIKE '$po_prefix%'";
$po_result = $connection->query($po_query);
$po_count = $po_result->fetch_assoc()['count'] + 1;
$po_number = $po_prefix . '-' . str_pad($po_count, 4, '0', STR_PAD_LEFT);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $order_date = $_POST['order_date'] ?? date('Y-m-d');
    $expected_delivery = $_POST['expected_delivery'] ?? null;
    $notes = trim($_POST['notes'] ?? '');
    $item_ids = $_POST['item_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $unit_costs = $_POST['unit_cost'] ?? [];
    
    // Validation
    $valid_items = [];
    foreach ($item_ids as $index => $item_id) {
        if (!empty($item_id) && isset($quantities[$index]) && !empty($quantities[$index]) && 
            isset($unit_costs[$index]) && !empty($unit_costs[$index])) {
            $valid_items[] = [
                'item_id' => $item_id,
                'quantity' => floatval($quantities[$index]),
                'unit_cost' => floatval($unit_costs[$index])
            ];
        }
    }
    
    if (empty($valid_items)) {
        $errors[] = "At least one item with quantity and cost is required";
    }
    
    if (empty($supplier_id) && empty($supplier_name)) {
        $errors[] = "Supplier information is required";
    }
    
    if (empty($errors)) {
        $connection->begin_transaction();
        
        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($valid_items as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }
            
            // Handle new supplier if selected
            if ($supplier_id === 'new' && !empty($supplier_name)) {
                // Insert new supplier
                $new_supplier_stmt = $connection->prepare(
                    "INSERT INTO suppliers (supplier_name, is_active) VALUES (?, 1)"
                );
                $new_supplier_stmt->bind_param("s", $supplier_name);
                $new_supplier_stmt->execute();
                $supplier_id = $new_supplier_stmt->insert_id;
                $new_supplier_stmt->close();
            }
            
            // Insert purchase order
            $po_stmt = $connection->prepare(
                "INSERT INTO purchase_orders (
                    po_number, supplier_id, order_date, expected_delivery,
                    subtotal, total_amount, notes, created_by, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ordered')"
            );
            
            $total_amount = $subtotal;
            $user_id = $_SESSION['user_id'];
            
            $po_stmt->bind_param(
                "sissddsi",
                $po_number, $supplier_id, $order_date, $expected_delivery,
                $subtotal, $total_amount, $notes, $user_id
            );
            $po_stmt->execute();
            $po_id = $po_stmt->insert_id;
            $po_stmt->close();
            
            // Insert items
            $item_stmt = $connection->prepare(
                "INSERT INTO purchase_order_items (
                    purchase_order_id, inventory_item_id, quantity_ordered, unit_cost, quantity_received
                ) VALUES (?, ?, ?, ?, 0)"
            );
            
            foreach ($valid_items as $item) {
                $item_stmt->bind_param("iidd", $po_id, $item['item_id'], $item['quantity'], $item['unit_cost']);
                $item_stmt->execute();
            }
            $item_stmt->close();
            
            $connection->commit();
            $success_message = "Purchase order created successfully! PO Number: $po_number";
            
        } catch (Exception $e) {
            $connection->rollback();
            $errors[] = "Failed to create purchase order: " . $e->getMessage();
            error_log("Purchase order error: " . $e->getMessage());
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><i class="bi bi-cart-plus me-2"></i>New Purchase Order</h1>
            <div>
                <a href="inventory.php?source=view_purchases" class="btn btn-info me-2">
                    <i class="bi bi-list"></i> View Purchases
                </a>
                <a href="inventory.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Purchase Order Details</h5>
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
                
                <form method="POST" action="" id="purchaseForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">PO Number</label>
                                <input type="text" class="form-control" value="<?php echo $po_number; ?>" readonly disabled>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="order_date" class="form-label">Order Date *</label>
                                <input type="date" class="form-control" id="order_date" name="order_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="expected_delivery" class="form-label">Expected Delivery</label>
                                <input type="date" class="form-control" id="expected_delivery" name="expected_delivery" 
                                       value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier_id" class="form-label">Select Supplier</label>
                                <select class="form-select" id="supplier_id" name="supplier_id">
                                    <option value="">-- Select Supplier --</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['id']; ?>">
                                        <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <option value="new">+ Add New Supplier</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3" id="new_supplier_fields" style="display: none;">
                                <label for="supplier_name" class="form-label">New Supplier Name</label>
                                <input type="text" class="form-control" id="supplier_name" name="supplier_name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Items</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Item</th>
                                    <th style="width: 20%">Quantity</th>
                                    <th style="width: 20%">Unit Cost (AED)</th>
                                    <th style="width: 15%">Total</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- First row will be added by JavaScript -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end fw-bold" id="subtotal">0.00 AED</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <button type="button" class="btn btn-secondary mb-3" id="addItemBtn">
                        <i class="bi bi-plus-circle"></i> Add Item
                    </button>
                    
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="inventory.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Create Purchase Order
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
                <h4 class="my-3">Purchase Order Created!</h4>
                <p><?php echo $success_message; ?></p>
            </div>
            <div class="modal-footer">
                <a href="inventory.php?source=view_purchases" class="btn btn-primary">View All Purchases</a>
                <a href="inventory.php?source=add_purchase" class="btn btn-success">Create Another</a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($success_message)): ?>
<script>
// Ensure jQuery is loaded before this script
if (typeof $ === 'undefined') {
    alert('jQuery is not loaded. Please ensure jQuery is included before this script.');
}
    window.addEventListener('load', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>
<?php endif; ?>

<script>
// Items data from PHP
const itemsList = <?php echo json_encode($items_list); ?>;
let itemIndex = 0;

$(document).ready(function() {
    // Add first row on page load
    addNewRow();
    
    // Add item row button
    $('#addItemBtn').click(function() {
        addNewRow();
    });
    
    // Show/hide new supplier fields
    $('#supplier_id').change(function() {
        if ($(this).val() === 'new') {
            $('#new_supplier_fields').slideDown();
            $('#supplier_name').prop('required', true);
        } else {
            $('#new_supplier_fields').slideUp();
            $('#supplier_name').prop('required', false).val('');
        }
    });
    
    // Form submission validation
    $('#purchaseForm').on('submit', function(e) {
        let hasValidItems = false;
        $('.item-row').each(function() {
            let itemId = $(this).find('.item-select').val();
            let qty = $(this).find('.item-qty').val();
            let cost = $(this).find('.item-cost').val();
            
            if (itemId && qty && cost && parseFloat(qty) > 0 && parseFloat(cost) > 0) {
                hasValidItems = true;
            }
        });
        
        if (!hasValidItems) {
            e.preventDefault();
            alert('Please add at least one valid item with quantity and cost');
            return false;
        }
        return true;
    });
});

function addNewRow() {
    let optionsHtml = '<option value="">-- Select Item --</option>';
    itemsList.forEach(function(item) {
        optionsHtml += `<option value="${item.id}" data-unit="${item.unit_of_measure || 'unit'}" data-cost="${item.cost_per_unit || 0}">${escapeHtml(item.item_name)} (${item.sku || 'No SKU'})</option>`;
    });
    
    let newRow = `
        <tr class="item-row" data-index="${itemIndex}">
            <td>
                <select class="form-select item-select" name="item_id[]" required>
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="number" class="form-control item-qty" name="quantity[]" 
                       step="0.01" min="0.01" required placeholder="0.00">
                <small class="text-muted unit-display"></small>
            </td>
            <td>
                <input type="number" class="form-control item-cost" name="unit_cost[]" 
                       step="0.01" min="0" required placeholder="0.00">
            </td>
            <td class="item-total text-end align-middle fw-bold">0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-item" title="Remove item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#itemsTableBody').append(newRow);
    setupRowHandlers($('#itemsTableBody tr').last());
    itemIndex++;
}

function setupRowHandlers(row) {
    // Remove item button
    $(row).find('.remove-item').click(function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            calculateSubtotal();
        } else {
            alert('You need at least one item. Add a new item first if you want to remove this one.');
        }
    });
    
    // Item selection change
    $(row).find('.item-select').change(function() {
        let currentRow = $(this).closest('tr');
        let selected = $(this).find('option:selected');
        let unit = selected.data('unit');
        let cost = selected.data('cost');
        
        currentRow.find('.unit-display').text('per ' + unit);
        if (cost > 0) {
            currentRow.find('.item-cost').val(cost);
        }
        calculateRowTotal(currentRow);
    });
    
    // Quantity or cost change
    $(row).find('.item-qty, .item-cost').on('input', function() {
        let currentRow = $(this).closest('tr');
        calculateRowTotal(currentRow);
    });
}

function calculateRowTotal(row) {
    let qty = parseFloat($(row).find('.item-qty').val()) || 0;
    let cost = parseFloat($(row).find('.item-cost').val()) || 0;
    let total = qty * cost;
    $(row).find('.item-total').text(total.toFixed(2));
    calculateSubtotal();
}

function calculateSubtotal() {
    let subtotal = 0;
    $('.item-total').each(function() {
        let val = parseFloat($(this).text()) || 0;
        subtotal += val;
    });
    $('#subtotal').text(subtotal.toFixed(2) + ' AED');
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>