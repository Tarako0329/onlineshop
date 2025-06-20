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
		include "head_bs5.php" 
		?>
		<style>
			.btn{
				min-width: 30px;
			}
		</style>
		<meta name="robots" content="noindex,nofollow"><!--googleクローラ不要-->
		<TITLE><?php echo TITLE;?></TITLE>
</head>
<BODY>
	<div id='app' style='height: 100%'>
	<?php 
		include "header_tag_admin.php";
	?>
	<MAIN class='container common_main' style='height: 100%;padding-top:60px;padding-bottom:220px;'>
			<!-- 問合せ一覧画面。日時、名前、商品名（件名）、Q_and_A.phpで問い合わせ内容を確認するためのリンクボタンをリスト形式で表示 -->
			<div class='row pb-3 pt-3'>
				<div class='col-12 col-md-10'>
					<table class='table table-striped'>
						<thead>
							<tr>
								<th>最終更新日</th>
								<th>受付番号</th>
								<th>件名</th>
								<th>お客様名</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<template v-for='(list,index) in talk' :key='list.askNO'>
								<tr>
									<td>{{list.最終更新日}}</td>
									<td>{{list.askNO}}</td>
									<td>{{list.shouhinNM}}</td>
									<td>{{list.name}}</td>
									<td><a :href='`Q_and_A.php?askNO=${list.askNO_hash}&QA=<?php echo rot13encrypt2("A");?>&key=<?php echo $user_hash;?>`' class='btn btn-primary'>回答</a></td>
								</tr>
							</template>
						</tbody>
					</table>
				</div>
			</div>
			
	</MAIN>
	<div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>
	</div><!--app-->

	<script src="script/vue3.js?<?php echo $time; ?>"></script>
	<script>
		admin_menu('Q_and_A_mgr.php','','<?php echo $user_hash;?>').mount('#admin_menu');
		createApp({//販売画面
			setup() {
				const talk = ref([{askNO:0,shouhinNM:""}])
				const message = ref('')
				const loader = ref(false)

				const get_talk = () =>{
					axios.get('ajax_get_QAtalk_list.php')
					.then((response)=>{
						console_log(response.data)
						talk.value = response.data
					})
				}

				let token = '<?php echo $token;?>'


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