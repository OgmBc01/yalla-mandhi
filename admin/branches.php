<?php
session_start();
require_once "../includes/database.php";

// Check if user is logged in and has admin/superadmin role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
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
        if(isset($_GET['source'])) {
          $source = $_GET['source'];
        } else {
          $source = '';
        }

        switch($source) {
          case 'add_branch':
            include "includes/add_branch.php";
            break;
            
          case 'edit_branch':
            include "includes/edit_branch.php";
            break;
            
          case 'view_branch':
            include "includes/view_branch_details.php";
            break;
            
          default:
            include "includes/view_all_branches.php";
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