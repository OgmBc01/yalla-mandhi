<?php
session_start();
require_once "../includes/database.php";

// Check if user has permission
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
    header("Location: login.php");
    exit();
}

include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";
?>

<div class="row">
    <div class="col-md-12">
        <?php
        if (isset($_GET['source'])) {
            $source = $_GET['source'];
        } else {
            $source = 'view_inventory';
        }

        switch ($source) {
            case 'add_stock':
            case 'add_item':
                include "includes/inventory_add_item.php";
                break;
                
            case 'edit_item':
                include "includes/inventory_edit_item.php";
                break;
                
            case 'view_item':
                include "includes/inventory_view_item.php";
                break;
                
            case 'stock_history':
                include "includes/inventory_stock_history.php";
                break;
                
            case 'low_stock':
                include "includes/inventory_low_stock.php";
                break;
                
            case 'add_purchase':
                include "includes/inventory_add_purchase.php";
                break;
                
            case 'view_purchases':
                include "includes/inventory_purchases.php";
                break;
                
            case 'adjust_stock':
                include "includes/inventory_adjust_stock.php";
                break;
                
            case 'suppliers':
                include "includes/inventory_suppliers.php";
                break;
                
            case 'add_supplier':
                include "includes/inventory_add_supplier.php";
                break;
                
            case 'edit_supplier':
                include "includes/inventory_edit_supplier.php";
                break;
                
            default:
                include "includes/inventory_view_all.php";
                break;
        }
        ?>
    </div>
</div>

<style>
/* Inventory specific styles */
.inventory-stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 20px;
    transition: transform 0.3s;
}

.inventory-stats-card:hover {
    transform: translateY(-5px);
}

.inventory-stats-card.warning { background: linear-gradient(135deg, #fdc830 0%, #f37335 100%); }
.inventory-stats-card.danger { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
.inventory-stats-card.success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }

.stock-level-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}

.stock-level-high { background-color: #27ae60; }
.stock-level-medium { background-color: #f39c12; }
.stock-level-low { background-color: #e74c3c; }
.stock-level-out { background-color: #95a5a6; }

.transaction-type-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.type-purchase { background: #27ae60; color: white; }
.type-usage { background: #e74c3c; color: white; }
.type-adjustment { background: #f39c12; color: white; }
.type-return { background: #3498db; color: white; }
</style>

<?php
include "includes/footer.php";
?>