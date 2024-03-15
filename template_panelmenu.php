<?php
    require "php_header.php";
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
<BODY>
  <div id='app'>
  <HEADER class='container text-center common_header'>
    <h1><?php echo TITLE;?></h1>
  </HEADER>
  <MAIN class='container common_main'>
    <div v-for='(list,index) in menu' :key='list.name' class='row mb-3'>
      <div class='col-md-6 col-12'>
        <button type="button" class='btn btn-info'>{{list.name}}</button>
      </div>
    </div>
  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    admin_menu().mount('#app');
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