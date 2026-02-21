<?php
session_start();
require_once "../includes/database.php";

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'employee'])) {
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
          case 'add_testimonial':
            include "includes/add_testimonial.php";
            break;
            
          case 'edit_testimonial':
            include "includes/edit_testimonial.php";
            break;
            
          case 'view_testimonial':
            include "includes/view_testimonial_details.php";
            break;
            
          default:
            include "includes/view_all_testimonials.php";
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