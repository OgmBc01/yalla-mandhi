<?php
session_start();
require_once "../includes/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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
          case 'add_reservation':
            include "includes/add_reservation.php";
            break;
            
          case 'edit_reservation':
            include "includes/edit_reservation.php";
            break;
            
          case 'view_pending':
            include "includes/view_pending_reservations.php";
            break;
            
          default:
            include "includes/view_all_reservations.php";
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