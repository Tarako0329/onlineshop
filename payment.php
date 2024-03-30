<?php
	require "php_header.php";
	$token = csrf_create();
	if(empty($_GET["key"])){
		echo "参照用のURLが異なります。";
		exit();
	}
	$user_hash = $_GET["key"] ;
	$_SESSION["user_id"] = rot13decrypt2($user_hash);

	$kingaku = ($_GET["val"]);
	$orderNO = ($_GET["no"]);

	//log_writer2("\$orderNO",$orderNO,"lv3");
	//log_writer2("\$kingaku",$kingaku,"lv3");

	try{
		$stripe = new \Stripe\StripeClient(S_KEY);
		//log_writer2("S_KEY",S_KEY,"lv3");
	
		$product = $stripe->products->create(
				['name' => $orderNO]
				,['stripe_account' => $_SESSION["stripe_connect_id"]]
		);
		//log_writer2("\$product",$product,"lv3");
		
		$price = $stripe->prices->create(
				[
						'currency' => 'jpy',
						//'custom_unit_amount' => ['enabled' => true],
						'unit_amount' => $kingaku,
						'product' => $product->id,
				]
				,['stripe_account' => $_SESSION["stripe_connect_id"]]
		);
		//log_writer2("\$price",$price,"lv3");
		
		$session = $stripe->checkout->sessions->create(
			[
				'payment_method_types' => ['card'],
				'line_items' => [
					[
					'price' => $price->id,
					'quantity' => 1,
					],
				],
				'payment_intent_data' => ['application_fee_amount' => 100],
				'mode' => 'payment',
				// ご自身のサイトURLを入力
				'success_url' => ROOT_URL."pay_success.php?key=".$user_hash."&orderNO=".$orderNO."&val=".$kingaku."csrf_token=".$token,	//支払ありがとうページ
				'cancel_url' => ROOT_URL."payment.php?key=".$user_hash."&val=".$kingaku."&no=".$orderNO,
			]
			,['stripe_account' => $_SESSION["stripe_connect_id"]]
		);
		//log_writer2("\$session",$session,"lv3");
	}catch(Exception $e){
		log_writer2("Exception \$e",$e,"lv0");
	}

?>
<!DOCTYPE html>
<html lang='ja'>
<head>
		<?php 
		//共通部分、bootstrap設定、フォントCND、ファビコン等
		include "head_admin.php" 
		?>
		<script src="https://js.stripe.com/v3/"></script>
		<style>

		</style>
		<TITLE><?php echo TITLE;?> 商品管理</TITLE>
</head>
<BODY>
	<?php //include "header_tag_admin.php"  ?>
	<div id='app'>
	<MAIN class='container common_main' data-bs-spy="scroll">
		<div class='row mb-5'>
			<div class='text-center m-3'><h1>御請求額：<?php echo number_format($kingaku);?> - 円</h1></div>
			<hr>
			<div v-if='credit==="use"' class='p-3'>
				<!--<a :href="`payment_stripe.php?key=<?php //echo $user_hash;?>`" type="button" class="btn btn-primary" id='checkout-button'>クレジットで決済する⇒</a>-->
				<a href="<?php echo $session->url;?>" type="button" class="btn btn-primary btn-lg" style='width:100%;'>クレジットで決済する⇒</a>
				<!--<button type="button" class="btn btn-primary" id='checkout-button' onclick="go_checkout()">クレジットで決済する⇒</button>-->
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