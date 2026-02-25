<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../includes/database.php';
$connection = getDBConnection();

// Fetch categories
$categories = $connection->query("
    SELECT id, name 
    FROM menu_categories 
    WHERE is_active = 1 
    ORDER BY sort_order ASC, name ASC
");

// Get first category ID for initial load
$firstCat = null;
if ($categories && $categories->num_rows > 0) {
    $categories->data_seek(0);
    $firstRow = $categories->fetch_assoc();
    $firstCat = $firstRow['id'];
    $categories->data_seek(0); // Reset pointer
}
?>

<div class="main-content">
<div class="container-fluid">

<!-- ================= HEADER ================= -->

<!-- Page Title -->
<div class="mt-3 mb-2">
    <h1 style="font-size:2rem; font-weight:800; color:#c41e3a; letter-spacing:1px; margin-bottom:0.2em; text-shadow:0 2px 8px rgba(196,30,58,0.08);">Punch Orders</h1>
</div>

<!-- Active Orders Card with Breakdown (compact, above row) -->
<div id="activeOrdersCard" class="card shadow-sm mb-2" style="max-width:100%; border-radius:12px; border:1px solid #f39c12; background:linear-gradient(135deg,#fffbe6,#fff);">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div style="font-size:1.05rem; color:#c41e3a; font-weight:600;">Active Orders: <span id="activeOrdersCount" style="font-size:1.5rem; font-weight:700; color:#f39c12;">0</span></div>
            <div id="ordersTypeBreakdown" class="d-flex flex-wrap gap-2"></div>
        </div>
    </div>
</div>

<!-- Main Row: New Order Button and Tabs -->
<div class="d-flex align-items-center mb-3" style="gap:18px;">
    <button class="btn btn-theme-gradient" id="btnNewOrder" style="background: linear-gradient(135deg, #c41e3a 0%, #f39c12 100%); color: #fff; border-radius: 14px; border: none; font-size: 1.1rem; font-weight: 600; box-shadow: 0 4px 16px rgba(196,30,58,0.12); padding: 12px 24px;">
        <i class="bi bi-plus-circle display-6 me-2"></i> Punch New Order
    </button>
    <div id="ordersTabsContainer" class="orders-tabs-card" style="background:#fff;border-radius:12px;padding:12px 8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);overflow-x:auto;white-space:nowrap;scroll-behavior:smooth;flex:1;">
        <div id="ordersTabs" class="d-flex flex-row gap-2"></div>
    </div>
</div>

<!-- ================= POS CONTAINER ================= -->

<div class="pos-container">

    <!-- 1️⃣ CATEGORY PANEL -->
    <div class="category-panel">
        <?php $catIndex = 0; while($cat = $categories->fetch_assoc()): ?>
            <div class="category-item<?= $catIndex === 0 ? ' active' : '' ?>" data-category="<?= $cat['id'] ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </div>
            <?php $catIndex++; ?>
        <?php endwhile; ?>
        <div class="d-flex flex-column align-items-center my-3">
            <a href="categories.php?source=add_category" class="btn btn-outline-success btn-sm mb-2 w-100" style="border-radius:8px; font-weight:600;"><i class="bi bi-plus-circle me-1"></i> Add Category</a>
        </div>
    </div>

    <!-- 2️⃣ MENU PANEL -->
    <div class="menu-panel">
        <div id="menuItems" class="row g-2"></div>
        <div class="d-flex flex-column align-items-center my-3">
            <a href="menu_items.php?source=add_item" class="btn btn-outline-primary btn-sm w-100" style="border-radius:8px; font-weight:600;"><i class="bi bi-plus-circle me-1"></i> Add Menu Item</a>
        </div>
    </div>

    <!-- 3️⃣ ORDER PANEL -->
    <div class="order-panel">
        <table class="table table-bordered mb-2">
            <thead>
                <tr>
                    <th>Item</th>
                    <th width="70">Qty</th>
                    <th width="100">Price</th>
                    <th width="100">Total</th>
                    <th width="40"></th>
                </tr>
            </thead>
            <tbody id="orderItemsBody">
                <tr class="empty-row">
                    <td colspan="5" class="text-center text-muted">
                        Create or select an order to begin
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- ================= ENHANCED FINANCIAL SUMMARY ================= -->
        <div class="financial-summary mt-3">
            <!-- Subtotal -->
            <div class="summary-row d-flex justify-content-between align-items-center py-2">
                <span class="summary-label">
                    <i class="bi bi-calculator me-2" style="color: #c41e3a;"></i>Subtotal:
                </span>
                <span class="summary-value fw-bold" id="summarySubtotal">0.00 AED</span>
            </div>
            
            <!-- Discount Row with Edit -->
            <div class="summary-row d-flex justify-content-between align-items-center py-2 bg-light rounded px-2">
                <div class="d-flex align-items-center">
                    <i class="bi bi-tag-fill me-2" style="color: #f39c12;"></i>
                    <span class="summary-label me-2">Discount:</span>
                    <div class="input-group input-group-sm" style="width: 110px;">
                        <input type="number" class="form-control form-control-sm" id="discountAmount" value="0" min="0" step="0.01" style="border-color: #f39c12;">
                        <span class="input-group-text bg-white" style="border-color: #f39c12;">AED</span>
                    </div>
                </div>
                <span class="summary-value text-warning fw-bold" id="summaryDiscount">-0.00 AED</span>
            </div>
            
            <!-- Discount Type Toggle -->
            <div class="d-flex justify-content-end mb-2">
                <div class="btn-group btn-group-sm" role="group" id="discountTypeGroup">
                    <button type="button" class="btn btn-outline-warning active" data-discount-type="fixed">Fixed</button>
                    <button type="button" class="btn btn-outline-warning" data-discount-type="percentage">%</button>
                </div>
            </div>
            
            <!-- Tax -->
            <div class="summary-row d-flex justify-content-between align-items-center py-2">
                <span class="summary-label">
                    <i class="bi bi-percent me-2" style="color: #3498db;"></i>Tax (0%):
                </span>
                <span class="summary-value" id="summaryTax">0.00 AED</span>
            </div>
            
            <!-- Delivery Fee (conditional) -->
            <div class="summary-row d-flex justify-content-between align-items-center py-2" id="deliveryFeeRow" style="display: none;">
                    <span class="summary-label">
                        <i class="bi bi-truck me-2" style="color: #2ecc71;"></i>Delivery Fee:
                    </span>
                    <div class="input-group input-group-sm" style="width: 110px;">
                        <input type="number" class="form-control form-control-sm" id="deliveryFeeInput" value="0" min="0" step="0.01" style="border-color: #2ecc71; text-align: right; font-weight: 600;">
                        <span class="input-group-text bg-white" style="border-color: #2ecc71;">AED</span>
                    </div>
                    <span class="summary-value ms-2" id="summaryDeliveryFee">0.00 AED</span>
                </div>
            
            <!-- Divider -->
            <div class="dropdown-divider my-2"></div>
            
            <!-- Net Total -->
            <div class="summary-row d-flex justify-content-between align-items-center py-3" style="background: linear-gradient(135deg, #c41e3a 0%, #f39c12 100%); border-radius: 8px; padding: 10px 15px !important; margin-top: 5px;">
                <span class="summary-label text-white fw-bold fs-5">
                    <i class="bi bi-cash-stack me-2"></i>NET TOTAL:
                </span>
                <span class="summary-value text-white fw-bold fs-4" id="orderTotal">0.00 AED</span>
            </div>
            
            <!-- Quick Discount Presets -->
            <div class="discount-presets mt-2">
                <small class="text-muted me-2">Quick discounts:</small>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary discount-preset" data-preset="5">5%</button>
                    <button class="btn btn-outline-secondary discount-preset" data-preset="10">10%</button>
                    <button class="btn btn-outline-secondary discount-preset" data-preset="15">15%</button>
                    <button class="btn btn-outline-secondary discount-preset" data-preset="20">20%</button>
                    <button class="btn btn-outline-secondary discount-preset" data-preset="25">25%</button>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-warning w-50" id="btnSendKitchen" disabled>
                <i class="bi bi-send me-2"></i> Send to Kitchen
            </button>
            <button class="btn btn-primary w-50" id="btnPrint" disabled>
                <i class="bi bi-printer me-2"></i> Print Receipt
            </button>
        </div>
        
        <!-- Additional Action Buttons Row -->
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-outline-secondary btn-sm flex-fill" id="btnHoldOrder">
                <i class="bi bi-pause-circle me-1"></i> Hold
            </button>
            <button class="btn btn-outline-info btn-sm flex-fill" id="btnAddNote">
                <i class="bi bi-chat-dots me-1"></i> Add Note
            </button>
            <button class="btn btn-outline-danger btn-sm flex-fill" id="btnCancelOrder">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </button>
        </div>

        <!-- Payment/Close Button - Will be toggled between states -->
        <div class="d-flex mt-3 justify-content-center">
            <button class="btn btn-success btn-lg fw-bold" id="btnPaymentAction" style="font-size:1.15rem; border-radius:12px; box-shadow:0 2px 8px rgba(39,174,96,0.08); min-width:240px;">
                <i class="bi bi-credit-card me-2"></i>Choose Payment Method
            </button>
        </div>
    </div> <!-- Close order-panel -->
</div> <!-- Close pos-container -->
</div> <!-- Close container-fluid -->
</div> <!-- Close main-content -->

<!-- ================= MODALS ================= -->

<!-- Payment Method Modal (Simplified) -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card-2-front display-6 text-white"></i>
                    <h5 class="modal-title mb-0 text-white">Select Payment Method</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body reservation-modal-body">
                <!-- Order Summary (Brief) -->
                <div class="alert alert-info mb-3">
                    <small>
                        <strong>Closing order:</strong> <span id="modalOrderNumber"></span> - 
                        <span id="modalCustomerName"></span><br>
                        <strong>Total:</strong> <span id="modalTotal" class="fw-bold text-success"></span>
                    </small>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold fs-5 mb-3">Select Payment Method</label>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="payment-method-card" data-method="cash" style="background: linear-gradient(135deg, #f39c12 0%, #f7b733 100%); color: #fff; cursor:pointer; border-radius:12px; padding:20px; text-align:center; transition:all 0.3s;">
                                <i class="bi bi-cash-coin display-5 mb-2"></i>
                                <h6 class="mb-0">Cash</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method-card" data-method="card" style="background: linear-gradient(135deg, #3498db 0%, #6dd5fa 100%); color: #fff; cursor:pointer; border-radius:12px; padding:20px; text-align:center; transition:all 0.3s;">
                                <i class="bi bi-credit-card-2-front display-5 mb-2"></i>
                                <h6 class="mb-0">POS Card</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method-card" data-method="credit" style="background: linear-gradient(135deg, #c41e3a 0%, #f39c12 100%); color: #fff; cursor:pointer; border-radius:12px; padding:20px; text-align:center; transition:all 0.3s;">
                                <i class="bi bi-credit-card display-5 mb-2"></i>
                                <h6 class="mb-0">Credit Card</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method-card" data-method="debit" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: #fff; cursor:pointer; border-radius:12px; padding:20px; text-align:center; transition:all 0.3s;">
                                <i class="bi bi-credit-card-2-back display-5 mb-2"></i>
                                <h6 class="mb-0">Debit Card</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reference (Optional) -->
                <div class="mb-3" id="referenceField" style="display: none;">
                    <label class="form-label">Reference/Transaction ID (Optional)</label>
                    <input type="text" class="form-control" id="paymentReference" placeholder="e.g., Transaction ID">
                </div>

                <input type="hidden" id="selectedPaymentMethod">
            </div>
            <div class="modal-footer reservation-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" id="confirmPaymentMethod" disabled>
                    <i class="bi bi-check2-circle me-2"></i>Confirm Payment Method
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="orderSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check2-circle me-2"></i>
                    Order Saved Successfully
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3" id="successOrderNumber">Order #</h4>
                <p class="text-muted">The order has been closed and saved to the database.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- New Order Modal -->
<div class="modal fade" id="initOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content reservation-modal-theme">
            <div class="modal-header reservation-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle display-6 text-theme"></i>
                    <h5 class="modal-title mb-0">Create New Order</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body reservation-modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Order Type</label>
                    <div class="d-flex gap-2 order-type-cards">
                        <div class="order-type-card" data-type="dine_in" style="background: linear-gradient(135deg, #3498db 0%, #6dd5fa 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:18px; text-align:center;">
                            <i class="bi bi-shop display-5 mb-2"></i><br>Dine In
                        </div>
                        <div class="order-type-card" data-type="pickup" style="background: linear-gradient(135deg, #f39c12 0%, #f7b733 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:18px; text-align:center;">
                            <i class="bi bi-bag display-5 mb-2"></i><br>Pickup
                        </div>
                        <div class="order-type-card" data-type="delivery" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:18px; text-align:center;">
                            <i class="bi bi-truck display-5 mb-2"></i><br>Delivery
                        </div>
                    </div>
                </div>
                <div id="deliveryOptions" class="d-none mb-3">
                    <label class="form-label fw-bold">Delivery Source</label>
                    <div class="d-flex gap-2 delivery-source-cards">
                        <div class="delivery-source-card" data-source="internal" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-shop display-6 mb-1"></i><br>Restaurant
                        </div>
                        <div class="delivery-source-card" data-source="noon" style="background: linear-gradient(135deg, #fbb034 0%, #ffdd00 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-sun display-6 mb-1"></i><br>Noon
                        </div>
                        <div class="delivery-source-card" data-source="keeta" style="background: linear-gradient(135deg, #e74c3c 0%, #e67e22 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-bicycle display-6 mb-1"></i><br>Keeta
                        </div>
                        <div class="delivery-source-card" data-source="deliveroo" style="background: linear-gradient(135deg, #00c3e3 0%, #2f80ed 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-bag-check display-6 mb-1"></i><br>Deliveroo
                        </div>
                        <div class="delivery-source-card" data-source="smile" style="background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%); color: #fff; cursor:pointer; flex:1; border-radius:10px; padding:14px; text-align:center;">
                            <i class="bi bi-emoji-smile display-6 mb-1"></i><br>Smile
                        </div>
                    </div>
                </div>
                <input type="hidden" id="orderTypeSelect" value="">
                <input type="hidden" id="deliverySource" value="internal">
                <div id="dineInFields" class="d-none mb-2">
                    <label class="form-label fw-bold">Select Table</label>
                    <div id="tableSelector" class="d-flex flex-wrap gap-2 mb-2"></div>
                    <input type="number" id="numCustomers" class="form-control mb-2" placeholder="Number of Customers" min="1">
                </div>
                <input type="text" id="customerName" class="form-control mb-2" placeholder="Customer Name">
                <input type="text" id="customerPhone" class="form-control mb-2" placeholder="Phone">
                <input type="text" id="customerAddress" class="form-control mb-2 d-none" placeholder="Address">
            </div>
            <div class="modal-footer reservation-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-theme" id="confirmCreateOrder">Create Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Soft Delete Confirmation Modal -->
<div class="modal fade delete-confirm-modal" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Delete Order
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="warning-icon">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <h4 class="mb-3">Are you sure?</h4>
                <p class="text-muted mb-4">This order will be moved to trash. You can restore it later from the recovery panel.</p>
                
                <div class="order-details" id="deleteOrderDetails">
                    <!-- Will be filled dynamically -->
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="permanentDeleteCheck">
                    <label class="form-check-label" for="permanentDeleteCheck">
                        <strong class="text-danger">Permanently delete</strong> <span class="text-muted">(cannot be undone)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-2"></i> Delete Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recovery Modal (Trash Bin) -->
<div class="modal fade recovery-modal" id="recoveryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #6c757d, #495057); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-trash3-fill me-2"></i>
                    Deleted Orders Recovery
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="deletedOrdersList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-secondary"></div>
                        <p class="mt-2">Loading deleted orders...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="emptyTrashBtn" style="display: none;">
                    <i class="bi bi-trash3 me-2"></i> Empty Trash
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Undo Toast -->
<div id="undoToast" class="undo-toast" style="display: none;">
    <i class="bi bi-check-circle-fill me-2"></i>
    <span id="undoMessage">Order deleted</span>
    <button class="undo-btn" id="undoDeleteBtn">UNDO</button>
</div>

<!-- Trash Bin Button -->
<div class="trash-bin-panel">
    <button class="trash-bin-btn" id="trashBinBtn" title="View deleted orders">
        <i class="bi bi-trash3-fill"></i>
    </button>
</div>

<style>
.pos-container{
    display:flex;
    height:calc(100vh - 180px);
    background:#fff;
    border-radius:8px;
    overflow:hidden;
}
.category-panel{
    width:220px;
    background:#2c3e50;
    color:#fff;
    overflow-y:auto;
}
.category-item{
    padding:12px;
    cursor:pointer;
    transition: all 0.3s;
}
.category-item:hover{
    background:#34495e;
}
.category-item.active{
    background:#c41e3a;
    font-weight:500;
}
.menu-panel{
    width:320px;
    overflow-y:auto;
    padding:10px;
    border-left:1px solid #ddd;
}
.menu-item{
    border:1px solid #ddd;
    padding:10px;
    cursor:pointer;
    border-radius:6px;
    background:#fafafa;
    transition: all 0.2s;
}
.menu-item:hover{
    background:#f0f0f0;
    border-color:#c41e3a;
}
.order-panel{
    flex:1;
    padding:10px;
    display:flex;
    flex-direction:column;
    border-left:1px solid #ddd;
    min-height:0;
    overflow-y:auto;
    max-height:calc(100vh - 220px);
}
.order-tab{
    padding:6px 12px;
    background:#eee;
    border-radius:6px;
    cursor:pointer;
    transition:box-shadow 0.2s,border 0.2s;
    white-space:normal;
    font-size:0.98rem;
    line-height:1.2;
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    min-width:180px;
    max-width:220px;
    overflow:hidden;
    text-overflow:ellipsis;
}
.order-tab.active{
    background:#fff;
    border:2px solid #c41e3a!important;
    box-shadow:0 4px 12px rgba(196,30,58,0.12);
}
.orders-tabs-card{
    flex:1;
    overflow-x:auto;
    white-space:nowrap;
    scrollbar-width:thin;
    scrollbar-color:#c41e3a #eee;
    scroll-behavior:smooth;
}
.orders-tabs-card::-webkit-scrollbar{
    height:8px;
    background:#eee;
    border-radius:8px;
}
.orders-tabs-card::-webkit-scrollbar-thumb{
    background:#c41e3a;
    border-radius:8px;
}
.financial-summary {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 1px solid #e0e0e0;
}
.summary-row {
    transition: all 0.2s ease;
}
.summary-row:hover {
    background-color: #f8f9fa;
    border-radius: 6px;
}
.summary-label {
    font-size: 0.95rem;
    color: #2c3e50;
}
.summary-value {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}
#discountAmount {
    text-align: right;
    font-weight: 600;
}
#discountAmount:focus {
    border-color: #c41e3a;
    box-shadow: 0 0 0 0.2rem rgba(196,30,58,0.25);
}
.discount-presets {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
}
.discount-presets .btn-group {
    flex-wrap: wrap;
}
.discount-presets .btn-outline-secondary {
    border-color: #e0e0e0;
    color: #2c3e50;
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
}
.discount-presets .btn-outline-secondary:hover {
    background: #f39c12;
    border-color: #f39c12;
    color: #fff;
}
#discountTypeGroup .btn-outline-warning {
    border-color: #f39c12;
    color: #f39c12;
    font-size: 0.8rem;
    padding: 0.2rem 0.8rem;
}
#discountTypeGroup .btn-outline-warning.active {
    background: #f39c12;
    border-color: #f39c12;
    color: #fff;
}
.payment-method-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}
.payment-method-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.payment-method-card.selected {
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #27ae60;
    transform: scale(1.02);
}
.delete-order-tab-btn:hover {
    background: #dc3545 !important;
    color: #fff !important;
}
.undo-toast {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #28a745;
    color: white;
    padding: 12px 24px;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1100;
    display: flex;
    align-items: center;
    gap: 15px;
    animation: slideUp 0.3s ease;
}
.undo-toast .undo-btn {
    background: white;
    color: #28a745;
    border: none;
    border-radius: 4px;
    padding: 5px 15px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}
.undo-toast .undo-btn:hover {
    background: #f8f9fa;
    transform: scale(1.05);
}
@keyframes slideUp {
    from {
        bottom: -100px;
        opacity: 0;
    }
    to {
        bottom: 20px;
        opacity: 1;
    }
}
.trash-bin-panel {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1050;
}
.trash-bin-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.trash-bin-btn:hover {
    transform: scale(1.1);
    background: linear-gradient(135deg, #dc3545, #c82333);
}
.trash-bin-btn.has-items {
    background: linear-gradient(135deg, #dc3545, #c82333);
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}
</style>

<!-- Add Bootstrap and jQuery scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let orders = [];
let activeOrderId = null;
let isLoading = true;
let savedScrollPosition = 0;

// Financial variables
let discountAmount = 0;
let discountType = 'fixed';
let deliveryFee = 0;
const TAX_RATE = 0.0;

// Soft delete variables
let deletedOrders = [];
let lastDeletedOrder = null;
let undoTimeout = null;

// Payment variables
let selectedPaymentMethod = null;
let paymentReference = '';

// --- DRAFT ORDER PERSISTENCE FUNCTIONS ---
function saveDraftOrders() {
    localStorage.setItem('pos_orders', JSON.stringify(orders));
    
    orders.forEach(order => {
        $.ajax({
            url: 'includes/pos_order_drafts.php',
            method: 'POST',
            data: { 
                action: 'save', 
                order: JSON.stringify(order) 
            },
            success: function(response) {},
            error: function(xhr) {
                console.error('Failed to save order:', order.id, xhr.responseText);
            }
        });
    });
}

function loadDraftOrdersFromLocal() {
    let local = localStorage.getItem('pos_orders');
    if(local) {
        try {
            return JSON.parse(local) || [];
        } catch(e) { 
            console.error('Error parsing localStorage:', e);
            return []; 
        }
    }
    return [];
}

function loadDraftOrdersFromDB(callback) {
    $.ajax({
        url: 'includes/pos_order_drafts.php',
        method: 'GET',
        data: {action: 'load'},
        dataType: 'json',
        success: function(data) {
            callback(Array.isArray(data) ? data : []);
        },
        error: function(xhr) {
            console.error('Failed to load from DB:', xhr.responseText);
            callback([]);
        }
    });
}

function mergeOrders(local, db) {
    let map = {};
    
    db.forEach(o => {
        if (o && o.id) {
            map[o.id] = o;
        }
    });
    
    local.forEach(o => {
        if (!o || !o.id) return;
        
        if (!map[o.id]) {
            map[o.id] = o;
        } else {
            if (o.items && o.items.length > 0) {
                if (!map[o.id].items || map[o.id].items.length === 0) {
                    map[o.id].items = o.items;
                } else if (o.items.length > map[o.id].items.length) {
                    map[o.id].items = o.items;
                }
            }
        }
    });
    
    return Object.values(map);
}

// --- SCROLL POSITION FUNCTIONS ---
function saveScrollPosition() {
    let container = document.querySelector('.orders-tabs-card');
    if (container) {
        savedScrollPosition = container.scrollLeft;
    }
}

function restoreScrollPosition() {
    let container = document.querySelector('.orders-tabs-card');
    if (container) {
        container.scrollLeft = savedScrollPosition;
    }
}

function scrollActiveTabIntoView() {
    setTimeout(() => {
        let container = document.querySelector('.orders-tabs-card');
        let activeTab = document.querySelector('.order-tab.active');
        
        if (container && activeTab) {
            let containerRect = container.getBoundingClientRect();
            let tabRect = activeTab.getBoundingClientRect();
            
            let tabLeft = tabRect.left - containerRect.left + container.scrollLeft;
            let tabRight = tabLeft + tabRect.width;
            
            if (tabLeft < container.scrollLeft) {
                container.scrollLeft = tabLeft - 20;
            } else if (tabRight > container.scrollLeft + container.clientWidth) {
                container.scrollLeft = tabRight - container.clientWidth + 20;
            }
        }
    }, 50);
}

// --- FINANCIAL CALCULATION FUNCTIONS ---
function calculateFinancials() {
    let order = orders.find(o => o.id === activeOrderId);
    if (!order) return { subtotal: 0, discount: 0, tax: 0, deliveryFee: 0, total: 0 };
    
    let subtotal = 0;
    if (order.items && order.items.length > 0) {
        subtotal = order.items.reduce((sum, item) => {
            return sum + (item.qty * item.price);
        }, 0);
    }
    
    let discount = 0;
    if (discountType === 'fixed') {
        discount = Math.min(discountAmount, subtotal);
    } else {
        discount = (subtotal * discountAmount) / 100;
        discount = Math.min(discount, subtotal);
    }
    
    let deliveryFee = 0;
    if (order.type === 'delivery') {
        $('#deliveryFeeRow').show();
        // Use the value from the input, default to 0
        let inputVal = parseFloat($('#deliveryFeeInput').val());
        deliveryFee = isNaN(inputVal) ? 0 : inputVal;
    } else {
        $('#deliveryFeeRow').hide();
    }
    
    let taxableAmount = subtotal - discount;
    let tax = taxableAmount * TAX_RATE;
    let total = taxableAmount + tax + deliveryFee;
    
    return {
        subtotal: subtotal,
        discount: discount,
        tax: tax,
        deliveryFee: deliveryFee,
        total: total
    };
}

function calculateOrderTotal(order) {
    if (!order || !order.items) return 0;
    return order.items.reduce((sum, item) => {
        return sum + (item.qty * item.price);
    }, 0);
}

// --- RENDERING FUNCTIONS ---
function renderTabs() {
    saveScrollPosition();
    
    let html = '';
    let visibleOrders = orders.filter(order => order && !order.is_deleted);
    
    // Update active orders count card
    $('#activeOrdersCount').text(visibleOrders.length);
    
    // Breakdown by type
    let breakdown = {
        dine_in: 0,
        pickup: 0,
        delivery_internal: 0,
        delivery_noon: 0,
        delivery_keeta: 0,
        delivery_deliveroo: 0,
        delivery_smile: 0
    };
    
    visibleOrders.forEach(order => {
        if (order.type === 'dine_in') breakdown.dine_in++;
        else if (order.type === 'pickup') breakdown.pickup++;
        else if (order.type === 'delivery') {
            let src = order.delivery_source || 'internal';
            if (src === 'internal') breakdown.delivery_internal++;
            else if (src === 'noon') breakdown.delivery_noon++;
            else if (src === 'keeta') breakdown.delivery_keeta++;
            else if (src === 'deliveroo') breakdown.delivery_deliveroo++;
            else if (src === 'smile') breakdown.delivery_smile++;
        }
    });
    
    let breakdownHtml = '';
    breakdownHtml += `<span class="badge me-1" style="background:#3498db;"><i class="bi bi-shop"></i> Dine: ${breakdown.dine_in}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#f39c12;"><i class="bi bi-bag"></i> Pickup: ${breakdown.pickup}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#27ae60;"><i class="bi bi-truck"></i> Del: ${breakdown.delivery_internal}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#fbb034;"><i class="bi bi-sun"></i> Noon: ${breakdown.delivery_noon}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#e74c3c;"><i class="bi bi-bicycle"></i> Keeta: ${breakdown.delivery_keeta}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#00c3e3;"><i class="bi bi-bag-check"></i> Roo: ${breakdown.delivery_deliveroo}</span>`;
    breakdownHtml += `<span class="badge me-1" style="background:#f1c40f;"><i class="bi bi-emoji-smile"></i> Smile: ${breakdown.delivery_smile}</span>`;
    $('#ordersTypeBreakdown').html(breakdownHtml);
    
    visibleOrders.forEach(order => {
        let active = order.id === activeOrderId ? 'active' : '';
        let typeColor = '';
        let typeIcon = '';
        let deliveryBadge = '';
        
        if(order.type === 'dine_in'){
            typeColor = 'background:linear-gradient(135deg,#3498db,#6dd5fa);color:#fff;';
            typeIcon = '<i class="bi bi-shop me-1"></i>';
        } else if(order.type === 'pickup'){
            typeColor = 'background:linear-gradient(135deg,#f39c12,#f7b733);color:#fff;';
            typeIcon = '<i class="bi bi-bag me-1"></i>';
        } else if(order.type === 'delivery'){
            typeColor = 'background:linear-gradient(135deg,#2ecc71,#27ae60);color:#fff;';
            typeIcon = '<i class="bi bi-truck me-1"></i>';
            let src = order.delivery_source || 'internal';
            let srcMap = {
                internal: {label:'Restaurant',color:'#2ecc71',icon:'<i class="bi bi-shop"></i>'},
                noon: {label:'Noon',color:'#fbb034',icon:'<i class="bi bi-sun"></i>'},
                keeta: {label:'Keeta',color:'#e74c3c',icon:'<i class="bi bi-bicycle"></i>'},
                deliveroo: {label:'Deliveroo',color:'#00c3e3',icon:'<i class="bi bi-bag-check"></i>'},
                smile: {label:'Smile',color:'#f1c40f',icon:'<i class="bi bi-emoji-smile"></i>'}
            };
            if(srcMap[src]){
                deliveryBadge = `<span class="badge ms-1" style="background:${srcMap[src].color};color:#fff;font-size:0.8em;vertical-align:middle;">${srcMap[src].icon} ${srcMap[src].label}</span>`;
            }
        }
        
        let customerName = order.customer && order.customer.name ? order.customer.name : 'Guest';
        
        html += `
            <div class="order-tab ${active}" 
                 style="${typeColor}margin-right:8px;min-width:180px;display:inline-block;cursor:pointer;border-radius:8px;padding:8px 16px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid ${active ? "#c41e3a" : "transparent"};position:relative;" 
                 onclick="switchOrder('${order.id}')">
                <button class="delete-order-tab-btn" onclick="event.stopPropagation(); showDeleteModal('${order.id}')" title="Delete order" style="position:absolute; top:6px; right:6px; width:22px; height:22px; border-radius:50%; background:rgba(255,255,255,0.9); border:1px solid #dc3545; color:#dc3545; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:10; font-size:13px; padding:0; box-shadow:0 2px 4px rgba(0,0,0,0.1);"><i class="bi bi-x"></i></button>
                <div style="font-weight:600;">
                    ${typeIcon}${order.type.toUpperCase()} - ${customerName} ${deliveryBadge}
                </div>
                <div style="font-size:0.85rem;opacity:0.9;">
                    Items: ${order.items ? order.items.length : 0}
                </div>
            </div>
        `;
    });
    
    if (visibleOrders.length === 0) {
        html = '<div class="text-muted p-2">No active orders. Click "Punch New Order" to start.</div>';
    }
    
    $('#ordersTabs').html(html);
    restoreScrollPosition();
    scrollActiveTabIntoView();
}

function renderOrder() {
    if (isLoading) return;
    
    let order = orders.find(o => o.id === activeOrderId);
    if (!order) {
        $('#orderItemsBody').html('<tr><td colspan="5" class="text-center text-muted">Select an order to begin</td></tr>');
        $('#summarySubtotal').text('0.00 AED');
        $('#summaryDiscount').text('-0.00 AED');
        $('#summaryTax').text('0.00 AED');
        $('#summaryDeliveryFee').text('0.00 AED');
        $('#orderTotal').text('0.00 AED');
        $('#btnSendKitchen, #btnPrint').prop('disabled', true);
        return;
    }

    if (!order.items) order.items = [];

    let body = $('#orderItemsBody');
    body.html('');

    order.items.forEach((item, i) => {
        if (!item.name) item.name = 'Unknown Item';
        if (!item.price) item.price = 0;
        if (!item.qty) item.qty = 1;
        
        let line = item.qty * item.price;
        
        body.append(`
            <tr>
                <td style="font-size:1.15rem; font-weight:700; color:#222;">${item.name}</td>
                <td>
                    <div class="input-group input-group-sm justify-content-center">
                        <button class="btn btn-qty-minus btn-sm" data-index="${i}" style="background:linear-gradient(135deg,#e74c3c,#f39c12);color:#fff;border:none;width:32px;">-</button>
                        <span class="form-control text-center border-0" style="width:40px;background:transparent;">${item.qty}</span>
                        <button class="btn btn-qty-plus btn-sm" data-index="${i}" style="background:linear-gradient(135deg,#27ae60,#2ecc71);color:#fff;border:none;width:32px;">+</button>
                    </div>
                </td>
                <td>${item.price.toFixed(2)}</td>
                <td>${line.toFixed(2)}</td>
                <td><button class="btn btn-sm btn-danger" onclick="removeItem(${i})">×</button></td>
            </tr>
        `);
    });

    if (order.items.length === 0) {
        body.html(`
            <tr>
                <td colspan="5">
                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                        <div style="font-size:2.2rem; color:#f39c12; margin-bottom:0.5em;"><i class="bi bi-emoji-neutral"></i></div>
                        <div class="card shadow-sm p-3 mb-2" style="border-radius:12px; background:linear-gradient(135deg,#fffbe6,#fff); border:1px solid #f39c12; max-width:340px;">
                            <div style="font-size:1.1rem; color:#c41e3a; font-weight:600;">No items added</div>
                            <div style="font-size:0.98rem; color:#495057;">Click on menu items to add them to the order.</div>
                        </div>
                    </div>
                </td>
            </tr>
        `);
    }

    let finances = calculateFinancials();
    $('#summarySubtotal').text(finances.subtotal.toFixed(2) + ' AED');
    $('#summaryDiscount').text('-' + finances.discount.toFixed(2) + ' AED');
    $('#summaryTax').text(finances.tax.toFixed(2) + ' AED');
    $('#summaryDeliveryFee').text(finances.deliveryFee.toFixed(2) + ' AED');
    $('#orderTotal').text(finances.total.toFixed(2) + ' AED');
    // Set delivery fee input value if delivery
    if (order.type === 'delivery') {
        $('#deliveryFeeInput').val(order.delivery_fee !== undefined ? order.delivery_fee : 0);
    }
    $('#btnSendKitchen, #btnPrint').prop('disabled', order.items.length === 0);
    saveDraftOrders();
// Delivery fee input handler
$(document).on('input', '#deliveryFeeInput', function() {
    let order = orders.find(o => o.id === activeOrderId);
    if (order && order.type === 'delivery') {
        let val = parseFloat($(this).val());
        order.delivery_fee = isNaN(val) ? 0 : val;
        renderOrder();
    }
});
}

function ordersChanged() {
    renderTabs();
    renderOrder();
    saveDraftOrders();
}

function switchOrder(id) {
    activeOrderId = id;
    discountAmount = 0;
    discountType = 'fixed';
    $('#discountAmount').val(0);
    $('#discountTypeGroup .btn[data-discount-type="fixed"]').click();
    
    // Reset payment button when switching orders
    resetPaymentButton();
    renderTabs();
    renderOrder();
}

function resetPaymentButton() {
    selectedPaymentMethod = null;
    paymentReference = '';
    $('#btnPaymentAction')
        .removeClass('btn-primary')
        .addClass('btn-success')
        .html('<i class="bi bi-credit-card me-2"></i>Choose Payment Method')
        .prop('disabled', false);
}

function removeItem(i) {
    // Allow both admin and super-admin to remove/cancel order items
    <?php if (!in_array($_SESSION['role'], ['admin', 'super-admin'])): ?>
        showNotification('Only Admin or Super Admin can remove items.', 'danger');
        return;
    <?php endif; ?>
    let order = orders.find(o => o.id === activeOrderId);
    if (order && order.items) {
        order.items.splice(i, 1);
        ordersChanged();
    }
}

function loadMenu(category) {
    $.get('includes/get_menu_items.php', {category_id: category}, function(data) {
        $('#menuItems').html(data);
    }).fail(function() {
        $('#menuItems').html('<div class="alert alert-danger">Failed to load menu items</div>');
    });
}

// --- TABLE SELECTOR FUNCTIONS ---
function renderTableSelector() {
    const tables = Array.from({length: 15}, (_, i) => ({ id: 'T'+(i+1), label: 'Table ' + (i+1), type: 'table' }));
    const halls = [
        { id: 'HALL', label: 'Hall', type: 'hall' },
        { id: 'FAMILY', label: 'Family Hall', type: 'family' }
    ];
    
    let occupied = new Set();
    orders.forEach(o => {
        if(o.type === 'dine_in' && o.table_number && o.items && o.items.length > 0) {
            occupied.add(o.table_number);
        }
    });
    
    let html = '';
    tables.concat(halls).forEach(t => {
        let isOccupied = occupied.has(t.id);
        html += `<button type="button" class="btn btn-outline-${isOccupied ? 'secondary' : 'danger'} table-btn mb-1" data-table="${t.id}" style="min-width:90px;${isOccupied?'opacity:0.5;pointer-events:none;':''}">${t.label}</button>`;
    });
    
    $('#tableSelector').html(html);
    $('#tableSelector .table-btn').removeClass('active');
    $('#tableNumber').val('');
}

// --- SOFT DELETE FUNCTIONS ---
function softDeleteOrder(orderId, permanent = false) {
    let order = orders.find(o => o.id === orderId);
    if (!order) return;
    
    if (permanent) {
        $.ajax({
            url: 'includes/pos_order_drafts.php',
            method: 'POST',
            data: { action: 'hard_delete', id: orderId },
            success: function(response) {
                if (response.success) {
                    let index = orders.findIndex(o => o.id === orderId);
                    if (index !== -1) {
                        orders.splice(index, 1);
                    }
                    if (activeOrderId === orderId) {
                        activeOrderId = orders.length > 0 ? orders[0].id : null;
                    }
                    ordersChanged();
                    showNotification('Order permanently deleted', 'danger');
                } else {
                    alert('Error: ' + (response.error || 'Failed to delete order'));
                }
            },
            error: function(xhr) {
                console.error('Failed to delete order:', xhr.responseText);
                alert('Server error occurred');
            }
        });
    } else {
        $.ajax({
            url: 'includes/pos_order_drafts.php',
            method: 'POST',
            data: { action: 'soft_delete', id: orderId },
            success: function(response) {
                if (response.success) {
                    lastDeletedOrder = {...order};
                    let idx = orders.findIndex(o => o.id === orderId);
                    if (idx !== -1) {
                        orders.splice(idx, 1);
                    }
                    if (activeOrderId === orderId) {
                        activeOrderId = orders.length > 0 ? orders[0].id : null;
                    }
                    ordersChanged();
                    showUndoToast(order);
                    updateTrashBinIndicator();
                } else {
                    alert('Error: ' + (response.error || 'Failed to delete order'));
                }
            },
            error: function(xhr) {
                console.error('Failed to delete order:', xhr.responseText);
                alert('Server error occurred');
            }
        });
    }
}

function restoreOrder(orderId) {
    $.ajax({
        url: 'includes/pos_order_drafts.php',
        method: 'POST',
        data: { 
            action: 'restore', 
            id: orderId 
        },
        success: function(response) {
            if (response.success) {
                loadDraftOrdersFromDB(function(dbOrders) {
                    let localOrders = loadDraftOrdersFromLocal();
                    orders = mergeOrders(localOrders, dbOrders);
                    activeOrderId = orderId;
                    ordersChanged();
                    showNotification('Order restored successfully', 'success');
                    updateTrashBinIndicator();
                });
            } else {
                alert('Error: ' + (response.error || 'Failed to restore order'));
            }
        },
        error: function(xhr) {
            console.error('Failed to restore order:', xhr.responseText);
            alert('Server error occurred');
        }
    });
}

function loadDeletedOrders() {
    $.ajax({
        url: 'includes/pos_order_drafts.php',
        method: 'GET',
        data: { action: 'load_deleted' },
        dataType: 'json',
        success: function(data) {
            deletedOrders = Array.isArray(data) ? data : [];
            displayDeletedOrders();
            updateTrashBinIndicator();
        },
        error: function(xhr) {
            console.error('Failed to load deleted orders:', xhr.responseText);
            $('#deletedOrdersList').html('<div class="alert alert-danger">Failed to load deleted orders</div>');
        }
    });
}

function displayDeletedOrders() {
    let html = '';
    
    if (deletedOrders.length === 0) {
        html = '<div class="text-center py-4"><i class="bi bi-trash3 display-1 text-muted mb-3"></i><h5 class="text-muted">Trash is empty</h5><p class="text-muted">No deleted orders found</p></div>';
        $('#emptyTrashBtn').hide();
    } else {
        html = '<div class="list-group">';
        deletedOrders.forEach(order => {
            let customerName = order.customer && order.customer.name ? order.customer.name : 'Guest';
            let itemCount = order.items ? order.items.length : 0;
            let deletedTime = order.deleted_at ? new Date(order.deleted_at).toLocaleString() : 'Unknown';
            
            html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <span class="badge bg-secondary me-2">#${order.id.substr(-6)}</span>
                                ${order.type.toUpperCase()} - ${customerName}
                            </h6>
                            <small class="text-muted">
                                <i class="bi bi-box me-1"></i>${itemCount} items |
                                <i class="bi bi-clock me-1"></i>Deleted: ${deletedTime}
                            </small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-success restore-btn" data-order-id="${order.id}">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                            <button class="btn btn-sm btn-danger hard-delete-btn" data-order-id="${order.id}">
                                <i class="bi bi-trash3"></i> Permanent
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        $('#emptyTrashBtn').show();
    }
    
    $('#deletedOrdersList').html(html);
    
    $('.restore-btn').click(function() {
        let orderId = $(this).data('order-id');
        restoreOrder(orderId);
        $('#recoveryModal').modal('hide');
    });
    
    $('.hard-delete-btn').click(function() {
        let orderId = $(this).data('order-id');
        if (confirm('Permanently delete this order? This cannot be undone!')) {
            softDeleteOrder(orderId, true);
            loadDeletedOrders();
        }
    });
}

function updateTrashBinIndicator() {
    if (deletedOrders.length > 0) {
        $('#trashBinBtn').addClass('has-items');
    } else {
        $('#trashBinBtn').removeClass('has-items');
    }
}

function showUndoToast(order) {
    if (undoTimeout) {
        clearTimeout(undoTimeout);
    }
    
    let customerName = order.customer && order.customer.name ? order.customer.name : 'Guest';
    $('#undoMessage').text(`Order for ${customerName} deleted`);
    $('#undoToast').fadeIn(300);
    
    undoTimeout = setTimeout(() => {
        $('#undoToast').fadeOut(300);
        lastDeletedOrder = null;
    }, 5000);
}

function showNotification(message, type = 'success') {
    let toast = $(`
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
            <div class="toast align-items-center text-bg-${type} border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    setTimeout(() => toast.fadeOut(300, function() { $(this).remove(); }), 3000);
}

window.showDeleteModal = function(orderId) {
    // Only allow super-admin to delete/cancel order
    <?php if ($_SESSION['role'] !== 'super-admin'): ?>
        showNotification('Only Super Admin can delete/cancel an order.', 'danger');
        return;
    <?php endif; ?>
    let order = orders.find(o => o.id === orderId);
    if (!order) return;
    let customerName = order.customer && order.customer.name ? order.customer.name : 'Guest';
    let itemCount = order.items ? order.items.length : 0;
    $('#deleteOrderDetails').html(`
        <strong>Order #${orderId.substr(-6)}</strong><br>
        Type: ${order.type.toUpperCase()}<br>
        Customer: ${customerName}<br>
        Items: ${itemCount}<br>
        Total: ${calculateOrderTotal(order).toFixed(2)} AED
    `);
    $('#confirmDeleteBtn').data('order-id', orderId);
    $('#deleteOrderModal').modal('show');
};

// --- PAYMENT & CLOSE ORDER FUNCTIONS ---
function openPaymentModal() {
    if (!activeOrderId) {
        alert('No active order selected');
        return;
    }
    
    let order = orders.find(o => o.id === activeOrderId);
    if (!order || order.items.length === 0) {
        alert('Cannot close an empty order');
        return;
    }
    
    // Calculate finances
    let finances = calculateFinancials();
    
    // Fill modal summary
    $('#modalOrderNumber').text(order.id.substr(-8));
    $('#modalCustomerName').text(order.customer ? order.customer.name : 'Guest');
    $('#modalTotal').text(finances.total.toFixed(2) + ' AED');
    
    // Reset modal fields
    $('.payment-method-card').removeClass('selected border border-3 border-success shadow');
    $('#selectedPaymentMethod').val('');
    $('#referenceField').hide();
    $('#paymentReference').val('');
    $('#confirmPaymentMethod').prop('disabled', true);
    
    // Show modal
    $('#paymentMethodModal').modal('show');
}

function closeOrderAndSave() {
    if (!selectedPaymentMethod) {
        alert('Please select a payment method first');
        return;
    }
    
    let order = orders.find(o => o.id === activeOrderId);
    if (!order) return;
    
    let finances = calculateFinancials();
    paymentReference = $('#paymentReference').val();
    
    // Disable button
    let btn = $('#btnPaymentAction');
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
    btn.prop('disabled', true);
    
    // Prepare data - only send what's needed
    // Map 'debit' to 'online' for DB
    let paymentMethodToSend = selectedPaymentMethod === 'debit' ? 'online' : selectedPaymentMethod;
    let saveData = {
        order_id: activeOrderId,
        payment_method: paymentMethodToSend,
        payment_reference: paymentReference,
        discount_amount: discountAmount,
        discount_type: discountType
    };
    
    console.log('Sending data:', saveData); // Debug log
    
    $.ajax({
        url: 'includes/ajax/close_order_from_draft.php',
        method: 'POST',
        data: JSON.stringify(saveData),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            console.log('Success response:', response); // Debug log
            if (response.success) {
                // Remove order from active orders
                orders = orders.filter(o => o.id !== activeOrderId);
                
                // Set active order to next available
                if (orders.length > 0) {
                    activeOrderId = orders[0].id;
                } else {
                    activeOrderId = null;
                }
                
                // Update UI
                ordersChanged();
                
                // Show success modal
                $('#successOrderNumber').text('Order #' + response.order_number);
                let successModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));
                successModal.show();
                
                // Reset payment button
                resetPaymentButton();
                
                // Clear from localStorage
                let localOrders = JSON.parse(localStorage.getItem('pos_orders') || '[]');
                localOrders = localOrders.filter(o => o.id !== activeOrderId);
                localStorage.setItem('pos_orders', JSON.stringify(localOrders));
                
            } else {
                alert('Error: ' + response.message);
                btn.html('<i class="bi bi-check2-circle me-2"></i>Save & Close Order');
                btn.prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Response Text:', xhr.responseText);
            let errorMsg = 'Server error occurred';
            try {
                const resp = JSON.parse(xhr.responseText);
                if (resp && resp.message) errorMsg = resp.message;
            } catch (e) {
                if (xhr.responseText) errorMsg = xhr.responseText;
            }
            alert('Error: ' + errorMsg);
            btn.html('<i class="bi bi-check2-circle me-2"></i>Save & Close Order');
            btn.prop('disabled', false);
        }
    });
}

// --- INITIALIZATION ---
$(document).ready(function() {
    $('.category-item:first').addClass('active');
    
    loadDraftOrdersFromDB(function(dbOrders) {
        let localOrders = loadDraftOrdersFromLocal();
        orders = mergeOrders(localOrders, dbOrders);
        
        if (orders.length > 0) {
            activeOrderId = orders[0].id;
        }
        
        isLoading = false;
        renderTabs();
        renderOrder();
        
        let firstCat = <?= json_encode($firstCat) ?>;
        if (firstCat) loadMenu(firstCat);
    });

    // Category click handler
    $(document).on('click', '.category-item', function() {
        $('.category-item').removeClass('active');
        $(this).addClass('active');
        loadMenu($(this).data('category'));
    });

    // Menu item click handler
    $(document).on('click', '.menu-item', function() {
        if (!activeOrderId) {
            alert('Please create or select an order first');
            return;
        }
        
        let order = orders.find(o => o.id === activeOrderId);
        if (!order) return;

        let id = $(this).data('id');
        let name = $(this).data('name');
        let price = parseFloat($(this).data('price'));

        let existing = order.items.find(item => item.id === id);
        if (existing) {
            existing.qty += 1;
        } else {
            order.items.push({id, name, price, qty: 1});
        }
        ordersChanged();
    });

    // Quantity buttons
    $(document).on('click', '.btn-qty-plus', function() {
        let order = orders.find(o => o.id === activeOrderId);
        let idx = $(this).data('index');
        if (order && order.items && order.items[idx]) {
            order.items[idx].qty += 1;
            ordersChanged();
        }
    });

    $(document).on('click', '.btn-qty-minus', function() {
        let order = orders.find(o => o.id === activeOrderId);
        let idx = $(this).data('index');
        if (order && order.items && order.items[idx]) {
            if (order.items[idx].qty > 1) {
                order.items[idx].qty -= 1;
            } else {
                order.items.splice(idx, 1);
            }
            ordersChanged();
        }
    });

    // New order button
    $('#btnNewOrder').click(function() {
        $('#orderTypeSelect').val("");
        $('#deliverySource').val("internal");
        $('#customerName').val("");
        $('#customerPhone').val("");
        $('#customerAddress').val("");
        $('.order-type-card').removeClass('border border-3 border-primary shadow');
        $('.delivery-source-card').removeClass('border border-3 border-warning shadow');
        $('#deliveryOptions').addClass('d-none');
        $('#customerAddress').addClass('d-none');
        $('#dineInFields').addClass('d-none');
        $('#initOrderModal').modal('show');
    });

    // Order type selection
    $(document).on('click', '.order-type-card', function() {
        $('.order-type-card').removeClass('border border-3 border-primary shadow');
        $(this).addClass('border border-3 border-primary shadow');
        let type = $(this).data('type');
        $('#orderTypeSelect').val(type);
        
        if(type === 'delivery'){
            $('#deliveryOptions').removeClass('d-none');
            $('#customerAddress').removeClass('d-none');
            $('#dineInFields').addClass('d-none');
        } else if(type === 'dine_in'){
            $('#deliveryOptions').addClass('d-none');
            $('#customerAddress').addClass('d-none');
            $('#dineInFields').removeClass('d-none');
            renderTableSelector();
        } else {
            $('#deliveryOptions').addClass('d-none');
            $('#customerAddress').addClass('d-none');
            $('#dineInFields').addClass('d-none');
        }
    });

    // Table selection
    $(document).on('click', '#tableSelector .table-btn', function() {
        $('#tableSelector .table-btn').removeClass('active');
        $(this).addClass('active');
        let table = $(this).data('table');
        if($('#tableNumber').length === 0) {
            $('<input type="hidden" id="tableNumber">').appendTo('#dineInFields');
        }
        $('#tableNumber').val(table);
    });

    // Delivery source selection
    $(document).on('click', '.delivery-source-card', function() {
        $('.delivery-source-card').removeClass('border border-3 border-warning shadow');
        $(this).addClass('border border-3 border-warning shadow');
        let source = $(this).data('source');
        $('#deliverySource').val(source);
    });

    // Confirm create order
    $('#confirmCreateOrder').off('click').on('click', function(){
        let type = $('#orderTypeSelect').val();
        if(!type) return alert('Select type');
        
            let order = {
                id: 'ORD' + Date.now(),
                type: type,
                delivery_source: $('#deliverySource').val(),
                customer: {
                    name: $('#customerName').val() || 'Guest',
                    phone: $('#customerPhone').val() || '',
                    address: $('#customerAddress').val() || ''
                },
                table_number: type === 'dine_in' ? $('#tableNumber').val() : null,
                num_customers: type === 'dine_in' ? $('#numCustomers').val() : null,
                items: [],
                order_status: 'pending'
            };
        
        orders.push(order);
        activeOrderId = order.id;
        ordersChanged();
        $('#initOrderModal').modal('hide');
    });



    // --- QZ Tray Integration for Printing Receipts ---
    // Improved: Wait for QZ Tray script to load before printing
    let qzTrayLoaded = false;
    let qzTrayLoading = false;
    function ensureQZTrayLoaded(callback) {
        if (window.qz) {
            qzTrayLoaded = true;
            callback();
            return;
        }
        if (qzTrayLoading) {
            setTimeout(() => ensureQZTrayLoaded(callback), 200);
            return;
        }
        qzTrayLoading = true;
        var qzScript = document.createElement('script');
        qzScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/qz-tray/2.1.0/qz-tray.js';
        qzScript.onload = function() {
            qzTrayLoaded = true;
            callback();
        };
        qzScript.onerror = function() {
            // Try loading from local QZ Tray websocket server as fallback
            var localScript = document.createElement('script');
            localScript.src = 'https://localhost:8181/qz-tray.js';
            localScript.onload = function() {
                qzTrayLoaded = true;
                callback();
            };
            localScript.onerror = function() {
                alert('Failed to load QZ Tray script from both CDN and local server.\nPlease ensure QZ Tray is running and accessible.');
            };
            document.head.appendChild(localScript);
        };
        document.head.appendChild(qzScript);
    }

    function printReceiptQZ(orderId, type) {
        ensureQZTrayLoaded(function() {
            const printerName = type === 'kitchen' ? 'XP-80C' : 'POS-80C';
            const url = `includes/print_receipt.php?id=${orderId}&type=${type}`;
            fetch(url)
                .then(res => res.text())
                .then(data => {
                    if (!window.qz) { alert('QZ Tray not available!'); return; }
                    qz.websocket.connect().then(() => qz.printers.find(printerName))
                    .then(printer => {
                        var config = qz.configs.create(printer, { encoding: 'UTF-8' });
                        var printData = [{ type: 'raw', format: 'plain', data: data }];
                        return qz.print(config, printData);
                    })
                    .catch(e => alert('QZ Print Error: ' + e));
                });
        });
    }

    // Print Receipt (Counter)
    $('#btnPrint').off('click').on('click', function() {
        if (!activeOrderId) return;
        let order = orders.find(o => o.id === activeOrderId);
        if (!order || order.items.length === 0) {
            alert('No items to print');
            return;
        }
        printReceiptQZ(order.id, 'counter');
    });

    // Send to Kitchen (Kitchen Printer)
    $('#btnSendKitchen').off('click').on('click', function() {
        if (!activeOrderId) return;
        let order = orders.find(o => o.id === activeOrderId);
        if (!order || order.items.length === 0) {
            alert('No items to send to kitchen');
            return;
        }
        printReceiptQZ(order.id, 'kitchen');
        order.order_status = 'in_preparation';
        ordersChanged();
        alert('Order sent to kitchen!');
    });

    // Discount handlers
    $('#discountAmount').on('input', function() {
        let val = parseFloat($(this).val()) || 0;
        if (val < 0) val = 0;
        discountAmount = val;
        renderOrder();
    });

    $('#discountTypeGroup .btn').click(function() {
        $('#discountTypeGroup .btn').removeClass('active');
        $(this).addClass('active');
        discountType = $(this).data('discount-type');
        
        if (discountType === 'fixed') {
            $('#discountAmount').attr('placeholder', 'Amount');
        } else {
            $('#discountAmount').attr('placeholder', 'Percentage');
        }
        
        renderOrder();
    });

    $('.discount-preset').click(function() {
        let preset = $(this).data('preset');
        $('#discountTypeGroup .btn[data-discount-type="percentage"]').click();
        $('#discountAmount').val(preset);
        discountAmount = preset;
        renderOrder();
    });

    // Hold order button
    $('#btnHoldOrder').click(function() {
        if (!activeOrderId) return;
        
        let order = orders.find(o => o.id === activeOrderId);
        if (order) {
            order.status = 'on_hold';
            ordersChanged();
            alert('Order placed on hold');
        }
    });

    // Add note button
    $('#btnAddNote').click(function() {
        if (!activeOrderId) return;
        
        let note = prompt('Enter order note:');
        if (note !== null) {
            let order = orders.find(o => o.id === activeOrderId);
            if (order) {
                if (!order.notes) order.notes = [];
                order.notes.push({
                    text: note,
                    timestamp: new Date().toISOString()
                });
                saveDraftOrders();
                alert('Note added');
            }
        }
    });

    // Cancel order button
    $('#btnCancelOrder').click(function() {
        if (!activeOrderId) return;
        showDeleteModal(activeOrderId);
    });

    // Main payment/close button - handles both states
    $('#btnPaymentAction').click(function() {
        if (!selectedPaymentMethod) {
            // No payment method selected yet - show payment modal
            openPaymentModal();
        } else {
            // Payment method already selected - close and save order
                let order = orders.find(o => o.id === activeOrderId);
                if (order) {
                    order.order_status = 'completed';
                }
                closeOrderAndSave();
        }
    });

    // Payment method selection in modal
    $(document).on('click', '.payment-method-card', function() {
        $('.payment-method-card').removeClass('selected border border-3 border-success shadow');
        $(this).addClass('selected border border-3 border-success shadow');
        selectedPaymentMethod = $(this).data('method');
        $('#selectedPaymentMethod').val(selectedPaymentMethod);
        
        // Show reference field for card payments
        if (selectedPaymentMethod === 'card' || selectedPaymentMethod === 'credit' || selectedPaymentMethod === 'debit') {
            $('#referenceField').show();
        } else {
            $('#referenceField').hide();
        }
        
        // Enable confirm button
        $('#confirmPaymentMethod').prop('disabled', false);
    });

    // Confirm payment method button
    $('#confirmPaymentMethod').click(function() {
        // Close modal
        $('#paymentMethodModal').modal('hide');

        // Update main button to "Save & Close Order"
        $('#btnPaymentAction')
            .removeClass('btn-success')
            .addClass('btn-primary')
            .html('<i class="bi bi-check2-circle me-2"></i>Save & Close Order');

        // For all payment methods, immediately close and save order
        setTimeout(function() {
            closeOrderAndSave();
        }, 300); // slight delay to allow modal to close smoothly
    });

    // Reset modal when hidden
    $('#paymentMethodModal').on('hidden.bs.modal', function() {
        // Don't reset selectedPaymentMethod if we're just closing after selection
        // Only reset if modal is closed without selection
        if (!$('#selectedPaymentMethod').val()) {
            // Modal was closed without selecting - do nothing
        }
    });

    // Delete confirmation
    $('#confirmDeleteBtn').click(function() {
        let orderId = $(this).data('order-id');
        let permanent = $('#permanentDeleteCheck').is(':checked');
        
        softDeleteOrder(orderId, permanent);
        $('#deleteOrderModal').modal('hide');
        $('#permanentDeleteCheck').prop('checked', false);
        
        // Reset payment button if the deleted order was active
        if (orderId === activeOrderId) {
            resetPaymentButton();
        }
    });

    // Undo delete
    $('#undoDeleteBtn').click(function() {
        if (lastDeletedOrder) {
            restoreOrder(lastDeletedOrder.id);
            $('#undoToast').fadeOut(300);
            lastDeletedOrder = null;
            if (undoTimeout) {
                clearTimeout(undoTimeout);
            }
        }
    });

    // Trash bin button
    $('#trashBinBtn').click(function() {
        loadDeletedOrders();
        $('#recoveryModal').modal('show');
    });

    // Empty trash button
    $('#emptyTrashBtn').click(function() {
        if (deletedOrders.length === 0) return;
        
        if (confirm(`Permanently delete ${deletedOrders.length} orders? This cannot be undone!`)) {
            alert('Bulk delete functionality - implement based on your needs');
        }
    });

    // Modal reset
    $('#deleteOrderModal').on('hidden.bs.modal', function() {
        $('#permanentDeleteCheck').prop('checked', false);
    });

    // Scroll position save
    $('.orders-tabs-card').on('scroll', function() {
        savedScrollPosition = this.scrollLeft;
    });
});

// Make functions globally available
window.removeItem = removeItem;
window.switchOrder = switchOrder;
window.showDeleteModal = showDeleteModal;
window.calculateOrderTotal = calculateOrderTotal;
window.resetPaymentButton = resetPaymentButton;
</script>