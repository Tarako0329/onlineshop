<?php
  require "php_header.php";
  $token = csrf_create();
  //$_SESSION["user_id"] = "%";
  if(empty($_GET)){
    echo "見せないよ！er1";
    exit();
  }
  $_SESSION["askNO"] = $_GET["askNO"];  //暗号
  $_SESSION["sts"] = $_GET["QA"];        //暗号
  $sts = rot13decrypt2($_SESSION["sts"]);

  if($sts==="Q" || $sts==="CA"){
    $subject="返信が届きました";
    $body="返信内容";
  }else if($sts==="A" || $sts==="BQ"){
    $subject="回答が届きました";
    $body="回答内容";
  }else{
    echo "見せないよ！er2:".$sts;
    exit();
  }
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
        margin: 10px 20px;
        background: #ccffcc; /*吹き出しのカラーはここで変更*/
        text-align: left; /*テキストの位置はここで変更*/
        border-radius: 12px; /*吹き出しの丸み具合を変更*/

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
      } 
        /* アイコンの作成 */
      .balloon-chat figure img {
        border-radius: 50%; /*丸の設定*/
        border: 2px solid #333300; /*アイコンの枠のカラーと太さはここで変更*/
        margin: 0;
        object-fit: cover;
      }
        /* アイコンの大きさ */
      .icon-img {
        width: 60px;
        height: 60px;
        overflow: hidden;
      }
        /* アイコンの名前の設定 */
      .icon-name {
        width: 60px; /* アイコンの大きさと合わせる */
        font-size: 12px;
        text-align: center;
      }
    </style>
    <meta name="robots" content="noindex,nofollow"><!--googleクローラ不要-->
    <TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY>
  <div id='app' style='height: 100%'>
  <?php include "header_tag.php"  ?>
  <MAIN class='container common_main' style='height: 100%;padding-top:60px;padding-bottom:220px;'>
    
    <div class='row pb-3 pt-1 p-5'>
      <div class='col-12 col-md-8'>
        <h3>受付番号：{{talk[0].askNO}}</h3>
        <h3>件名：{{talk[0].shouhinNM}}</h3>
      </div>
    </div>
    <div class='row pb-3 pt-3'>
      <div class='col-12 col-md-8'>
        <template v-for='(list,index) in talk' :key='list.seq'>
          <div v-if='list.sts==="Q" || list.sts==="CA"' class="balloon-chat left">
            <!-- 左の吹き出し -->
            <figure class="icon-img"><img style='height:100%' src="./img/hito.png" alt="代替えテキスト" >
              <figcaption class="icon-name">{{list.name}} 様</figcaption>
            </figure>
            <div class="chatting p-4 pt-1">
              <small>{{list.insdate}}</small>
              <p class="chat-text">{{list.body}}</p>
            </div>
          </div>
          <div v-if='list.sts==="A" || list.sts==="BQ"' class="balloon-chat right">
            <!-- 右の吹き出し -->
            <figure class="icon-img"><img style='height:100%' :src="list.logo" alt="代替えテキスト" >
              <figcaption class="icon-name">{{list.yagou}}</figcaption>
            </figure>
            <div class="chatting p-4 pt-1">
              <small>{{list.insdate}}</small>
              <p class="chat-text">{{list.body}}</p>
            </div>
          </div>
        </template>
      </div>
    </div>
    <div id='end'><a class="nav-link" href='#end' id='move_end'></a></div>
  </MAIN>
  <FOOTER class='container common_footer position-fixed bottom-0 start-50 translate-middle-x pt-3'style='height:180px;'>
    <div class='row' >
      <div class='col-12 col-md-8'>
        <div class='row'>
        <div class='col-10'>
          <textarea type='memo' class='form-control' rows='8' v-model='message'></textarea>
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
	</div>
  </div><!--app-->

  <script src="script/vue3.js?<?php echo $time; ?>"></script>
  <script>
    createApp({//販売画面
      setup() {
        const talk = ref([{askNO:0,shouhinNM:""}])
        const message = ref('')
        const loader = ref(false)

        const get_talk = () =>{
          axios.get('ajax_get_QAtalk.php')
          .then((response)=>{
            talk.value = [{askNO:0,shouhinNM:""}]//初期化
            talk.value = response.data
            document.getElementById('move_end').click()
          })
        }

        let token = '<?php echo $token;?>'

        const send_msg = () =>{
          loader.value=true
          const form = new FormData();
          form.append(`mailto`, talk.value[0].customer)
          //form.append(`mailtoBCC`, talk.value[0].mail)
          //form.append(`lineid`, talk.value[0].line_id)
          form.append(`shop_id`, talk.value[0].shop_id)
          form.append(`qa_head`, talk.value[0].shouhinNM)
          form.append(`qa_name`, talk.value[0].name)
          form.append(`subject`, `【${talk.value[0].yagou}】<?php echo $subject;?>「${talk.value[0].shouhinNM}」`)
          form.append(`mailbody`, `※このメールは送信専用です。※\n<?php echo $body;?>\n\n${message.value}`)
          form.append(`qa_text`, message.value)
          form.append(`sts`, "session")
          form.append(`csrf_token`, token)
          //form.append(`hash`, hash)

          axios.post("ajax_sendmail_custmor.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
          .then((response)=>{
            console_log(response.data)
            loader.value = false
            token = response.data.csrf_create
            if(response.data.status==="alert-success"){
              message.value = ""
              alert('メールを送信しました')
              get_talk()
            }else{
              alert('送信失敗')
            }

          })
          .catch((error,response)=>{
            console_log(error)
            alert('送信失敗(catch error)')
            token = response.data.csrf_create
          })
          .finally(()=>{
            loader.value=false
          })
        }

        onBeforeMount(()=>{
          console_log(`onBeforeMount`)
          get_talk()
        })
        onMounted(()=>{
          console_log(`onMounted`)

          //get_talk()
        })
        return{
          talk,
          message,
          loader,
          send_msg,
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