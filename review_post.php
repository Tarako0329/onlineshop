<?php
  require "php_header.php";
  $token = csrf_create();
  if(!empty($_GET["key"])){
    $orderNO = rot13decrypt2($_GET["key"]);
  }else{
    echo "不正アクセスです<br>";
    echo rot13encrypt2("05699240");
    exit();
  }
  
  $sql="SELECT 
    u.yagou
    ,h.uid
    ,h.orderNO
    ,h.name
    ,m.shouhinNM
    ,m.shouhinCD
    ,p.pic 
    ,IFNULL(r.review,'') as review
    ,IFNULL(r.score,2.5) as score
    ,IFNULL(r.NoName,'false') as NoName
    ,IF(r.review IS NOT NULL,'更新','投稿') as btn_name
  FROM juchuu_head h 
    inner join juchuu_meisai m 
    on h.orderNO = m.orderNO 
    inner join shouhinMS_online_pic p
    on h.uid = p.uid
    and m.shouhinCD = p.shouhinCD
    and p.sort=1
    inner join Users_online u
    on h.uid = u.uid
    left join review_online r
    on h.orderNO = r.orderNO
    and h.uid = r.shop_id
    and m.shouhinCD = r.shouhinCD
    where h.orderNO = :orderNO";
  $stmt = $pdo_h->prepare($sql);
  $stmt->bindValue("orderNO", $orderNO, PDO::PARAM_STR);
  $stmt->execute();
  $buylist = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang='ja'>
<head prefix="og: http://ogp.me/ns#">
    <?php 
    //共通部分、bootstrap設定、フォントCND、ファビコン等
    include "head_admin.php" 
    ?>
    <style>

    </style>
    <TITLE><?php echo TITLE;?>レビューポスト</TITLE>
</head>
<BODY >
  <div id='app' style='min-height: 100%' >
  <?php include "header_tag.php"  ?>
  <MAIN class='container common_main'>
    <div class='row'><div class='col-12'>
      <p class='fs-3 mb-3'>店舗名：{{buylist[0].yagou}} より</p>
      <h3><?php echo $buylist[0]["name"]." 様";?></h3>
      <h3>この度は商品をお買い上げいただき、ありがとうございます。</h3>
      <p class='mt-3 fs-4'>お届けした商品はいかがでしたでしょうか？</p>
      <p class='fs-4'>差し支えなければ、ご感想・レビューをお聞かせください。</p>
    </div></div>
    <!--<div class='row'>
      <hr>
      <div class='col-12'>
        <div class="form-check">
          <input type='radio' class='form-check-input' name='rev_type' value='shouhin' id='shouhin' v-model='review_type'>
          <label for='shouhin' class="form-check-label">個別に商品レビューを書く</label>
        </div>
        <div class="form-check">
          <input type='radio' class='form-check-input' name='rev_type' value='shop' id='shop' v-model='review_type'>
          <label for='shop' class="form-check-label">オーダー商品をまとめてレビューを書く</label>
        </div>
      </div>
    </div>-->
    <template v-for='(list,index) in buylist' :key='list.uid+list.shouhinCD'>
      <div v-show='review_type==="shouhin"' class='row mt-3'>
        <hr>
        <div class='col-12 d-flex' style='height:80px;'>
          <img :src=list.pic style='height:100%;'><img>
          <div style='margin-top: auto;margin-bottom: auto;'>
            <p class='fs-3 ps-3 pe-3'>商品名：{{list.shouhinNM}}</p>
          </div>
        </div>
        <!--レビュースコア(0-5の間で0.5刻み)のSELECTタグ-->
        <div class='col-12 col-md-10 mb-3 mt-3'>
          <label for='score'>評価</label>
          <select id='score' class='form-select' v-model='list.score'>
            <option value=5.0>★★★★★</option>
            <option value=4.5>★★★★☆</option>
            <option value=4.0>★★★★</option>
            <option value=3.5>★★★☆</option>
            <option value=3.0>★★★</option>
            <option value=2.5>★★☆</option>
            <option value=2.0>★★</option>
            <option value=1.5>★☆</option>
            <option value=1.0>★</option>
            <option value=0.5>☆</option>
            <option value=0.0>無評価</option>
          </select>
        </div>
        <div class='col-12 col-md-10 mb-3 mt-3'>
          <label for='review'>ご感想・レビュー</label>
          <textarea id='review' class='form-control' rows="10" v-model='list.review'></textarea>
        </div>
        <div class='col-8 d-md-none'></div>
        <div class='col-4 col-md-2' style='margin:auto;'>
          <div class="form-check mb-3 fs-3">
            <input type='checkbox' :id='`${index}_tokumei`' v-model='list.NoName' class='form-check-input'>
            <label :for='`${index}_tokumei`' class='form-check-label'>匿名で投稿</label>
          </div>
          <button type='button' class='btn btn-primary fs-3' style='width:90%;max-width:130px;height:40px;' @click='review_post_submit(index)'>{{list.btn_name}}</button>
        </div>
      </div>
    </template>    
    <hr>
    <div class='row'>
      <div class='col-12'>
        <p class='fs-4'>レビュー投稿は、ショップの改善や、他のお客様の参考になります。</p>
        <p class='fs-4'>ご協力よろしくお願いいたします。</p>
      </div>
    </div>
    
  </MAIN>
  <FOOTER class='container common_footer fixed-bottom'>
  </FOOTER>
  <!--<div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>-->
  <!-- Modal -->

  </div>
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script src="script/review_post.js?<?php echo $time; ?>"></script>
  <script>
    const BUYLIST = <?php echo json_encode($buylist,JSON_UNESCAPED_UNICODE);?>;
    const TOKEN = '<?php echo $token;?>';
    review_post(BUYLIST,TOKEN).mount('#app');
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