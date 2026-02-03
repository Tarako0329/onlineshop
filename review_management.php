<?php
	require "php_header_admin.php";
	if(empty($_GET["key"])){
		exit();
	}
	$user_hash = $_GET["key"] ;
  $_SESSION["user_id"] = rot13decrypt2($user_hash);


	$token = csrf_create();
?>
<!DOCTYPE html>
<html lang='ja'>
<head>
	<?php 
	//共通部分、bootstrap設定、フォントCND、ファビコン等
	include "head_admin.php" 
	?>
	<style>
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
        max-width:  calc(100% - 50px);
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
	<TITLE>レビュー管理</TITLE>
</head>
<BODY>
	<?php include "header_tag_admin.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll">
		<div class='row mb-5'>
			<div class='col-12'>
				<div class='text-center'> <h1>レビュー管理</h1></div>
			</div>
			<!--レビューの一覧と回答ボタン-->
			<div class='col-12'>
				<template v-for='(list,index) in reviews' :key='list.seq'>
					<div v-if="list.review!==null" class="balloon-chat left">
						<!-- 左の吹き出し -->
						<div class="col-12 text-start pt-2 ps-3 fs-3">{{list.Contributor}} 様より</div>
						<div class="col-12 text-start ps-3 fs-3">『{{list.shouhinNM}}』 の評価と感想</div>
						<div class="chatting p-4 pt-1">
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
					<template v-if='list.review!==null' >
						<div class="balloon-chat right mb-3">
            	<!-- 右の吹き出し -->
            	<div class="chatting p-4 pt-1">
            	  <small>{{list.reply_date}}</small>
            	  <textarea class="form-control fs-2" style='width:300px' rows=5 v-model='list.reply'></textarea>
								<!--返事ボタン-->
								<div class='text-end mt-3'>
									<!--<form method="post" action="review_management.php?key=<?php echo $user_hash;?>">
										<button type='submit' class='btn btn-primary'>{{list.btn_name}}</button>
										<input type="hidden" name="reply" :value="list.reply">
										<input type="hidden" name="seq" :value="list.SEQ">
									</form>-->
									<button type='button' class='btn btn-primary' @click='upd_reply(index)'>{{list.btn_name}}</button>
								</div>
            	</div>
						</div>
						<div>
							以降、個別でのやり取りが必要な場合はコチラから
						</div>
          </template>

					<div v-else class='fs-3 p-5'>
						
					</div>
					<hr>
				</template>
			</div>
			
		</div>

	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	</div><!--app-->
	<script src="script/admin_menu.js?<?php echo $time; ?>"></script>
	<script>
		admin_menu('review_management.php','','<?php echo $user_hash;?>').mount('#admin_menu');
	</script>
	<script>
		createApp({
			setup(){
				//const reviews = ref(<?php echo json_encode($reviews, JSON_UNESCAPED_UNICODE);?>)
				const reviews = ref([])
				let token = '<?php echo $token;?>'

				const upd_reply = (index) =>{
					//axios postでajax_upd_review_reply.phpに送信
					const form = new FormData();
					form.append(`reply`, reviews.value[index].reply)
					form.append(`seq`, reviews.value[index].SEQ)
					form.append(`csrf_token`, token)
					axios.post("ajax_upd_review_reply.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
					.then((response)=>{
						console_log(response.data)
						token = response.data.csrf_create
						if(response.data.status==="alert_success"){
							alert('返信を登録しました')
						}else{
							alert('送信失敗')
							console_log(response.data.MSG)
						}
					})
					.catch((error,response)=>{
						console_log(error)
						alert('送信失敗(catch error)')
					})
					.finally(()=>{
						// 現在のページをリロードします（キャッシュを利用する場合があります）
					})
				}

				const get_review = () =>{
					//axios postでajax_upd_review_reply.phpに送信
					const form = new FormData();
					form.append(`csrf_token`, token)
					axios.post("ajax_get_review.php",form, {headers: {'Content-Type': 'multipart/form-data'}})
					.then((response)=>{
						console_log(response.data)
						token = response.data.csrf_create
						reviews.value = response.data.reviews
						if(response.data.status==="alert_success"){
						}else{
							alert('レビュー取得失敗')
							console_log(response.data.MSG)
						}
					})
					.catch((error,response)=>{
						console_log(error)
						alert('レビュー取得失敗(catch error)')
					})
					.finally(()=>{
						// 現在のページをリロードします（キャッシュを利用する場合があります）
					})
				}

				onMounted(()=>{
					console_log("onMounted")
					get_review()
				})

				return{
					reviews,
					upd_reply,
				}
			}
		}).mount('#app');
	</script>
</BODY>
</html>