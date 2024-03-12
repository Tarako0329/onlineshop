<?php
  require "php_header.php";
  $token = csrf_create();
  $_SESSION["user_id"] = "%";
?>
<!DOCTYPE html>
<html lang='ja'>
<head>
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_bs5.php" 
    ?>
    <!--<script src="./script/flow.js"></script>-->
    <style>
      .btn{
        min-width: 30px;
      }
    </style>
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY class='bd'>
  <div id='app'>
  <?php include "header_tag.php"  ?>
  <MAIN class='container common_main'>
    <!--<div class='cart text-center' role='button'><i class="bi bi-cart4" style="font-size: 3rem; color: cornflowerblue;"></i></div>-->
    <div class='row pb-3 pt-3'>
      <template v-for='(list,index) in orderlist_hd' :key='list.orderNO'>
        <div class='col-xl-6 col-md-6 col-12'><!--外枠-->
          <div class='container-fluid'>
            <div class='row pb-1'>
            </div>
            <div class='row'>
              <div class='col-12'><!--詳細-->
                <div class="accordion" :id="`accordion_${index}`">
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button" style='font-size:11px;font-weight:800;' type="button" data-bs-toggle="collapse" :data-bs-target="`#collapseOne_${index}`" aria-expanded="true" aria-controls="collapseOne">
                        <div style="width: 100%;">
                          <div style="width: 100%;">注文日[{{String(list.juchuu_date).substring(0,10)}}] 注文者：{{list.name}}  ￥{{Number(list.税込総額).toLocaleString()}}</div>
                          <div style="width: 100%;">
                            受付NO:[{{list.orderNO}}] 　
                            <template v-if='list.cancel===0'>
                              <template v-if='chk_recept'><template v-if='list.オーダー受付==="済"'><span style='color:blue;'>受付{{list.オーダー受付}}</span></template><template v-else ><span style='color:red;'>{{list.オーダー受付}}受付</span></template> 　</template>
                              <template v-if='chk_paid'><template v-if='list.入金==="済"'><span style='color:blue;'>入金{{list.入金}}</span></template><template v-else ><span style='color:red;'>{{list.入金}}入金</span></template> 　</template>
                              <template v-if='chk_sent'><template v-if='list.発送==="済"'><span style='color:blue;'>発送{{list.発送}}</span></template><template v-else ><span style='color:red;'>{{list.発送}}発送</span></template> </template>
                            </template>
                            <template v-else>
                              キャンセル済み
                            </template>
                          </div>
                        </div>
                      </button>
                    </h2>
                    <div :id="`collapseOne_${index}`" class="accordion-collapse collapse" :data-bs-parent="`#accordion_${index}`">
                      <div class="accordion-body">
                      <h3><i class="bi bi-shop"></i>【 {{list.yagou}} 】</h3>
                        <table class='table table-sm table-bordered caption-top'>
                          <caption>ご注文内容</caption>
                          <thead>
                            <tr>
                              <th>商品名</th>
                              <th>価格</th>
                              <th>注文数</th>
                              <th>総額</th>
                            </tr>
                          </thead>
                          <tbody v-for='(list2,index2) in orderlist_bd' :key='list2.orderNO+list2.shouhinCD'>
                            <template v-if='list.orderNO===list2.orderNO' >
                              <tr class="align-bottom">
                                <td>
                                  {{list2.shouhinNM}}
                                </td>
                                <td>{{(Number(list2.tanka)).toLocaleString()}}</td>
                                <td>
                                  <p>{{list2.su}}</p>
                                </td>
                                <td>{{(Number(list2.goukeitanka)+Number(list2.zei)).toLocaleString()}}</td>
                              </tr>
                              <tr v-if='list2.zei==="0.00"'>
                                <td colspan="4">備考:{{list.customer_bikou}}</td>
                              </tr>
                            </template>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td></td>
                              <td colspan="2">税込合計金額</td>
                              <td>{{Number(list.税込総額).toLocaleString()}}</td>
                            </tr>
                          </tfoot>
                        </table>
                        <p>【注文者要望】</p>
                        <p>{{list.bikou}}</p>
                        <hr>
                        <p>【注文者情報】</p>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label class="form-label">お名前・宛名</label>
                            <div class="input-group">
                              {{list.name}}
                            </div>
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label class="form-label">郵便番号</label>
                            <div class="input-group">
                              {{list.yubin}}
                            </div>
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label class="form-label">住所</label>
                            <div class="input-group">
                              {{list.jusho}}
                            </div>
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label class="form-label">TEL</label>
                            <div class="input-group">
                              {{list.tel}}
                            </div>
                          </div>
                        </div>
                        <div class='row mb-3'>
                          <div class='col-md-6 col-12'>
                            <label class="form-label">e-mail</label>
                            <div class="input-group">
                              {{list.mail}}
                            </div>
                          </div>
                        </div>
                        <div v-show='list.発送先有無==="有"'>
                          <hr>
                          <p>【発送先】</p>
                          <div class='row mb-3'>
                            <div class='col-md-6 col-12'>
                              <label class="form-label">お届け先：お名前・宛名</label>
                              <div class="input-group">
                                {{list.st_name}}
                              </div>
                            </div>
                          </div>
                          <div class='row mb-3'>
                            <div class='col-md-6 col-12'>
                              <label class="form-label">お届け先：郵便番号</label>
                              <div class="input-group">
                                {{list.st_yubin}}
                              </div>
                            </div>
                          </div>
                          <div class='row mb-3'>
                            <div class='col-md-6 col-12'>
                              <label class="form-label">お届け先：住所</label>
                              <div class="input-group">
                                {{list.st_jusho}}
                              </div>
                            </div>
                          </div>
                          <div class='row mb-3'>
                            <div class='col-md-6 col-12'>
                              <label class="form-label">お届け先：TEL</label>
                              <div class="input-group">
                                {{list.st_tel}}
                              </div>
                            </div>
                          </div>
                        </div>
                        <hr>
                        <div class='row mb-3'>
                          <div v-if='list.cancel===0 && cancel_lock[index].cancel==="unlock"' class='col-md-6 col-12'>
                            <button type='button' class='btn btn-danger ms-3' @click='set_order_sts(list.orderNO,"cancel",1,index)'>ご注文キャンセル</button>
                          </div>
                          <div v-else-if='list.cancel===1' class='col-md-6 col-12'>
                          <button type='button' class='btn btn-danger ms-3' @click='set_order_sts(list.orderNO,"cancel",1,index)' disabled>キャンセル済です</button>
                          </div>
                          <div v-else-if='cancel_lock[index].cancel==="lock"'class='col-md-6 col-12'>
                            ご注文の対応中のためキャンセルできません。<br>
                            直接ショップへお問い合わせください。<br>
                            <br>
                            【お問い合わせ先】<br>
                            {{list.yagou}}<br>
                            tel:{{list.tel}}<br>
                            mail:{{list.mail}}<br>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div><!--詳細-->
            </div>
            
          </div>
          <hr>
        </div><!--外枠-->
        
      </template>
    </div>

  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  <div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>
  </div><!--app-->

  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    order_mng('order_management','<?php echo $token; ?>').mount('#app');
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