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
      .frame {
        position: relative; /* 位置を相対的に設定 */
        border: 0.5px solid black; /* 枠線 */
        padding: 10px; /* 内側の余白 */
      }

      .frame h4 {
        position: absolute; /* 位置を絶対的に設定 */
        top: -10px;
        left: 10;
        /*width: 100%;  枠線いっぱいに広げる */
        margin: 0; /* マージンを0に */
        padding:0 5px;
        background-color: var(--main_bg_color);
      }
    </style>
    <TITLE>SNS拡散計画</TITLE>
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
    <div class='row' style=''>
      <p>SNSに投稿する商品を選択してください（投稿用の画面が開きます）</p>
      <div class='col-md-8 col-12 overflow-y-scroll p-1 mb-1' :style='shouhin_table'>
        <table class='table table-sm mb-1'>
          <tbody>
            <template v-for='(list,index) in shouhinMS' :key='list.shouhinCD+list.uid'>
              <tr>
                <td class='ps-3 pt-2'>{{list.shouhinNM}}</td>
                <td :id="`${list.uid}-${list.shouhinCD}`" style='display:none;'>{{RTURL}}product.php?id={{list.uid}}-{{list.shouhinCD}}</td>
                <td style='width: 80px'>
                  <span v-if='list.status==="show"' style='color:blue'>販売中</span>
                  <span v-if='list.status==="soldout"' style='color:darkorange'>受付停止</span>
                  <span v-if='list.status==="stop"' style='color:red'>販売停止</span>
                <td style='width: 60px'><button type='button' style='min-width: 50px' class='btn btn-primary' @click='set_shouhinNM(list.shouhinNM)'>選択</button></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
    <div v-show='disp!=="none"'>
      <hr>
      <div class='row mb-3'>
        <div class='col-md-8 col-12 ps-3'>
          <span for='hinmei' >商品名</span>
          <h1>{{shouhinNM}}</h1>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <div class='row'>
            <div class='col-md-4 col-12 pt-2'>
              <label for='post_sns' class="form-label">投稿内容 (文字数 {{post_sns.text.length}})</label>
            </div>
            <div class='col-lg-8 col-12 d-inline-flex justify-content-end flex-wrap'>
              <!--<label for='sns_type' class='form-label'>AI に SNS を伝える</label>-->
              <select class='form-select' id='sns_type' style='width:110px;height:30px;' v-model='timing' >
                <option value="">通常広告として</option>
                <option value="新商品発売です！">新商品として</option>
                <option value="リニューアルしました！">リニューアル商品として</option>
              </select>
              <select class='form-select' id='sns_type' style='width:110px;height:30px;' v-model='sns_type' >
                <option value="SNS">SNS選択</option>
                <option value="X(twitter)">X(twitter) 向けに</option>
                <option value="FACEBOOK">FACEBOOK 向けに</option>
                <option value="instagram">instagram 向けに</option>
                <option value="公式Line">公式Line 向けに</option>
              </select>
              <button class='btn btn-sm btn-info ' style='min-width:110px;height:30px;' @click='get_AI_post()' id='gemini_btn'>
                <template v-if='loader2===false'><p>Google AIが提案</p></template>
                <template v-else><p><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Google AI 考え中...</p></template>
              </button>
            </div>
          </div>

          <textarea type='memo' class='form-control mt-1 ' id='post_sns' rows="10" v-model='post_sns.text' placeholder="AI は商品名・見出し・詳細をもとに文章を自動作成します"></textarea>
          <div class='row ps-3 mb-3'><small>X.com の場合、95文字程度を目安としてください。</small></div>
          <label for='hash_tag'>ハッシュタグ(半角カンマ区切り)</label>
          <input type='text' class='form-control ' v-model='post_sns.tag_disp' placeholder='例：#おいしい,#お土産,#ティータイム'>

          <div class='row mt-5'>
            <div class=col-12>
              <div class='frame'>
                <h4>ご自身のアカウントに投稿(各SNSに移動します)</h4>
                <p><small>FACEBOOK,instagram は、文章が自動反映されません。<button type='button' @click='copy_sns("post_sns")' class='btn btn-primary btn-sm p-0' style='height:20px;min-width:40px;'>copy</button>ボタンでコピペしてください</small></p>
                <!--LINE-->
                <a :href='`https://line.me/R/share?text=${post_sns.text}${post_sns.URL_line}`' target="_blank" rel="noopener noreferrer"><i class="bi bi-line line-green fs-1 p-2"></i></a>
                <!--FACEBOOK-->
                <a :href='`https://www.facebook.com/share.php?u=${post_sns.URL}fb`' target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook facebook-blue fs-1 p-2"></i></a>
                <!--instagram-->
                <a :href='`https://instagram.com`' target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram instagram-orange fs-1 p-2"></i></a>
                <!--TWITTER-->
                <a :href='`https://x.com/intent/tweet?text=${post_sns.text}&url=${post_sns.URL}X&via=${x_id}&related=${x_id}&hashtags=${tag_param}`' rel="nofollow noopener noreferrer" target="_blank">
                  <i class="bi bi-twitter-x twitter-black fs-1 p-2"></i>
                </a>
              </div>
            </div>
          </div>
          <div class='row mt-4'>
            <div class=col-12>
              <div class='frame'>
                <h4 class='mb-1'>ショップアカウントに一括投稿</h4>
                <div class='row'>
                  <div class=col-6>
                    <p><small>第三者として投稿できます。</small></p>
                    <button type='button' class='btn btn-primary btn-sm' @click='posting'>
                      <template v-if='loader2===false'><p>投稿</p></template>
                      <template v-else><p><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>投稿中...</p></template>
                    </button>
                  </div>
                  <div class=col-6>
                    <p><small>ショップアカウントを確認</small></p>  
                    <!--FACEBOOK-->
                    <!--<a :href='`https://www.facebook.com/`' target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook facebook-blue fs-1 p-3"></i></a>-->
                    <!--TWITTER-->
                    <a href='https://x.com/presentselect' rel="nofollow noopener noreferrer" target="_blank">
                      <i class="bi bi-twitter-x twitter-black fs-1"></i>
                    </a>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <hr>
      <div class='row mb-3'>
        <div class='col-md-8 col-12 ps-3'>
          <span for='hinmei' >商品名</span>
          <h1>{{shouhinNM}}</h1>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-8 col-12 text-end pe-3'>
          <p>税込価格：{{zeikomi.toLocaleString()}} ({{shouhizei.toLocaleString()}})</p>
        </div>
      </div>
      <div class='row mb-5'>
        <div class='col-md-8 col-12'>
          <div class='frame'>
            <h4>商品説明(見出し)</h4>
            <p>{{midasi}}</p>
          </div>
        </div>
      </div>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <div class='frame'>
            <h4>商品説明(詳細)</h4>
            <p>{{info}}</p>
          </div>
        </div>
      </div>
      <hr>
      <div class='row mb-3'>
        <div class='col-md-8 col-12'>
          <div class='row'>
          <template v-for='(list,index) in pic_list' :key='list.filename'>
            <div class='col-md-4 col-6' style='padding:10px;'>
              <div class='img-div' style='position:relative;'>
                <img :src="list.filename" class="d-block img-item-sm">
              </div>
            </div>
          </template>
          </div>
        </div>
      </div>
    </div>

  </MAIN>
  <FOOTER class='container-fluid common_footer'>
  </FOOTER>
  <!-- Modal SEO-->
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal_seo" style="display: none;" id="modalon"></button>
  <div class="modal fade" id="exampleModal_seo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          お気に入りの投稿文を選択してください。（後から編集できます）
        </div>
        <div class="modal-body fs-2">
          <div class="btn-group-vertical" role="group" aria-label="Vertical radio toggle button group">
            <template v-for='(list,index) in AI_answer.posts.texts'>
                <input class="btn-check" type="radio" name='gemini_seo' :id="`tag_${index}`" @click='set_sns(list)' >
                <label class="btn btn-outline-primary text-start mb-2" style='border-radius:2px;' :for="`tag_${index}`">
                  <p>{{list.text}}</p>
                  <span><template v-for='(tag) in list.tags'>{{tag}}</template></span>
                </label>
            </template>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id='mail_modal_close'>Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>
  </div><!--app-->
  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script src="script/shouhinMS_vue3.js?<?php echo $time; ?>"></script>
  <script>
    admin_menu('sales_via_SNS.php','','<?php echo $user_hash;?>').mount('#admin_menu');
    shouhinMS('sales_via_SNS.php','<?php echo $token; ?>','<?php echo $user_hash;?>').mount('#app');
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