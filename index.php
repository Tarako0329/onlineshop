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
    <script src="./script/flow.js"></script>
    <style>
      .btn{
        min-width: 50px;
      }
    </style>
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY class='bd'>
  <div id='app'>
  <?php include "header_tag.php"  ?>
  <MAIN class='container common_main'>
    <!--<div class='cart text-center' role='button'><i class="bi bi-cart4" style="font-size: 3rem; color: cornflowerblue;"></i></div>-->
    <div v-if='mode==="shopping"' class='row pb-3 pt-3'>
      <template v-for='(list,index) in shouhinMS_SALE' :key='list.shouhinCD'>
        <div class='col-xl-4 col-md-6 col-12'><!--外枠-->
          <div class='container-fluid'>
            <div class='row pb-1'>
              <div class='col-6'><!--写真-->
                <div :id="`carouselExample_${index}`" class="carousel slide">
                  <div class="carousel-inner">

                    <template v-for='(pic_list,index2) in shouhinMS_pic' :key='pic_list.shouhinCD'>
                      <div v-if='list.shouhinCD===pic_list.shouhinCD'>
                        <div v-if='pic_list.sort===1' class="carousel-item active" style='text-align: center;'>
                          <img :src="pic_list.filename" class="d-block img-item">
                        </div>
                        <div v-else class="carousel-item" style='text-align: center;'>
                          <img :src="pic_list.filename" class="d-block img-item">
                        </div>
                      </div>
                    </template>

                  </div>
                  <button class="carousel-control-prev" type="button" :data-bs-target="`#carouselExample_${index}`" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" :data-bs-target="`#carouselExample_${index}`" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
              </div><!--写真-->
              <div class='col-6'><!--見出-->
                <h3>『{{list.shouhinNM}}』</h3>
                <div class='pb-3'>
                  <p>税込価格：<span class='kakaku'>{{(Number(list.zeikomikakaku)).toLocaleString()}} 円</span></p>
                  <p>内税：<span class='zei'>{{Number(list.shouhizei).toLocaleString()}}</span></p>
                </div>
                <p>{{list.short_info}}</p>
              </div><!--見出-->
            </div>
            <div class='row'>
              <div class='col-12'><!--詳細-->
                <div class="accordion" :id="`accordion_${index}`">
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button" style='font-size:15px;font-weight:800;' type="button" data-bs-toggle="collapse" :data-bs-target="`#collapseOne_${index}`" aria-expanded="true" aria-controls="collapseOne">
                        商品詳細・ご注文はコチラ
                      </button>
                    </h2>
                    <div :id="`collapseOne_${index}`" class="accordion-collapse collapse" :data-bs-parent="`#accordion_${index}`">
                      <div class="accordion-body">
                        <div class='pb-1'><p>{{list.infomation}}</p></div>
                        <div>ご注文数：<span class='order'>{{list.ordered}}</span></div>
                        <div class='pb-3'>
                          <input type='radio' class='btn-check' name='status' value='show' autocomplete='off' v-model='status' :id='`show_${index}`'>
				                  <label class='btn btn-primary ' :for='`show_${index}`' style='border-radius:0;' @click='order_count(index,1)'>＋</label>
				                  <input type='radio' class='btn-check' name='status' value='stop' autocomplete='off' v-model='status' :id='`stop_${index}`'>
				                  <label class='btn btn-secondary ' :for='`stop_${index}`' style='border-radius:0;' @click='order_count(index,-1)'>－</label>
                        </div>
                        <div class="form-floating">
                          <textarea class="form-control" placeholder="Leave a comment here" :id="`floating_${index}`" style="height: 100px">{{list.customer_bikou}}</textarea>
                          <label :for="`floating_${index}`">お客様備考記入欄</label>
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

    <div v-if='mode==="ordering"'>
      <div class='row mb-3'>
        <div class='col-md-6 col-12'>
          <table class='table table-sm table-bordered caption-top'>
            <caption>ご注文内容</caption>
            <thead>
              <tr>
                <th>商品名</th>
                <th>税込単価</th>
                <th>ご購入数</th>
                <th>税込金額</th>
              </tr>
            </thead>
            <tbody v-for='(list,index) in get_ordered' :key='list.shouhinCD'>
              <tr>
                <td>{{list.shouhinNM}}</td>
                <td>{{(Number(list.zeikomikakaku)).toLocaleString()}}</td>
                <td>
                  <input type='number' class='form-control' style='width:100px;' v-model='list.ordered'>
                  <input type='radio' class='btn-check' name='status' value='show' autocomplete='off' v-model='status' :id='`show2_${index}`'>
				          <label class='btn btn-primary ' :for='`show2_${index}`' style='border-radius:0;' @click='ordered_count(index,1)'>＋</label>
				          <input type='radio' class='btn-check' name='status' value='stop' autocomplete='off' v-model='status' :id='`stop2_${index}`'>
				          <label class='btn btn-secondary ' :for='`stop2_${index}`' style='border-radius:0;' @click='ordered_count(index,-1)'>－</label>
                </td>
                <td>{{((Number(list.tanka)+Number(list.shouhizei))*Number(list.ordered)).toLocaleString()}}</td>
              </tr>
              <tr>
                <td colspan="4">{{list.customer_bikou}}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td></td>
                <td></td>
                <td>合計金額</td>
                <td>{{order_kakaku.toLocaleString()}}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-6 col-12'>
          <label for='od_atena' class="form-label">お名前・宛名</label>
          <input type='text' v-model='od_atena' class='form-control' id='od_atena' placeholder='必須'>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-6 col-12'>
          <label for='od_yubin' class="form-label">郵便番号</label>
          <input type='number' v-model='od_yubin' class='form-control' id='od_yubin' placeholder='必須'>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-6 col-12'>
          <label for='od_jusho' class="form-label">住所</label>
          <input type='text' v-model='od_jusho' class='form-control' id='od_jusho' placeholder='必須'>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-6 col-12'>
          <label for='od_tel' class="form-label">TEL</label>
          <input type='tel' v-model='od_tel' class='form-control' id='od_tel'>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-6 col-12'>
          <label for='od_mail' class="form-label">e-mail</label>
          <input type='email' v-model='od_mail' class='form-control' id='od_mail' placeholder='必須'>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-6 col-12 form-floating'>
          <textarea class="form-control" placeholder="ご要望等ございましたらご記入ください。" id="floating" style="height: 100px"></textarea>
          <label for="floating">自由記入欄</label>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-6 col-12'>
          <button type='button' class='btn btn-primary' @click='order_submit()'>ご注文送信</button>
        </div>
        <small>ご注文送信後、お客様メールアドレスにご注文内容の自動送信されます。その後、別途ショップオーナーからのメールをもってご注文確定となります。</small>
      </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style='width:150px;'>
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style='display: block;'>
        <div class="toast-header">
          <img src="img/icon-16x16.png" class="rounded me-2" >
          <strong class="me-auto">ご注文金額</strong>
        </div>
        <div class="toast-body">
          <div>合計：<span class='kakaku'>{{Math.floor(order_kakaku).toLocaleString()}} 円</span></div>
          <button type='button' class='btn btn-primary' @click='ordering'>{{btn_name}}</button>
        </div>
      </div>
    </div>

  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    shouhinMS('index','<?php echo $token; ?>').mount('#app');
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