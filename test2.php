<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Get all active categories
$categories = [];
$cat_query = "SELECT id, name, sort_order FROM menu_categories WHERE is_active = 1 ORDER BY sort_order, name";
$cat_result = $connection->query($cat_query);
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get menu items for each category (will load via AJAX, but initial load for first category)
$menu_items = [];
if (!empty($categories)) {
    $first_category = $categories[0]['id'];
    $item_query = "SELECT id, name, price, image_url, is_available, available_quantity, 
                          is_daily_limited, track_inventory
                   FROM menu_items 
                   WHERE category_id = ? AND is_available = 1 
                   ORDER BY name";
    $stmt = $connection->prepare($item_query);
    $stmt->bind_param("i", $first_category);
    $stmt->execute();
    $item_result = $stmt->get_result();
    while ($row = $item_result->fetch_assoc()) {
        $menu_items[] = $row;
    }
    $stmt->close();
}

// Get pending orders for the board
$orders_query = "SELECT o.id, o.order_number, o.order_type, o.delivery_source, 
                        o.order_status, o.payment_status, o.total_amount,
                        o.customer_name_snapshot, o.table_number,
                        COUNT(oi.id) as item_count
                 FROM orders o
                 LEFT JOIN order_items oi ON o.id = oi.order_id
                 WHERE o.order_status IN ('pending', 'confirmed', 'in_preparation', 'ready', 'out_for_delivery')
                 GROUP BY o.id
                 ORDER BY FIELD(o.order_status, 'pending', 'confirmed', 'in_preparation', 'ready', 'out_for_delivery'), 
                          o.created_at DESC";
$orders_result = $connection->query($orders_query);
?>

<style>
/* Fix for sidebar and main content layout */
body {
    overflow-x: hidden;
}

.main-content {
    margin-left: 240px;
    width: calc(100% - 240px);
    transition: margin-left 0.3s, width 0.3s;
    padding: 20px;
    background: #f4f6f9;
    min-height: 100vh;
}

/* When sidebar is collapsed */
body.sidebar-collapsed .main-content {
    margin-left: 60px !important;
    width: calc(100% - 60px) !important;
}

/* POS Container Styles */
.pos-container {
    display: flex;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    height: calc(100vh - 140px);
    overflow: hidden;
}

/* Category Sidebar */
.category-sidebar {
    width: 220px;
    background: #2c3e50;
    color: white;
    padding: 20px 0;
    overflow-y: auto;
    border-radius: 10px 0 0 10px;
}

.category-panel-title {
    padding: 0 15px 15px 15px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 10px;
    color: #ecf0f1;
}

.category-item {
    padding: 12px 20px;
    cursor: pointer;
    transition: all 0.3s;
    border-left: 3px solid transparent;
    color: #ecf0f1;
}

.category-item:hover {
    background: rgba(255,255,255,0.1);
    border-left-color: #e74c3c;
}

.category-item.active {
    background: rgba(255,255,255,0.15);
    border-left-color: #e74c3c;
    font-weight: 500;
}

.category-item i {
    margin-right: 10px;
    width: 20px;
    color: #e67e22;
}

/* Menu Items Section */
.menu-items-section {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: white;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.menu-item-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.menu-item-card:hover:not(.disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #e67e22;
}

.menu-item-card.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f5f5f5;
}

.menu-item-card .price {
    color: #e74c3c;
    font-weight: bold;
    font-size: 1.2rem;
    margin-top: 10px;
}

.menu-item-card .stock-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #e74c3c;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
}

/* Order Summary Panel */
.order-summary {
    width: 350px;
    background: #2c3e50;
    color: white;
    display: flex;
    flex-direction: column;
    border-left: 1px solid rgba(255,255,255,0.1);
    border-radius: 0 10px 10px 0;
}

.order-header {
    padding: 20px;
    background: rgba(0,0,0,0.2);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.order-type-selector {
    display: flex;
    gap: 5px;
    margin: 15px 0 10px;
}

.type-btn {
    flex: 1;
    padding: 8px;
    border: 1px solid rgba(255,255,255,0.2);
    background: transparent;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.type-btn.active {
    background: #e74c3c;
    border-color: #e74c3c;
}

.type-btn:hover:not(.active) {
    background: rgba(255,255,255,0.1);
}

.delivery-source {
    margin: 10px 0;
    padding: 10px;
    background: rgba(0,0,0,0.2);
    border-radius: 5px;
}

.order-items {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: rgba(255,255,255,0.05);
    margin-bottom: 5px;
    border-radius: 5px;
    font-size: 0.9rem;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 500;
}

.item-meta {
    font-size: 0.8rem;
    color: #aaa;
}

.item-actions button {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 4px;
    cursor: pointer;
    margin-left: 3px;
}

.item-actions button:hover {
    background: rgba(255,255,255,0.2);
}

.order-totals {
    padding: 20px;
    background: rgba(0,0,0,0.3);
    border-top: 1px solid rgba(255,255,255,0.1);
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.grand-total {
    font-size: 1.3rem;
    font-weight: bold;
    color: #e67e22;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(255,255,255,0.2);
}

.order-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 15px;
}

.order-actions button {
    padding: 10px;
    border: none;
    border-radius: 5px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.order-actions button:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

#punch-new-order-btn {
    background: #27ae60;
    border: none;
    padding: 8px 15px;
    font-weight: 500;
    margin-bottom: 10px;
    width: 100%;
}

#punch-new-order-btn:hover {
    background: #2ecc71;
}

/* Orders Board */
.orders-board {
    display: flex;
    gap: 20px;
    padding: 20px 0;
    overflow-x: auto;
    min-height: 200px;
}

.order-column {
    min-width: 300px;
    background: white;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.column-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid;
}

.column-header.pending { border-color: #f1c40f; }
.column-header.confirmed { border-color: #3498db; }
.column-header.in_preparation { border-color: #e67e22; }
.column-header.ready { border-color: #27ae60; }
.column-header.out_for_delivery { border-color: #9b59b6; }

.order-pill {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.order-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.order-pill.pending { border-left: 4px solid #f1c40f; }
.order-pill.confirmed { border-left: 4px solid #3498db; }
.order-pill.in_preparation { border-left: 4px solid #e67e22; }
.order-pill.ready { border-left: 4px solid #27ae60; }
.order-pill.out_for_delivery { border-left: 4px solid #9b59b6; }

.order-pill-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.order-number {
    font-weight: bold;
    color: #e74c3c;
}

.order-type-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    background: #e9ecef;
}

.vendor-badge {
    background: #e67e22;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    margin-left: 5px;
}

.payment-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}

.payment-indicator.paid { background: #27ae60; }
.payment-indicator.unpaid { background: #e74c3c; }
.payment-indicator.vendor_settled { background: #e67e22; }

/* Responsive */
@media (max-width: 991.98px) {
    .main-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    
    .pos-container {
        flex-direction: column;
        height: auto;
    }
    
    .category-sidebar {
        width: 100%;
        border-radius: 10px 10px 0 0;
    }
    
    .order-summary {
        width: 100%;
        border-radius: 0 0 10px 10px;
    }
}
</style>

<!-- Main POS Container -->
<div class="pos-container">
    
    <!-- Category Sidebar -->
    <div class="category-sidebar">
        <div class="category-panel-title">
            <i class="bi bi-list-ul me-2"></i> Categories
        </div>
        <div class="category-item active" data-category="all">
            <i class="bi bi-grid"></i> All Items
        </div>
        <?php foreach ($categories as $category): ?>
        <div class="category-item" data-category="<?php echo $category['id']; ?>">
            <i class="bi bi-tag"></i> <?php echo htmlspecialchars($category['name']); ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Menu Items Section -->
    <div class="menu-items-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <span id="current-category">All Items</span>
                <small class="text-muted ms-2" id="item-count"></small>
            </h4>
            <div class="search-box">
                <input type="text" class="form-control" id="search-menu" placeholder="Search items...">
            </div>
        </div>

        <div class="menu-grid" id="menu-grid">
            <?php foreach ($menu_items as $item): ?>
            <?php 
            $disabled = '';
            $stock_status = '';
            if ($item['track_inventory'] && $item['available_quantity'] !== null && $item['available_quantity'] <= 0) {
                $disabled = 'disabled';
                $stock_status = 'out-of-stock';
            } elseif ($item['is_daily_limited'] && $item['available_quantity'] !== null && $item['available_quantity'] < 5) {
                $stock_status = 'low-stock';
            }
            ?>
            <div class="menu-item-card <?php echo $disabled; ?>" 
                 data-id="<?php echo $item['id']; ?>"
                 data-name="<?php echo htmlspecialchars($item['name']); ?>"
                 data-price="<?php echo $item['price']; ?>"
                 data-stock="<?php echo $item['available_quantity']; ?>"
                 data-track="<?php echo $item['track_inventory']; ?>">
                <?php if ($stock_status == 'low-stock'): ?>
                <span class="stock-badge">Low: <?php echo $item['available_quantity']; ?></span>
                <?php elseif ($stock_status == 'out-of-stock'): ?>
                <span class="stock-badge">Out of Stock</span>
                <?php endif; ?>
                
                <?php if ($item['image_url']): ?>
                <img src="../<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="img-fluid mb-2" style="height: 80px; object-fit: cover;">
                <?php else: ?>
                <i class="bi bi-egg-fried" style="font-size: 3rem; color: #e0e0e0;"></i>
                <?php endif; ?>
                
                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                <div class="price"><?php echo number_format($item['price'], 2); ?> SAR</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order Summary Panel -->
    <div class="order-summary">
        <div class="order-header">
            <button class="btn btn-success w-100 mb-3" id="punch-new-order-btn" type="button">
                <i class="bi bi-plus-circle"></i> Punch New Order
            </button>
            
            <h5 class="mb-3"><i class="bi bi-cart3"></i> Current Order</h5>
            
            <!-- Order Type Selection -->
            <div class="order-type-selector">
                <button type="button" class="type-btn active" data-type="dine_in">
                    <i class="bi bi-shop"></i> Dine In
                </button>
                <button type="button" class="type-btn" data-type="pickup">
                    <i class="bi bi-bag"></i> Pickup
                </button>
                <button type="button" class="type-btn" data-type="delivery">
                    <i class="bi bi-truck"></i> Delivery
                </button>
            </div>

            <!-- Delivery Source (for delivery orders) -->
            <div class="delivery-source" id="delivery-source-container" style="display: none;">
                <label class="form-label text-white-50 small">Delivery Source</label>
                <select class="form-select form-select-sm" id="delivery_source">
                    <option value="internal">Internal</option>
                    <option value="noon">Noon</option>
                    <option value="deliveroo">Deliveroo</option>
                    <option value="keeta">Keeta</option>
                    <option value="smile">Smile</option>
                </select>
            </div>

            <!-- Table Number (for dine in) -->
            <div class="mt-2" id="table-number-container">
                <input type="text" class="form-control form-control-sm" id="table_number" 
                       placeholder="Table Number (optional)">
            </div>

            <!-- Customer Details (for delivery) -->
            <div id="delivery-details" style="display: none;">
                <hr class="bg-white opacity-25">
                <div class="mb-2">
                    <input type="text" class="form-control form-control-sm mb-2" id="customer_phone" 
                           placeholder="Customer Phone *">
                    <input type="text" class="form-control form-control-sm mb-2" id="customer_name" 
                           placeholder="Customer Name">
                    <textarea class="form-control form-control-sm" id="delivery_address" 
                              placeholder="Delivery Address" rows="2"></textarea>
                </div>
            </div>

            <input type="hidden" id="current_order_id" value="">
        </div>

        <!-- Order Items -->
        <div class="order-items" id="order-items">
            <div class="text-center text-white-50 py-4">
                <i class="bi bi-cart" style="font-size: 3rem;"></i>
                <p class="mt-2">Click on items to add to order</p>
            </div>
        </div>

        <!-- Order Totals -->
        <div class="order-totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span id="subtotal">0.00 SAR</span>
            </div>
            <div class="total-row">
                <span>Tax (15%):</span>
                <span id="tax">0.00 SAR</span>
            </div>
            <div class="total-row">
                <span>Delivery Fee:</span>
                <span id="delivery-fee">0.00 SAR</span>
            </div>
            <div class="total-row">
                <span>Discount:</span>
                <span id="discount">0.00 SAR</span>
            </div>
            <div class="grand-total">
                <span>Total:</span>
                <span id="grand-total">0.00 SAR</span>
            </div>

            <!-- Payment Section -->
            <div class="mt-3">
                <label class="form-label text-white-50 small">Payment Method</label>
                <select class="form-select form-select-sm mb-2" id="payment_method">
                    <option value="cash">Cash</option>
                    <option value="card_gateway">Card Gateway</option>
                    <option value="pos_card">POS Card</option>
                    <option value="vendor_debit">Vendor Debit</option>
                </select>
            </div>

            <!-- Order Actions -->
            <div class="order-actions">
                <button class="btn btn-warning" id="save-draft-btn">
                    <i class="bi bi-save"></i> Draft
                </button>
                <button class="btn btn-success" id="place-order-btn">
                    <i class="bi bi-check-circle"></i> Place
                </button>
                <button class="btn btn-danger" id="clear-order-btn">
                    <i class="bi bi-trash"></i> Clear
                </button>
                <button class="btn btn-info" id="hold-order-btn">
                    <i class="bi bi-pause"></i> Hold
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Orders Board -->
<div class="mt-4">
    <h5><i class="bi bi-kanban"></i> Active Orders</h5>
    <div class="orders-board" id="orders-board">
        <?php
        $status_columns = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'in_preparation' => 'In Preparation',
            'ready' => 'Ready',
            'out_for_delivery' => 'Out for Delivery'
        ];
        
        if ($orders_result) {
            $orders_result->data_seek(0);
            $all_orders = [];
            while ($row = $orders_result->fetch_assoc()) {
                $all_orders[] = $row;
            }
        } else {
            $all_orders = [];
        }
        
        foreach ($status_columns as $status => $label):
            $status_orders = array_filter($all_orders, function($order) use ($status) {
                return $order['order_status'] == $status;
            });
        ?>
        <div class="order-column">
            <div class="column-header <?php echo $status; ?>">
                <h6 class="mb-0"><?php echo $label; ?></h6>
                <span class="badge bg-secondary"><?php echo count($status_orders); ?></span>
            </div>
            <div class="order-pills-container" data-status="<?php echo $status; ?>">
                <?php foreach ($status_orders as $order): ?>
                <div class="order-pill <?php echo $status; ?>" onclick="openOrderDetails(<?php echo $order['id']; ?>)">
                    <div class="order-pill-header">
                        <span class="order-number">#<?php echo $order['order_number']; ?></span>
                        <span class="order-type-badge">
                            <?php 
                            $type_icons = [
                                'dine_in' => '<i class="bi bi-shop"></i>',
                                'pickup' => '<i class="bi bi-bag"></i>',
                                'delivery' => '<i class="bi bi-truck"></i>'
                            ];
                            echo $type_icons[$order['order_type']] ?? '';
                            ?>
                            <?php echo ucfirst($order['order_type']); ?>
                            <?php if ($order['delivery_source'] != 'internal'): ?>
                            <span class="vendor-badge"><?php echo $order['delivery_source']; ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($order['customer_name_snapshot'] ?? 'Guest'); ?></strong>
                            <?php if ($order['table_number']): ?>
                            <span class="badge bg-secondary">Table <?php echo $order['table_number']; ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="payment-indicator <?php echo $order['payment_status']; ?>" 
                              title="<?php echo ucfirst($order['payment_status']); ?>"></span>
                    </div>
                    <div class="small text-muted">
                        <?php echo $order['item_count']; ?> items • 
                        <?php echo number_format($order['total_amount'], 2); ?> SAR
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Item Quantity Modal -->
<div class="modal fade" id="itemQuantityModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="modal-item-name"></h6>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" id="decrease-qty">-</button>
                        <input type="number" class="form-control text-center" id="item-quantity" value="1" min="1" max="99">
                        <button class="btn btn-outline-secondary" type="button" id="increase-qty">+</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Special Instructions</label>
                    <textarea class="form-control" id="item-instructions" rows="2" 
                              placeholder="Any special requests?"></textarea>
                </div>
                <input type="hidden" id="modal-item-id">
                <input type="hidden" id="modal-item-price">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add-to-order-btn">Add to Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="order-details-content">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p>Loading order details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" onclick="reprintReceipt()">Reprint</button>
                <button type="button" class="btn btn-danger" onclick="cancelOrder()">Cancel Order</button>
                <button type="button" class="btn btn-success" onclick="updateOrderStatus()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="orderSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="orderToastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
let currentOrder = {
    id: null,
    type: 'dine_in',
    delivery_source: 'internal',
    table_number: '',
    customer: {
        phone: '',
        name: '',
        address: ''
    },
    items: [],
    payment_method: 'cash',
    subtotal: 0,
    tax: 0,
    delivery_fee: 0,
    discount: 0,
    total: 0
};

let itemQuantityModal;
let currentItem = null;

$(document).ready(function() {
    itemQuantityModal = new bootstrap.Modal(document.getElementById('itemQuantityModal'));
    
    // Load draft if exists
    checkForDraft();
    
    // Category click handler
    $('.category-item').click(function() {
        $('.category-item').removeClass('active');
        $(this).addClass('active');
        const categoryId = $(this).data('category');
        loadMenuItems(categoryId);
    });
    
    // Menu item click handler
    $(document).on('click', '.menu-item-card:not(.disabled)', function() {
        const item = {
            id: $(this).data('id'),
            name: $(this).data('name'),
            price: $(this).data('price'),
            stock: $(this).data('stock'),
            track: $(this).data('track')
        };
        
        $('#modal-item-name').text(item.name);
        $('#modal-item-id').val(item.id);
        $('#modal-item-price').val(item.price);
        $('#item-quantity').val(1);
        $('#item-instructions').val('');
        
        currentItem = item;
        itemQuantityModal.show();
    });
    
    // Add to order button
    $('#add-to-order-btn').click(function() {
        addItemToOrder({
            id: $('#modal-item-id').val(),
            name: $('#modal-item-name').text(),
            price: parseFloat($('#modal-item-price').val()),
            quantity: parseInt($('#item-quantity').val()),
            instructions: $('#item-instructions').val()
        });
        itemQuantityModal.hide();
        triggerAutoSave();
    });
    
    // Quantity buttons
    $('#decrease-qty').click(() => {
        let qty = parseInt($('#item-quantity').val()) || 1;
        if (qty > 1) $('#item-quantity').val(qty - 1);
    });
    
    $('#increase-qty').click(() => {
        let qty = parseInt($('#item-quantity').val()) || 1;
        if (qty < 99) $('#item-quantity').val(qty + 1);
    });
    
    // Order type change
    $('.type-btn').click(function() {
        $('.type-btn').removeClass('active');
        $(this).addClass('active');
        const type = $(this).data('type');
        currentOrder.type = type;
        
        // Show/hide relevant fields
        if (type === 'delivery') {
            $('#delivery-source-container').show();
            $('#delivery-details').show();
            $('#table-number-container').hide();
        } else if (type === 'dine_in') {
            $('#delivery-source-container').hide();
            $('#delivery-details').hide();
            $('#table-number-container').show();
        } else {
            $('#delivery-source-container').hide();
            $('#delivery-details').hide();
            $('#table-number-container').hide();
        }
        
        triggerAutoSave();
    });
    
    // Delivery source change
    $('#delivery_source').change(function() {
        currentOrder.delivery_source = $(this).val();
        triggerAutoSave();
    });
    
    // Table number change
    $('#table_number').on('input', function() {
        currentOrder.table_number = $(this).val();
        triggerAutoSave();
    });
    
    // Customer fields
    $('#customer_phone, #customer_name, #delivery_address').on('input', function() {
        currentOrder.customer.phone = $('#customer_phone').val();
        currentOrder.customer.name = $('#customer_name').val();
        currentOrder.customer.address = $('#delivery_address').val();
        triggerAutoSave();
    });
    
    // Payment method change
    $('#payment_method').change(function() {
        currentOrder.payment_method = $(this).val();
    });
    
    // Punch New Order button
    $('#punch-new-order-btn').click(function() {
        if (currentOrder.items.length > 0) {
            if (confirm('Start a new order? Current order will be saved as draft.')) {
                saveOrderDraft(true);
                clearOrder();
                showSuccess('New order started');
            }
        } else {
            clearOrder();
            showSuccess('Ready for new order');
        }
    });
    
    // Place order
    $('#place-order-btn').click(function() {
        placeOrder();
    });
    
    // Save draft
    $('#save-draft-btn').click(function() {
        saveOrderDraft(true);
    });
    
    // Clear order
    $('#clear-order-btn').click(function() {
        if (confirm('Are you sure you want to clear the current order?')) {
            clearOrder();
            showSuccess('Order cleared');
        }
    });
    
    // Hold order
    $('#hold-order-btn').click(function() {
        if (currentOrder.items.length > 0) {
            saveOrderDraft(true);
            clearOrder();
            showSuccess('Order saved and held');
        }
    });
    
    // Search
    $('#search-menu').on('input', function() {
        const search = $(this).val().toLowerCase();
        $('.menu-item-card').each(function() {
            const name = $(this).data('name').toLowerCase();
            $(this).toggle(name.includes(search));
        });
    });
});

// Auto-save timer
let autoSaveTimer;
const DRAFT_SAVE_DELAY = 3000;

function triggerAutoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        saveOrderDraft(false);
    }, DRAFT_SAVE_DELAY);
}

function loadMenuItems(categoryId) {
    $('#menu-grid').html('<div class="text-center py-4"><div class="spinner-border"></div><p>Loading items...</p></div>');
    
    $.ajax({
        url: 'includes/ajax/get_menu_items.php',
        method: 'GET',
        data: { category_id: categoryId },
        success: function(response) {
            if (response.success) {
                renderMenuItems(response.items);
                $('#current-category').text(response.category_name);
                $('#item-count').text(`(${response.items.length} items)`);
            }
        }
    });
}

function renderMenuItems(items) {
    let html = '';
    items.forEach(item => {
        const disabled = item.track_inventory && item.available_quantity !== null && item.available_quantity <= 0 ? 'disabled' : '';
        const stockStatus = item.is_daily_limited && item.available_quantity !== null && item.available_quantity < 5 ? 'low-stock' : '';
        
        html += `
        <div class="menu-item-card ${disabled}" 
             data-id="${item.id}"
             data-name="${item.name}"
             data-price="${item.price}"
             data-stock="${item.available_quantity || ''}"
             data-track="${item.track_inventory}">
            ${stockStatus === 'low-stock' ? `<span class="stock-badge">Low: ${item.available_quantity}</span>` : ''}
            ${disabled ? '<span class="stock-badge">Out of Stock</span>' : ''}
            ${item.image_url ? 
                `<img src="../${item.image_url}" alt="${item.name}" class="img-fluid mb-2" style="height: 80px; object-fit: cover;">` :
                `<i class="bi bi-egg-fried" style="font-size: 3rem; color: #e0e0e0;"></i>`
            }
            <h6 class="mb-1">${item.name}</h6>
            <div class="price">${parseFloat(item.price).toFixed(2)} SAR</div>
        </div>
        `;
    });
    $('#menu-grid').html(html);
}

function addItemToOrder(item) {
    currentOrder.items.push({
        id: item.id,
        name: item.name,
        price: item.price,
        quantity: item.quantity,
        instructions: item.instructions,
        total: item.price * item.quantity
    });
    
    renderOrderItems();
    calculateTotals();
    triggerAutoSave();
}

function renderOrderItems() {
    const container = $('#order-items');
    
    if (currentOrder.items.length === 0) {
        container.html(`
            <div class="text-center text-white-50 py-4">
                <i class="bi bi-cart" style="font-size: 3rem;"></i>
                <p class="mt-2">Click on items to add to order</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    currentOrder.items.forEach((item, index) => {
        html += `
        <div class="order-item" data-index="${index}">
            <div class="item-details">
                <div class="item-name">${item.name}</div>
                <div class="item-meta">
                    ${item.quantity} x ${item.price.toFixed(2)} SAR
                    ${item.instructions ? `<br><small class="text-warning">Note: ${item.instructions}</small>` : ''}
                </div>
            </div>
            <div class="item-total fw-bold">${item.total.toFixed(2)} SAR</div>
            <div class="item-actions">
                <button class="btn-sm" onclick="editItem(${index})" title="Edit"><i class="bi bi-pencil"></i></button>
                <button class="btn-sm" onclick="removeItem(${index})" title="Remove"><i class="bi bi-x"></i></button>
            </div>
        </div>
        `;
    });
    
    container.html(html);
}

function calculateTotals() {
    currentOrder.subtotal = currentOrder.items.reduce((sum, item) => sum + item.total, 0);
    currentOrder.tax = currentOrder.subtotal * 0.15;
    currentOrder.delivery_fee = currentOrder.type === 'delivery' ? 10 : 0;
    currentOrder.total = currentOrder.subtotal + currentOrder.tax + currentOrder.delivery_fee - currentOrder.discount;
    
    $('#subtotal').text(currentOrder.subtotal.toFixed(2) + ' SAR');
    $('#tax').text(currentOrder.tax.toFixed(2) + ' SAR');
    $('#delivery-fee').text(currentOrder.delivery_fee.toFixed(2) + ' SAR');
    $('#discount').text(currentOrder.discount.toFixed(2) + ' SAR');
    $('#grand-total').text(currentOrder.total.toFixed(2) + ' SAR');
}

function removeItem(index) {
    currentOrder.items.splice(index, 1);
    renderOrderItems();
    calculateTotals();
    triggerAutoSave();
}

function editItem(index) {
    const item = currentOrder.items[index];
    $('#modal-item-name').text(item.name);
    $('#modal-item-id').val(item.id);
    $('#modal-item-price').val(item.price);
    $('#item-quantity').val(item.quantity);
    $('#item-instructions').val(item.instructions || '');
    
    currentItem = { id: item.id, name: item.name, price: item.price };
    
    // Remove old item and add edited one
    currentOrder.items.splice(index, 1);
    itemQuantityModal.show();
}

function placeOrder() {
    if (currentOrder.items.length === 0) {
        alert('Please add items to the order');
        return;
    }
    
    if (currentOrder.type === 'delivery' && !currentOrder.customer.phone) {
        alert('Customer phone is required for delivery orders');
        return;
    }
    
    const btn = $('#place-order-btn');
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
    btn.prop('disabled', true);
    
    $.ajax({
        url: 'includes/ajax/place_order.php',
        method: 'POST',
        data: JSON.stringify(currentOrder),
        contentType: 'application/json',
        success: function(response) {
            btn.html('<i class="bi bi-check-circle"></i> Place');
            btn.prop('disabled', false);
            
            if (response.success) {
                showSuccess('Order placed successfully!');
                
                if (response.print_kitchen) {
                    printKitchenReceipt(response.order_id);
                }
                
                clearOrder();
                refreshOrdersBoard();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            btn.html('<i class="bi bi-check-circle"></i> Place');
            btn.prop('disabled', false);
            alert('Server error. Please try again.');
        }
    });
}

function printKitchenReceipt(orderId) {
    window.open(`orders.php?source=print_receipt&id=${orderId}&type=kitchen`, '_blank');
}

function openOrderDetails(orderId) {
    $('#order-details-content').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p>Loading order details...</p>
        </div>
    `);
    
    $('#orderDetailsModal').modal('show');
    
    $.ajax({
        url: 'includes/ajax/get_order_details.php',
        method: 'GET',
        data: { order_id: orderId },
        success: function(response) {
            $('#order-details-content').html(response);
        }
    });
}

function showSuccess(message) {
    $('#orderToastMessage').text(message);
    const toast = new bootstrap.Toast(document.getElementById('orderSuccessToast'));
    toast.show();
}

function clearOrder() {
    currentOrder = {
        id: null,
        type: 'dine_in',
        delivery_source: 'internal',
        table_number: '',
        customer: {
            phone: '',
            name: '',
            address: ''
        },
        items: [],
        payment_method: 'cash',
        subtotal: 0,
        tax: 0,
        delivery_fee: 0,
        discount: 0,
        total: 0
    };
    
    renderOrderItems();
    calculateTotals();
    $('#customer_phone, #customer_name, #delivery_address, #table_number').val('');
    $('#delivery_source').val('internal');
    
    localStorage.removeItem('pos_order_draft');
}

function checkForDraft() {
    const draft = localStorage.getItem('pos_order_draft');
    if (draft) {
        if (confirm('You have an unsaved draft order. Would you like to restore it?')) {
            currentOrder = JSON.parse(draft);
            
            $(`.type-btn[data-type="${currentOrder.type}"]`).click();
            $('#delivery_source').val(currentOrder.delivery_source);
            $('#table_number').val(currentOrder.table_number);
            $('#customer_phone').val(currentOrder.customer.phone);
            $('#customer_name').val(currentOrder.customer.name);
            $('#delivery_address').val(currentOrder.customer.address);
            $('#payment_method').val(currentOrder.payment_method);
            
            renderOrderItems();
            calculateTotals();
        } else {
            localStorage.removeItem('pos_order_draft');
        }
    }
}

function saveOrderDraft(showMessage = true) {
    localStorage.setItem('pos_order_draft', JSON.stringify(currentOrder));
    
    if (currentOrder.items.length > 0) {
        $.ajax({
            url: 'includes/ajax/save_order_draft.php',
            method: 'POST',
            data: JSON.stringify(currentOrder),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    currentOrder.id = response.order_id;
                    if (showMessage) {
                        showSuccess('Draft saved');
                    }
                }
            }
        });
    }
}

function refreshOrdersBoard() {
    $.ajax({
        url: 'includes/ajax/get_active_orders.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                location.reload(); // Simple reload for now
            }
        }
    });
}

// Listen for sidebar toggle
document.addEventListener('DOMContentLoaded', function() {
    function updateSidebarState() {
        var sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        var isCollapsed = sidebar.classList.contains('collapsed') || sidebar.offsetWidth < 100;
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
        } else {
            document.body.classList.remove('sidebar-collapsed');
        }
    }

    updateSidebarState();

    var sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            setTimeout(updateSidebarState, 350);
        });
    }

    window.addEventListener('resize', updateSidebarState);
});

// Auto-refresh orders board every 30 seconds
setInterval(refreshOrdersBoard, 30000);
</script>


==============================================================


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
?>

<div class="main-content">
<div class="container-fluid">

<!-- ================= HEADER ================= -->

<div class="d-flex align-items-center mb-3">
    <button class="btn btn-success me-3" id="btnNewOrder">
        <i class="bi bi-plus-circle"></i> Punch New Order
    </button>

    <div id="ordersTabs" class="d-flex flex-row gap-2 overflow-auto"></div>
</div>

<!-- ================= POS CONTAINER ================= -->

<div class="pos-container">

    <!-- 1️⃣ CATEGORY PANEL -->
    <div class="category-panel">
        <div class="category-item active" data-category="all">All Items</div>
        <?php while($cat = $categories->fetch_assoc()): ?>
            <div class="category-item" data-category="<?= $cat['id'] ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- 2️⃣ MENU PANEL -->
    <div class="menu-panel">
        <div id="menuItems" class="row g-2"></div>
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

        <div class="mt-auto border-top pt-2">
            <h5>Total: <span id="orderTotal">0.00</span> AED</h5>

            <div class="d-flex gap-2">
                <button class="btn btn-warning w-50" id="btnSendKitchen" disabled>
                    Send to Kitchen
                </button>
                <button class="btn btn-primary w-50" id="btnPrint" disabled>
                    Print Receipt
                </button>
            </div>
        </div>

    </div>

</div>
</div>
</div>


<!-- ================= MODAL ================= -->

<div class="modal fade" id="initOrderModal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
    <h5>Create New Order</h5>
</div>
<div class="modal-body">

<select class="form-select mb-3" id="orderTypeSelect">
    <option value="">Select Order Type</option>
    <option value="dine_in">Dine In</option>
    <option value="pickup">Pickup</option>
    <option value="delivery">Delivery</option>
</select>

<div id="deliveryOptions" class="d-none">
    <select class="form-select mb-2" id="deliverySource">
        <option value="internal">Restaurant Delivery</option>
        <option value="noon">Noon</option>
        <option value="keeta">Keeta</option>
        <option value="deliveroo">Deliveroo</option>
        <option value="smile">Smile</option>
    </select>
</div>

<input type="text" id="customerName" class="form-control mb-2" placeholder="Customer Name">
<input type="text" id="customerPhone" class="form-control mb-2" placeholder="Phone">
<input type="text" id="customerAddress" class="form-control mb-2 d-none" placeholder="Address">

</div>
<div class="modal-footer">
    <button class="btn btn-success" id="confirmCreateOrder">Create Order</button>
</div>
</div>
</div>
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
}
.category-item.active{
    background:#34495e;
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
}
.order-panel{
    flex:1;
    padding:10px;
    display:flex;
    flex-direction:column;
    border-left:1px solid #ddd;
}
.order-tab{
    padding:6px 12px;
    background:#eee;
    border-radius:6px;
    cursor:pointer;
}
.order-tab.active{
    background:#fff;
    border:1px solid #ddd;
}
</style>



<!-- Move script after footer to ensure Bootstrap and jQuery are loaded -->
<?php include 'footer.php'; ?>
<script>
let orders = [];
let activeOrderId = null;

function renderTabs(){
    $('#ordersTabs').html('');
    orders.forEach(order=>{
        let active = order.id===activeOrderId?'active':'';
        $('#ordersTabs').append(`
            <div class="order-tab ${active}" onclick="switchOrder('${order.id}')">
                ${order.type.toUpperCase()} - ${order.customer.name}
            </div>
        `);
    });
}

function switchOrder(id){
    activeOrderId=id;
    renderTabs();
    renderOrder();
}

function renderOrder(){
    let order=orders.find(o=>o.id===activeOrderId);
    if(!order)return;

    let body=$('#orderItemsBody');
    body.html('');
    let total=0;

    order.items.forEach((item,i)=>{
        let line=item.qty*item.price;
        total+=line;
        body.append(`
        <tr>
            <td>${item.name}</td>
            <td>${item.qty}</td>
            <td>${item.price.toFixed(2)}</td>
            <td>${line.toFixed(2)}</td>
            <td><button class="btn btn-sm btn-danger" onclick="removeItem(${i})">×</button></td>
        </tr>
        `);
    });

    if(order.items.length===0){
        body.html(`<tr><td colspan="5" class="text-center text-muted">No items added</td></tr>`);
    }

    $('#orderTotal').text(total.toFixed(2));
    $('#btnSendKitchen,#btnPrint').prop('disabled',order.items.length===0);
}

function removeItem(i){
    let order=orders.find(o=>o.id===activeOrderId);
    order.items.splice(i,1);
    renderOrder();
}

function loadMenu(category){
    $.get('includes/get_menu_items.php',{category_id:category},function(data){
        $('#menuItems').html(data);
    });
}

$(document).on('click','.category-item',function(){
    $('.category-item').removeClass('active');
    $(this).addClass('active');
    loadMenu($(this).data('category'));
});

$(document).on('click','.menu-item',function(){
    let order=orders.find(o=>o.id===activeOrderId);
    if(!order)return alert('Create order first');

    let id=$(this).data('id');
    let name=$(this).data('name');
    let price=parseFloat($(this).data('price'));

    order.items.push({id,name,price,qty:1});
    renderOrder();
});

$('#btnNewOrder').click(()=>$('#initOrderModal').modal('show'));

$('#orderTypeSelect').change(function(){
    if($(this).val()==='delivery'){
        $('#deliveryOptions').removeClass('d-none');
        $('#customerAddress').removeClass('d-none');
    }else{
        $('#deliveryOptions,#customerAddress').addClass('d-none');
    }
});

$('#confirmCreateOrder').click(function(){
    let type=$('#orderTypeSelect').val();
    if(!type)return alert('Select type');

    let order={
        id:'ORD'+Date.now(),
        type:type,
        delivery_source:$('#deliverySource').val(),
        customer:{
            name:$('#customerName').val(),
            phone:$('#customerPhone').val(),
            address:$('#customerAddress').val()
        },
        items:[]
    };

    orders.push(order);
    activeOrderId=order.id;
    renderTabs();
    renderOrder();
    $('#initOrderModal').modal('hide');
});

$('#btnPrint').click(function(){
    let order=orders.find(o=>o.id===activeOrderId);
    if(!order)return;
    window.open('print_receipt.php?temp_id='+order.id+'&type=counter','_blank');
});

$('#btnSendKitchen').click(function(){
    let order=orders.find(o=>o.id===activeOrderId);
    if(!order)return;
    window.open('print_receipt.php?temp_id='+order.id+'&type=kitchen','_blank');
});

loadMenu('all');
</script>
