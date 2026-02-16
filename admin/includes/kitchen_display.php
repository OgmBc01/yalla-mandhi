<?php
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
}

body {
    background-color: var(--kds-bg);
    color: var(--kds-text);
    font-family: 'Segoe UI', system-ui, sans-serif;
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

.kds-time {
    font-size: 1.5rem;
    background: #333;
    padding: 10px 20px;
    border-radius: 10px;
}

.kds-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    height: calc(100vh - 120px);
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
}

.kds-column-header.pending { background: var(--kds-pending); color: #000; }
.kds-column-header.preparing { background: var(--kds-preparing); }
.kds-column-header.ready { background: var(--kds-ready); }

.kds-orders {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
}

.kds-order-card {
    background: #333;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 5px solid;
    animation: fadeIn 0.3s;
}

.kds-order-card.pending { border-left-color: var(--kds-pending); }
.kds-order-card.preparing { border-left-color: var(--kds-preparing); }
.kds-order-card.ready { border-left-color: var(--kds-ready); }

.kds-order-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 1.2rem;
}

.kds-order-number {
    font-weight: bold;
    color: var(--color-copper);
}

.kds-order-time {
    color: #aaa;
    font-size: 0.9rem;
}

.kds-order-type {
    display: inline-block;
    padding: 3px 10px;
    background: #444;
    border-radius: 15px;
    font-size: 0.8rem;
    margin-bottom: 10px;
}

.kds-order-items {
    margin-top: 10px;
}

.kds-order-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px solid #444;
}

.kds-item-name {
    font-weight: 500;
}

.kds-item-qty {
    background: #444;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.9rem;
}

.kds-item-notes {
    font-size: 0.8rem;
    color: #ffc107;
    font-style: italic;
    margin-left: 15px;
}

.kds-order-footer {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.kds-prep-time {
    font-size: 0.8rem;
    color: #aaa;
}

.kds-action-btn {
    padding: 5px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
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
</style>

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
                <i class="bi bi-clock-history"></i> Pending
                <span class="badge bg-dark ms-2" id="pending-count">0</span>
            </div>
            <div class="kds-orders" id="pending-orders"></div>
        </div>
        
        <!-- In Preparation Column -->
        <div class="kds-column">
            <div class="kds-column-header preparing">
                <i class="bi bi-fire"></i> In Preparation
                <span class="badge bg-dark ms-2" id="preparing-count">0</span>
            </div>
            <div class="kds-orders" id="preparing-orders"></div>
        </div>
        
        <!-- Ready Column -->
        <div class="kds-column">
            <div class="kds-column-header ready">
                <i class="bi bi-check-circle"></i> Ready
                <span class="badge bg-dark ms-2" id="ready-count">0</span>
            </div>
            <div class="kds-orders" id="ready-orders"></div>
        </div>
    </div>
</div>

<!-- Audio notification for new orders -->
<audio id="notification-sound" preload="auto">
    <source src="../assets/sounds/notification.mp3" type="audio/mpeg">
</audio>

<script>
let previousOrders = { pending: [], preparing: [], ready: [] };

$(document).ready(function() {
    updateTime();
    setInterval(updateTime, 1000);
    
    // Load initial orders
    loadKitchenOrders();
    
    // Auto-refresh every 10 seconds
    setInterval(loadKitchenOrders, 10000);
});

function updateTime() {
    const now = new Date();
    $('#current-time').text(now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    }));
}

function loadKitchenOrders() {
    $.ajax({
        url: 'includes/ajax/get_kitchen_orders.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateKitchenDisplay(response.orders);
            }
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
    if (status === 'pending') container = $('#pending-orders');
    if (status === 'preparing') container = $('#preparing-orders');
    if (status === 'ready') container = $('#ready-orders');
    
    if (orders.length === 0) {
        container.html('<div class="text-center text-muted py-4">No orders</div>');
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
                ${order.table_number ? `- Table ${order.table_number}` : ''}
            </div>
            
            <div class="kds-order-items">
        `;
        
        order.items.forEach(item => {
            html += `
            <div class="kds-order-item">
                <span class="kds-item-name">${item.item_name_snapshot}</span>
                <span class="kds-item-qty">x${item.quantity}</span>
            </div>
            `;
            if (item.special_instructions) {
                html += `<div class="kds-item-notes">📝 ${item.special_instructions}</div>`;
            }
        });
        
        html += `
            </div>
            
            <div class="kds-order-footer">
                <span class="kds-prep-time">⏱️ ${order.prep_time || 'New'}</span>
        `;
        
        if (status === 'pending') {
            html += `<button class="kds-action-btn start" onclick="updateOrderStatus(${order.id}, 'in_preparation')">Start Preparing</button>`;
        } else if (status === 'preparing') {
            html += `<button class="kds-action-btn ready" onclick="updateOrderStatus(${order.id}, 'ready')">Mark Ready</button>`;
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
    return Math.floor(diffMinutes / 60) + 'h ' + (diffMinutes % 60) + 'm';
}

function updateOrderStatus(orderId, newStatus) {
    $.ajax({
        url: 'includes/ajax/update_order_status.php',
        method: 'POST',
        data: {
            order_id: orderId,
            status: newStatus
        },
        success: function(response) {
            if (response.success) {
                loadKitchenOrders();
            }
        }
    });
}

function playNotification() {
    const audio = document.getElementById('notification-sound');
    audio.play().catch(e => console.log('Audio play failed:', e));
}
</script>