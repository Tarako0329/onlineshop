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

                    <template v-for='(pic_list,index2) in shouhinMS_pic' :key='pic_list.uid+pic_list.shouhinCD'>
                      <div v-if='list.shouhinCD===pic_list.shouhinCD && list.uid===pic_list.uid'>
                        <div v-if='pic_list.sort===1' class="carousel-item active" style='text-align: center;'>
                          <img :src="pic_list.filename" class="d-block img-item" @click='pic_zoom(list.shouhinCD)'>
                        </div>
                        <div v-else class="carousel-item" style='text-align: center;'>
                          <img :src="pic_list.filename" class="d-block img-item" @click='pic_zoom(list.shouhinCD)'>
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
                <small><i class="bi bi-shop"></i>【 {{list.yagou}} 】</small>
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
                      <button class="accordion-button" style='font-size:15px;font-weight:400;' type="button" data-bs-toggle="collapse" :data-bs-target="`#collapseOne_${index}`" aria-expanded="true" aria-controls="collapseOne">
                        商品詳細・ご注文はコチラ
                      </button>
                    </h2>
                    <div :id="`collapseOne_${index}`" class="accordion-collapse collapse" :data-bs-parent="`#accordion_${index}`">
                      <div class="accordion-body">
                        <div class='pb-1'><p>{{list.infomation}}</p></div>
                        <div>ご注文数：<span class='order'>{{list.ordered}}</span></div>
                        <div class='pb-3'>
                          <input type='radio' class='btn-check' name='status' value='show' autocomplete='off'  :id='`show_${index}`'>
				                  <label class='btn btn-primary ' :for='`show_${index}`' style='border-radius:0;' @click='order_count(index,1)'>＋</label>
				                  <input type='radio' class='btn-check' name='status' value='stop' autocomplete='off'  :id='`stop_${index}`'>
				                  <label class='btn btn-secondary ' :for='`stop_${index}`' style='border-radius:0;' @click='order_count(index,-1)'>－</label>
                        </div>
                        <div>
                          <label :for="`floating_${index}`">お客様備考記入欄</label>
                          <textarea class="form-control" placeholder="Leave a comment here" :id="`floating_${index}`" style="height: 100px" v-model='list.customer_bikou'></textarea>
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

    <div v-show='mode==="ordering"' data-bs-spy="scroll">
      <div class='row mb-1' id="scrollspyHeading1">
        <div class='col-md-6 col-12'>
          <h3><i class="bi bi-shop"></i>【 {{Charge_amount_by_store[0].yagou}} 】</h3>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-6 col-12'>
          <table class='table table-sm table-bordered caption-top'>
            <caption>ご注文内容</caption>
            <thead>
              <tr>
                <th>商品名</th>
                <th>税込価格</th>
                <th>ご購入数</th>
                <th>税込総額</th>
              </tr>
            </thead>
            <tbody v-for='(list,index) in get_ordered' :key='list.uid+list.shouhinCD'>
              <tr class="align-bottom">
                <td>
                  {{list.shouhinNM}}
                  <template v-for='(pic_list,index2) in shouhinMS_pic' :key='pic_list.uid+pic_list.shouhinCD'>
                    <template v-if='list.shouhinCD===pic_list.shouhinCD && list.uid===pic_list.uid'>
                      <div v-if='pic_list.sort===1' class="" style='text-align: center;width:100px;'>
                        <img :src="pic_list.filename" class="d-block img-item-sm">
                      </div>
                    </template>
                  </template>
                  <div style='width:120px;'><small>{{list.short_info}}</small></div>
                </td>
                <td>{{(Number(list.zeikomikakaku)).toLocaleString()}}</td>
                <td>
                  <p>{{list.ordered}}</p>
                  <input type='radio' class='btn-check' name='status' value='show' autocomplete='off'  :id='`show2_${index}`'>
				          <label class='btn btn-primary ' :for='`show2_${index}`' style='border-radius:0;' @click='ordered_count(index,1)'>＋</label>
				          <input type='radio' class='btn-check' name='status' value='stop' autocomplete='off'  :id='`stop2_${index}`'>
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
                <!--<td>{{Math.floor(order_kakaku).toLocaleString()}}</td>-->
                <td>{{Math.floor(Charge_amount_by_store[0].seikyu).toLocaleString()}}</td>
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
        <div class='col-md-6 col-12'>
          <input type='checkbox' v-model='order_sent_same' class='form-check-input' id='order_sent_same' placeholder='必須'>
          <label for='order_sent_same' class="form-check-label" style='margin-left:5px;'>お届け先は上記と同一</label>
        </div>
      </div>
      <div v-show='order_sent_same===false'>
        <div class='row mb-3'>
          <div class='col-md-6 col-12'>
            <label for='st_atena' class="form-label">お届け先：お名前・宛名</label>
            <input type='text' v-model='st_atena' class='form-control' id='st_atena' placeholder='必須'>
          </div>
        </div>
        <div class='row mb-3'>
          <div class='col-md-6 col-12'>
            <label for='st_yubin' class="form-label">お届け先：郵便番号</label>
            <input type='number' v-model='st_yubin' class='form-control' id='st_yubin' placeholder='必須'>
          </div>
        </div>
        <div class='row mb-3'>
          <div class='col-md-6 col-12'>
            <label for='st_jusho' class="form-label">お届け先：住所</label>
            <input type='text' v-model='st_jusho' class='form-control' id='st_jusho' placeholder='必須'>
          </div>
        </div>
        <div class='row mb-3'>
          <div class='col-md-6 col-12'>
            <label for='st_tel' class="form-label">お届け先：TEL</label>
            <input type='tel' v-model='st_tel' class='form-control' id='st_tel'>
          </div>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-6 col-12'>
          <label for="floating">自由記入欄</label>
          <textarea class="form-control" placeholder="ご要望等ございましたらご記入ください。" id="floating" style="height: 100px" v-model='od_bikou'></textarea>
        </div>
      </div>
      <div class='row mb-3'>
        <small>ご注文送信後、お客様メールアドレスにご注文内容確認のメールが自動配信されます。</small>
        <small>その後、別途ショップオーナーからのメールをもってご注文確定となります。</small>
        <small><span style='color:red;'><?php echo FROM;?></span> からのメールを受信できるよう設定をお願いします。</small>
        <div class='col-md-6 col-12'>
          <button type='button' class='btn btn-primary' @click='order_submit()'>ご注文送信</button>
        </div>
      </div>
    </div>

    <div v-show='mode==="ordered"'>
      <p>受付番号：[<span style='color:red;'>{{orderNO}}</span>] にてショップにご注文を送信いたしました。</p>
      <br>
      <p>ご注文内容を確認する自動配信メールを送信いたしましたのでご確認ください。</p>
      <br>
      <p>その後、ショップより改めてご注文内容、配送、お支払い等 についてのメールをお送りいたします。</p>
      <br>
      <p>もしメールが届いてないようでしたら、お手数をおかけしますが・・・・までご連絡いただけますでしょうか。</p>
      <br>
      <p>また、その際は受付番号をお知らせ頂けると、その後のやり取りがスムーズになります。</p>
      <br>
      <button type='button' class='btn btn-warning' @click='order_clear()'>上記を確認の上、受付番号を控えたらボタンを押してください。</button>
    </div>

    <div v-show='mode!=="ordered"' class="toast-container position-fixed bottom-0 end-0 p-3" style='width:250px;'>
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style='display: block;'>
        <div class="toast-header">
          <img src="img/icon-16x16.png" class="rounded me-2" >
          <strong class="me-auto">ご注文金額</strong>
        </div>
        <div class="toast-body" style='padding:5px;' v-for='(list,index) in Charge_amount_by_store' :key='list.uid'>
          <div class='row ' style='padding:0;'>
            <div class='col-12 d-flex flex-row-reverse'>
              <!--<div><button type='button' class='btn btn-primary ms-3' style='width:30px;' @click='ordering(list.uid)'>{{btn_name}}</button></div>-->
              <div><a href="#scrollspyHeading1" type='button' class='btn btn-primary ms-3' style='width:30px;' @click='ordering(list.uid)'>{{btn_name}}</a></div>
              <div class='text-end' style='width:130px;'><span class='kakaku'>{{Math.floor(list.seikyu).toLocaleString()}} 円</span></div>
              <div class='text-start' style='max-width:90px;white-space: nowrap;overflow: hidden;'>{{list.yagou}}：</div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  <Transition>
    <div v-show='img_zoom' class='img-wrap'>
      <button type="button" class="btn-close" aria-label="Close" style='position:fixed;top:5px;right:5px;' @click='pic_zoom(0)'></button>
      <div id="carousel" class="carousel slide" style='width:90%;max-width: 800px;'>
        <div class="carousel-inner">
          <template v-for='(pic_list,index2) in shouhinMS_pic_sel' :key='pic_list.shouhinCD'>
            <div v-if='pic_list.sort===1' class="carousel-item active" style='text-align: center;'>
              <img :src="pic_list.filename" class="d-block img-item-xl">
            </div>
            <div v-else class="carousel-item" style='text-align: center;'>
              <img :src="pic_list.filename" class="d-block img-item-xl">
            </div>
          </template>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
      <!--<div><img src="upload/2_1_20240222-111959_41d6edd81784741326e30c20678fad59_t.jpeg" class="d-block img-item-xl"></div>-->
    </div>
  </Transition>
  <div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>

  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    sales('index','<?php echo $token; ?>').mount('#app');
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