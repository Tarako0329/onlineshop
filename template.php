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
  <HEADER class='container-fluid text-center common_header'>
    <h1><?php echo TITLE;?></h1>
  </HEADER>
  <MAIN class='container common_main'>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
      <label for='hinmei' class="form-label">商品名</label>
      <input type='text' list='hinmeilist' class='form-control' id='hinmei'>
      <datalist id='hinmeilist'>
        <option value='パウンドケーキ'></option>
      </datalist>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
      <label for='tanka' class="form-label">単価</label>
      <input type='text' class='form-control' id='tanka'>
      <label for='zei' class="form-label">税区分</label>
      <input type='text' class='form-control' id='zei'>
      </div>
      {{tanka.toLocaleString()}}
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <label for='setumei' class="form-label">商品説明</label>
        <textarea type='memo' class='form-control' id='setumei' rows="5"></textarea>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <button type='button' class='btn btn-primary'>写真アップロード</button>
        <input type='file' name='filename' style='display:none;' id='pic_file'>
      </div>
    </div>

    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <div id="carouselExample" class="carousel slide">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="..." class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
              <img src="..." class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
              <img src="..." class="d-block w-100" alt="...">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </div>

    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <button type='button' class='btn btn-primary'>登録</button>
      </div>
    </div>



  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    shouhinMS().mount('#app');
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