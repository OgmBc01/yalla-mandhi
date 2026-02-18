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

<div class="d-flex align-items-center mb-3">
    <button class="btn btn-theme-gradient me-3" id="btnNewOrder" style="background: linear-gradient(135deg, #c41e3a 0%, #f39c12 100%); color: #fff; border-radius: 14px; border: none; font-size: 1.1rem; font-weight: 600; box-shadow: 0 4px 16px rgba(196,30,58,0.12); padding: 12px 24px;">
        <i class="bi bi-plus-circle display-6 me-2"></i> Punch New Order
    </button>

    <div id="ordersTabsContainer" class="orders-tabs-card" style="background:#fff;border-radius:12px;padding:12px 8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);overflow-x:auto;white-space:nowrap;scroll-behavior:smooth;">
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
</style>

<!-- Add Bootstrap and jQuery scripts since footer is not included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let orders = [];
let activeOrderId = null;
let isLoading = true; // Flag to prevent rendering before data is loaded
let savedScrollPosition = 0; // Store scroll position

// --- DRAFT ORDER PERSISTENCE FUNCTIONS ---
function saveDraftOrders() {
    // Save to localStorage
    localStorage.setItem('pos_orders', JSON.stringify(orders));
    
    // Save to server
    orders.forEach(order => {
        $.ajax({
            url: 'includes/pos_order_drafts.php',
            method: 'POST',
            data: { 
                action: 'save', 
                order: JSON.stringify(order) 
            },
            success: function(response) {
                console.log('Order saved:', order.id);
            },
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
    
    // Add DB orders first (server is source of truth)
    db.forEach(o => {
        if (o && o.id) {
            map[o.id] = o;
        }
    });
    
    // Merge local orders, preferring local items if they exist
    local.forEach(o => {
        if (!o || !o.id) return;
        
        if (!map[o.id]) {
            map[o.id] = o;
        } else {
            // If local has items and DB doesn't, use local items
            if (o.items && o.items.length > 0) {
                if (!map[o.id].items || map[o.id].items.length === 0) {
                    map[o.id].items = o.items;
                } else if (o.items.length > map[o.id].items.length) {
                    // Prefer the one with more items (more recent activity)
                    map[o.id].items = o.items;
                }
            }
        }
    });
    
    return Object.values(map);
}

// Function to save current scroll position
function saveScrollPosition() {
    let container = document.querySelector('.orders-tabs-card');
    if (container) {
        savedScrollPosition = container.scrollLeft;
    }
}

// Function to restore scroll position
function restoreScrollPosition() {
    let container = document.querySelector('.orders-tabs-card');
    if (container) {
        container.scrollLeft = savedScrollPosition;
    }
}

// Function to scroll active tab into view
function scrollActiveTabIntoView() {
    setTimeout(() => {
        let container = document.querySelector('.orders-tabs-card');
        let activeTab = document.querySelector('.order-tab.active');
        
        if (container && activeTab) {
            let containerRect = container.getBoundingClientRect();
            let tabRect = activeTab.getBoundingClientRect();
            
            // Calculate if tab is outside visible area
            let tabLeft = tabRect.left - containerRect.left + container.scrollLeft;
            let tabRight = tabLeft + tabRect.width;
            
            if (tabLeft < container.scrollLeft) {
                // Tab is to the left of visible area
                container.scrollLeft = tabLeft - 20; // 20px padding
            } else if (tabRight > container.scrollLeft + container.clientWidth) {
                // Tab is to the right of visible area
                container.scrollLeft = tabRight - container.clientWidth + 20;
            }
        }
    }, 50); // Small delay to ensure DOM is updated
}

// --- RENDERING FUNCTIONS ---
function renderTabs() {
    // Save current scroll position before re-rendering
    saveScrollPosition();
    
    let html = '';
    orders.forEach(order => {
        if (!order || !order.id) return;
        
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
                 style="${typeColor}margin-right:8px;min-width:180px;display:inline-block;cursor:pointer;border-radius:8px;padding:8px 16px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid ${active ? "#c41e3a" : "transparent"};" 
                 onclick="switchOrder('${order.id}')">
                <div style="font-weight:600;">
                    ${typeIcon}${order.type.toUpperCase()} - ${customerName} ${deliveryBadge}
                </div>
                <div style="font-size:0.85rem;opacity:0.9;">
                    Items: ${order.items ? order.items.length : 0}
                </div>
            </div>
        `;
    });
    
    if (orders.length === 0) {
        html = '<div class="text-muted p-2">No active orders. Click "Punch New Order" to start.</div>';
    }
    
    $('#ordersTabs').html(html);
    
    // Restore scroll position after re-rendering
    restoreScrollPosition();
    
    // Ensure active tab is visible
    scrollActiveTabIntoView();
}

function renderOrder() {
    if (isLoading) return; // Don't render while loading
    
    let order = orders.find(o => o.id === activeOrderId);
    if (!order) {
        $('#orderItemsBody').html('<tr><td colspan="5" class="text-center text-muted">Select an order to begin</td></tr>');
        $('#orderTotal').text('0.00');
        $('#btnSendKitchen, #btnPrint').prop('disabled', true);
        return;
    }

    // Ensure items array exists
    if (!order.items) order.items = [];

    let body = $('#orderItemsBody');
    body.html('');
    let total = 0;

    order.items.forEach((item, i) => {
        // Ensure item has required properties
        if (!item.name) item.name = 'Unknown Item';
        if (!item.price) item.price = 0;
        if (!item.qty) item.qty = 1;
        
        let line = item.qty * item.price;
        total += line;
        
        body.append(`
            <tr>
                <td>${item.name}</td>
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
        body.html('<tr><td colspan="5" class="text-center text-muted">No items added. Click on menu items to add.</td></tr>');
    }

    $('#orderTotal').text(total.toFixed(2));
    $('#btnSendKitchen, #btnPrint').prop('disabled', order.items.length === 0);
    
    // Save after rendering to ensure data persistence
    saveDraftOrders();
}

function ordersChanged() {
    renderTabs();
    renderOrder();
    saveDraftOrders();
}

function switchOrder(id) {
    activeOrderId = id;
    renderTabs();
    renderOrder();
}

function removeItem(i) {
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

// --- INITIALIZATION ---
$(document).ready(function() {
    // Load categories and set first category active
    $('.category-item:first').addClass('active');
    
    // Load all orders first
    loadDraftOrdersFromDB(function(dbOrders) {
        let localOrders = loadDraftOrdersFromLocal();
        orders = mergeOrders(localOrders, dbOrders);
        
        // Set active order to first order if exists
        if (orders.length > 0) {
            activeOrderId = orders[0].id;
        }
        
        isLoading = false; // Data loaded, allow rendering
        renderTabs();
        renderOrder();
        
        // Load menu items for first category
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

        // Check if item already exists in order
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
        // Clear modal fields and selections
        $('#orderTypeSelect').val("");
        $('#deliverySource').val("internal");
        $('#customerName').val("");
        $('#customerPhone').val("");
        $('#customerAddress').val("");
        $('.order-type-card').removeClass('border border-3 border-primary shadow');
        $('.delivery-source-card').removeClass('border border-3 border-warning shadow');
        $('#deliveryOptions').addClass('d-none');
        $('#customerAddress').addClass('d-none');
        $('#initOrderModal').modal('show');
    });

    $(document).on('click', '.order-type-card', function() {
        $('.order-type-card').removeClass('border border-3 border-primary shadow');
        $(this).addClass('border border-3 border-primary shadow');
        let type = $(this).data('type');
        $('#orderTypeSelect').val(type);
        if(type === 'delivery'){
            $('#deliveryOptions').removeClass('d-none');
            $('#customerAddress').removeClass('d-none');
            $('#dineInFields').addClass('d-none');
        }else if(type === 'dine_in'){
            $('#deliveryOptions').addClass('d-none');
            $('#customerAddress').addClass('d-none');
            $('#dineInFields').removeClass('d-none');
            renderTableSelector();
        }else{
            $('#deliveryOptions').addClass('d-none');
            $('#customerAddress').addClass('d-none');
            $('#dineInFields').addClass('d-none');
        }
    });
// Render table/hall/family hall selector
function renderTableSelector() {
    // Define tables and halls
    const tables = Array.from({length: 15}, (_, i) => ({ id: 'T'+(i+1), label: 'Table ' + (i+1), type: 'table' }));
    const halls = [
        { id: 'HALL', label: 'Hall', type: 'hall' },
        { id: 'FAMILY', label: 'Family Hall', type: 'family' }
    ];
    // Find occupied tables from open orders
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
    // Preselect none
    $('#tableSelector .table-btn').removeClass('active');
    $('#tableNumber').val('');
}

// Table selection logic
$(document).on('click', '#tableSelector .table-btn', function() {
    $('#tableSelector .table-btn').removeClass('active');
    $(this).addClass('active');
    let table = $(this).data('table');
    // Store selected table in a hidden input for order creation
    if($('#tableNumber').length === 0) {
        $('<input type="hidden" id="tableNumber">').appendTo('#dineInFields');
    }
    $('#tableNumber').val(table);
});

    // Delivery source card selection
    $(document).on('click', '.delivery-source-card', function() {
        $('.delivery-source-card').removeClass('border border-3 border-warning shadow');
        $(this).addClass('border border-3 border-warning shadow');
        let source = $(this).data('source');
        $('#deliverySource').val(source);
    });

    // Confirm create order
    $('#confirmCreateOrder').off('click').on('click', function(){
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
            table_number: type==='dine_in' ? $('#tableNumber').val() : null,
            num_customers: type==='dine_in' ? $('#numCustomers').val() : null,
            items:[]
        };
        orders.push(order);
        activeOrderId=order.id;
        ordersChanged();
        $('#initOrderModal').modal('hide');
    });

    // Send to kitchen
    $('#btnSendKitchen').click(function() {
        if (!activeOrderId) return;
        
        let order = orders.find(o => o.id === activeOrderId);
        if (!order || order.items.length === 0) {
            alert('No items to send to kitchen');
            return;
        }
        
        // Update order status
        order.status = 'sent_to_kitchen';
        ordersChanged();
        
        // Print kitchen receipt
        // window.open(`orders.php?source=print_receipt&id=${order.id}&type=kitchen`, '_blank');
        alert('Order sent to kitchen!');
    });

    // Print receipt
    $('#btnPrint').click(function() {
        if (!activeOrderId) return;
        
        let order = orders.find(o => o.id === activeOrderId);
        if (!order || order.items.length === 0) {
            alert('No items to print');
            return;
        }
        
        // window.open(`orders.php?source=print_receipt&id=${order.id}&type=counter`, '_blank');
        alert('Print receipt!');
    });
    
    // Save scroll position when user manually scrolls
    $('.orders-tabs-card').on('scroll', function() {
        savedScrollPosition = this.scrollLeft;
    });
});

// Make removeItem globally available
window.removeItem = removeItem;
</script>