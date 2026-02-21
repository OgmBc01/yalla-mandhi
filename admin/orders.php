<?php
session_start();
require_once "../includes/database.php";

// Check if user has permission
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager', 'cashier'])) {
    header("Location: ../login.php");
    exit();
}

include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Get current user's role for permissions
$current_user_role = $_SESSION['role'] ?? '';
$current_user_id = $_SESSION['user_id'];
?>

<div class="row">
    <div class="col-md-12">
        <?php
        if (isset($_GET['source'])) {
            $source = $_GET['source'];
        } else {
            $source = 'pos_view'; // Default to POS view instead of list
        }

        switch ($source) {
            case 'add_order':
            case 'pos':
            case 'pos_view':
                include "includes/pos_order.php";
                break;
                
            case 'edit_order':
                include "includes/edit_order.php";
                break;
                
            case 'view_order':
                include "includes/view_order_details.php";
                break;
                
            case 'order_list':
                include "includes/view_all_orders.php";
                break;
                
            case 'kitchen_display':
                include "includes/kitchen_display.php";
                break;
                
            case 'print_receipt':
                include "includes/print_receipt.php";
                break;
                
            case 'online_orders':
                include "includes/online_orders.php";
                break;
                
            default:
                include "includes/pos_order.php"; // Default to POS
                break;
        }
        ?>
    </div>
</div>

<style>
/* POS Specific Styles */
.pos-container {
    display: flex;
    height: calc(100vh - 120px);
    overflow: hidden;
    background: var(--color-beige);
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* Category Sidebar */
.category-sidebar {
    width: 200px;
    background: var(--color-soft-black);
    color: white;
    overflow-y: auto;
    padding: 15px 0;
}

.category-item {
    padding: 12px 20px;
    cursor: pointer;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.category-item:hover,
.category-item.active {
    background: rgba(255,255,255,0.1);
    border-left-color: var(--color-red);
}

.category-item i {
    margin-right: 10px;
    width: 20px;
    color: var(--color-copper);
}

/* Menu Items Grid */
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
    border: 1px solid var(--color-sand);
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
    border-color: var(--color-copper);
}

.menu-item-card.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f5f5f5;
}

.menu-item-card .price {
    color: var(--color-red);
    font-weight: bold;
    font-size: 1.2rem;
    margin-top: 10px;
}

.menu-item-card .stock-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: var(--color-red);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
}

/* Order Summary Panel */
.order-summary {
    width: 350px;
    background: var(--color-soft-black);
    color: white;
    display: flex;
    flex-direction: column;
    border-left: 1px solid rgba(255,255,255,0.1);
}

.order-header {
    padding: 20px;
    background: rgba(0,0,0,0.3);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.order-type-selector {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.type-btn {
    flex: 1;
    padding: 10px;
    border: 1px solid rgba(255,255,255,0.2);
    background: transparent;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
}

.type-btn.active {
    background: var(--color-red);
    border-color: var(--color-red);
}

.delivery-source {
    margin-top: 15px;
    padding: 10px;
    background: rgba(0,0,0,0.2);
    border-radius: 5px;
}

.order-items {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: rgba(255,255,255,0.05);
    margin-bottom: 5px;
    border-radius: 5px;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: bold;
}

.item-meta {
    font-size: 0.8rem;
    color: #aaa;
}

.item-actions {
    display: flex;
    gap: 5px;
}

.item-actions button {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 4px;
    cursor: pointer;
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
}

.grand-total {
    font-size: 1.3rem;
    font-weight: bold;
    color: var(--color-copper);
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(255,255,255,0.2);
}

.order-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 15px;
}

/* Order Pills */
.orders-board {
    display: flex;
    gap: 20px;
    padding: 20px;
    overflow-x: auto;
    min-height: 200px;
    background: var(--color-beige);
    border-radius: 10px;
}

.order-column {
    min-width: 280px;
    background: white;
    border-radius: 8px;
    padding: 15px;
}

.column-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid;
}

.column-header.pending { border-color: #ffc107; }
.column-header.preparation { border-color: #0d6efd; }
.column-header.ready { border-color: #198754; }
.column-header.delivery { border-color: #fd7e14; }

.order-pill {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.order-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.order-pill.pending { border-left: 4px solid #ffc107; }
.order-pill.preparation { border-left: 4px solid #0d6efd; }
.order-pill.ready { border-left: 4px solid #198754; }
.order-pill.delivery { border-left: 4px solid #fd7e14; }
.order-pill.completed { border-left: 4px solid #6c757d; }
.order-pill.cancelled { border-left: 4px solid #dc3545; }

.order-pill-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.order-number {
    font-weight: bold;
    color: var(--color-red);
}

.order-type-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    background: var(--color-sand);
}

.vendor-badge {
    background: var(--color-copper);
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

.payment-indicator.paid { background: #198754; }
.payment-indicator.unpaid { background: #dc3545; }
.payment-indicator.vendor { background: #fd7e14; }
</style>

<script>
// Auto-save draft functionality
let autoSaveTimer;
const DRAFT_SAVE_DELAY = 2000; // 2 seconds

function triggerAutoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        saveOrderDraft();
    }, DRAFT_SAVE_DELAY);
}

function saveOrderDraft() {
    // Get current order data
    const orderData = {
        order_id: $('#current_order_id').val(),
        order_type: $('.type-btn.active').data('type'),
        delivery_source: $('#delivery_source').val(),
        table_number: $('#table_number').val(),
        customer: {
            phone: $('#customer_phone').val(),
            name: $('#customer_name').val(),
            address: $('#delivery_address').val()
        },
        items: getOrderItems(),
        totals: calculateTotals()
    };

    $.ajax({
        url: 'includes/ajax/save_order_draft.php',
        method: 'POST',
        data: JSON.stringify(orderData),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                $('#current_order_id').val(response.order_id);
                showToast('Draft saved', 'success');
            }
        }
    });
}

// Load draft on page load
$(document).ready(function() {
    const savedDraft = localStorage.getItem('pos_order_draft');
    if (savedDraft) {
        // Ask user if they want to restore draft
        if (confirm('You have an unsaved draft order. Would you like to restore it?')) {
            loadDraft(JSON.parse(savedDraft));
        }
    }
});
</script>

<?php
include "includes/footer.php";
?>