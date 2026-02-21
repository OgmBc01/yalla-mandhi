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
        if(isset($_GET['source'])) {
          $source = $_GET['source'];
        } else {
          $source = '';
        }

        switch($source) {
          case 'add_item':
            include "includes/add_menu_item.php";
            break;
            
          case 'edit_item':
            include "includes/edit_menu_item.php";
            break;
            
          case 'manage_categories':
            include "includes/manage_categories.php";
            break;
            
          default:
            include "includes/view_all_menu_items.php";
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