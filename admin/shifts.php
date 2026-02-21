<?php
session_start();
require_once "../includes/database.php";

// Check if user has permission
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super-admin', 'manager'])) {
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
          case 'add_shift':
              include "includes/add_shift.php";
              break;
              
          case 'edit_shift':
              include "includes/edit_shift.php";
              break;
              
          case 'view_shift':
              include "includes/view_shift_details.php";
              break;
              
          case 'bulk_assign':
              include "includes/bulk_assign_shifts.php";
              break;
              
          case 'calendar_view':
              include "includes/shift_calendar.php";
              break;
              
          case 'attendance':
              include "includes/attendance.php";
              break;
              
          case 'mark_attendance':
              include "includes/mark_attendance.php";
              break;
              
          case 'view_attendance':
              include "includes/view_attendance_details.php";
              break;
              
          default:
              include "includes/view_all_shifts.php";
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