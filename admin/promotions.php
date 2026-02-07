<?php

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
          case 'add_promotion':
            include "includes/add_promotion.php";
            break;
            
          case 'edit_promotion':
            include "includes/edit_promotion.php";
            break;
            
          case 'view_promotion':
            include "includes/view_promotion_details.php";
            break;
            
          default:
            include "includes/view_all_promotions.php";
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