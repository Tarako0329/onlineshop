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
<BODY class='bd'>
  <div id='app'>
  <?php include "header_tag.php"  ?>
  <MAIN class='container common_main'>
    <transition>
      <div v-show="msg!==''" class="alert alert-warning" role="alert">
        {{msg}}
      </div>
    </transition>
    <div class='row mb-3 pt-3'>
      <div class='col-md-6 col-12'>
				<input type='radio' class='btn-check' name='mode' value='new' autocomplete='off' v-model='mode' id='eatin'>
				<label class='btn btn-outline-success ' for='eatin' style='border-radius:0;'>新規登録</label>
				<input type='radio' class='btn-check' name='mode' value='upd' autocomplete='off' v-model='mode' id='takeout'>
				<label class='btn btn-outline-success ' for='takeout' style='border-radius:0;'>修正</label>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <label for='hinmei' class="form-label">商品名</label>
        <input type='text' list='hinmeilist' v-model='shouhinNM' class='form-control' id='hinmei'>
        <datalist id='hinmeilist'>
          <template v-for='list in shouhinMS' :key='list.shouhinCD'>
            <option :value='list.shouhinNM'></option>
          </template>
        </datalist>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
				<input type='radio' class='btn-check' name='status' value='show' autocomplete='off' v-model='status' id='show'>
				<label class='btn btn-outline-primary ' for='show' style='border-radius:0;'>販売中</label>
				<input type='radio' class='btn-check' name='status' value='stop' autocomplete='off' v-model='status' id='stop'>
				<label class='btn btn-outline-danger ' for='stop' style='border-radius:0;'>販売停止中</label>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <label for='tanka' class="form-label">単価</label>
        <input type='text' class='form-control' id='tanka' v-model='tanka'>
        <label for='zei' class="form-label">税区分</label>
        <select class='form-select' id='zei' v-model='zei'>
          <option value="0">非課税</option>
          <option value="1001">8%</option>
          <option value="1101">10%</option>
        </select>
        <p>税込価格：{{zeikomi.toLocaleString()}} ({{shouhizei.toLocaleString()}})</p>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <label for='midasi' class="form-label">商品説明(見出し)</label>
        <textarea type='memo' class='form-control' id='midasi' rows="2" v-model='midasi'></textarea>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <label for='setumei' class="form-label">商品説明(詳細)</label>
        <textarea type='memo' class='form-control' id='setumei' rows="5" v-model='info'></textarea>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <button type='button' class='btn btn-info' @click='input_file_btn("pic_file")'>写真アップロード</button>
        <input type='file' name='filename' style='display:none;' id='pic_file' @change='uploadfile("pic_file")' multiple accept="image/*">
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <div class='row'>
        <template v-for='(list,index) in pic_list' :key='list.filename'>
          <div class='col-md-3 col-6' style='padding:10px;'>
            <button type='button' class='btn btn-info mb-1' @click='resort(index)' style='min-width: 50px;'>表示順：{{list.sort}}</button>
            <img :src="list.filename" class="d-block" style='width:90%;margin-bottom:5px;'>
          </div>
        </template>
        </div>
      </div>
    </div>

    <!--
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <div id="carouselExample" class="carousel slide">
          <div class="carousel-inner">
            <template v-for='list in pic_list' :key='pic_list'>
              <div class="carousel-item active">
                <img :src="list" class="d-block w-100" alt="...">
              </div>
            </template>
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
-->
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <button type='button' class='btn btn-primary' @click='ins_shouhinMS'>登録</button>
      </div>
    </div>



  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    shouhinMS('shouhinMS').mount('#app');
  </script>
  <script>// Enterキーが押された時にSubmitされるのを抑制する
      window.onload = function() {
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