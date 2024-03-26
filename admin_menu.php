<?php
  require "php_header.php";
  if(empty($_GET["key"])){
    echo "参照用のURLが異なります。";
    exit();
  }

  $user_hash = $_GET["key"] ;
  $_SESSION["user_id"] = rot13decrypt2($user_hash);
?>
<!DOCTYPE html>
<html lang='ja'>
<head>
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_admin.php" 
    ?>
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY>
  <div id='app'>
  <?php include "header_tag_admin.php"  ?>
  <MAIN class='container common_main'>
    <!--<a href="line_push_msg.php" type='button' class='btn btn-danger'>test</a>-->
    test
  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    admin_menu('admin_menu.php','','<?php echo $user_hash;?>').mount('#admin_menu');
  </script>
</BODY>
</html>