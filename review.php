<?php
  require "php_header.php";
  $token = csrf_create();
  if(!empty($_GET["key"])){
    $shouhinCD = rot13decrypt2($_GET["key"]);
    $shop_id = rot13decrypt2($_GET["key2"]);
  }else{
    //echo "不正アクセスです<br>";
    //echo rot13encrypt2("05699240");
    //exit();
  }
  //$shouhinCD = '4';
  //$shop_id = '2';

  $sql="SELECT 
    u.yagou
    ,u.logo
    ,m.shouhinNM
    ,r.*
    ,IF(r.NoName = 'true','匿名希望',r.Contributor) as 投稿者
    ,p.pic 
  FROM shouhinMS_online m
    left join review_online r
    on r.shop_id = m.uid
    and r.shouhinCD = m.shouhinCD
    inner join Users_online u
    on m.uid = u.uid
    inner join shouhinMS_online_pic p
    on m.shouhinCD = p.shouhinCD
    and m.uid = p.uid
    and p.sort=1
    where m.shouhinCD = :shouhinCD
    and m.uid = :shop_id
  order by r.insdatetime desc";
  $stmt = $pdo_h->prepare($sql);
  $stmt->bindValue("shouhinCD", $shouhinCD, PDO::PARAM_STR);
  $stmt->bindValue("shop_id", $shop_id, PDO::PARAM_STR);
  $stmt->execute();
  $review = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang='ja'>
<head>
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_bs5.php" 
    ?>
    <style>
      .btn{
        min-width: 30px;
      }
      .balloon-chat {
        display: flex;
        flex-wrap: wrap;
      }
        /* 左の吹き出し */
      .balloon-chat.left { 
        flex-direction: row; /* 左から右に並べる */
      }
        /* 右の吹き出し */
      .balloon-chat.right { 
        flex-direction: row-reverse; /* 右から左に並べる */
      }
        /* 吹き出しの入力部分の作成 */
      .chatting {
        position: relative;
        display: inline-block; /* 吹き出しが文字幅に合わせます */ 
        margin: 10px ;
        background: #ccffcc; /*吹き出しのカラーはここで変更*/
        text-align: left; /*テキストの位置はここで変更*/
        border-radius: 12px; /*吹き出しの丸み具合を変更*/
        max-width:  calc(100% - 120px);
      }
        /* 吹き出しの三角部分の作成 */
      .chatting::after {
        content: "";
        border: 15px solid transparent;
        border-top-color: #ccffcc; /*カラーはここで変更（吹き出し部分と合わせる）*/
        position: absolute;
        top: 10px; /*三角部分の高さを変更*/
      }
        .left .chatting::after {
        left: -15px; /*左側の三角部分の位置を変更*/
      }
        .right .chatting::after {
        right: -15px; /*右側の三角部分の位置を変更*/
        border-top-color:lemonchiffon;
      } 
        /* アイコンの作成 */
      .balloon-chat figure img {
        border-radius: 50%; /*丸の設定*/
        border: 2px solid #333300; /*アイコンの枠のカラーと太さはここで変更*/
        margin: 0;
      }
        /* アイコンの大きさ */
      .icon-img {
        width: 100px;
        height: 90px;
      }
        /* アイコンの名前の設定 */
      .icon-name {
        width: 100px; /* アイコンの大きさと合わせる */
        font-size: 12px;
        text-align: left;
      }
    </style>
    <meta name="robots" content="noindex,nofollow"><!--googleクローラ不要-->
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY>
  <div id='app' style='height: 100%'>
  <?php include "header_tag.php"  ?>
  <MAIN class='container common_main' style='height: 100%;padding-top:60px;padding-bottom:220px;position:relative'>
    <div class='fixed-top' style='z-index:100;top:48px;'>
      <div class="container" style='background-color:var(--footer_bg_color)'>
        <div class='row'>
          <div class='col-12 d-flex align-items-end mb-3 ps-5 pt-3'>
            <div style='width:30px;height:30px;padding:0;' class="me-3"><img class='img_icon' :src='review[0].logo'></div>
            <div><small>【 {{review[0].yagou}} 】</small></div>
          </div>
          <div class='col-12 d-flex align-items-end mb-3 ps-5'>
            <div style='width:100px;height:100px;padding:0;' class="me-3"><img class='img_icon' :src='review[0].pic'></div>
            <div style='margin-top:auto;margin-bottom:auto;'>
              <h3>【 {{review[0].shouhinNM}} 】</h3>
              <h3>感想・レビュー</h3>
            </div>
          </div>
          <hr>
        </div>
      </div>
    </div>
    <div class='row pb-3' style='padding-top:160px;'>
      <div class='col-12 '>
        <template v-for='(list,index) in review' :key='list.seq'>
          <div v-if="list.review!==null" class="balloon-chat left">
            <!-- 左の吹き出し -->
            <figure class="icon-img text-center pt-3" style=''>
              <img style='height:60px;' src="./img/hito.png" alt="代替えテキスト" >
              <figcaption class="icon-name text-center pt-2">{{list.投稿者}} 様</figcaption>
            </figure>
            <div class="chatting p-4 pt-3">
              <small>[{{list.insdate}}]</small>
              <!--レビューの星(0-5の間で0.5刻み)-->
              <div class='mb-1'>
                {{list.score}}
                <template v-for='(n,index) in 5' :key='index'>
                  <template v-if='list.score>=index+1'>
                    <i class="bi bi-star-fill" style='color:gold;'></i>
                  </template>
                  <template v-else-if='list.score>index'>
                    <i class="bi bi-star-half" style='color:gold;'></i>
                  </template>
                  <template v-else>
                    <i class="bi bi-star" style='color:gold;'></i>
                  </template>
                </template>
              </div>
              
              <p class="fs-2">{{list.review}}</p>
            </div>
          </div>
          <div v-else class='fs-3 p-5'>
            レビューは０件です。
          </div>
          <div v-if='list.reply!==null' class="balloon-chat right mb-3">
            <!-- 右の吹き出し -->
            <div class="chatting p-4 pt-1" style='background-color:lemonchiffon;'>
              <small>返信日[{{list.reply_date}}]</small>
              <p class="fs-2">{{list.reply}}</p>
            </div>
          </div>

        </template>
      </div>
    </div>
    <div id='end'><a class="nav-link" href='#end' id='move_end'></a></div>
  </MAIN>
  <!--<FOOTER class='container common_footer position-fixed bottom-0 start-50 translate-middle-x pt-3'style='height:180px;'>
    <div class='row' >
      <div class='col-12 col-md-8'>
        <div class='row'>
        <div class='col-10'>
          <textarea type='memo' class='form-control' rows='10' v-model='message'></textarea>
        </div>
        <div class='col-2 p-0 position-relative'>
          <button type='button' @click='send_msg()' class='btn btn-primary position-absolute top-50 translate-middle-y' style='width:100%;height:60px;'>送信</button>
        </div>
        </div>
      </div>
    </div>
  </FOOTER>
  <div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>-->
  </div><!--app-->

  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    createApp({//販売画面
      setup() {
        const review = ref(<?php echo json_encode($review,JSON_UNESCAPED_UNICODE);?>)

        onBeforeMount(()=>{
          console_log(`onBeforeMount`)
        })

        onMounted(()=>{
          console_log(`onMounted`)
        })
        return{
          review,
        }
      }
    }).mount('#app');
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