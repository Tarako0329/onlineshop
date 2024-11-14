<?php
  require "php_header.php";
  $token = csrf_create();
  if(!empty($_GET["key"])){
    $user_hash = $_GET["key"] ;
    $_SESSION["user_id"] = rot13decrypt2($user_hash);
  }else{
    $_SESSION["user_id"]='%';
  }
  if(empty($_GET["id"])){
    echo "URLが不正です";
    exit();
  }
  /*if(empty($_GET["shop_id"])){
    echo "URLが不正です";
    exit();
  }*/
  $shouhin_id = $_GET["id"];
//  $shop_id = $_GET["shop_id"];
?>
<!DOCTYPE html>
<html lang='ja'>
<head prefix="og: http://ogp.me/ns#">
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_bs5.php" 
    ?>
    <style>
      .btn{
        min-width: 50px;
      }
      .img-item {
      	width: 100%;
      	max-width: 350px;
      	height: 100%;
      	max-height: 350px;
      	object-fit: cover; /* width, heightといっしょに使います */
      	border: 1px solid #111;
        margin: auto;
        display: block;
      }
      .kakaku{
      	font-size:1.6rem;
      	color:#FF0080;
      	font-weight: 900;
      }
      .zei{
      	font-size:1.4rem;
      	color:#FF0080;
      }
      p{
        font-size:14px;
      }

    </style>
    <script type="importmap">
      {
        "imports": {
          "unheadvue": "https://cdn.jsdelivr.net/npm/@unhead/vue@1.11.11/+esm"
          ,"vue":"https://unpkg.com/vue@3/dist/vue.esm-browser.js"
        }
      }
    </script>

</head>
<BODY >
  <div id='app' style='min-height: 100%' >
  <?php include "header_tag.php"  ?>
  <MAIN class='container common_main' data-bs-spy="scroll" data-bs-target="#scrollspy">
    <div id='scrollspyHeading' style='margin-top:-50px;height:50px;'></div>
    <div v-if='mode==="shopping"' class='row pb-3 pt-3' style='min-height: 100%'>
      <template v-for='(list,index) in shouhinMS' :key='list.shouhinCD+list.uid'>
        <div v-if='list.uid + "-" + list.shouhinCD ==="<?php echo $shouhin_id; ?>"' class='col-12'><!--外枠-->
          <div class='container-fluid'>
            <div class='row pb-1'>
              <div class='col-md-6 mb-3'><!--写真-->
                <div :id="`carouselExample_${index}`" class="carousel slide">
                  <div class="carousel-inner">

                    <template v-for='(pic_list,index2) in shouhinMS_pic' :key='pic_list.uid+pic_list.shouhinCD+pic_list.filename'>
                      <div v-if='list.shouhinCD===pic_list.shouhinCD && list.uid===pic_list.uid'>
                        <div v-if='pic_list.sort===1' class="carousel-item active" style='text-align: center;'>
                          <img :src="pic_list.filename" class="d-block img-item" @click='pic_zoom(pic_list.uid,list.shouhinCD)'>
                        </div>
                        <div v-else class="carousel-item" style='text-align: center;'>
                          <img :src="pic_list.filename" class="d-block img-item" @click='pic_zoom(pic_list.uid,list.shouhinCD)'>
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
              <div class='col-md-6 ps-5'><!--見出-->
                <h3><i class="bi bi-shop"></i>【 {{list.yagou}} 】</h3>
                <h1>『{{list.shouhinNM}}』</h1>
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
                        商品詳細
                      </button>
                    </h2>
                    <div :id="`collapseOne_${index}`" class="accordion-collapse collapse show" :data-bs-parent="`#accordion_${index}`">
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
  </MAIN>
  
  <FOOTER class='container common_footer fixed-bottom'>
    <div v-show='mode!=="ordered"' class="toast-container position-absolute bottom-0 end-0 p-3" style='width:250px;'>
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style='display: block;'>
        <div class="toast-header">
          <img src="img/icon-16x16.png" class="rounded me-2" >
          <strong class="me-auto">ご注文金額</strong>
        </div>
        <div class="toast-body" style='padding:5px;' v-for='(list,index) in Charge_amount_by_store' :key='list.uid'>
          <div class='row ' style='padding:0;'>
            <div class='col-12 d-flex flex-row-reverse'>
              <div id='scrollspy'><a href="#scrollspyHeading" type='button' class='btn btn-primary ms-3' style='width:30px;' @click='ordering(list.uid)'>{{btn_name}}</a></div>
              <div class='text-end' style='width:130px;'><span class='kakaku'>{{Math.floor(list.seikyu).toLocaleString()}} 円</span></div>
              <div class='text-start' style='max-width:90px;white-space: nowrap;overflow: hidden;'>{{list.yagou}}：</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FOOTER>
  <Transition><!--写真ズーム-->
    <div v-show='img_zoom' class='img-wrap' >
      <button type="button" class="btn-close" aria-label="Close" style='position:fixed;top:55px;right:5px;opacity:1;width: 4em;height: 4em;' @click='pic_zoom(0,0)'></button>
      <div id="carousel" class="carousel slide" style='max-width:90%;width: 800px;aspect-ratio: 4 / 3;'>
        <div class="carousel-inner">
          <template v-for='(pic_list,index2) in shouhinMS_pic' :key='pic_list.shouhinCD'>
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
    </div>
  </Transition>
  <div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>

  </div>
  <script type="module">
    import {product_page} from "./script/product_vue3.js?<?php echo $time; ?>"
    product_page('product','<?php echo $token; ?>','<?php echo $shouhin_id; ?>','<?php echo TITLE; ?>').mount('#app');
  </script>
  <script>// Enterキーが押された時にSubmitされるのを抑制する
      window.onload = function() {
        console.log('window.onload')
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