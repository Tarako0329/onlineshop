<?php
	require "php_header.php";
	$token = csrf_create();
	if(empty($_GET["key"])){
		echo "参照用のURLが異なります。";
		exit();
	}
	$user_hash = $_GET["key"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);
	$_SESSION["kingaku"] = $_GET["val"];
?>
<!DOCTYPE html>
<html lang='ja'>
<head>
		<?php 
		//共通部分、bootstrap設定、フォントCND、ファビコン等
		include "head_admin.php" 
		?>
		<style>

		</style>
		<TITLE><?php echo TITLE;?> 商品管理</TITLE>
</head>
<BODY>
	<?php //include "header_tag_admin.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll">
		<div class='row mb-5'>
			<div class='text-center m-3'><h1>御請求額：<?php echo $_GET["val"];?> - 円</h1></div>
			<hr>
			<div v-if='credit==="use"'>
				<a href="payment_stripe.php?key=<?php echo $user_hash;?>" type="button" class="btn btn-primary">クレジットで決済する⇒</a>
				<hr>
			</div>

			<template v-for='(list,index) in pay_lists' :key='list.source'>
				<div class='col-12 text-center mb-5'>
					<div style='font-size:medium;'>【　{{list.payname}}　】</div>
					<div v-if='list.types==="QR"' class='p-3'><div class='img-div mx-auto' style="height:100px;width:100px;"><img :src="list.source" class='img-item-sm'></div></div>
					<div v-else  class='p-3' style='white-space: pre-wrap;'>{{list.source}}</div>
					<div>{{list.hosoku}}</div>
				</div>
				<hr>
			</template>
		</div>


	</MAIN>
	<FOOTER class='container-fluid common_footer'>
	</FOOTER>
	<div class="loader-wrap" v-show='loader'>
		<div class="loader">Loading...</div>
	</div>
	</div><!--app-->
	<script src="script/vue3.js?<?php echo $time; ?>"></script>
	<script src="script/settlement_vue3.js?<?php echo $time; ?>"></script>
	<script>
		admin_menu('settlement.php','','<?php echo $user_hash;?>').mount('#admin_menu');
		settlement('settlement.php','<?php echo $token; ?>','<?php echo $user_hash;?>').mount('#app');
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