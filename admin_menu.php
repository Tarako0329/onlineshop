<?php
  require "php_header.php";
  $user_hash = $_GET["key"] ;
  $_SESSION["user_id"] = rot13decrypt2($user_hash);
?>
<!DOCTYPE html>
<html lang='ja'>
<head>
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_bs5.php" 
    ?>
    <script src="./script/flow.js"></script>
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY class='bd'>
  <div id='app'>
  <?php include "header_tag_admin.php"  ?>
  <MAIN class='container common_main pt-3'>
    <div v-for='(list,index) in menu' :key='list.name' class='row mb-3'>
      <div class='col-md-6 col-12'>
        <a type="button" :href="list.url" class='btn btn-primary' style='font-size:15px;font-weight:700;width:200px;height:40px;padding-top:7px;'>{{list.name}}</a>
      </div>
    </div>
  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    admin_menu('admin_menu.php','','<?php echo $user_hash;?>').mount('#app');
  </script>
  <script>// Enterキーが押された時にSubmitされるのを抑制する
      window.onload = function() {
        document.getElementById("form1").onkeypress = (e) => {
          // form1に入力されたキーを取得
          const key = e.keyCode || e.charCode || 0;
          if (key == 13) {// 13はEnterキーのキーコード
            e.preventDefault();// アクションを行わない
          }
        }    
      };    
  </script>
</BODY>
</html>