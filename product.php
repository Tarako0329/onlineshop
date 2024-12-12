<?php
  require "php_header.php";
  if(false === strpos($_SERVER['HTTP_REFERER'],ROOT_URL."shouhinMS.php")){
    $token = csrf_create();
  }
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
  $_SESSION["askNO"]="";
  $hinmei = $_GET["id"];
  $shouhin_id = $_GET["id"];
  $sql = "select 
    online.shouhinCD
    ,online.shouhinNM
    ,online.status
    ,online.short_info
    ,online.infomation
    ,online.customer_bikou
    ,online.tanka
    ,online.zeikbn
    ,online.shouhizei
    ,ifnull(online.hash_tag,'') as hash_tag
    ,NULL as rezCD
    ,online.tanka + online.shouhizei as zeikomikakaku
    ,'0' as ordered
    ,'0' as goukeikingaku
    ,ums_inline.*
    ,pic.pic as filename
  from shouhinMS_online online 
  inner join Users_online ums_inline
  on online.uid = ums_inline.uid
  left join shouhinMS_online_pic pic 
  on online.uid = pic.uid 
  and online.shouhinCD = pic.shouhinCD
  and pic.sort=1
  where concat(online.uid,'-',online.shouhinCD) = :hinmei 
  order by online.uid,online.shouhinCD";

  $stmt = $pdo_h->prepare($sql);
  $stmt->bindValue("hinmei", $hinmei, PDO::PARAM_STR);
  $stmt->execute();
  $count = $stmt->rowCount();
  $dataset = $stmt->fetchAll(PDO::FETCH_ASSOC);


  $sql = "select 
    online.uid
    ,online.shouhinCD
    ,online.shouhinNM
    ,pic.sort
    ,pic.pic as filename
  from shouhinMS_online online 
  left join shouhinMS_online_pic pic 
  on online.uid = pic.uid 
  and online.shouhinCD = pic.shouhinCD
  where concat(online.uid,'-',online.shouhinCD) = :hinmei 
  order by online.uid,online.shouhinCD,pic.sort";
  $stmt = $pdo_h->prepare($sql);
  $stmt->bindValue("hinmei", $hinmei, PDO::PARAM_STR);
  $stmt->execute();
  $pic_set = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $discription = "販売元:".$dataset[0]["yagou"]."/税込:".$dataset[0]["zeikomikakaku"]."円　".$dataset[0]["short_info"];

  $meta  = "<title>".$dataset[0]["shouhinNM"]." - 通販サイト『".TITLE." - ".$dataset[0]["yagou"]."』</title> \r\n";
  $meta .= "<meta name='description' content='".$discription."'>\r\n";
  $meta .= "<meta property='og:title' content='".$dataset[0]["shouhinNM"]." - 通販サイト『".TITLE." - ".$dataset[0]["yagou"]."』'>\r\n";
  $meta .= "<meta property='og:description' content=''.$discription.''>\r\n";
  $meta .= "<meta property='og:url' content=''.ROOT_URL.'product.php?id=".$hinmei."'>\r\n";
  $meta .= "<meta property='og:type' content='website'>\r\n";
  $meta .= "<meta property='og:site_name' content='通販サイト『".TITLE."』'>\r\n";
  $meta .= "<meta property='og:image' content='".ROOT_URL.$dataset[0]['filename']."'>\r\n";
  $meta .= "<meta property='fb:app_id' content='".$dataset[0]["fb_id"]."'>\r\n";
  $meta .= "<meta name='twitter:card' content='summary'>\r\n";
  $meta .= "<meta name='twitter:site' content='@".$dataset[0]["x_id"]."'>\r\n";

  $url = ROOT_URL."product.php?id=".$shouhin_id;
?>
<!DOCTYPE html>
<html lang='ja'>
<head prefix="og: http://ogp.me/ns#">
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_bs5.php" ;
    echo $meta;
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
            <div v-if='list.status==="soldout"' class='row'><div class='alert alert-warning'>品切れ中です</div></div>
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
              <div class='col-6 mt-2 mb-2 ps-3'>
                <button type="button" class="btn btn-primary fs-5" data-bs-toggle="modal" data-bs-target="#exampleModal" @click='set_qa_index(index)'>お問合せ<i class="bi bi-envelope-at-fill ms-2"></i></button>
              </div>
              <div class='col-6 mt-2 mb-2 ps-3'>
                <div class=''>
                  <!--LINE-->
                  <a href='https://line.me/R/share?text=<?php echo urlencode("私のおすすめ！".$dataset[0]["shouhinNM"]."\n".$discription."\n".$url)?>' target="_blank" rel="noopener noreferrer"><i class="bi bi-line line-green fs-1"></i></a>
                  <!--FACEBOOK-->
                  <a href='https://www.facebook.com/share.php?u=<?php echo $url; ?>' target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook facebook-blue fs-1 p-3"></i></a>
                  <!--TWITTER-->
                  <!--<a href="https://twitter.com/share?text=<?php //echo urlencode("私のおすすめ！".$dataset[0]["shouhinNM"]."\n".$discription."\n".$url)?>&url=<?php //echo $url; ?>&hashtags=<?php //echo $dataset[0]["hash_tag"];?>" rel="nofollow noopener noreferrer" target="_blank">-->
                  <a href="https://x.com/intent/tweet?text=<?php echo urlencode("私のおすすめ！".$dataset[0]["shouhinNM"]."\n".$discription."\n".$url)?>&url=<?php echo $url; ?>&hashtags=<?php echo $dataset[0]["hash_tag"];?>" rel="nofollow noopener noreferrer" target="_blank">
                  <i class="bi bi-twitter-x twitter-black fs-1"></i></a>
                  紹介する
                </div>
              </div>
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
                        <div class='pb-1'>
                          <p>【商品詳細】</p>
                          <p>{{list.infomation}}</p></div>
                        <div class='pb-1'>
                          <p>【送料・配送・納期などについて】</p>
                          <p>{{list.haisou}}</p>
                        </div>
                        <div>ご注文数：<span class='order'>{{list.ordered}}</span></div>
                        <div v-if='list.status==="show"' class='pb-3'>
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
          <template v-for='(pic_list,index2) in shouhinMS_pic' :key='pic_list.uid + pic_list.shouhinCD'>
            <template v-if='pic_list.uid + "-" + pic_list.shouhinCD ==="<?php echo $shouhin_id; ?>"'>
              <div v-if='pic_list.sort===1' class="carousel-item active" style='text-align: center;'>
                <img :src="pic_list.filename" class="d-block img-item-xl">
              </div>
              <div v-else class="carousel-item" style='text-align: center;'>
                <img :src="pic_list.filename" class="d-block img-item-xl">
              </div>
            </template>
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
  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">お問い合わせ</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for='umail' class="form-label">お名前</label>
          <input v-model='qa_name' type='mail' class='form-control mb-3' id='umail' placeholder="匿名可">
          <label for='umail' class="form-label">回答送付先メールアドレス</label>
          <input v-model='qa_mail' type='mail' class='form-control mb-3' id='umail'>
          <div class="form-check">
            <input v-model='qa_head' name='qa_head' type='radio' :value='qa_shouhinNM' class='form-check-input' id='qa_shouhinNM'>
            <label for='qa_shouhinNM' class="form-check-label">{{qa_shouhinNM}} について</label>
          </div>
          <div class="form-check mb-3">
            <input v-model='qa_head' name='qa_head' type='radio' :value='qa_yagou' class='form-check-input' id='qa_yagou'>
            <label for='qa_yagou' class="form-check-label">{{qa_yagou}} への問合せ</label>
          </div>
          <label for='send_mailbody' class="form-label">お問い合わせ内容</label>
          <textarea v-model='qa_text' type='memo' class='form-control' rows="20" id='send_mailbody' placeholder="なんでもお気軽にお問い合わせください。"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id='mail_modal_close'>Close</button>
          <button type='button' class='btn btn-primary' @click='send_email()' id='mail_send_btn'>送信</button>
        </div>
      </div>
    </div>
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