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
    <style>
      .btn{
        min-width: 50px;
      }
    </style>
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY class='bd'>
  <div id='app'>
  <HEADER class='container-fluid text-center common_header'>
    <h2><?php echo TITLE;?></h2>
  </HEADER>
  <MAIN class='container common_main'>
    <div class='row mb-3 pt-3'>
      <template v-for='(list,index) in shouhinMS' :key='list.shouhinCD'>
        <div class='col-md-6 col-12 p-3'><!--外枠-->
          <div class='container-fluid'>
            <div class='row'>
              <div class='col-6'><!--写真-->
                <div :id="`carouselExample_${index}`" class="carousel slide">
                  <div class="carousel-inner">

                    <template v-for='(pic_list,index2) in shouhinMS_pic' :key='pic_list.shouhinCD'>
                      <div v-if='list.shouhinCD===pic_list.shouhinCD'>
                        <div v-if='pic_list.sort===1' class="carousel-item active">
                          <img :src="pic_list.filename" class="d-block img-item">
                        </div>
                        <div v-else class="carousel-item">
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
              <h3>{{list.shouhinCD}} : {{list.shouhinNM}}</h3>
              <p>税込価格：{{list.tanka.toLocaleString()}} 円</p>
              <p>内消費税：{{list.shouhizei.toLocaleString()}} 円</p>
              <p>{{list.short_info}}</p>
            </div><!--見出-->
            </div>
            <div class='row'>
              <div class='col-12'><!--詳細-->
                <div class="accordion" :id="`accordion_${index}`">
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button" type="button" data-bs-toggle="collapse" :data-bs-target="`#collapseOne_${index}`" aria-expanded="true" aria-controls="collapseOne">
                        商品詳細・注文
                      </button>
                    </h2>
                    <div :id="`collapseOne_${index}`" class="accordion-collapse collapse" :data-bs-parent="`#accordion_${index}`">
                      <div class="accordion-body">
                        <div><p>{{list.infomation}}</p></div>
                        <div>
                          注文
                          <input type='radio' class='btn-check' name='status' value='show' autocomplete='off' v-model='status' id='show'>
				                  <label class='btn btn-primary ' for='show' style='border-radius:0;'>＋</label>
				                  <input type='radio' class='btn-check' name='status' value='stop' autocomplete='off' v-model='status' id='stop'>
				                  <label class='btn btn-secondary ' for='stop' style='border-radius:0;'>－</label>
                        </div>
                        <div class="form-floating">
                          <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" style="height: 100px"></textarea>
                          <label for="floatingTextarea2">オーダー備考</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div><!--詳細-->
            </div>
          </div>
        </div><!--外枠-->
        <hr>
      </template>
    </div>

  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    shouhinMS('index').mount('#app');
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