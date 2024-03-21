<?php
  require "php_header.php";
  $token = csrf_create();
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
    <style>

    </style>
    <TITLE><?php echo TITLE;?> 商品管理</TITLE>
</head>
<BODY>
  <?php include "header_tag_admin.php"  ?>
  <div id='app'>
  <MAIN class='container common_main' data-bs-spy="scroll">
    <div class='row mb-3'>
      <div class='col-md-5 col-7'>
        <label for='hinmei' class="form-label">決済名</label>
        <input type='text' class='form-control' id='hinmei' v-model='new_type[0].payname'>
      </div>
      <div class='col-md-3 col-5'>
        <label for='zei' class="form-label">種類</label>
        <select class='form-select' id='zei' v-model='new_type[0].types'>
          <option value="bank">銀行振込</option>
          <option value="QR">QR決済</option>
          <option value="other">その他</option>
        </select>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-8 col-12'>
        <label for='hinmei' class="form-label">振込先</label>
        <input type='text' class='form-control' id='hinmei' v-model='new_type[0].source'>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-8 col-12'>
        <label for='hinmei' class="form-label">QRコード選択</label>
        <input type="file" name='filename' class="form-control" id='logo' v-model='new_type[0].source'>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-8 col-12'>
        <label for='hinmei' class="form-label">補足</label>
        <input type='text' class='form-control' id='hinmei' v-model='new_type[0].hosoku'>
      </div>
    </div>

     <div class='row mb-3'>
      <div class='col-md-8 col-12'>
        <button type='button' class='btn btn-primary' >追加</button>
      </div>
    </div>


  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  <div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>
  </div><!--app-->
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script src="script/shouhinMS_vue3.js?<?php echo $time; ?>"></script>
  <script>
    admin_menu('settlement.php','','<?php echo $user_hash;?>').mount('#admin_menu');
    settlement('settlement.php','<?php echo $token; ?>','<?php echo $user_hash;?>').mount('#app');
  </script>
  <script>// Enterキーが押された時にSubmitされるのを抑制する
      window.onload = function() {
        //document.getElementById("menu_01").classList.add("active");
        //console_log(document.getElementById("menu_01").classList)
        document.getElementById("app").onkeypress = (e) => {
          // form1に入力されたキーを取得
          const key = e.keyCode || e.charCode || 0;
          if (key == 13) {// 13はEnterキーのキーコード
            //e.preventDefault();// アクションを行わない
          }
        }    
      };    
  </script>
</BODY>
</html>