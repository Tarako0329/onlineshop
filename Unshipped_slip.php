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
    <TITLE>発送サポート</TITLE>
</head>
<BODY>
  <div id='app'>
  <?php include "header_tag_admin.php"  ?>
  <MAIN class='container common_main'>
    <small>※受注管理画面で「未発送」になっている商品の一覧です</small>
    <div class='row mt-3'>
      <div class='col-12'>受注日で絞込</div>
      <div class='col-xl-3 col-md-3 col-6'>
        <label for='FROM' class="form-label">From</label>
        <input type='date' class='form-control' id='FROM' v-model='FROM'>
      </div>
      <div class='col-xl-3 col-md-3 col-6'>
        <label for='TO' class="form-label">To</label>
        <input type='date' class='form-control' id='TO' v-model='TO'>
      </div>
    </div>
    <div class='row mt-3'>
      <div class='col-xl-6 col-md-6 col-12'>
        <table class='table table-sm table-bordered caption-top'>
          <caption>未発送商品集計</caption>
          <thead>
            <tr>
              <th>商品名</th>
              <th>価格</th>
              <th>注文数</th>
            </tr>
          </thead>
          <tbody v-for='(list,index) in Unsippedlist' :key='list.shouhinNM'>
            <tr class="align-bottom">
              <td>
                {{list.shouhinNM}}
              </td>
              <td>{{(Number(list.tanka)).toLocaleString()}}</td>
              <td>
                <p>{{list.goukei}}</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class='row mt-3'>
      <div class='col-xl-6 col-md-6 col-12'>
        <table class='table table-sm table-bordered caption-top'>
          <caption>未発送商品内訳</caption>
          <thead>
            <tr>
              <th>商品名</th>
              <th>価格</th>
              <th>注文数</th>
            </tr>
          </thead>
          <tbody v-for='(list,index) in Unsippedlist_uchiwake' :key='list.shouhinNM+list.order_dt+list.name+list.orderNO'>
            <tr v-if='index===0'><td  class='pt-3 table-info' style='text-align:left;' colspan='5'>受注日：{{list.order_dt}}<br>{{list.name}} 様 受付番号：{{list.orderNO}}</td></tr>
            <tr v-else-if='(list.order_dt+list.name+list.orderNO)!==(Unsippedlist_uchiwake[index-1].order_dt+Unsippedlist_uchiwake[index-1].name+Unsippedlist_uchiwake[index-1].orderNO)'>
              <td  class='pt-3 table-info' style='text-align:left;' colspan='5'>受注日：{{list.order_dt}}<br>{{list.name}} 様 受付番号：{{list.orderNO}}</td></tr>
            <tr class="align-bottom">
              <td class='ps-3'>
                {{list.shouhinNM}}
              </td>
              <td>{{(Number(list.tanka)).toLocaleString()}}</td>
              <td>
                <p>{{list.goukei}}</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </MAIN>
  <FOOTER class='container common_footer' style='position:fixed;bottom: 0;'>
    <a :href='pdf_url' target="_blank" rel="noopener noreferrer" type='button' class='btn btn-outline-primary' style='min-width:40px;position:absolute;bottom:5px;right:5px;'><i class="bi bi-printer" style='font-size:14px;'></i></a>
  </FOOTER>
  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    admin_menu('Unshipped_slip.php','','<?php echo $user_hash;?>').mount('#admin_menu');
    Unsipped_slip('Unshipped_slip.php','<?php echo $token;?>','<?php echo $user_hash;?>').mount('#app');
  </script>
</BODY>
</html>