<?php
  require "php_header.php";
  $token = csrf_create();
?>
<!DOCTYPE html>
<html lang='ja'>
<head>
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_bs5.php" 
    ?>
    <!--<script src="./script/flow.js"></script>-->
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY class='bd'>
  <div id='app'>
  <?php include "header_tag.php"  ?>
  <MAIN class='container common_main'>
    <!--<transition>
      <div v-show="msg!==''" class="alert alert-warning" role="alert">
        {{msg}}
      </div>
    </transition>-->
    <div class='row mb-3 pt-3'>
      <div class='col-md-6 col-12'>
        <label for='hinmei' class="form-label">サイト名</label>
        <input type='text' class='form-control' id='hinmei' v-model='site_name'>
      </div>
    </div>
    <div class='row mb-3 pt-3'>
      <div class='col-md-6 col-12'>
        <label for='hinmei' class="form-label">屋号</label>
        <input type='text' class='form-control' id='hinmei' v-model='yagou'>
      </div>
    </div>
    <div class='row mb-3 pt-3'>
      <div class='col-md-6 col-12'>
        <label for='hinmei' class="form-label">代表者</label>
        <input type='text' class='form-control' id='hinmei' v-model='shacho'>
      </div>
    </div>
    <div class='row mb-3 pt-3'>
      <div class='col-md-6 col-12'>
        <label for='hinmei' class="form-label">屋号所在地</label>
        <input type='text' class='form-control' id='hinmei' v-model='jusho'>
      </div>
    </div>
    <div class='row mb-3 pt-3'>
      <div class='col-md-6 col-12'>
        <label for='hinmei' class="form-label">問い合せ担当者</label>
        <input type='text' class='form-control' id='hinmei' v-model='tantou'>
      </div>
    </div>
    <div class='row mb-3 pt-3'>
      <div class='col-md-6 col-12'>
        <label for='hinmei' class="form-label">問い合せ窓口（TEL）</label>
        <input type='text' class='form-control' id='hinmei' v-model='tel'>
      </div>
    </div>
    <div class='row mb-3 pt-3'>
      <div class='col-md-6 col-12'>
        <label for='hinmei' class="form-label">問い合せ窓口（MAIL）</label>
        <input type='mail' class='form-control' id='hinmei' v-model='mail'>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <label for='midasi' class="form-label">注文受付メールテンプレート</label><button type='button' class='btn btn-warning m-2' style='width:70px;min-width:50px;' @click='()=>{mail_body=""}'>クリア</button>
        <textarea type='memo' class='form-control' id='midasi' rows="10" v-model='mail_body'></textarea>
        <div class='row mb-3 mt-2'>
          <div class='col-12'>
            定型値(例：購入者ボタンを押すと、メール本文で「購入者」に変換されます。)
          </div>
        </div>
        <div class='row mb-3'>
          <div class='col-12'>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<購入者名>"}' style='width:70px;min-width:50px;'>購入者名</button>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<注文内容>"}' style='width:70px;min-width:50px;'>注文内容</button>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<購入者情報>"}' style='width:70px;min-width:50px;'>購入者情報</button>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<届け先情報>"}' style='width:70px;min-width:50px;'>届け先情報</button>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<問合担当者>"}' style='width:70px;min-width:50px;'>問合担当者</button>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<問合TEL>"}' style='width:70px;min-width:50px;'>問合TEL</button>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<問合MAIL>"}' style='width:70px;min-width:50px;'>問合MAIL</button>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<自社名>"}' style='width:70px;min-width:50px;'>自社名</button>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<代表者>"}' style='width:70px;min-width:50px;'>代表者</button>
            <button type='button' class='btn btn-info m-2' @click='()=>{mail_body=mail_body+"<自社住所>"}' style='width:70px;min-width:50px;'>自社住所</button>
          </div>
        </div>
        <small>メールサンプル</small>
        <div class='p-2' style='white-space: pre-wrap;border:1px solid black;'>{{mail_body_sample}}</div>
      </div>
    </div>
    <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <button type='button' class='btn btn-primary m-2' @click='set_user'>登録</button>
      </div>
    </div>


<!--
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

     <div class='row mb-3'>
      <div class='col-md-6 col-12'>
        <button type='button' class='btn btn-primary' @click='ins_shouhinMS'>登録</button>
      </div>
    </div>
-->

  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    configration('configration','<?php echo $token; ?>').mount('#app');
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