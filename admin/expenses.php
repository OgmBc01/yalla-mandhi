<?php
session_start();
require_once "../includes/database.php";

// Check if user has permission
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager', 'accountant'])) {
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
            $source = 'list_expenses';
        }

        switch ($source) {
            case 'add_expense':
                include "includes/expense_add.php";
                break;
                
            case 'edit_expense':
                include "includes/expense_edit.php";
                break;
                
            case 'view_expense':
                include "includes/expense_view.php";
                break;
                
            case 'list_expenses':
                include "includes/expense_list.php";
                break;
                
            case 'expense_categories':
                include "includes/expense_categories.php";
                break;
                
            case 'add_category':
                include "includes/expense_add_category.php";
                break;
                
            case 'edit_category':
                include "includes/expense_edit_category.php";
                break;
                
            case 'recurring_expenses':
                include "includes/expense_recurring.php";
                break;
                
            case 'profit_report':
                include "includes/profit_report.php";
                break;
                
            default:
                include "includes/expense_list.php";
                break;
        }
        ?>
    </div>
</div>

<?php
include "includes/footer.php";
?>