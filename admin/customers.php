<?php
session_start();
require_once "../includes/database.php";

include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";
?>

<div class="row">
    <div class="col-md-12">
      <?php
        if(isset($_GET['source'])) {
          $source = $_GET['source'];
        } else {
          $source = '';
        }

        switch($source) {
          case 'add_customer':
            include "includes/add_customer.php";
            break;
            
          case 'edit_customer':
            include "includes/edit_customer.php";
            break;
            
          case 'view_customer':
            include "includes/view_customer_details.php";
            break;
            
          default:
            include "includes/view_all_customers.php";
            break;
        }
      ?>
    </div>
  </div>  
</div>

</body></br>
</html>

<?php
include "includes/footer.php";
?>