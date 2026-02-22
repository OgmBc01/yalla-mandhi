<?php
session_start();
require_once "../includes/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
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
            $source = '';
        }

        switch ($source) {
            case 'dashboard':
                include "includes/report_dashboard.php";
                break;

            case 'daily':
                include "includes/report_daily_sales.php";
                break;

            case 'monthly':
                include "includes/report_monthly.php";
                break;

            case 'items':
                include "includes/report_items.php";
                break;

            case 'payment':
                include "includes/report_payment_methods.php";
                break;

            case 'vendor':
                include "includes/report_vendor.php";
                break;

            case 'staff':
                include "includes/report_staff.php";
                break;

            case 'tax':
                include "includes/report_tax.php";
                break;

            default:
                include "includes/report_dashboard.php";
                break;
        }
        ?>
    </div>
</div>

</body>
</html>

<?php
include "includes/footer.php";
?>