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
        <?php $firstCat = null; $catIndex = 0; while($cat = $categories->fetch_assoc()): ?>
            <?php if ($catIndex === 0) $firstCat = $cat['id']; ?>
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
    <div class="modal-dialog modal-lg"> <!-- Added modal-lg for wider modal -->
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
    transition:box-shadow 0.2s,border 0.2s;
}
.order-tab.active{
    background:#fff;
    border:2px solid #c41e3a!important;
    box-shadow:0 4px 12px rgba(196,30,58,0.12);
}
.orders-tabs-card{
    overflow-x:auto;
    white-space:nowrap;
    scrollbar-width:thin;
    scrollbar-color:#c41e3a #eee;
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



<!-- Move script after footer to ensure Bootstrap and jQuery are loaded -->
<?php include 'footer.php'; ?>
<script>
let orders = [];
let activeOrderId = null;

function renderTabs(){
    let html = '';
    orders.forEach(order=>{
        let active = order.id===activeOrderId?'active':'';
        let typeColor = '';
        let typeIcon = '';
        let deliveryBadge = '';
        if(order.type==='dine_in'){
            typeColor = 'background:linear-gradient(135deg,#3498db,#6dd5fa);color:#fff;';
            typeIcon = '<i class="bi bi-shop me-1"></i>';
        }else if(order.type==='pickup'){
            typeColor = 'background:linear-gradient(135deg,#f39c12,#f7b733);color:#fff;';
            typeIcon = '<i class="bi bi-bag me-1"></i>';
        }else if(order.type==='delivery'){
            typeColor = 'background:linear-gradient(135deg,#2ecc71,#27ae60);color:#fff;';
            typeIcon = '<i class="bi bi-truck me-1"></i>';
            let src = order.delivery_source||'internal';
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
        html += `
            <div class="order-tab ${active}" style="${typeColor}margin-right:8px;min-width:180px;display:inline-block;cursor:pointer;border-radius:8px;padding:8px 16px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid ${active?"#c41e3a":"transparent"};\" onclick="switchOrder('${order.id}')">
                <div style="font-weight:600;">${typeIcon}${order.type.toUpperCase()} - ${order.customer.name} ${deliveryBadge}</div>
            </div>
        `;
    });
    $('#ordersTabs').html('<div class="orders-tabs-card" style="background:#fff;border-radius:12px;padding:12px 8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);overflow-x:auto;white-space:nowrap;">'+html+'</div>');
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

    if(order.items.length===0){
        body.html(`<tr><td colspan="5" class="text-center text-muted">No items added</td></tr>`);
    }

    $('#orderTotal').text(total.toFixed(2));
    $('#btnSendKitchen,#btnPrint').prop('disabled',order.items.length===0);
    saveDraftOrders(); // Save on every render
}

function removeItem(i){
    let order=orders.find(o=>o.id===activeOrderId);
    order.items.splice(i,1);
    ordersChanged();
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

    // Check if item already exists in order
    let existing = order.items.find(item => item.id === id);
    if(existing){
        existing.qty += 1;
    }else{
        order.items.push({id,name,price,qty:1});
    }
    renderOrder();
});

// Handle quantity plus/minus buttons
$(document).on('click','.btn-qty-plus',function(){
    let order=orders.find(o=>o.id===activeOrderId);
    let idx = $(this).data('index');
    if(order && order.items[idx]){
        order.items[idx].qty += 1;
        ordersChanged();
    }
});

$(document).on('click','.btn-qty-minus',function(){
    let order=orders.find(o=>o.id===activeOrderId);
    let idx = $(this).data('index');
    if(order && order.items[idx]){
        if(order.items[idx].qty > 1){
            order.items[idx].qty -= 1;
        }else{
            order.items.splice(idx,1);
        }
        ordersChanged();
    }
});

$('#btnNewOrder').click(function(){
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


// Order type card selection
$(document).on('click', '.order-type-card', function() {
    $('.order-type-card').removeClass('border border-3 border-primary shadow');
    $(this).addClass('border border-3 border-primary shadow');
    let type = $(this).data('type');
    $('#orderTypeSelect').val(type);
    if(type === 'delivery'){
        $('#deliveryOptions').removeClass('d-none');
        $('#customerAddress').removeClass('d-none');
    }else{
        $('#deliveryOptions').addClass('d-none');
        $('#customerAddress').addClass('d-none');
    }
});

// Delivery source card selection
$(document).on('click', '.delivery-source-card', function() {
    $('.delivery-source-card').removeClass('border border-3 border-warning shadow');
    $(this).addClass('border border-3 border-warning shadow');
    let source = $(this).data('source');
    $('#deliverySource').val(source);
});

// Remove any previous direct binding for #confirmCreateOrder
$(function() {
    // --- DRAFT ORDER PERSISTENCE ---
    function saveDraftOrders() {
        localStorage.setItem('pos_orders', JSON.stringify(orders));
        orders.forEach(order => {
            $.post('includes/pos_order_drafts.php', {action:'save', order: JSON.stringify(order)});
        });
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
        if(order.items.length===0){
            body.html(`<tr><td colspan="5" class="text-center text-muted">No items added</td></tr>`);
        }
        $('#orderTotal').text(total.toFixed(2));
        $('#btnSendKitchen,#btnPrint').prop('disabled',order.items.length===0);
        saveDraftOrders(); // Save on every render
    }
    function ordersChanged() {
        renderTabs();
        renderOrder();
    }
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
            items:[]
        };
        orders.push(order);
        activeOrderId=order.id;
        ordersChanged();
        $('#initOrderModal').modal('hide');
    });
    $(document).off('click','.btn-qty-plus').on('click','.btn-qty-plus',function(){
        let order=orders.find(o=>o.id===activeOrderId);
        let idx = $(this).data('index');
        if(order && order.items[idx]){
            order.items[idx].qty += 1;
            ordersChanged();
        }
    });
    $(document).off('click','.btn-qty-minus').on('click','.btn-qty-minus',function(){
        let order=orders.find(o=>o.id===activeOrderId);
        let idx = $(this).data('index');
        if(order && order.items[idx]){
            if(order.items[idx].qty > 1){
                order.items[idx].qty -= 1;
            }else{
                order.items.splice(idx,1);
            }
            ordersChanged();
        }
    });
    window.removeItem = function(i){
        let order=orders.find(o=>o.id===activeOrderId);
        order.items.splice(i,1);
        ordersChanged();
    };
    function loadDraftOrdersFromLocal() {
        let local = localStorage.getItem('pos_orders');
        if(local) {
            try {
                return JSON.parse(local) || [];
            } catch(e) { return []; }
        }
        return [];
    }
    function loadDraftOrdersFromDB(cb) {
        $.get('includes/pos_order_drafts.php', {action:'load'}, function(data) {
            cb(Array.isArray(data) ? data : []);
        }, 'json');
    }
    function mergeOrders(local, db) {
        let map = {};
        db.forEach(o => map[o.id] = o);
        local.forEach(o => { if(!map[o.id]) map[o.id] = o; });
        return Object.values(map);
    }
    loadDraftOrdersFromDB(function(dbOrders){
        let localOrders = loadDraftOrdersFromLocal();
        orders = mergeOrders(localOrders, dbOrders);
        window.orders = orders; // update global reference
        if(orders.length>0) activeOrderId = orders[0].id;
        window.activeOrderId = activeOrderId;
        renderTabs();
        renderOrder();
    });
    // Show menu items for first category by default
    var firstCat = <?= json_encode($firstCat ?? '') ?>;
    if(firstCat) loadMenu(firstCat);
});
</script>
