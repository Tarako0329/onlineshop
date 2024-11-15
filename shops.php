<?php
	require "php_header.php";
	$token = csrf_create();
	$_SESSION["user_id"] = "%";
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
	<?php include "header_tag.php"  ?>
	<MAIN class='container common_main'>
		<div class='row pb-3 pt-3'>
			<template v-for='(list,index) in shoplist' :key='list.uid'>
				<div class='col-xl-6 col-md-6 col-12'><!--外枠-->
					<div class='container-fluid'>
						<div class='row'>
							<div class='col-12 d-flex original-gradient' style='height:80px;'><!--詳細-->
								<div style='height:80px;padding:2px;border:solid 2px #fff;'><img style='height:100%' :src="list.logo" ></div>
								<div class='ms-3 pt-3'>
									<h3 class="card-title mb-3">{{list.yagou}}</h3>
									<p class='m-0'>代表：{{list.shacho}}</p>
									<p class='m-0'>mail：{{list.mail}}</p>
								</div>
							</div><!--詳細-->
							<div class='col-12 p-3'>
								<div style='white-space: pre-wrap;' v-html='list.site_pr'></div>
							</div>
						</div>
						
					</div>
					<hr>
				</div><!--外枠-->
				
			</template>
		</div>

	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	<!--<div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>-->
	</div><!--app-->

	<script src="script/vue3.js?<?php echo $time; ?>"></script>
	<script>
		shops('shops.php','<?php echo $token; ?>').mount('#app');
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