<?php
session_start();
require_once "../includes/database.php";

// Check if user has permission
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin'])) {
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
          case 'add_employee':
            include "includes/add_employee.php";
            break;
            
          case 'edit_employee':
            include "includes/edit_employee.php";
            break;
            
          case 'view_employee':
            include "includes/view_employee_details.php";
            break;
            
          default:
            include "includes/view_all_employees.php";
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