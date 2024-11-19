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
      .btn{
	      min-width: 120px;
      }
    </style>
    <TITLE><?php echo TITLE;?> 商品管理</TITLE>
</head>
<BODY>
  <?php include "header_tag_admin.php"  ?>
  <div id='app'>
  <MAIN class='container common_main' data-bs-spy="scroll">
    <transition>
      <div v-show="msg!==''" class="alert alert-warning" role="alert">
        {{msg}}
      </div>
    </transition>
    <div class='row mb-3 pt-3'>
      <div class='col-md-8 col-12'>
				<input type='radio' class='btn-check' name='mode' value='new' autocomplete='off' v-model='mode' id='eatin'>
				<label class='btn btn-outline-success ' for='eatin' style='border-radius:0;'>新規登録</label>
				<input type='radio' class='btn-check' name='mode' value='upd' autocomplete='off' v-model='mode' id='takeout'>
				<label class='btn btn-outline-success ' for='takeout' style='border-radius:0;'>修正</label>
      </div>
    </div>
    <div v-show='mode==="upd"' class='row' style=''>
      <div class='col-md-8 col-12 overflow-y-scroll ps-3 pe-3 mb-3' style='height:100px;'>
        <table class='table table-sm'>
          <tbody>
            <template v-for='(list,index) in shouhinMS' :key='list.shouhinCD+list.uid'>
              <tr>
                <td>{{list.shouhinNM}}</td>
                <td>
                  <select style='width: 90px' class='form-select' v-model='list.status' @change='upd_status(list.status,list.shouhinCD)'>
                    <option value='show'>販売中</option>
                    <option value='soldout'>受付停止</option>
                    <option value='stop'>販売停止</option>
                  </select>
                </td>
                <td><button type='button' style='min-width: 50px' class='btn btn-primary' @click='set_shouhinNM(list.shouhinNM)'>編集</button></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
    <div v-show='disp!=="none"'>
      <hr>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <label for='hinmei' class="form-label">商品名</label>
          <input type='text' v-model='shouhinNM' class='form-control' id='hinmei'>
          <!--<input type='text' list='hinmeilist' v-model='shouhinNM' class='form-control' id='hinmei'>
          <datalist id='hinmeilist'>
            <template v-for='list in shouhinMS' :key='list.shouhinCD'>
              <option :value='list.shouhinNM'></option>
            </template>
          </datalist>-->
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
		  		<input type='radio' class='btn-check' name='status' value='show' autocomplete='off' v-model='status' id='show'>
		  		<label class='btn btn-outline-primary ' for='show' style='border-radius:0;min-width: 90px;'>販売中</label>
		  		<input type='radio' class='btn-check' name='status' value='soldout' autocomplete='off' v-model='status' id='soldout'>
		  		<label class='btn btn-outline-warning ' for='soldout' style='border-radius:0;min-width: 90px;'>受付停止</label>
		  		<input type='radio' class='btn-check' name='status' value='stop' autocomplete='off' v-model='status' id='stop'>
		  		<label class='btn btn-outline-danger ' for='stop' style='border-radius:0;min-width: 90px;'>販売停止</label>
        </div>
        <small>受付停止：品切れ中で表示（お勧め）</small>
        <small>販売停止：商品は非表示（今後販売予定がない場合はコチラ）</small>
      </div>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <label for='tanka' class="form-label">単価</label>
          <input type='number' class='form-control' id='tanka' v-model='tanka'>
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
        <div class='col-md-8 col-12'>
          <label for='midasi' class="form-label">商品説明(見出し)</label>
          <small>Googleの検索結果や商品一覧の画面に表示されます。商品のアピールポイントを記入してください。(推奨80～110文字)</small>
          <textarea type='memo' class='form-control' id='midasi' rows="2" v-model='midasi' placeholder="商品一覧の画面に表示されます。商品のアピールポイントを記入してください。"></textarea>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <label for='setumei' class="form-label">商品説明(詳細)</label>
          <textarea type='memo' class='form-control' id='setumei' rows="5" v-model='info' placeholder='商品の仕様・原材料名　等、商品に関する詳細を記入'></textarea>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <label for='customer_bikou' class="form-label">お客様の備考</label>
          <textarea type='memo' class='form-control' id='customer_bikou' rows="3" v-model='customer_bikou' aria-labelledby="customer_bikou_help"></textarea>
          <div id="customer_bikou_help" class="form-text">
            お客様に記入いただくエリアの初期表示です。<br>
            例：セット商品の場合 => A～Eの商品から３種類を入力してください。
          </div>
        </div>
      </div>
      <hr>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <button type='button' class='btn btn-info' @click='input_file_btn("pic_file")'>写真アップロード</button>
          <input type='file' name='filename' style='display:none;' id='pic_file' @change='uploadfile("pic_file")' multiple accept="image/*">
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <small>写真の『削除』は『登録』ボタンに関係なく、即反映されます。</small>
          <div class='row'>
          <template v-for='(list,index) in pic_list' :key='list.filename'>
            <div class='col-md-4 col-6' style='padding:10px;'>
              <div style='width:100%;'><button type='button' class='btn btn-info mb-1' @click='resort(index)' style='min-width: 50px;'>表示順：{{list.sort}}</button></div>
              <div class='img-div' style='position:relative;'>
                <button type="button" class='btn btn-danger' style='position:absolute;top:0;right:0;min-width: 40px;' @click='pic_delete(list.filename)'>削除</button>
                <img :src="list.filename" class="d-block img-item-sm">
              </div>
            </div>
          </template>
          </div>
        </div>
      </div>

       <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <button type='button' class='btn btn-primary' @click='ins_shouhinMS'>登録</button>
          <button type='button' class='btn btn-warning ms-3' @click='set_shouhinNM("")'>キャンセル</button>
        </div>
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
    admin_menu('shouhinMS.php','','<?php echo $user_hash;?>').mount('#admin_menu');
    shouhinMS('shouhinMS.php','<?php echo $token; ?>','<?php echo $user_hash;?>').mount('#app');
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