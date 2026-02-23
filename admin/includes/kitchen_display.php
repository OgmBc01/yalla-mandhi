<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kitchen display - no financial info shown
?>

<style>
:root {
    --kds-bg: #1a1a1a;
    --kds-card-bg: #2d2d2d;
    --kds-text: #ffffff;
    --kds-pending: #ffc107;
    --kds-preparing: #0d6efd;
    --kds-ready: #198754;
    --color-copper: #b87333;
}

.main-content {
    margin-left: 240px;
    width: calc(100% - 240px);
    transition: margin-left 0.3s, width 0.3s;
    padding: 0;
    background: var(--kds-bg);
    min-height: 100vh;
}

/* When sidebar is collapsed */
body.sidebar-collapsed .main-content {
    margin-left: 60px !important;
    width: calc(100% - 60px) !important;
}

.kds-container {
    padding: 20px;
    height: 100vh;
    overflow: hidden;
}

.kds-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #444;
}

.kds-title {
    font-size: 2rem;
    font-weight: bold;
    color: var(--color-copper);
}

.kds-title i {
    margin-right: 10px;
}

.kds-time {
    font-size: 1.5rem;
    background: #333;
    padding: 10px 20px;
    border-radius: 10px;
    font-family: monospace;
}

.kds-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    height: calc(100vh - 140px);
}

.kds-column {
    background: #222;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.kds-column-header {
    padding: 15px;
    text-align: center;
    font-weight: bold;
    font-size: 1.3rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.kds-column-header.pending { background: var(--kds-pending); color: #000; }
.kds-column-header.preparing { background: var(--kds-preparing); color: #fff; }
.kds-column-header.ready { background: var(--kds-ready); color: #fff; }

.kds-column-header .badge {
    background: rgba(0,0,0,0.3);
    color: white;
    font-size: 1rem;
    padding: 5px 10px;
}

.kds-orders {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
}

/* Custom scrollbar for kitchen display */
.kds-orders::-webkit-scrollbar {
    width: 8px;
}

.kds-orders::-webkit-scrollbar-track {
    background: #333;
    border-radius: 4px;
}

.kds-orders::-webkit-scrollbar-thumb {
    background: #555;
    border-radius: 4px;
}

.kds-orders::-webkit-scrollbar-thumb:hover {
    background: #777;
}

.kds-order-card {
    background: #333;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 5px solid;
    animation: fadeIn 0.3s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.kds-order-card.pending { border-left-color: var(--kds-pending); }
.kds-order-card.preparing { border-left-color: var(--kds-preparing); }
.kds-order-card.ready { border-left-color: var(--kds-ready); }

.kds-order-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.kds-order-number {
    font-weight: bold;
    color: var(--color-copper);
    font-size: 1.2rem;
}

.kds-order-time {
    color: #aaa;
    font-size: 0.85rem;
    background: #444;
    padding: 3px 8px;
    border-radius: 12px;
}

.kds-order-type {
    display: inline-block;
    padding: 4px 12px;
    background: #444;
    border-radius: 20px;
    font-size: 0.85rem;
    margin-bottom: 12px;
    color: #ddd;
}

.kds-order-type i {
    margin-right: 5px;
}

.kds-order-items {
    margin-top: 10px;
}

.kds-order-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px solid #444;
}

.kds-order-item:last-child {
    border-bottom: none;
}

.kds-item-name {
    font-weight: 500;
    color: #fff;
}

.kds-item-qty {
    background: #444;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
    color: #ffc107;
}

.kds-item-notes {
    font-size: 0.8rem;
    color: #ffc107;
    font-style: italic;
    margin: 5px 0 0 0;
    padding: 5px;
    background: #3a3a3a;
    border-radius: 4px;
}

.kds-order-footer {
    margin-top: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 10px;
    border-top: 1px solid #444;
}

.kds-prep-time {
    font-size: 0.85rem;
    color: #aaa;
    background: #3a3a3a;
    padding: 4px 10px;
    border-radius: 20px;
}

.kds-action-btn {
    padding: 8px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.kds-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.kds-action-btn.start {
    background: var(--kds-preparing);
    color: white;
}

.kds-action-btn.ready {
    background: var(--kds-ready);
    color: white;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.kds-new-order {
    animation: pulse 1s;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
    70% { box-shadow: 0 0 0 15px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .kds-grid {
        gap: 15px;
    }
}

@media (max-width: 991.98px) {
    .main-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    
    .kds-grid {
        grid-template-columns: 1fr;
        height: auto;
        overflow-y: auto;
    }
    
    .kds-container {
        height: auto;
        overflow-y: auto;
    }
    
    .kds-column {
        margin-bottom: 20px;
    }
}

@media (max-width: 768px) {
    .kds-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .kds-title {
        font-size: 1.5rem;
    }
    
    .kds-time {
        font-size: 1.2rem;
    }
}
</style>

<div class="main-content">
    <div class="kds-container">
        <div class="kds-header">
            <div class="kds-title">
                <i class="bi bi-tv"></i> Kitchen Display System
            </div>
            <div class="kds-time" id="current-time"></div>
        </div>
        
        <div class="kds-grid">
            <!-- Pending Column -->
            <div class="kds-column">
                <div class="kds-column-header pending">
                    <span><i class="bi bi-clock-history me-2"></i>Pending</span>
                    <span class="badge bg-dark" id="pending-count">0</span>
                </div>
                <div class="kds-orders" id="pending-orders"></div>
            </div>
            
            <!-- In Preparation Column -->
            <div class="kds-column">
                <div class="kds-column-header preparing">
                    <span><i class="bi bi-fire me-2"></i>In Preparation</span>
                    <span class="badge bg-dark" id="preparing-count">0</span>
                </div>
                <div class="kds-orders" id="preparing-orders"></div>
            </div>
            
            <!-- Ready Column -->
            <div class="kds-column">
                <div class="kds-column-header ready">
                    <span><i class="bi bi-check-circle me-2"></i>Ready</span>
                    <span class="badge bg-dark" id="ready-count">0</span>
                </div>
                <div class="kds-orders" id="ready-orders"></div>
            </div>
        </div>
    </div>
</div>

<!-- Audio notification for new orders -->
<audio id="notification-sound" preload="auto">
    <source src="../assets/sounds/notification.mp3" type="audio/mpeg">
</audio>

<script>
let previousOrders = { pending: [], preparing: [], ready: [] };
let notificationSound = document.getElementById('notification-sound');

$(document).ready(function() {
    updateTime();
    setInterval(updateTime, 1000);
    
    // Load initial orders
    loadKitchenOrders();
    
    // Auto-refresh every 10 seconds
    setInterval(loadKitchenOrders, 10000);
    
    // Listen for sidebar toggle to adjust layout
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            setTimeout(updateSidebarState, 350);
        });
    }
});

function updateSidebarState() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    const isCollapsed = sidebar.classList.contains('collapsed') || sidebar.offsetWidth < 100;
    if (isCollapsed) {
        document.body.classList.add('sidebar-collapsed');
    } else {
        document.body.classList.remove('sidebar-collapsed');
    }
}

function updateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    });
}

function loadKitchenOrders() {
    $.ajax({
        url: 'includes/ajax/get_kitchen_orders.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateKitchenDisplay(response.orders);
            }
        },
        error: function(xhr) {
            console.error('Failed to load kitchen orders:', xhr.responseText);
        }
    });
}

function updateKitchenDisplay(orders) {
    // Check for new orders
    const currentPending = orders.filter(o => o.order_status === 'pending').length;
    if (currentPending > previousOrders.pending.length) {
        playNotification();
    }
    
    previousOrders = {
        pending: orders.filter(o => o.order_status === 'pending'),
        preparing: orders.filter(o => o.order_status === 'in_preparation'),
        ready: orders.filter(o => o.order_status === 'ready')
    };
    
    // Update counts
    $('#pending-count').text(previousOrders.pending.length);
    $('#preparing-count').text(previousOrders.preparing.length);
    $('#ready-count').text(previousOrders.ready.length);
    
    // Render columns
    renderOrders('pending', previousOrders.pending);
    renderOrders('preparing', previousOrders.preparing);
    renderOrders('ready', previousOrders.ready);
}

function renderOrders(status, orders) {
    const container = $(`#${status}-orders`);
    
    if (orders.length === 0) {
        container.html('<div class="text-center text-muted py-5"><i class="bi bi-inbox display-1 d-block mb-3"></i>No orders</div>');
        return;
    }
    
    let html = '';
    orders.forEach(order => {
        const timeElapsed = getTimeElapsed(order.created_at);
        const isNew = (new Date() - new Date(order.created_at)) < 300000; // 5 minutes
        
        html += `
        <div class="kds-order-card ${order.order_status} ${isNew ? 'kds-new-order' : ''}" data-order-id="${order.id}">
            <div class="kds-order-header">
                <span class="kds-order-number">#${order.order_number}</span>
                <span class="kds-order-time">${timeElapsed}</span>
            </div>
            
            <div class="kds-order-type">
                <i class="bi ${order.order_type === 'dine_in' ? 'bi-shop' : (order.order_type === 'pickup' ? 'bi-bag' : 'bi-truck')}"></i>
                ${order.order_type.replace('_', ' ')}
                ${order.table_number ? `<span class="ms-2">• Table ${order.table_number}</span>` : ''}
            </div>
            
            <div class="kds-order-items">
        `;
        
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                html += `
                <div class="kds-order-item">
                    <span class="kds-item-name">${item.item_name_snapshot || item.item_name}</span>
                    <span class="kds-item-qty">x${item.quantity}</span>
                </div>
                `;
                if (item.special_instructions) {
                    html += `<div class="kds-item-notes">📝 ${item.special_instructions}</div>`;
                }
            });
        }
        
        html += `
            </div>
            
            <div class="kds-order-footer">
                <span class="kds-prep-time">⏱️ ${timeElapsed}</span>
        `;
        
        if (status === 'pending') {
            html += `<button class="kds-action-btn start" onclick="updateOrderStatus(${order.id}, 'in_preparation')">Start Preparing</button>`;
        } else if (status === 'preparing') {
            html += `<button class="kds-action-btn ready" onclick="updateOrderStatus(${order.id}, 'ready')">Mark Ready</button>`;
        } else if (status === 'ready') {
            html += `<span class="badge bg-success">Ready to serve</span>`;
        }
        
        html += `
            </div>
        </div>
        `;
    });
    
    container.html(html);
}

function getTimeElapsed(datetime) {
    const now = new Date();
    const orderTime = new Date(datetime);
    const diffMinutes = Math.floor((now - orderTime) / 60000);
    
    if (diffMinutes < 1) return 'Just now';
    if (diffMinutes < 60) return diffMinutes + ' min ago';
    const hours = Math.floor(diffMinutes / 60);
    const mins = diffMinutes % 60;
    return hours + 'h ' + mins + 'm';
}

function updateOrderStatus(orderId, newStatus) {
    $.ajax({
        url: 'includes/ajax/update_order_status.php',
        method: 'POST',
        data: {
            order_id: orderId,
            status: newStatus
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                loadKitchenOrders();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error('Failed to update order status:', xhr.responseText);
            alert('Failed to update order status');
        }
    });
}

function playNotification() {
    if (notificationSound) {
        notificationSound.play().catch(e => console.log('Audio play failed:', e));
    }
}
</script>