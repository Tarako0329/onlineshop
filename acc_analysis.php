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
		<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
		<div class='row' style='min-height:240px;'>
			<div class='col-xl-12' style='height:100%;display:flex;justify-content: center;'>
				<div style="position:relative;max-width:900px;width:90%;height:100%;">
					<canvas id="myChart"></canvas>
				</div>
			</div>
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
		acc_analysis('acc_analysis.php','<?php echo $token; ?>').mount('#app');
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