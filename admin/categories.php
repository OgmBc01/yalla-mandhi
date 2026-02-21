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
          case 'add_category':
            include "includes/add_category.php";
            break;
            
          case 'edit_category':
            include "includes/edit_category.php";
            break;
            
          default:
            include "includes/view_all_categories.php";
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